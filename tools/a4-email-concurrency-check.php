<?php

use App\Models\Application;
use App\Models\NotificationOutbox;
use App\Services\SelectionService;
use App\Services\VolunteerEmailDelivery;
use App\Services\VolunteerEmailService;
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
    // This executable must never contact SMTP, including in its independent worker processes.
    config(['mail.default' => 'array', 'app.url' => 'https://skillmatch.example']);
    if (isset($args['worker'])) {
        while (microtime(true) < (float) $args['go']) {
            usleep(10000);
        }
        $application = Application::findOrFail((int) $args['application']);
        if ($args['worker'] === 'enqueue') {
            app(VolunteerEmailService::class)->enqueue($application, VolunteerEmailService::AVAILABLE);
            echo 'queued';
        } else {
            $row = NotificationOutbox::where('application_id', $application->id)->sole();
            echo app(VolunteerEmailDelivery::class)->deliver($row->id);
        }
        exit(0);
    }
    $fixture = new class
    {
        use ParticipantAssessmentFixture;
    };
    $application = $fixture->participantApplication();
    $cfg = $db->getConfig();
    $env = ['APP_ENV' => 'testing', 'DB_CONNECTION' => 'mysql', 'DB_DATABASE' => 'skillmatch_testing', 'DB_URL' => '',
        'DB_HOST' => $cfg['host'], 'DB_PORT' => (string) $cfg['port'], 'DB_USERNAME' => $cfg['username'], 'DB_PASSWORD' => (string) $cfg['password'],
        'MAIL_MAILER' => 'array'];
    foreach (['enqueue', 'deliver'] as $mode) {
        $go = (string) (microtime(true) + 2);
        $workers = [];
        for ($i = 0; $i < 2; $i++) {
            $worker = new Process([PHP_BINARY, __FILE__, '--env=testing', '--worker='.$mode,
                '--application='.$application->id, '--go='.$go], base_path(), $env);
            $worker->setTimeout(30);
            $worker->start();
            $workers[] = $worker;
        }
        $outcomes = [];
        foreach ($workers as $worker) {
            $worker->wait();
            if (! $worker->isSuccessful()) {
                throw new RuntimeException('Worker failed; output suppressed');
            }
            $outcomes[] = trim($worker->getOutput());
        }
        sort($outcomes);
        if ($outcomes !== ($mode === 'enqueue' ? ['queued', 'queued'] : ['ignored', 'sent'])) {
            throw new RuntimeException('Unexpected concurrent result');
        }
        if (NotificationOutbox::where('application_id', $application->id)->count() !== 1) {
            throw new RuntimeException('Duplicate outbox entries');
        }
    }
    $row = NotificationOutbox::where('application_id', $application->id)->sole();
    if ($row->status !== 'sent' || $row->attempts !== 1 || ! $row->delivered_at) {
        throw new RuntimeException('Inconsistent delivery state');
    }
    // Retain a terminal synthetic fixture so future reminder tests do not pick it up.
    app(SelectionService::class)->withdraw($application, $application->volunteer);
    echo "PASS: concurrent enqueue creates one outbox entry; two workers produce one send and one ignored claim.\n";
    echo "Array mailer only; synthetic fixture retained in skillmatch_testing.\n";
} catch (Throwable) {
    fwrite(STDERR, "Email concurrency check failed; sensitive details suppressed.\n");
    exit(1);
}
