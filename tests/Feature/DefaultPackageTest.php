<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Models\User;
use App\Services\PackageService;
use App\Services\PaymentService;
use Database\Seeders\DefaultPackageSeeder;
use Tests\DatabaseTestCase;
use Tests\Support\A2Fixture;

class DefaultPackageTest extends DatabaseTestCase
{
    use A2Fixture;

    public function test_initial_packages_are_seeded_without_resetting_admin_edits(): void
    {
        User::factory()->create(['role' => 'admin']);
        $this->seed(DefaultPackageSeeder::class);
        $this->assertDatabaseHas('packages', ['name' => 'Free', 'price' => 0, 'max_positions' => 2, 'max_applications' => 30, 'max_registration_days' => 7]);
        $this->assertDatabaseHas('packages', ['name' => 'Standard', 'price' => 30000, 'max_positions' => 5, 'max_applications' => 100, 'max_registration_days' => 30]);
        $this->assertDatabaseHas('packages', ['name' => 'Premium', 'price' => 50000, 'max_positions' => 10, 'max_applications' => 300, 'max_registration_days' => 60]);
        $package = Package::where('name', 'Standard')->firstOrFail();
        $package->update(['name' => 'Standard Baru', 'price' => 35000, 'is_active' => false]);
        $count = Package::count();
        $this->seed(DefaultPackageSeeder::class);
        $this->assertSame($count, Package::count());
        $this->assertDatabaseHas('packages', ['id' => $package->id, 'name' => 'Standard Baru', 'price' => 35000, 'is_active' => false]);
    }

    public function test_admin_updates_live_cards_without_changing_order_snapshots(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->seed(DefaultPackageSeeder::class);
        $package = Package::where('name', 'Standard')->firstOrFail();
        [$owner, $event] = $this->fixture();
        app(PackageService::class)->select($event, $owner, $package->id);
        $event = $event->fresh();
        $event->forceFill(['status' => 'approved'])->save();
        $order = app(PaymentService::class)->createOrder($event, $owner);
        $data = $package->only(['name', 'price', 'max_positions', 'max_applications', 'max_registration_days', 'is_active']);
        $data['price'] = 35000;
        $data['max_applications'] = 333;
        $this->actingAs($admin)->put('/admin/packages/'.$package->id, $data)->assertRedirect('/admin/packages');
        $this->get('/')->assertOk()->assertSee('Rp35.000')->assertSee('333 lamaran terkirim');
        $this->assertSame(30000, $order->fresh()->amount);
        $this->assertSame(100, $order->fresh()->package_snapshot['max_applications']);
        $this->assertSame(30000, $event->fresh()->package_snapshot['price']);
        $data['is_active'] = false;
        $this->put('/admin/packages/'.$package->id, $data)->assertRedirect('/admin/packages');
        $this->get('/')->assertViewHas('packages', fn ($packages) => ! $packages->contains('id', $package->id));
        $this->get('/register?role=organizer')->assertRedirect(); // Authenticated users keep their own role.
    }
}
