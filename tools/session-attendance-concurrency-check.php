<?php

use App\Models\AttendanceSession;
use App\Models\AuditLog;
use App\Models\Event;
use App\Services\AttendanceSessionService;
use App\Services\EventExecutionService;
use App\Services\SelectionService;
use Carbon\CarbonImmutable;
use Dotenv\Dotenv;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Process\Process;
use Tests\Support\A3ExecutionFixture;

require __DIR__.'/../vendor/autoload.php';
$args = getopt('', ['env:', 'worker:', 'session:', 'event:', 'go:']);
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
        CarbonImmutable::setTestNow($event->ends_at->addMinute());
        while (microtime(true) < (float) $args['go']) {
            usleep(10000);
        }
        try {
            if ($args['worker'] === 'complete') {
                app(EventExecutionService::class)->complete($event, $event->organizer);
            } else {
                $session = AttendanceSession::findOrFail((int) $args['session']);
                app(AttendanceSessionService::class)->record($session, $event->organizer, ['status' => 'present', 'revision' => 0]);
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
    foreach (['different_sessions', 'same_session', 'completion_race'] as $mode) {
        $application = DB::transaction(fn () => $fixture->reviewApplication());
        $start = $application->event->starts_at;
        $snapshot = $application->snapshot_json;
        $snapshot['schedules'] = [
            ['start' => $start->toIso8601String(), 'end' => $start->addHours(2)->toIso8601String()],
            ['start' => $start->addHours(3)->toIso8601String(), 'end' => $start->addHours(5)->toIso8601String()],
        ];
        $application->forceFill(['snapshot_json' => $snapshot])->save();
        app(SelectionService::class)->accept($application, $application->event->organizer);
        $parent = $application->fresh()->attendance;
        $sessions = $parent->sessions()->get();
        if ($mode === 'completion_race') {
            CarbonImmutable::setTestNow($application->event->ends_at->addMinute());
            app(AttendanceSessionService::class)->record($sessions[0], $application->event->organizer, ['status' => 'present', 'revision' => 0]);
            CarbonImmutable::setTestNow();
        }
        $go = (string) (microtime(true) + 2);
        $workers = [];
        for ($i = 0; $i < 2; $i++) {
            $workerMode = $mode === 'completion_race' && $i === 0 ? 'complete' : 'record';
            $session = $mode === 'same_session' ? $sessions[0] : $sessions[$i];
            $worker = new Process([PHP_BINARY, __FILE__, '--env=testing', '--worker='.$workerMode,
                '--event='.$application->event_id, '--session='.$session->id, '--go='.$go], base_path());
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
        $sessions = $parent->sessions()->get();
        if ($mode === 'same_session') {
            if ($outcomes !== ['ok', 'ok'] || $sessions[0]->revision !== 1 || $sessions[1]->status !== 'unrecorded'
                || $parent->fresh()->status !== 'unrecorded'
                || AuditLog::where('action', 'attendance.session_recorded')->where('subject_id', $sessions[0]->id)->count() !== 1) {
                throw new RuntimeException('Replay invariant failed');
            }
        } else {
            if ($sessions->where('status', 'present')->count() !== 2 || $parent->fresh()->status !== 'present') {
                throw new RuntimeException('Summary lost a concurrent update');
            }
            if ($mode === 'completion_race') {
                if (! in_array($outcomes[0], ['ok', 'blocked'], true) || $outcomes[1] !== 'ok') {
                    throw new RuntimeException('Unexpected completion outcome');
                }
                CarbonImmutable::setTestNow($application->event->ends_at->addMinute());
                app(EventExecutionService::class)->complete($application->event->fresh(), $application->event->organizer);
                CarbonImmutable::setTestNow();
                if ($application->event->fresh()->lifecycle_status !== 'completed') {
                    throw new RuntimeException('Completion did not converge');
                }
            } elseif ($outcomes !== ['ok', 'ok']) {
                throw new RuntimeException('Concurrent updates did not both commit');
            }
        }
        echo 'PASS '.$mode.": independent workers preserve session and summary invariants.\n";
    }
    echo "Synthetic fixtures retained only in skillmatch_testing; array mailer.\n";
} catch (Throwable $exception) {
    fwrite(STDERR, 'Session concurrency failed: '.get_class($exception).'; details suppressed.'.PHP_EOL);
    exit(1);
}
