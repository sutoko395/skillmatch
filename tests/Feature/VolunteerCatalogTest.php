<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class VolunteerCatalogTest extends TestCase
{
    use DatabaseTransactions;
    use \Tests\Support\A2Fixture;

    public function test_event_detail_keeps_panel_and_rejects_unpublished_events(): void
    {
        [$owner, $event] = $this->fixture();
        $volunteer = User::factory()->create(['role' => 'volunteer', 'is_active' => true]);
        $this->get(route('volunteer.events.show', $event))->assertRedirect(route('login'));
        $this->actingAs($volunteer)->get(route('volunteer.events.show', $event))->assertNotFound();
        $this->assessmentReady();
        $event->forceFill(['status' => 'approved'])->save();
        app(\App\Services\EventPublicationService::class)->publish($event, $owner);
        $this->get(route('volunteer.events.show', $event))->assertOk()
            ->assertSee('volunteer-sidebar')->assertSee('Jadwal tugas (WIB)')
            ->assertSee(route('volunteer.events.index'));
        $this->get(route('volunteer.events.index'))->assertOk()->assertSee(route('volunteer.events.show', $event));
        $this->get(route('events.show', $event))->assertOk()->assertDontSee('volunteer-sidebar');
    }

    public function test_catalog_stays_in_volunteer_panel_and_public_catalog_remains_public(): void
    {
        $this->get(route('volunteer.events.index'))->assertRedirect(route('login'));
        $volunteer = User::factory()->create(['role' => 'volunteer', 'is_active' => true]);
        $this->actingAs($volunteer)->get(route('volunteer.events.index', ['search' => 'Kegiatan']))
            ->assertOk()->assertViewIs('volunteer.events.index')
            ->assertSee('volunteer-sidebar')->assertSee(route('volunteer.events.index'));
        $this->get(route('events.index'))->assertOk()->assertViewIs('events.index');
        $organizer = User::factory()->create(['role' => 'organizer', 'is_active' => true]);
        $this->actingAs($organizer)->get(route('volunteer.events.index'))->assertForbidden();
    }
}
