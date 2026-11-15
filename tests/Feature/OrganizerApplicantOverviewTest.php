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
    public function test_applicant_navigation_returns_to_the_applicant_hub_and_keeps_owner_boundaries(): void
    {
        $application = $this->participantApplication();
        $this->actingAs($application->event->organizer);
        $this->get(route('organizer.applications.index', $application->event))->assertOk()
            ->assertSee('href="'.route('organizer.applications.overview').'" class="organizer-action-button organizer-detail-back"', false)
            ->assertDontSee('aria-label="Tab pelamar"', false)
            ->assertDontSee('href="'.route('organizer.events.show', $application->event).'"', false)
            ->assertSee(route('organizer.applications.show', $application))
            ->assertSee($application->position->name)
            ->assertViewHas('applications', fn ($items) => $items->total() === 1);
        $this->get(route('organizer.applications.show', $application))->assertOk()
            ->assertSee('href="'.route('organizer.applications.index', $application->event).'" class="organizer-action-button organizer-detail-back"', false)
            ->assertSee('Hasil penyaringan')->assertSee('Kecocokan pelamar')->assertDontSee('Keputusan seleksi')
            ->assertDontSee('Pelaksanaan dan kehadiran')
            ->assertDontSee('Berdasarkan data saat lamaran dikirim.')
            ->assertDontSee('Dihitung dari data saat melamar.')
            ->assertDontSee('Nilai assessment terpisah dari skor kecocokan.');
        $otherOwner = User::factory()->create(['role' => 'organizer', 'organizer_status' => 'active']);
        $this->actingAs($otherOwner)->get(route('organizer.applications.index', $application->event))->assertForbidden();
        $this->get(route('organizer.applications.show', $application))->assertForbidden();
    }

    public function test_selection_closure_lives_in_applicants_and_execution_links_back_until_resolved(): void
    {
        $application = $this->participantApplication();
        $this->actingAs($application->event->organizer);
        $list = route('organizer.applications.index', $application->event);
        $execution = route('organizer.execution.show', $application->event);
        $close = route('organizer.events.close-selection', $application->event);
        $this->get($list)->assertOk()->assertSee('id="tutup-seleksi"', false)->assertSee('action="'.$close.'"', false)
            ->assertViewHas('pendingSelectionCount', 1);
        $this->get(route('organizer.applications.index', [$application->event, 'position_id' => 999999]))->assertOk()
            ->assertSee('id="tutup-seleksi"', false)->assertViewHas('pendingSelectionCount', 1);
        $this->get($execution)->assertOk()->assertSee('Ke Kelola Pelamar')->assertSee($list.'#tutup-seleksi')
            ->assertDontSee('action="'.$close.'"', false)->assertViewHas('hasPendingSelection', true);
        $this->travelTo($application->event->registration_deadline->addMinute());
        $this->from($list)->post($close, ['reason' => 'Seleksi sudah berakhir.', 'confirmed' => 1])->assertRedirect($list);
        $this->assertSame('rejected', $application->fresh()->status);
        $this->get($list)->assertOk()->assertDontSee('id="tutup-seleksi"', false)->assertViewHas('pendingSelectionCount', 0);
        $this->get($execution)->assertOk()->assertDontSee('Ke Kelola Pelamar')->assertViewHas('hasPendingSelection', false);
        $application->forceFill(['status' => 'under_review'])->save();
        foreach (['completed', 'cancelled'] as $state) {
            $application->event->forceFill(['lifecycle_status' => $state])->save();
            $this->get($list)->assertOk()->assertDontSee('action="'.$close.'"', false);
            $this->get($execution)->assertOk()->assertDontSee('Ke Kelola Pelamar');
        }
    }


    public function test_closure_waits_for_registration_deadline_and_completion_shows_blockers(): void
    {
        $application = $this->participantApplication();
        $event = $application->event;
        $event->forceFill(['registration_deadline' => now()->addHour(), 'ends_at' => now()->addHours(3)])->save();
        $this->actingAs(User::findOrFail($event->organizer_id));
        $this->get(route('organizer.applications.index', $event))->assertOk()
            ->assertSee('Tersedia setelah tenggat pendaftaran')->assertSee('disabled', false);
        $this->get(route('organizer.execution.show', $event))->assertOk()
            ->assertViewHas('completionIssues', fn ($issues) => in_array('Jadwal kegiatan belum berakhir.', $issues))
            ->assertSee('Jadwal kegiatan belum berakhir.')->assertSee('Ke Kelola Pelamar');
        $event->forceFill(['registration_deadline' => now()->subMinute()])->save();
        $this->get(route('organizer.applications.index', $event))->assertOk()
            ->assertDontSee('Tersedia setelah tenggat pendaftaran');
    }
}
