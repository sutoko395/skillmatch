<?php

namespace Tests\Feature;

use App\Models\Assessment;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\City;
use App\Models\Package;
use App\Models\Skill;
use App\Models\User;
use App\Services\EventService;
use App\Services\OrganizerAssessmentService;
use App\Services\PackageService;
use App\Services\PositionSnapshotService;
use Tests\DatabaseTestCase;

class OrganizerAssessmentTest extends DatabaseTestCase
{
    private function setupPosition(): array
    {
        $owner = User::factory()->create(['role' => 'organizer', 'organizer_status' => 'active']);
        $event = app(EventService::class)->save($owner, [
            'title' => 'Assessment test', 'description' => 'Test', 'city' => 'Test city', 'location' => 'Test',
            'category_id' => Category::firstOrCreate(['name' => 'Assessment test'], ['is_active' => true])->id,
            'starts_at' => now()->addDays(10)->format('Y-m-d').'T08:00', 'ends_at' => now()->addDays(10)->format('Y-m-d').'T17:00',
            'registration_opens_at' => now()->format('Y-m-d').'T00:00', 'registration_deadline' => now()->addDays(9)->format('Y-m-d').'T23:00',
        ]);
        $event->forceFill(['city_id' => City::firstOrCreate(['name' => 'Assessment test'], ['is_active' => true])->id])->save();
        $position = app(EventService::class)->position($event, $owner, [
            'name' => 'Petugas', 'quota' => 1, 'required_full_availability' => false, 'required_same_city' => false,
            'skills' => [['skill_id' => Skill::firstOrCreate(['name' => 'Assessment test'], ['is_active' => true])->id,
                'minimum_level' => 'beginner', 'is_required' => true]],
        ]);
        $package = Package::create(['name' => 'Assessment test', 'price' => 0, 'max_positions' => 2, 'max_applications' => 10, 'max_registration_days' => 30, 'is_active' => true]);
        app(PackageService::class)->select($event, $owner, $package->id);

        return [$owner, $event, $position, "/organizer/positions/{$position->id}/assessment"];
    }

    private function payload(): array
    {
        return ['action' => 'draft', 'revision' => 0, 'duration_minutes' => 30, 'questions' => [[
            'text' => 'Pilihan?', 'options' => ['A' => 'Satu', 'B' => 'Dua', 'C' => 'Tiga', 'D' => 'Empat'], 'correct' => 'B',
        ]]];
    }

    public function test_draft_preview_publish_freeze_replay_and_real_readiness(): void
    {
        [$owner, $event, $position, $url] = $this->setupPosition();
        $this->actingAs($owner)->get($url)->assertOk();
        $this->postJson("/organizer/events/{$event->id}/submit")->assertUnprocessable()->assertJsonValidationErrors('assessment');
        $data = $this->payload();
        $this->put($url, $data)->assertRedirect();
        $assessment = Assessment::sole();
        $this->assertFalse($assessment->is_published);
        $data['revision'] = $assessment->revision;
        $data['action'] = 'preview';
        $this->put($url, $data)->assertOk()->assertSee('Konfirmasi Publikasi Assessment')->assertSee('Petugas');
        $this->assertFalse($assessment->fresh()->is_published);
        $data['action'] = 'publish';
        $this->putJson($url, $data)->assertUnprocessable()->assertJsonValidationErrors('confirmed');
        $data['confirmed'] = 1;
        $this->put($url, $data)->assertRedirect();
        $this->assertTrue($assessment->fresh()->is_published);
        $this->assertSame(1, app(OrganizerAssessmentService::class)->publishedVersion($position));
        $count = AuditLog::where('action', 'assessment.published')->count();
        $this->put($url, $data)->assertRedirect();
        $this->assertSame($count, AuditLog::where('action', 'assessment.published')->count());
        $data['action'] = 'draft';
        $this->putJson($url, $data)->assertUnprocessable();
        $this->post("/organizer/events/{$event->id}/submit")->assertRedirect();
        $this->assertSame('pending', $event->fresh()->status);
        $this->assertSame(1, app(PositionSnapshotService::class)->build($position)['assessment_version']);
        $this->assertArrayNotHasKey('is_correct', $assessment->questions()->first()->options()->first()->toArray());
    }

    public function test_partial_draft_and_stale_revision_and_owner_access(): void
    {
        [$owner, $event, $position, $url] = $this->setupPosition();
        $this->actingAs($owner)->put($url, ['action' => 'draft', 'revision' => 0])->assertRedirect();
        $this->assertNull(Assessment::sole()->duration_minutes);
        $this->putJson($url, $this->payload())->assertUnprocessable()->assertJsonValidationErrors('assessment');
        $other = User::factory()->create(['role' => 'organizer', 'organizer_status' => 'active']);
        $this->actingAs($other)->get($url)->assertForbidden();
        $this->putJson($url, $this->payload())->assertForbidden();
        $this->actingAs(User::factory()->create(['role' => 'volunteer']))->get($url)->assertForbidden();
        $this->putJson($url, $this->payload())->assertForbidden();
        $owner->forceFill(['is_active' => false])->save();
        $this->actingAs($owner)->putJson($url, $this->payload())->assertForbidden();
    }

    public function test_frozen_event_blocks_draft_and_corrupt_published_key_blocks_readiness(): void
    {
        [$owner, $event, $position, $url] = $this->setupPosition();
        $event->forceFill(['published_at' => now()])->save();
        $this->actingAs($owner)->putJson($url, $this->payload())->assertUnprocessable();
        $event->forceFill(['published_at' => null])->save();
        $assessment = app(OrganizerAssessmentService::class)->save($position, $owner, $this->payload(), true);
        $assessment->questions()->first()->options()->where('label', 'A')->update(['is_correct' => true]);
        $this->postJson("/organizer/events/{$event->id}/submit")->assertUnprocessable();
        $this->assertSame('draft', $event->fresh()->status);
    }
}
