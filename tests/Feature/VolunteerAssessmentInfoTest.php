<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\AuditLog;
use App\Models\ScreeningResult;
use App\Models\User;
use App\Services\OrganizerAssessmentService;
use Carbon\CarbonImmutable;
use Tests\DatabaseTestCase;
use Tests\Support\A2Fixture;

class VolunteerAssessmentInfoTest extends DatabaseTestCase
{
    use A2Fixture;

    private function application(): Application
    {
        [$owner, $event, $position] = $this->fixture();
        $assessment = app(OrganizerAssessmentService::class)->save($position, $owner, [
            'revision' => 0, 'duration_minutes' => 30,
            'questions' => [['text' => 'Pertanyaan privat assessment',
                'options' => ['A' => 'Opsi rahasia A', 'B' => 'Opsi rahasia B', 'C' => 'Opsi rahasia C', 'D' => 'Opsi rahasia D'],
                'correct' => 'B']],
        ], true);
        $application = Application::create([
            'event_id' => $event->id, 'event_position_id' => $position->id,
            'volunteer_id' => User::factory()->create(['role' => 'volunteer'])->id,
            'status' => 'assessment_pending', 'submitted_at' => now(), 'revision' => 3,
            'snapshot_json' => ['assessment_version' => $assessment->version,
                'assessment_deadline' => $event->assessment_deadline->toIso8601String(),
                'decision_deadline' => $event->decision_deadline->toIso8601String()],
        ]);
        ScreeningResult::create(['application_id' => $application->id, 'passed' => true,
            'checks_json' => [], 'rule_version' => 'match-v1.4', 'evaluated_at' => now()]);

        return $application;
    }

    public function test_owner_sees_snapshot_metadata_without_questions_keys_scores_or_mutations(): void
    {
        $application = $this->application();
        // A different version must not replace the metadata pinned to the application.
        Assessment::create(['event_position_id' => $application->event_position_id, 'version' => 2,
            'revision' => 1, 'duration_minutes' => 90, 'is_published' => true]);
        $application->event->update(['assessment_deadline' => now()->addDays(20), 'decision_deadline' => now()->addDays(21)]);
        $before = $application->fresh()->getRawOriginal();
        $auditCount = AuditLog::count();
        $response = $this->actingAs($application->volunteer)->get(route('volunteer.applications.show', $application));
        $response->assertOk()->assertSeeInOrder(['Informasi assessment', 'Perkembangan lamaran', 'Pengumuman hasil', 'Jadwal tugas', 'Informasi posisi'], false)
            ->assertSee('Hasil seleksi paling lambat:')->assertSee('Hasil dapat diumumkan lebih awal.')
            ->assertSee('Buka Assessment')
            ->assertSee('1 soal')->assertSee('30 menit')->assertDontSee('90 menit')
            ->assertSee(route('volunteer.assessments.show', $application), false)
            ->assertDontSee('Pertanyaan privat assessment')->assertDontSee('Opsi rahasia')->assertDontSee('is_correct')
            ->assertDontSee('Hasil kecocokan');
        foreach (['assessment_deadline', 'decision_deadline'] as $field) {
            $response->assertSee(CarbonImmutable::parse($application->snapshot_json[$field])->setTimezone('Asia/Jakarta')->format('d/m/Y H:i'));
        }
        $this->assertSame($before, $application->fresh()->getRawOriginal());
        $this->assertSame($auditCount, AuditLog::count());
        $this->assertNull($application->fresh()->assessmentAttempt);
    }

    public function test_deadline_boundary_and_application_states_never_offer_an_active_start(): void
    {
        $application = $this->application();
        $this->actingAs($application->volunteer);
        foreach ([
            'submitted' => 'Lamaranmu menunggu proses pencocokan sebelum assessment.',
            'screening_failed' => 'Assessment tidak tersedia karena lamaranmu belum memenuhi syarat wajib screening.',
            'under_review' => 'Lamaranmu sedang ditinjau EO.',
            'accepted' => 'Informasi pengerjaan assessment belum tersedia.',
            'cancelled' => 'Assessment tidak tersedia karena event telah dibatalkan.',
        ] as $status => $message) {
            $application->update(['status' => $status]);
            $this->get(route('volunteer.applications.show', $application))->assertOk()->assertSee($message)
                ->assertDontSee(route('volunteer.assessments.show', $application), false);
        }
        $application->update(['status' => 'assessment_pending']);
        $this->travelTo(CarbonImmutable::parse($application->snapshot_json['assessment_deadline']));
        $this->get(route('volunteer.applications.show', $application))->assertOk()
            ->assertSee('Tenggat assessment telah berakhir.')->assertDontSee('Mulai assessment');
        $this->travelBack();
        $application->update(['status' => 'draft']);
        $this->get(route('volunteer.applications.show', $application))->assertOk()->assertDontSee('Informasi assessment');
    }

