<?php

use App\Services\SelectionService;
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
        || $db->getDatabaseName() !== 'skillmatch_testing' || ($working['DB_DATABASE'] ?? '') === 'skillmatch_testing' || $db->getConfig('url')) {
        throw new RuntimeException('Unsafe test target');
    }
    config(['session.driver' => 'array']);
    app(Vite::class)->useHotFile(storage_path('app/private/a3-review/no-hot-file'));
    $fixture = new class
    {
        use A3ExecutionFixture;
    };
    $a = DB::transaction(fn () => $fixture->reviewApplication());
    $owner = $a->event->organizer;
    app(SelectionService::class)->accept($a, $owner);
    $a->event->forceFill(['lifecycle_status' => 'completed', 'completed_at' => now()])->save();
    $a->attendance->forceFill(['status' => 'present', 'recorded_at' => now(), 'recorded_by' => $owner->id])->save();
    $b = DB::transaction(fn () => $fixture->reviewApplication());
    $directory = storage_path('app/private/a3-review');
    File::ensureDirectoryExists($directory);
    foreach ([['execution', '/organizer/events/'.$a->event_id.'/execution', $owner],
        ['history', '/volunteer/history', $a->volunteer], ['selection', '/organizer/applications/'.$b->id, $b->event->organizer]] as [$name, $path, $actor]) {
        Auth::forgetGuards();
        Auth::guard('web')->setUser($actor);
        $request = Request::create($path, 'GET');
        $response = app(Illuminate\Contracts\Http\Kernel::class)->handle($request);
        if ($response->getStatusCode() !== 200) {
            throw new RuntimeException('Render failed');
        }
        $html = $response->getContent();
        $root = 'file:///'.str_replace('\\', '/', public_path());
        $html = preg_replace('#(?:https?://[^/"\s]+)?/(build/|fonts/)#', $root.'/$1', $html);
        File::put($directory.'/'.$name.'.html', $html);
        echo 'Rendered '.$name." (HTTP 200); private synthetic review artifact.\n";
    }
} catch (Throwable $e) {
    echo 'UI export failed: '.get_class($e).'; private details suppressed.'.PHP_EOL;
    exit(1);
}
