<?php

use App\Services\EventCoordinationService;
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
        || $db->getDatabaseName() !== 'skillmatch_testing' || ($working['DB_DATABASE'] ?? '') === 'skillmatch_testing'
        || $db->getConfig('url') || $db->selectOne('select database() as name')->name !== 'skillmatch_testing') {
        throw new RuntimeException('Unsafe test target');
    }
    config(['session.driver' => 'array', 'mail.default' => 'array']);
    $directory = storage_path('app/private/coordination-review');
    File::ensureDirectoryExists($directory);
    app(Vite::class)->useHotFile($directory.'/no-hot-file');
    DB::beginTransaction();
    $fixture = new class
    {
        use A3ExecutionFixture;
    };
    $application = $fixture->reviewApplication();
    $owner = $application->event->organizer;
    app(SelectionService::class)->accept($application, $owner);
    app(EventCoordinationService::class)->update($application->event, $owner, [
        'revision' => 0, 'group_url' => 'https://example.com/synthetic-review-group',
        'pic_name' => 'PIC review sintetis', 'pic_contact' => 'review@example.com',
    ]);
    foreach ([['coordination-owner', '/organizer/events/'.$application->event_id, $owner],
        ['coordination-participant', '/volunteer/applications/'.$application->id, $application->volunteer],
        ['coordination-empty', '/volunteer/applications/'.$application->id, $application->volunteer]] as [$name, $path, $actor]) {
        if ($name === 'coordination-empty') {
            app(EventCoordinationService::class)->update($application->event, $owner, [
                'revision' => 1, 'group_url' => null, 'pic_name' => null, 'pic_contact' => null,
            ]);
        }
        Auth::forgetGuards();
        Auth::guard('web')->setUser($actor);
        $response = app(Illuminate\Contracts\Http\Kernel::class)->handle(Request::create($path, 'GET'));
        if ($response->getStatusCode() !== 200) {
            throw new RuntimeException('Render failed: '.$name);
        }
        $root = 'file:///'.str_replace('\\', '/', public_path());
        $html = preg_replace('#(?:https?://[^/"\s]+)?/(build/|fonts/)#', $root.'/$1', $response->getContent());
        File::put($directory.'/'.$name.'.html', $html);
        echo 'Rendered '.$name." (HTTP 200).\n";
    }
    DB::rollBack();
    echo "Synthetic fixtures rolled back; private artifacts are not browser end-to-end evidence.\n";
} catch (Throwable $e) {
    while (DB::transactionLevel() > 0) {
        DB::rollBack();
    }
    fwrite(STDERR, 'UI review failed: '.get_class($e).'; sensitive details suppressed.'.PHP_EOL);
    exit(1);
}
