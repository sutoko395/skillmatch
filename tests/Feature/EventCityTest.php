<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\City;
use App\Models\Event;
use Tests\DatabaseTestCase;
use Tests\Support\A2Fixture;

class EventCityTest extends DatabaseTestCase
{
    use A2Fixture;

    private function input($event): array
    {
        $input = $event->only(['title', 'description', 'location', 'city_id', 'category_id']);
        $input['package_id'] = $event->package_snapshot['id'];
        foreach (['starts_at', 'ends_at', 'registration_opens_at', 'registration_deadline'] as $field) {
            $input[$field] = $event->{$field}->setTimezone('Asia/Jakarta')->format('Y-m-d\TH:i');
        }

        return $input;
    }

    public function test_create_popup_and_page_send_master_city_and_save_new_event(): void
    {
        [$owner, $event] = $this->fixture();
        $inactive = City::create(['name' => 'Kota modal nonaktif', 'is_active' => false]);
        $this->actingAs($owner);
        foreach (['/organizer/events', '/organizer/events/create'] as $path) {
            $this->get($path)->assertOk()->assertSee('name="city_id"', false)
                ->assertSee('name="package_id"', false)->assertSee('x-model="opens"', false)->assertSee('x-model="deadline"', false)
                ->assertDontSee('name="city"', false)->assertSee($event->city)->assertDontSee($inactive->name);
        }
        $input = array_replace($this->input($event), ['title' => 'Event dari popup uji']);
        $this->post('/organizer/events', $input)->assertRedirect('/organizer/events')->assertSessionHasNoErrors();
        $this->assertDatabaseHas('events', ['title' => $input['title'], 'city_id' => $event->city_id, 'city' => $event->city, 'organizer_id' => $owner->id, 'status' => 'draft']);
    }

    public function test_create_validation_reopens_popup_and_preserves_selected_city(): void
    {
        [$owner, $event] = $this->fixture();
        $before = Event::count();
        $input = array_replace($this->input($event), ['title' => '']);
        $this->actingAs($owner)->from('/organizer/events')->post('/organizer/events', $input)
            ->assertRedirect('/organizer/events')->assertSessionHasErrors('title');
        $response = $this->withCookie(config('session.cookie'), session()->getId())->get('/organizer/events')->assertOk();
        $response->assertSee('openCreateEvent: true', false)
            ->assertSee('value="'.$event->city_id.'" selected', false);
        $this->assertDatabaseCount('events', $before);
    }

    public function test_city_round_trip_uses_master_and_ignores_forged_text(): void
    {
        [$owner, $event] = $this->fixture();
        $this->assertSame($event->city_id, $event->load('cityRecord')->cityRecord->id);
        $this->assertSame($event->cityRecord->name, $event->city);
        $city = City::create(['name' => 'Kota baru uji', 'is_active' => true]);
        $input = array_replace($this->input($event), ['city_id' => $city->id]);
        $this->actingAs($owner)->put("/organizer/events/{$event->id}", $input)
            ->assertRedirect()->assertSessionHasNoErrors();
        $event->refresh()->load('cityRecord');
        $this->assertSame($city->id, $event->city_id);
        $this->assertSame($city->name, $event->city);
        $this->assertSame($city->id, $event->cityRecord->id);
        $this->put("/organizer/events/{$event->id}", array_replace($input, ['city' => str_repeat('Kota palsu ', 20)]))
            ->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame($city->name, $event->fresh()->city);
        $this->get("/organizer/events/{$event->id}/edit")->assertOk()->assertSee('name="city_id"', false)->assertSee($city->name);
    }

    public function test_invalid_or_inactive_city_cannot_replace_existing_city(): void
    {
        [$owner, $event] = $this->fixture();
        $inactive = City::create(['name' => 'Kota nonaktif uji', 'is_active' => false]);
        $before = AuditLog::count();
        $this->actingAs($owner);
        foreach ([null, 999999999, $inactive->id] as $cityId) {
            $this->putJson("/organizer/events/{$event->id}", array_replace($this->input($event), ['city_id' => $cityId]))
                ->assertUnprocessable()->assertJsonValidationErrors('city_id');
            $this->assertSame($event->city_id, $event->fresh()->city_id);
            $this->assertSame($before, AuditLog::count());
        }
        $this->get("/organizer/events/{$event->id}/edit")->assertDontSee($inactive->name);
    }

    public function test_legacy_text_city_requires_explicit_selection_before_moderation(): void
    {
        [$owner, $event] = $this->fixture();
        $event->forceFill(['city_id' => null, 'city' => 'Kota lama belum dipetakan'])->save();
        $before = AuditLog::count();
        $this->actingAs($owner)->get("/organizer/events/{$event->id}/edit")->assertOk()->assertSee('Kota lama belum dipetakan');
        $this->from("/organizer/events/{$event->id}")->post("/organizer/events/{$event->id}/submit")
            ->assertRedirect("/organizer/events/{$event->id}")->assertSessionHasErrors('city_id');
        $this->assertSame('draft', $event->fresh()->status);
        $this->assertNull($event->fresh()->submitted_at);
        $this->assertSame($before, AuditLog::count());
        $this->assertNull($event->fresh()->city_id);
        $this->withCookie(config('session.cookie'), session()->getId())
            ->get("/organizer/events/{$event->id}")->assertOk()->assertSee('Pilih kota aktif dari master');
    }

    public function test_city_deactivated_after_save_blocks_moderation_without_state_change(): void
    {
        [$owner, $event] = $this->fixture();
        $event->cityRecord->update(['is_active' => false]);
        $before = AuditLog::count();
        $this->actingAs($owner)->postJson("/organizer/events/{$event->id}/submit")
            ->assertUnprocessable()->assertJsonValidationErrors('city_id');
        $this->assertSame('draft', $event->fresh()->status);
        $this->assertNull($event->fresh()->submitted_at);
        $this->assertSame($before, AuditLog::count());
    }
}