    public function test_missing_legacy_dates_and_unpublished_assessment_are_not_invented(): void
    {
        $application = $this->application();
        $application->update(['snapshot_json' => ['assessment_version' => 1, 'assessment_deadline' => null, 'decision_deadline' => null]]);
        Assessment::where('event_position_id', $application->event_position_id)->update(['is_published' => false]);
        $this->actingAs($application->volunteer)->get(route('volunteer.applications.show', $application))->assertOk()
            ->assertSee('Jadwal hasil seleksi belum tersedia.')->assertSee('Tenggat assessment belum tersedia.')
            ->assertDontSee('30 menit')->assertDontSee('1 soal')->assertDontSee('Mulai assessment');
    }

    public function test_other_volunteer_and_other_roles_cannot_read_this_information(): void
    {
        $application = $this->application();
        $url = route('volunteer.applications.show', $application);
        $this->get($url)->assertRedirect(route('login'));
        foreach (['volunteer', 'organizer', 'admin'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role, 'organizer_status' => 'active']))->get($url)->assertForbidden();
        }
    }

    public function test_terminal_layout_uses_source_results_and_preserves_notes_without_offering_draft_actions(): void
    {
        $application = $this->application();
        $this->actingAs($application->volunteer);
        foreach (['accepted', 'rejected', 'withdrawn', 'cancelled', 'screening_failed'] as $status) {
            $application->update(['status' => $status, 'decision_at' => now(), 'decision_reason' => '<script>catatan privat</script>']);
            $response = $this->get(route('volunteer.applications.show', $application))->assertOk()
                ->assertDontSee('Assessment selesai')
                ->assertSee('&lt;script&gt;catatan privat&lt;/script&gt;', false)
                ->assertDontSee('<script>catatan privat</script>', false)
                ->assertDontSee('Pengumuman hasil')
                ->assertDontSee('Unggah Dokumen Baru')
                ->assertDontSee(route('volunteer.assessments.show', $application), false);
            if ($status === 'accepted') {
                $response->assertSee('Selamat, kamu DITERIMA!')
                    ->assertSeeInOrder(['Kehadiran', 'Jadwal tugas', 'Informasi posisi']);
            } else {
                if ($status === 'rejected') {
                    $response->assertSee('Maaf, kamu BELUM TERPILIH');
                }
                $response->assertDontSee('Kehadiran')
                    ->assertSeeInOrder([$status === 'rejected' ? 'Hasil seleksi' : 'Status lamaran', 'Jadwal posisi', 'Informasi posisi']);
            }
        }
    }

    public function test_in_progress_assessment_offers_resume_without_resetting_its_timer(): void
    {
        $application = $this->application();
        $assessment = Assessment::where('event_position_id', $application->event_position_id)->firstOrFail();
        $attempt = AssessmentAttempt::create(['application_id' => $application->id, 'assessment_id' => $assessment->id,
            'version' => $assessment->version, 'status' => 'in_progress', 'started_at' => now()->subMinutes(5),
            'expires_at' => now()->addMinutes(25), 'total' => 1, 'revision' => 0]);
        $before = $attempt->fresh()->getRawOriginal();
        $this->actingAs($application->volunteer)->get(route('volunteer.applications.show', $application))
            ->assertOk()->assertSee('Assessment sedang dikerjakan')->assertSee('Lanjutkan Assessment')
            ->assertSee(route('volunteer.assessments.show', $application), false)->assertDontSee('Buka Assessment');
        $this->assertSame($before, $attempt->fresh()->getRawOriginal());
    }

    public function test_submitted_assessment_shows_answer_confirmation_only_in_its_detail(): void
    {
        $application = $this->application();
        $assessment = Assessment::where('event_position_id', $application->event_position_id)->firstOrFail();
        AssessmentAttempt::create(['application_id' => $application->id, 'assessment_id' => $assessment->id,
            'version' => $assessment->version, 'status' => 'submitted', 'started_at' => now()->subMinutes(5),
            'expires_at' => now()->addMinutes(25), 'submitted_at' => now(), 'total' => 1, 'revision' => 0]);
        $application->update(['status' => 'accepted', 'decision_at' => now()]);

        $this->actingAs($application->volunteer)->get(route('volunteer.applications.show', $application))
            ->assertOk()->assertSee('Jawabanmu sudah dikirim.')
            ->assertDontSee('Assessment selesai')->assertDontSee('Proses seleksi lamaran ini telah berakhir.');
    }
}
