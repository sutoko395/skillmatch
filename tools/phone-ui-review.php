<?php

use App\Models\User;
use App\Services\PackageService;
use Dotenv\Dotenv;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Vite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

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
    $directory = storage_path('app/private/phone-review');
    File::ensureDirectoryExists($directory);
    app(Vite::class)->useHotFile($directory.'/no-hot-file');
    DB::beginTransaction();
    $volunteer = User::factory()->create(['role' => 'volunteer']);
    $newOwner = User::factory()->create(['role' => 'organizer', 'organizer_status' => 'pending']);
    $owner = User::factory()->create(['role' => 'organizer', 'organizer_status' => 'active']);
    $owner->organizerProfile()->create(['organization_name' => 'Organisasi uji validasi', 'contact_person' => 'PIC uji',
        'address' => 'Alamat uji', 'city' => 'Malang', 'phone' => '081234567890', 'description' => 'Data uji sintetis']);
    $admin = User::factory()->create(['role' => 'admin']);
    app(PackageService::class)->save($admin, ['name' => 'Phone review '.uniqid(), 'price' => 0,
        'max_positions' => 2, 'max_applications' => 30, 'max_registration_days' => 7, 'is_active' => true]);
    foreach ([['phone-volunteer', '/volunteer/profile', $volunteer],
        ['phone-organizer-create', '/organizer/profile', $newOwner],
        ['phone-organizer-edit', '/organizer/profile', $owner],
        ['phone-packages', '/admin/packages', $admin],
        ['phone-package-form', '/admin/packages/create', $admin]] as [$name, $path, $actor]) {
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
