<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\Package;
use App\Models\User;
use App\Services\AuditService;
use App\Services\PackageService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DefaultPackageSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new \RuntimeException('Initial local packages require local/testing environment.');
        }
        DB::transaction(function () {
            $admin = User::where('role', 'admin')->where('is_active', true)->whereNotNull('email_verified_at')->orderBy('id')->lockForUpdate()->firstOrFail();
            foreach ([
                ['name' => 'Free', 'price' => 0, 'max_positions' => 2, 'max_applications' => 30, 'max_registration_days' => 7],
                ['name' => 'Standard', 'price' => 30000, 'max_positions' => 5, 'max_applications' => 100, 'max_registration_days' => 30],
                ['name' => 'Premium', 'price' => 50000, 'max_positions' => 10, 'max_applications' => 300, 'max_registration_days' => 60],
            ] as $data) {
                $reason = 'initial_'.strtolower($data['name']);
                if (AuditLog::where('action', 'package.default_seeded')->where('reason', $reason)->exists()) {
                    continue;
                }
                $package = Package::where('name', $data['name'])->first()
                    ?? app(PackageService::class)->save($admin, $data + ['is_active' => true]);
                app(AuditService::class)->record($admin, 'package.default_seeded', $package, ['after' => $package->snapshot()], $reason);
            }
        }, 3);
    }
}
