<?php

use App\Services\AttendanceSessionService;
use App\Services\EventExecutionService;
use App\Services\SelectionService;
use Carbon\CarbonImmutable;
use Dotenv\Dotenv;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Vite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\Support\A3ExecutionFixture;

require __DIR__.'/../vendor/autoload.php';
if (! in_array('--env=testing', $argv, true)) {
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
    config(['session.driver' => 'array', 'mail.default' => 'array']);
    $directory = storage_path('app/private/session-review');
    File::ensureDirectoryExists($directory);
    app(Vite::class)->useHotFile($directory.'/no-hot-file');
    DB::beginTransaction();
    $fixture = new class
    {
        use A3ExecutionFixture;
    };
    $application = $fixture->reviewApplication();
    $event = $application->event;
    $snapshot = $application->snapshot_json;
    $snapshot['schedules'][] = ['start' => $event->starts_at->addDay()->toIso8601String(), 'end' => $event->starts_at->addDay()->addHours(3)->toIso8601String()];
    $application->forceFill(['snapshot_json' => $snapshot])->save();
    $event->forceFill(['ends_at' => $event->ends_at->addDay()])->save();
    app(SelectionService::class)->accept($application, $event->organizer);
    $sessions = $application->fresh()->attendance->sessions;
    CarbonImmutable::setTestNow($event->starts_at->addHour());
    app(AttendanceSessionService::class)->record($sessions[0], $event->organizer, ['status' => 'present', 'revision' => 0]);
    foreach ([['execution', '/organizer/events/'.$event->id.'/execution', $event->organizer],
        ['session-detail', '/volunteer/applications/'.$application->id, $application->volunteer],
        ['session-history', '/volunteer/history', $application->volunteer]] as [$name, $path, $actor]) {
        if ($name === 'session-history') {
            CarbonImmutable::setTestNow($event->ends_at->addMinute());
            app(AttendanceSessionService::class)->record($sessions[1], $event->organizer, ['status' => 'absent', 'revision' => 0]);
            app(EventExecutionService::class)->complete($event, $event->organizer);
        }
        Auth::forgetGuards();
        Auth::guard('web')->setUser($actor);
        $response = app(Illuminate\Contracts\Http\Kernel::class)->handle(Request::create($path, 'GET'));
        if ($response->getStatusCode() !== 200) {
            throw new RuntimeException('Render failed');
        }
        $root = 'file:///'.str_replace('\\', '/', public_path());
        $html = preg_replace('#(?:https?://[^/"\s]+)?/(build/|fonts/)#', $root.'/$1', $response->getContent());
        File::put($directory.'/'.$name.'.html', $html);
        echo 'Rendered '.$name." (HTTP 200).\n";
    }
    DB::rollBack();
    CarbonImmutable::setTestNow();
    echo "Synthetic fixtures rolled back; private render artifacts only.\n";
} catch (Throwable $exception) {
    while (DB::transactionLevel() > 0) {
        DB::rollBack();
    }
    CarbonImmutable::setTestNow();
    fwrite(STDERR, 'Session UI review failed: '.get_class($exception).'; details suppressed.'.PHP_EOL);
    exit(1);
}
