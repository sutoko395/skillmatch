<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\DatabaseTestCase;
use Tests\Support\ParticipantAssessmentFixture;

class OrganizerApplicantOverviewTest extends DatabaseTestCase
{
    use ParticipantAssessmentFixture;

    public function test_overview_only_lists_owned_events_and_counts_submitted_applications(): void
    {
        $application = $this->participantApplication();
        $foreign = $this->participantApplication();
        $draft = $application->replicate();
        $draft->forceFill([
            'volunteer_id' => User::factory()->create()->id,
            'status' => 'draft', 'submitted_at' => null,
        ])->save();

        $this->actingAs($application->event->organizer)
            ->get(route('organizer.applications.overview', ['organizer_id' => $foreign->event->organizer_id]))
            ->assertOk()->assertSee($application->event->title)->assertDontSee($foreign->event->title)
            ->assertSee(route('organizer.applications.index', $application->event))
            ->assertViewHas('events', fn ($events) => $events->total() === 1
                && $events->first()->submitted_applications_count === 1);
        $this->get(route('organizer.applications.index', $foreign->event))->assertForbidden();
    }

    public function test_overview_retains_organizer_access_boundaries(): void
    {
        $url = route('organizer.applications.overview');
        $this->get($url)->assertRedirect(route('login'));
        foreach (['volunteer', 'admin'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]))->get($url)->assertForbidden();
        }
        $pending = User::factory()->create(['role' => 'organizer', 'organizer_status' => 'pending']);
        $this->actingAs($pending)->get($url)->assertRedirect(route('organizer.profile.pending'));
        $inactive = User::factory()->create(['role' => 'organizer', 'organizer_status' => 'active', 'is_active' => false]);
        $this->actingAs($inactive)->getJson($url)->assertForbidden();
    }

    public function test_organizer_without_events_gets_an_empty_state(): void
    {
        $owner = User::factory()->create(['role' => 'organizer', 'organizer_status' => 'active']);
        $this->actingAs($owner)->get(route('organizer.applications.overview'))->assertOk()
            ->assertSee('Belum ada kegiatan')->assertSee('Kelola Pelamar')
            ->assertViewHas('events', fn ($events) => $events->isEmpty());
    }
}
