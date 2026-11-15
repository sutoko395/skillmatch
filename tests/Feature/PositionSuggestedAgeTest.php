<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\PositionSnapshotService;
use Tests\DatabaseTestCase;
use Tests\Support\A2Fixture;

class PositionSuggestedAgeTest extends DatabaseTestCase
{
    use A2Fixture;

    private function input($position): array
    {
        return ['name' => $position->name, 'quota' => 1, 'required_full_availability' => false,
            'required_same_city' => false, 'follows_event_schedule' => true,
            'skills' => $position->positionSkills->map->only(['skill_id', 'minimum_level', 'is_required'])->all()];
    }

    public function test_range_is_saved_rendered_and_can_be_cleared_without_changing_evaluation_snapshot(): void
    {
        [$owner, $event, $position] = $this->fixture();
        $this->assessmentReady();
        // Normalize schedule before comparing snapshots; only age changes thereafter.
        $url = "/organizer/events/{$event->id}/positions/{$position->id}";
        $this->actingAs($owner)->put($url, $this->input($position))->assertRedirect();
        $snapshot = app(PositionSnapshotService::class)->build($position->fresh());
        $this->put($url, $this->input($position) + ['suggested_min_age' => 18, 'suggested_max_age' => 30])->assertRedirect();
        $this->assertSame(18, $position->fresh()->suggested_min_age);
        $this->assertSame(30, $position->fresh()->suggested_max_age);
        $this->assertSame($snapshot, app(PositionSnapshotService::class)->build($position->fresh()));
        $this->get($url.'/edit')->assertOk()->assertSee('Usia minimum (tahun)');
        $this->get("/organizer/events/{$event->id}")->assertOk()->assertSee('Usia yang disarankan:');
        $html = view('components.position-suggested-age', ['position' => $position->fresh(), 'attributes' => new \Illuminate\View\ComponentAttributeBag])->render();
        $this->assertStringContainsString('bukan syarat melamar', $html);
        $this->put($url, $this->input($position) + ['suggested_min_age' => '', 'suggested_max_age' => ''])->assertRedirect();
        $this->assertNull($position->fresh()->suggested_min_age);
        $this->assertNull($position->fresh()->suggested_max_age);
    }

    public function test_invalid_ranges_are_rejected_and_one_sided_ranges_are_allowed(): void
    {
        [$owner, $event, $position] = $this->fixture();
        $url = "/organizer/events/{$event->id}/positions/{$position->id}";
        $this->actingAs($owner);
        foreach ([['suggested_min_age' => -1], ['suggested_min_age' => 'abc'], ['suggested_max_age' => 2.5],
            ['suggested_min_age' => 30, 'suggested_max_age' => 18]] as $range) {
            $this->putJson($url, $this->input($position) + $range)->assertUnprocessable();
        }
        $this->put($url, $this->input($position) + ['suggested_max_age' => 30])->assertRedirect();
        $this->assertNull($position->fresh()->suggested_min_age);
        $this->assertSame(30, $position->fresh()->suggested_max_age);
    }

    public function test_foreign_owner_and_published_position_remain_locked(): void
    {
        [$owner, $event, $position] = $this->fixture();
        $url = "/organizer/events/{$event->id}/positions/{$position->id}";
        $input = $this->input($position) + ['suggested_min_age' => 18];
        $other = User::factory()->create(['role' => 'organizer', 'organizer_status' => 'active']);
        $this->actingAs($other)->put($url, $input)->assertForbidden();
        $event->forceFill(['published_at' => now(), 'publication_status' => 'published'])->save();
        $this->actingAs($owner)->putJson($url, $input)->assertUnprocessable();
        $this->assertNull($position->fresh()->suggested_min_age);
    }
}
