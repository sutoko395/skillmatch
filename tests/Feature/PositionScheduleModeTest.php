<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Skill;
use App\Models\User;
use App\Services\EventService;
use App\Services\PositionSnapshotService;
use Tests\DatabaseTestCase;
use Tests\Support\A2Fixture;

class PositionScheduleModeTest extends DatabaseTestCase
{
    use A2Fixture;

    private function eventInput(): array
    {
        return [
            'title' => 'Schedule test', 'description' => 'Test', 'city' => 'Test city', 'location' => 'Test location',
            'category_id' => Category::firstOrCreate(['name' => 'Schedule test'], ['is_active' => true])->id,
            'starts_at' => now()->addDays(10)->format('Y-m-d').'T08:00',
            'ends_at' => now()->addDays(10)->format('Y-m-d').'T17:00',
            'registration_opens_at' => now()->format('Y-m-d').'T00:00',
            'registration_deadline' => now()->addDays(9)->format('Y-m-d').'T23:00',
        ];
    }

    private function positionInput(): array
    {
        return [
            'name' => 'Test position', 'quota' => 1,
            'required_full_availability' => false, 'required_same_city' => false,
            'skills' => [['skill_id' => Skill::firstOrCreate(['name' => 'Schedule test'], ['is_active' => true])->id,
                'minimum_level' => 'beginner', 'is_required' => true]],
        ];
    }

    private function setupEvent(): array
    {
        $owner = User::factory()->create(['role' => 'organizer', 'organizer_status' => 'active']);
        $input = $this->eventInput();

        return [$owner, app(EventService::class)->save($owner, $input), $input];
    }

    public function test_default_mode_uses_server_schedule_and_renders_reference(): void
    {
        [$owner, $event] = $this->setupEvent();
        $url = "/organizer/events/{$event->id}/positions";
        $this->actingAs($owner)->get($url.'/create')->assertOk()
            ->assertSee('Jadwal event')->assertSee('Ikuti jadwal event')->assertSee('Atur jadwal tugas khusus')
            ->assertSee('type="time"', false);
        $input = $this->positionInput();
        // Forged schedules are ignored in automatic mode.
        $this->post($url, $input + ['follows_event_schedule' => 1, 'schedules' => [['starts_at' => 'invalid']]])->assertRedirect();
        $position = $event->positions()->firstOrFail();
        $schedule = $position->schedules()->sole();
        $this->assertTrue($position->follows_event_schedule);
        $this->assertTrue($schedule->starts_at->equalTo($event->starts_at));
        $this->assertTrue($schedule->ends_at->equalTo($event->ends_at));
        $this->assessmentReady();
        $snapshot = app(PositionSnapshotService::class)->build($position);
        $this->assertSame($event->starts_at->toIso8601String(), $snapshot['schedules'][0]['start']);
    }

    public function test_custom_schedule_validation_and_legacy_mode_are_preserved(): void
    {
        [$owner, $event, $input] = $this->setupEvent();
        $url = "/organizer/events/{$event->id}/positions";
        $payload = $this->positionInput() + ['follows_event_schedule' => 0, 'schedules' => [[
            'starts_at' => substr($input['starts_at'], 0, 11).'07:00', 'ends_at' => $input['ends_at'],
        ]]];
        $this->actingAs($owner)->postJson($url, $payload)->assertUnprocessable()->assertJsonValidationErrors('schedules');
        $this->assertSame(0, $event->positions()->count());
        $payload['schedules'][0] = ['starts_at' => substr($input['starts_at'], 0, 11).'09:00',
            'ends_at' => substr($input['ends_at'], 0, 11).'12:00'];
        unset($payload['follows_event_schedule']);
        $this->post($url, $payload)->assertRedirect();
        $position = $event->positions()->sole();
        $this->assertFalse($position->follows_event_schedule);
        $this->assertSame('02:00', $position->schedules()->sole()->starts_at->format('H:i'));
    }

    public function test_event_edit_synchronizes_only_following_positions(): void
    {
        [$owner, $event, $input] = $this->setupEvent();
        $service = app(EventService::class);
        $following = $service->position($event, $owner, $this->positionInput());
        $custom = $service->position($event, $owner, $this->positionInput() + ['schedules' => [[
            'starts_at' => substr($input['starts_at'], 0, 11).'09:00',
            'ends_at' => substr($input['ends_at'], 0, 11).'12:00',
        ]]]);
        $oldCustom = $custom->schedules()->sole()->starts_at->toIso8601String();
        $input['ends_at'] = substr($input['ends_at'], 0, 11).'18:00';
        $updated = $service->save($owner, $input, $event);
        $this->assertTrue($following->schedules()->sole()->ends_at->equalTo($updated->ends_at));
        $this->assertSame($oldCustom, $custom->schedules()->sole()->starts_at->toIso8601String());

        $input['starts_at'] = substr($input['starts_at'], 0, 11).'10:00';
        $this->actingAs($owner)->putJson("/organizer/events/{$event->id}", $input)
            ->assertUnprocessable()->assertJsonValidationErrors('schedules');
        $this->assertTrue($event->fresh()->starts_at->equalTo($event->starts_at));
        $this->assertTrue($following->schedules()->sole()->starts_at->equalTo($event->starts_at));
    }

    public function test_mode_switch_and_endpoint_ownership_and_published_freeze(): void
    {
        [$owner, $event, $input] = $this->setupEvent();
        $payload = $this->positionInput();
        $position = app(EventService::class)->position($event, $owner, $payload + ['schedules' => [[
            'starts_at' => $input['starts_at'], 'ends_at' => $input['ends_at'],
        ]]]);
        $url = "/organizer/events/{$event->id}/positions/{$position->id}";
        $this->actingAs($owner)->put($url, $payload + ['follows_event_schedule' => 1])->assertRedirect();
        $this->assertTrue($position->fresh()->follows_event_schedule);
        $other = User::factory()->create(['role' => 'organizer', 'organizer_status' => 'active']);
        $this->actingAs($other)->putJson($url, $payload)->assertForbidden();
        $this->actingAs(User::factory()->create(['role' => 'volunteer']))->putJson($url, $payload)->assertForbidden();
        $event->forceFill(['published_at' => now(), 'publication_status' => 'published'])->save();
        $this->actingAs($owner)->putJson($url, $payload)->assertUnprocessable();
        $this->putJson("/organizer/events/{$event->id}", $input)->assertUnprocessable();
    }

    public function test_multiday_event_keeps_date_inputs(): void
    {
        [$owner, $event] = $this->setupEvent();
        $event->forceFill(['ends_at' => $event->ends_at->copy()->addDay()])->save();
        $this->actingAs($owner)->get("/organizer/events/{$event->id}/positions/create")->assertOk()
            ->assertSee('Pilih tanggal dan jam tugas dalam rentang event.')
            ->assertSee('type="datetime-local"', false);
    }
}
