<?php

use App\Models\Application;
use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\AvailabilitySlot;
use App\Models\User;
use App\Models\VolunteerProfile;
use App\Models\VolunteerSkill;
use App\Services\ApplicationService;
use App\Services\AssessmentService;
use App\Services\EventPublicationService;
use App\Services\SelectionService;
use Dotenv\Dotenv;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Process\Process;
use Tests\Support\A3ExecutionFixture;

require __DIR__.'/../vendor/autoload.php';
$args = getopt('', ['env:', 'worker:', 'application:', 'go:']);
if (($args['env'] ?? '') !== 'testing') {
    exit("Use --env=testing; only a separate MySQL test database is supported.\n");
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
        throw new RuntimeException('Unsafe database target');
    }
    if (isset($args['worker'])) {
        $application = Application::findOrFail((int) $args['application']);
        while (microtime(true) < (float) $args['go']) {
            usleep(10000);
        }
        try {
            match ($args['worker']) {
                'accept' => app(SelectionService::class)->accept($application, $application->event->organizer),
                'submit' => app(ApplicationService::class)->submit($application, $application->volunteer),
                'cancel' => app(EventPublicationService::class)->cancel($application->event, $application->event->organizer, 'Concurrent cancellation fixture.'),
                'attempt' => app(AssessmentService::class)->start($application, $application->volunteer),
                default => throw new RuntimeException('Unknown operation'),
            };
            echo 'ok';
        } catch (ValidationException) {
            echo 'denied';
        }
        exit(0);
    }
    $fixture = new class
    {
        use A3ExecutionFixture;

        public function draft(Application $reference): Application
        {
            $volunteer = User::factory()->create(['role' => 'volunteer']);
            VolunteerProfile::create(['user_id' => $volunteer->id, 'phone' => '08000000000', 'birth_date' => '2000-01-01',
                'gender' => 'male', 'address' => 'Synthetic fixture', 'bio' => 'Synthetic fixture', 'city_id' => $reference->event->city_id]);
            VolunteerSkill::create(['user_id' => $volunteer->id, 'skill_id' => $reference->position->positionSkills->first()->skill_id, 'level' => 'expert']);
            AvailabilitySlot::create(['user_id' => $volunteer->id, 'starts_at' => $reference->event->starts_at, 'ends_at' => $reference->event->ends_at]);

            return app(ApplicationService::class)->storeDraft($volunteer, $reference->position);
        }
    };
    $config = $db->getConfig();
    $env = ['APP_ENV' => 'testing', 'DB_CONNECTION' => 'mysql', 'DB_DATABASE' => 'skillmatch_testing', 'DB_URL' => '',
        'DB_HOST' => $config['host'], 'DB_PORT' => (string) $config['port'], 'DB_USERNAME' => $config['username'], 'DB_PASSWORD' => (string) $config['password']];
    $race = function (array $jobs) use ($env): array {
        $go = (string) (microtime(true) + 2);
        $processes = [];
        foreach ($jobs as [$operation, $application]) {
            $process = new Process([PHP_BINARY, __FILE__, '--env=testing', '--worker='.$operation,
                '--application='.$application->id, '--go='.$go], base_path(), $env);
            $process->setTimeout(60);
            $process->start();
            $processes[] = $process;
        }
        $results = [];
        foreach ($processes as $process) {
            $process->wait();
            $result = trim($process->getOutput());
            if (! $process->isSuccessful() || ! in_array($result, ['ok', 'denied'], true)) {
                throw new RuntimeException('Worker failed; private details suppressed.');
            }
            $results[] = $result;
        }

        return $results;
    };
    $oneSuccess = function (array $results): void {
        sort($results);
        if ($results !== ['denied', 'ok']) {
            throw new RuntimeException('Expected exactly one success.');
        }
    };

    [$a, $b] = DB::transaction(function () use ($fixture) {
        $a = $fixture->reviewApplication();

        return [$a, $fixture->secondCandidate($a)];
    });
    $oneSuccess($race([['accept', $a], ['accept', $b]]));
    if (Application::where('event_position_id', $a->event_position_id)->where('status', 'accepted')->count() !== 1
        || Attendance::whereIn('application_id', [$a->id, $b->id])->count() !== 1) {
        throw new RuntimeException('Quota or attendance invariant failed');
    }
    echo "PASS quota: two candidates, one slot, one acceptance/attendance.\n";

    [$a, $b] = DB::transaction(function () use ($fixture) {
        $a = $fixture->reviewApplication();
        $b = $fixture->reviewApplication();
        $b->forceFill(['volunteer_id' => $a->volunteer_id])->save();

        return [$a, $b];
    });
    $oneSuccess($race([['accept', $a], ['accept', $b]]));
    echo "PASS conflict: same volunteer, overlapping events, one acceptance.\n";

    [$a, $b] = DB::transaction(function () use ($fixture) {
        $reference = $fixture->reviewApplication();

        return [$fixture->draft($reference), $fixture->draft($reference)];
    });
    $oneSuccess($race([['submit', $a], ['submit', $b]]));
    if ($a->event->entitlement->submitted_applications !== 1
        || Application::whereIn('id', [$a->id, $b->id])->whereNotNull('submitted_at')->count() !== 1) {
        throw new RuntimeException('Submission counter invariant failed');
    }
    echo "PASS submit: two submissions at package limit one, one counter increment.\n";

    $a = DB::transaction(fn () => $fixture->reviewApplication());
    $results = $race([['accept', $a], ['accept', $a]]);
    if ($results !== ['ok', 'ok'] || Attendance::where('application_id', $a->id)->count() !== 1
        || AuditLog::where('subject_id', $a->id)->where('action', 'application.accepted')->count() !== 1) {
        throw new RuntimeException('Replay invariant failed');
    }
    echo "PASS replay: simultaneous duplicate accept, one attendance and audit.\n";

    foreach (['accept', 'submit', 'attempt'] as $operation) {
        $a = DB::transaction(function () use ($fixture, $operation) {
            $a = $fixture->reviewApplication();
            if ($operation === 'submit') {
                return $fixture->draft($a);
            }
            if ($operation === 'attempt') {
                $a->assessmentAttempt->forceFill(['status' => 'in_progress'])->save();
                $a->forceFill(['status' => 'assessment_pending'])->save();
            }

            return $a;
        });
        $race([[$operation, $a], ['cancel', $a]]);
        if ($a->fresh()->status !== 'cancelled' || $a->event->fresh()->lifecycle_status !== 'cancelled'
            || $a->fresh()->assessmentAttempt?->status === 'in_progress') {
            throw new RuntimeException('Cancellation invariant failed');
        }
        echo "PASS cancellation versus {$operation}: terminal application/attempt cannot revive.\n";
    }
    echo "Synthetic fixtures retained only in skillmatch_testing.\n";
} catch (Throwable $e) {
    echo 'Concurrency check failed: '.get_class($e).'; private details suppressed.'.PHP_EOL;
    exit(1);
}
