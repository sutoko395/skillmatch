<?php

namespace Tests\Support;

use App\Contracts\AssessmentReadiness;
use App\Models\Category;
use App\Models\City;
use App\Models\EventPosition;
use App\Models\Package;
use App\Models\Skill;
use App\Models\User;
use App\Services\EventService;
use App\Services\PackageService;

trait A2Fixture
{
    protected function fixture(int $price = 0): array
    {
        $owner = User::factory()->create(['role' => 'organizer', 'organizer_status' => 'active']);
        $city = City::firstOrCreate(['name' => 'A2 test city'], ['is_active' => true]);
        $category = Category::firstOrCreate(['name' => 'A2 test category'], ['is_active' => true]);
        $skill = Skill::firstOrCreate(['name' => 'A2 test skill'], ['is_active' => true]);
        $event = app(EventService::class)->save($owner, [
            'title' => 'A2 Test '.uniqid(), 'description' => 'Kegiatan uji', 'location' => 'Lokasi uji', 'city_id' => $city->id, 'category_id' => $category->id,
            'starts_at' => now()->addDays(10)->format('Y-m-d').'T09:00', 'ends_at' => now()->addDays(10)->format('Y-m-d').'T17:00',
            'registration_opens_at' => now()->subDay()->format('Y-m-d').'T00:00', 'registration_deadline' => now()->addDays(9)->format('Y-m-d').'T23:00',
        ]);
        $position = app(EventService::class)->position($event, $owner, [
            'name' => 'Petugas', 'quota' => 1, 'required_full_availability' => false, 'required_same_city' => false,
            'skills' => [['skill_id' => $skill->id, 'minimum_level' => 'beginner', 'is_required' => true]],
            'schedules' => [['starts_at' => now()->addDays(10)->format('Y-m-d').'T09:00', 'ends_at' => now()->addDays(10)->format('Y-m-d').'T12:00']],
            'requirements' => [['name' => 'CV', 'kind' => 'document', 'document_type' => 'cv', 'is_required' => true]],
        ]);
        $package = Package::create(['name' => 'A2 Test '.uniqid(), 'price' => $price, 'max_positions' => 2, 'max_applications' => 1, 'max_registration_days' => 30, 'is_active' => true]);
        app(PackageService::class)->select($event, $owner, $package->id);

        return [$owner, $event->fresh(), $position, $package];
    }

    protected function assessmentReady(): void
    {
        // Test-only collaborator; never registered in runtime providers.
        $this->app->bind(AssessmentReadiness::class, fn () => new class implements AssessmentReadiness
        {
            public function publishedVersion(EventPosition $position): int
            {
                return 1;
            }
        });
    }
}
