<?php

use App\Models\Application;
use App\Models\AssessmentAttempt;
use App\Models\AuditLog;
use App\Services\AssessmentService;
use Dotenv\Dotenv;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use Tests\Support\ParticipantAssessmentFixture;

require __DIR__.'/../vendor/autoload.php';
$args = getopt('', ['env:', 'worker:', 'application:', 'go:']);
if (($args['env'] ?? null) !== 'testing') {
    exit("Use --env=testing on a separate test database.\n");
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
        $service = app(AssessmentService::class);
        if ($args['worker'] === 'start') {
            $service->start($application, $application->volunteer);
        } else {
            $service->save($application->assessmentAttempt, $application->volunteer, ['revision' => 1, 'answers' => []], true);
        }
        echo 'ok';
        exit(0);
    }
    $fixture = new class
    {
        use ParticipantAssessmentFixture;
    };
    $application = $fixture->participantApplication();
    $cfg = $db->getConfig();
    $env = ['APP_ENV' => 'testing', 'DB_CONNECTION' => 'mysql', 'DB_DATABASE' => 'skillmatch_testing', 'DB_URL' => '',
        'DB_HOST' => $cfg['host'], 'DB_PORT' => (string) $cfg['port'], 'DB_USERNAME' => $cfg['username'], 'DB_PASSWORD' => (string) $cfg['password']];
    foreach (['start', 'submit'] as $mode) {
        if ($mode === 'submit') {
            $attempt = $application->fresh()->assessmentAttempt;
            $question = $attempt->assessment->questions()->with('options')->first();
            app(AssessmentService::class)->save($attempt, $application->volunteer, ['revision' => 0,
                'answers' => [$question->id => $question->options->firstWhere('label', 'B')->id]]);
        }
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
        if (AssessmentAttempt::where('application_id', $application->id)->count() !== 1) {
            throw new RuntimeException('Duplicate attempts');
        }
    }
    $attempt = $application->fresh()->assessmentAttempt;
    if ($attempt->score !== 50.0 || $attempt->status !== 'submitted'
        || $application->fresh()->status !== 'under_review' || $application->fresh()->revision !== 4
        || AuditLog::where('action', 'assessment.started')->where('subject_id', $attempt->id)->count() !== 1
        || AuditLog::where('action', 'assessment.finalized')->where('subject_id', $attempt->id)->count() !== 1) {
        throw new RuntimeException('Inconsistent score, status, revision or audit');
    }
    echo "PASS: concurrent start and submit preserve one attempt, one score and one transition.\n";
    echo "Synthetic fixtures retained only in skillmatch_testing.\n";
} catch (Throwable) {
    fwrite(STDERR, "Assessment concurrency check failed; details suppressed.\n");
    exit(1);
}
