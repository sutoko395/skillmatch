<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
$db = DB::connection();
if ($db->getDriverName() !== 'mysql' || $db->getDatabaseName() === 'skillmatch_testing') {
    throw new RuntimeException('Run from the local application MySQL configuration.');
}
$db->statement('CREATE DATABASE IF NOT EXISTS `skillmatch_testing` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
$path = __DIR__.'/../.env.testing';
if (! file_exists($path)) {
    $env = file_get_contents(__DIR__.'/../.env');
    foreach (['APP_ENV' => 'testing', 'DB_DATABASE' => 'skillmatch_testing', 'DB_URL' => '', 'SESSION_DRIVER' => 'array', 'CACHE_STORE' => 'array', 'MAIL_MAILER' => 'array', 'QUEUE_CONNECTION' => 'sync'] as $key => $value) {
        $env = preg_replace('/^'.preg_quote($key, '/').'=.*$/m', $key.'='.$value, $env, -1, $count);
        if (! $count) {
            $env .= "\n$key=$value\n";
        }
    }
    file_put_contents($path, $env);
}
echo "Separate test database prepared; existing .env.testing preserved.\n";
