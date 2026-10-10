<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\City;
use App\Models\Event;
use App\Models\Package;
use App\Models\Skill;
use App\Models\User;
use App\Services\EventService;
use App\Services\PackageService;
use Illuminate\Database\Seeder;

class A2DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new \RuntimeException('Demo seeder is local/testing only.');
        }
        $owner = User::where('email', 'organizer-a@example.test')->where('is_active', true)->where('organizer_status', 'active')->firstOrFail();
        $city = City::where('is_active', true)->firstOrFail();
        $category = Category::where('is_active', true)->firstOrFail();
        $skill = Skill::where('is_active', true)->firstOrFail();
        // Explicit demo fixtures, not production prices. Do not overwrite admin changes.
        $free = Package::firstOrCreate(['name' => 'Demo Free (fixture)'], ['price' => 0, 'max_positions' => 2, 'max_applications' => 10, 'max_registration_days' => 30, 'is_active' => true]);
        $paid = Package::firstOrCreate(['name' => 'Demo Standard (fixture)'], ['price' => 10000, 'max_positions' => 5, 'max_applications' => 100, 'max_registration_days' => 60, 'is_active' => true]);
        foreach (['Demo A2 - Gratis' => $free, 'Demo A2 - Berbayar approved' => $paid, 'Demo A2 - Draft overlap' => $free] as $title => $package) {
            if (Event::where('organizer_id', $owner->id)->where('title', $title)->exists()) {
                continue;
            }
            $event = app(EventService::class)->save($owner, ['package_id' => $package->id, 'title' => $title, 'description' => 'Fixture lokal A2. Assessment A4 belum tersedia; event tidak dipublikasikan.', 'location' => 'Lokasi demonstrasi', 'city_id' => $city->id, 'category_id' => $category->id,
                'starts_at' => now()->addDays(14)->format('Y-m-d').'T09:00', 'ends_at' => now()->addDays(14)->format('Y-m-d').'T17:00',
                'registration_opens_at' => now()->format('Y-m-d').'T00:00', 'registration_deadline' => now()->addDays(13)->format('Y-m-d').'T20:00']);
            app(EventService::class)->position($event, $owner, ['name' => 'Petugas demo', 'quota' => 1, 'required_full_availability' => false, 'required_same_city' => false, 'skills' => [['skill_id' => $skill->id, 'minimum_level' => 'beginner', 'is_required' => true]], 'schedules' => [['starts_at' => now()->addDays(14)->format('Y-m-d').'T09:00', 'ends_at' => now()->addDays(14)->format('Y-m-d').'T12:00']]]);
            app(PackageService::class)->select($event, $owner, $package->id);
            if ($package->price > 0) {
                $event->forceFill(['status' => 'approved'])->save();
            } // Explicit local fixture, no approval endpoint bypass.
        }
    }
}
