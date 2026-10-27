<?php

use App\Models\Application;
use App\Models\AuditLog;
use App\Models\MatchResult;
use App\Models\ScreeningResult;
use App\Models\User;
use App\Services\MatchingEvaluationService;
use App\Services\ScreeningService;
use Dotenv\Dotenv;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use Tests\Support\A2Fixture;
use Tests\Support\MatchingFixture;

require __DIR__.'/../vendor/autoload.php';
$args = getopt('', ['env:', 'worker:', 'application:', 'go:']);
if (($args['env'] ?? null) !== 'testing') {
    echo "Use --env=testing; this tool only supports a separate test database.\n";
    exit(1);
}
putenv('APP_ENV=testing');
$_ENV['APP_ENV'] = $_SERVER['APP_ENV'] = 'testing';
$app = require __DIR__.'/../bootstrap/app.php';
$app->loadEnvironmentFrom('.env.testing');
$app->make(Kernel::class)->bootstrap();

try {
    $db = DB::connection();
    $working = Dotenv::parse(file_get_contents(base_path('.env')));
    if (! app()->environment('testing') || app()->configurationIsCached()
        || $db->getDriverName() !== 'mysql' || $db->getDatabaseName() !== 'skillmatch_testing'
        || ($working['DB_DATABASE'] ?? '') === 'skillmatch_testing' || $db->getConfig('url')
        || $db->selectOne('select database() as name')->name !== 'skillmatch_testing') {
        throw new RuntimeException('Unsafe test target');
    }
    if (isset($args['worker'])) {
        while (microtime(true) < (float) $args['go']) {
            usleep(10000);
        }
        $application = Application::findOrFail((int) $args['application']);
        if ($args['worker'] === 'screening') {
            app(ScreeningService::class)->evaluate($application);
        } else {
            app(MatchingEvaluationService::class)->evaluate($application);
        }
        echo 'ok';
        exit(0);
    }

    $fixture = new class
    {
        use A2Fixture, MatchingFixture;

        public function prepare(bool $screened): Application
        {
            [$owner, $event, $position] = $this->fixture();
            $snapshot = $this->matchingSnapshot();
            $snapshot['city_id'] = $event->city_id;
            $snapshot['profile']['city_id'] = $event->city_id;
            $volunteer = User::factory()->create(['role' => 'volunteer']);
            $application = Application::create([
                'volunteer_id' => $volunteer->id, 'event_id' => $event->id, 'event_position_id' => $position->id,
                'status' => 'submitted', 'submitted_at' => now(), 'revision' => $screened ? 2 : 1,
                'snapshot_json' => $snapshot,
            ]);
            if ($screened) {
                ScreeningResult::create(['application_id' => $application->id, 'passed' => true,
                    'checks_json' => [], 'rule_version' => 'match-v1.4', 'evaluated_at' => now()]);
            }

            return $application;
        }
    };
    $cfg = $db->getConfig();
    $env = ['APP_ENV' => 'testing', 'DB_CONNECTION' => 'mysql', 'DB_DATABASE' => 'skillmatch_testing', 'DB_URL' => '',
        'DB_HOST' => $cfg['host'], 'DB_PORT' => (string) $cfg['port'], 'DB_USERNAME' => $cfg['username'], 'DB_PASSWORD' => (string) $cfg['password']];
    foreach (['matching', 'screening'] as $mode) {
        $application = $fixture->prepare($mode === 'matching');
        $go = (string) (microtime(true) + 2);
        $workers = [];
        for ($i = 0; $i < 2; $i++) {
            $worker = new Process([PHP_BINARY, __FILE__, '--env=testing', '--worker='.$mode,
                '--application='.$application->id, '--go='.$go], base_path(), $env);
            $worker->setTimeout(30);
            $worker->start();
            $workers[] = $worker;
        }
        foreach ($workers as $worker) {
            $worker->wait();
            if (! $worker->isSuccessful() || trim($worker->getOutput()) !== 'ok') {
                throw new RuntimeException('Worker failed; output suppressed');
            }
        }
        if (MatchResult::where('application_id', $application->id)->count() !== 1
            || ScreeningResult::where('application_id', $application->id)->count() !== 1
            || AuditLog::where('action', 'application.matched')->where('subject_id', $application->id)->count() !== 1
            || $application->fresh()->status !== 'assessment_pending'
            || $application->fresh()->revision !== 3
            || abs($application->fresh()->matchResult->total_score - 80) > 1e-10) {
            throw new RuntimeException('Duplicate or inconsistent matching state');
        }
        if ($mode === 'screening'
            && AuditLog::where('action', 'application.screened')->where('subject_id', $application->id)->count() !== 1) {
            throw new RuntimeException('Duplicate screening audit');
        }
        echo "PASS: two concurrent {$mode} requests produced one match score and one matching audit.\n";
    }
    echo "Synthetic fixtures retained only in skillmatch_testing.\n";
} catch (Throwable $e) {
    echo 'Concurrency check failed: '.get_class($e).'; details suppressed.'.PHP_EOL;
    exit(1);
}
