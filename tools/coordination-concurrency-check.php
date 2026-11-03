<?php

use App\Models\Application;
use App\Models\AuditLog;
use App\Models\Event;
use App\Models\EventCoordination;
use App\Models\NotificationOutbox;
use App\Services\EventCoordinationService;
use App\Services\SelectionService;
use Dotenv\Dotenv;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Process\Process;
use Tests\Support\A3ExecutionFixture;

require __DIR__.'/../vendor/autoload.php';
$args = getopt('', ['env:', 'worker:', 'variant:', 'event:', 'go:']);
if (($args['env'] ?? '') !== 'testing') {
    exit("Use --env=testing.\n");
}
putenv('APP_ENV=testing');
$_ENV['APP_ENV'] = $_SERVER['APP_ENV'] = 'testing';
$app = require __DIR__.'/../bootstrap/app.php';
$app->loadEnvironmentFrom('.env.testing');
$app->make(Kernel::class)->bootstrap();
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
        $event = Event::findOrFail((int) $args['event']);
        while (microtime(true) < (float) $args['go']) {
            usleep(10000);
        }
        try {
            if ($args['worker'] === 'withdraw') {
                $application = Application::where('event_id', $event->id)->where('status', 'accepted')->firstOrFail();
                app(SelectionService::class)->withdraw($application, $event->organizer, 'Synthetic withdrawal race');
            } else {
                app(EventCoordinationService::class)->update($event, $event->organizer, [
                    'revision' => 0, 'group_url' => 'https://example.com/concurrency-'.$args['variant'],
                    'pic_name' => null, 'pic_contact' => null,
                ]);
            }
            echo 'ok';
        } catch (ValidationException) {
            echo 'blocked';
        }
        exit(0);
    }
    $fixture = new class
    {
        use A3ExecutionFixture;
    };
    foreach (['same_update', 'stale_updates', 'withdrawal_race'] as $mode) {
        $application = DB::transaction(fn () => $fixture->reviewApplication());
        app(SelectionService::class)->accept($application, $application->event->organizer);
        $go = (string) (microtime(true) + 2);
        $workers = [];
        for ($i = 0; $i < 2; $i++) {
            $workerMode = $mode === 'withdrawal_race' && $i === 1 ? 'withdraw' : 'update';
            $variant = $mode === 'stale_updates' ? (string) $i : 'same';
            $worker = new Process([PHP_BINARY, __FILE__, '--env=testing', '--worker='.$workerMode,
                '--event='.$application->event_id, '--variant='.$variant, '--go='.$go], base_path());
            $worker->setTimeout(30);
            $worker->start();
            $workers[] = $worker;
        }
        $outcomes = [];
        foreach ($workers as $worker) {
            $worker->wait();
            if (! $worker->isSuccessful()) {
                throw new RuntimeException('Worker failed');
            }
            $outcomes[] = trim($worker->getOutput());
        }
        $row = EventCoordination::where('event_id', $application->event_id)->sole();
        $audits = AuditLog::where('action', 'event.coordination_updated')->where('subject_id', $application->event_id)->count();
        $notifications = NotificationOutbox::where('type', 'event.coordination_updated')->where('application_id', $application->id)->count();
        if ($row->revision !== 1 || $audits !== 1) {
            throw new RuntimeException('Coordination revision/audit invariant failed');
        }
        if ($mode === 'stale_updates') {
            sort($outcomes);
            if ($outcomes !== ['blocked', 'ok'] || $notifications !== 1) {
                throw new RuntimeException('Stale concurrent update was not rejected');
            }
        } elseif ($mode === 'same_update') {
            if ($outcomes !== ['ok', 'ok'] || $notifications !== 1) {
                throw new RuntimeException('Replay produced duplicate effects');
            }
        } elseif ($outcomes !== ['ok', 'ok'] || $application->fresh()->status !== 'withdrawn'
            || app(EventCoordinationService::class)->forParticipant($application, $application->volunteer) !== null
            || $notifications > 1) {
            throw new RuntimeException('Withdrawal failed to revoke access');
        }
        echo 'PASS '.$mode.': independent workers preserve privacy, revisions and deduplication.'.PHP_EOL;
    }
    echo 'Synthetic fixtures retained only in skillmatch_testing; array mailer.'.PHP_EOL;
} catch (Throwable $exception) {
    fwrite(STDERR, 'Coordination concurrency failed: '.get_class($exception).'; line='.$exception->getLine().'; details suppressed.'.PHP_EOL);
    exit(1);
}
