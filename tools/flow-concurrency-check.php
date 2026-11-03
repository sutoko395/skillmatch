<?php

use App\Models\AuditLog;
use App\Models\Event;
use App\Models\NotificationOutbox;
use App\Services\NotificationService;
use App\Services\RecruitmentScheduleService;
use Dotenv\Dotenv;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use Tests\Support\ParticipantAssessmentFixture;

require __DIR__.'/../vendor/autoload.php';
$args = getopt('', ['env:', 'worker:', 'event:', 'go:']);
if (($args['env'] ?? '') !== 'testing') {
    exit("Use --env=testing.\n");
}
putenv('APP_ENV=testing');
$_ENV['APP_ENV'] = $_SERVER['APP_ENV'] = 'testing';
$app = require __DIR__.'/../bootstrap/app.php';
$app->loadEnvironmentFrom('.env.testing');
$app->make(Kernel::class)->bootstrap();
$stage = 'guard';
try {
    $db = DB::connection();
    $working = Dotenv::parse(file_get_contents(base_path('.env')));
    if (! app()->environment('testing') || app()->configurationIsCached() || $db->getDriverName() !== 'mysql'
        || $db->getDatabaseName() !== 'skillmatch_testing' || ($working['DB_DATABASE'] ?? '') === 'skillmatch_testing'
        || $db->getConfig('url') || $db->selectOne('select database() as name')->name !== 'skillmatch_testing') {
        throw new RuntimeException('Unsafe target');
    }
    config(['mail.default' => 'array']);
    if (isset($args['worker'])) {
        $stage = 'worker-'.$args['worker'];
        $event = Event::findOrFail((int) $args['event']);
        while (microtime(true) < (float) $args['go']) {
            usleep(10000);
        }
        if ($args['worker'] === 'notification') {
            DB::transaction(fn () => app(NotificationService::class)->enqueue('event.test:'.$event->id.':1:'.$event->organizer_id,
                $event->organizer, ['type' => 'event.test', 'event_id' => $event->id]), 3);
        } else {
            app(RecruitmentScheduleService::class)->repair($event, $event->organizer, [
                'assessment_deadline' => $event->starts_at->setTimezone('Asia/Jakarta')->subHours(7)->format('Y-m-d\TH:i'),
                'decision_deadline' => $event->starts_at->setTimezone('Asia/Jakarta')->subHours(3)->format('Y-m-d\TH:i'),
                'confirmed' => 1, 'reason' => 'Synthetic concurrent legacy repair',
            ]);
        }
        exit(0);
    }
    $stage = 'fixture';
    $fixture = new class
    {
        use ParticipantAssessmentFixture;
    };
    $application = $fixture->participantApplication();
    $snapshot = $application->snapshot_json;
    $event = $application->event;
    $event->forceFill(['assessment_deadline' => null, 'decision_deadline' => null])->save();
    foreach (['notification', 'repair'] as $mode) {
        $stage = 'concurrent-'.$mode;
        $go = (string) (microtime(true) + 2);
        $workers = [];
        for ($i = 0; $i < 2; $i++) {
            $worker = new Process([PHP_BINARY, __FILE__, '--env=testing', '--worker='.$mode, '--event='.$event->id, '--go='.$go], base_path());
            $worker->setTimeout(30);
            $worker->start();
            $workers[] = $worker;
        }
        foreach ($workers as $worker) {
            $worker->wait();
            if (! $worker->isSuccessful()) {
                if (preg_match('/Concurrency verification failed: [a-zA-Z\\\\]+; stage=[a-z-]+; line=[0-9]+/', $worker->getErrorOutput(), $safe)) {
                    fwrite(STDERR, $safe[0].PHP_EOL);
                }
                throw new RuntimeException('Concurrent worker failed');
            }
        }
    }
    if (NotificationOutbox::where('dedupe_key', 'event.test:'.$event->id.':1:'.$event->organizer_id)->count() !== 1
        || AuditLog::where('subject_id', $event->id)->where('action', 'event.recruitment_repaired')->count() !== 1
        || $application->fresh()->snapshot_json != $snapshot) {
        throw new RuntimeException('Concurrency invariant failed');
    }
    echo "PASS: two connections create one internal notification and one legacy repair audit; snapshot unchanged.\n";
    echo "Array mailer; synthetic fixtures retained only in skillmatch_testing.\n";
} catch (Throwable $e) {
    fwrite(STDERR, 'Concurrency verification failed: '.get_class($e).'; stage='.$stage.'; line='.$e->getLine().'; sensitive details suppressed.'.PHP_EOL);
    exit(1);
}
