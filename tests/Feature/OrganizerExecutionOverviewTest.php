<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\SelectionService;
use Tests\DatabaseTestCase;
use Tests\Support\A3ExecutionFixture;

class OrganizerExecutionOverviewTest extends DatabaseTestCase
{
    use A3ExecutionFixture;

    public function test_only_owned_events_and_accepted_participants_are_listed(): void
    {
        $application = $this->reviewApplication();
        $owner = $application->event->organizer;
        app(SelectionService::class)->accept($application, $owner);
        $this->secondCandidate($application);
        $foreign = $this->participantApplication();

        $this->actingAs($owner)->get(route('organizer.execution.index', ['organizer_id' => $foreign->event->organizer_id]))
            ->assertOk()->assertDontSee('@yield(', false)->assertSee($application->event->title)->assertDontSee($foreign->event->title)
            ->assertSee(route('organizer.execution.show', $application->event))
            ->assertViewHas('events', fn ($events) => $events->total() === 1
                && $events->first()->accepted_applications_count === 1);
        $this->get(route('organizer.execution.show', $foreign->event))->assertForbidden();
        $this->get(route('organizer.events.show', $application->event))->assertOk()
            ->assertSee(route('organizer.execution.index'))
            ->assertDontSee('class="organizer-event-actions', false);
    }

    public function test_access_is_limited_to_active_approved_organizers(): void
    {
        $url = route('organizer.execution.index');
        $this->get($url)->assertRedirect(route('login'));
        foreach (['volunteer', 'admin'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]))->get($url)->assertForbidden();
        }
        $pending = User::factory()->create(['role' => 'organizer', 'organizer_status' => 'pending']);
        $this->actingAs($pending)->get($url)->assertRedirect(route('organizer.profile.pending'));
        $inactive = User::factory()->create(['role' => 'organizer', 'organizer_status' => 'active', 'is_active' => false]);
        $this->actingAs($inactive)->getJson($url)->assertForbidden();
    }

    public function test_organizer_without_events_has_an_empty_overview(): void
    {
        $owner = User::factory()->create(['role' => 'organizer', 'organizer_status' => 'active']);
        $this->actingAs($owner)->get(route('organizer.execution.index'))->assertOk()
            ->assertSee('Belum ada kegiatan')
            ->assertViewHas('events', fn ($events) => $events->isEmpty());
    }
}
