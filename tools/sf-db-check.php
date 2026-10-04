<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

// Read-only environment check. Never print credentials or connection exceptions.
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
$connection = DB::connection();
if ($app->environment('testing') && ($app->configurationIsCached() || $connection->getDriverName() !== 'mysql' || $connection->getDatabaseName() !== 'skillmatch_testing' || $connection->getConfig('url'))) {
    throw new RuntimeException('Unsafe testing configuration. Expected uncached mysql/skillmatch_testing without DB_URL.');
}
echo 'PDO MySQL: '.(extension_loaded('pdo_mysql') ? 'yes' : 'no').PHP_EOL;
echo 'Configured driver: '.$connection->getDriverName().PHP_EOL;
echo 'Configured database: '.$connection->getDatabaseName().PHP_EOL;
try {
    $row = $connection->selectOne('SELECT VERSION() AS version, @@default_storage_engine AS engine');
    echo 'Server: '.$row->version.'; engine: '.$row->engine.PHP_EOL;
    $tables = $connection->selectOne("SELECT COUNT(*) AS count FROM information_schema.tables WHERE table_schema = DATABASE() AND table_type = 'BASE TABLE' AND engine <> 'InnoDB'");
    echo 'Non-InnoDB tables: '.$tables->count.PHP_EOL;
    $exists = $connection->selectOne("SELECT COUNT(*) AS count FROM information_schema.schemata WHERE schema_name = 'skillmatch_testing'");
    echo 'Separate skillmatch_testing exists: '.$exists->count.PHP_EOL;
} catch (Throwable $e) {
    echo 'Database check failed (credentials/details suppressed): '.get_class($e).PHP_EOL;
    exit(1);
}
