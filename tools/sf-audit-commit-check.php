<?php

use App\Models\City;
use App\Services\AuditService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

// Actual commit/rollback proof using two connections, only on the dedicated test database.
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
$db = DB::connection();
if (! $app->environment('testing') || $app->configurationIsCached() || $db->getDriverName() !== 'mysql' || $db->getDatabaseName() !== 'skillmatch_testing') {
    throw new RuntimeException('Requires --env=testing and isolated skillmatch_testing.');
}
config(['database.connections.audit_observer' => $db->getConfig()]);
$observer = DB::connection('audit_observer');
$key = 'audit-proof-'.bin2hex(random_bytes(6));
$db->beginTransaction();
$city = City::create(['name' => $key]);
$log = app(AuditService::class)->record(null, 'foundation.commit-proof', $city);
if ($observer->table('audit_logs')->where('id', $log->id)->exists()) {
    throw new RuntimeException('Uncommitted audit visible');
}
$db->commit();
if (! $observer->table('audit_logs')->where('id', $log->id)->exists()) {
    throw new RuntimeException('Committed audit missing');
}
$db->beginTransaction();
$rolledBack = app(AuditService::class)->record(null, 'foundation.rollback-proof', $city);
$db->rollBack();
if ($observer->table('audit_logs')->where('id', $rolledBack->id)->exists()) {
    throw new RuntimeException('False success after rollback');
}
echo "PASS: independent connection sees committed audit only; rollback leaves no false success. Test proof retained (append-only).\n";
