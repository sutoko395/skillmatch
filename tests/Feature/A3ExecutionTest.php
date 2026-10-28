<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\AssessmentService;
use App\Services\AuditService;
use App\Services\EventPublicationService;
use App\Services\SelectionService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\DatabaseTestCase;
use Tests\Support\A3ExecutionFixture;

class A3ExecutionTest extends DatabaseTestCase
{
    use A3ExecutionFixture;

    public function test_approved_organizer_without_verified_email_can_accept_consistently_with_existing_organizer_flow(): void
    {
        $a = $this->reviewApplication();
        $owner = $a->event->organizer;
        $owner->forceFill(['email_verified_at' => null])->save();
        $this->actingAs($owner)->post(route('organizer.applications.accept', $a))
            ->assertRedirect(route('organizer.applications.show', $a))
            ->assertSessionHas('success');
        $this->assertSame('accepted', $a->fresh()->status);
        $this->assertSame('unrecorded', $a->fresh()->attendance->status);
        $this->assertFalse($owner->fresh()->hasVerifiedEmail());
    }

    public function test_unverified_email_does_not_allow_pending_or_inactive_organizer_to_select(): void
    {
        $a = $this->reviewApplication();
        $owner = $a->event->organizer;
        $owner->forceFill(['email_verified_at' => null, 'organizer_status' => 'pending'])->save();
        $this->actingAs($owner)->post(route('organizer.applications.accept', $a))
            ->assertRedirect(route('organizer.profile.pending'));
        $owner->forceFill(['organizer_status' => 'active', 'is_active' => false])->save();
        $this->actingAs($owner)->post(route('organizer.applications.accept', $a))->assertForbidden();
        $this->assertSame('under_review', $a->fresh()->status);
        $this->assertNull($a->fresh()->attendance);
    }

    public function test_accept_returns_to_candidate_even_when_previous_page_was_dashboard(): void
    {
        $a = $this->reviewApplication();
        $this->actingAs($a->event->organizer)->from(route('organizer.activity.index'))
            ->post(route('organizer.applications.accept', $a))
            ->assertRedirect(route('organizer.applications.show', $a))
            ->assertSessionHas('success', 'Pelamar diterima. Kehadiran belum dicatat.');
        $this->get(route('organizer.applications.show', $a))->assertOk()->assertSee('Diterima')->assertDontSee('Terima pelamar');
        $this->assertSame('unrecorded', $a->fresh()->attendance->status);
    }

    public function test_failed_accept_returns_to_candidate_with_reason_and_no_attendance(): void
    {
        $a = $this->reviewApplication();
        $b = $this->secondCandidate($a);
        app(SelectionService::class)->accept($a, $a->event->organizer);
        $this->actingAs($a->event->organizer)->from(route('organizer.activity.index'))
            ->post(route('organizer.applications.accept', $b))
            ->assertRedirect(route('organizer.applications.show', $b))
            ->assertSessionHas('error', 'Kuota posisi sudah penuh.');
        $this->get(route('organizer.applications.show', $b))->assertOk()->assertSee('Kuota posisi sudah penuh.');
        $this->assertSame('under_review', $b->fresh()->status);
        $this->assertNull($b->fresh()->attendance);
    }

    public function test_accept_is_idempotent_creates_unrecorded_and_enforces_quota(): void
    {
        $a = $this->reviewApplication();
        $other = $this->secondCandidate($a);
        $this->actingAs($a->event->organizer)->post(route('organizer.applications.accept', $a))->assertRedirect();
        $this->post(route('organizer.applications.accept', $a))->assertRedirect();
        $this->assertSame('accepted', $a->fresh()->status);
        $this->assertSame('unrecorded', $a->attendance->status);
        $this->assertSame(1, Attendance::where('application_id', $a->id)->count());
        $this->assertSame(1, AuditLog::where('subject_id', $a->id)->where('action', 'application.accepted')->count());
        $this->postJson(route('organizer.applications.accept', $other))->assertUnprocessable();
        $this->assertSame('under_review', $other->fresh()->status);
    }

    public function test_accept_only_under_review_and_reject_requires_reason_and_is_final(): void
    {
        $a = $this->reviewApplication();
        $this->actingAs($a->event->organizer)->postJson(route('organizer.applications.reject', $a), ['reason' => ''])->assertUnprocessable();
        $a->forceFill(['status' => 'assessment_pending'])->save();
        $this->postJson(route('organizer.applications.accept', $a))->assertUnprocessable();
        $a->forceFill(['status' => 'under_review'])->save();
        $this->post(route('organizer.applications.reject', $a), ['reason' => 'Kebutuhan posisi sudah berubah.'])->assertRedirect();
        $this->post(route('organizer.applications.reject', $a), ['reason' => 'Percobaan mengganti alasan.'])->assertRedirect();
        $this->assertSame('Kebutuhan posisi sudah berubah.', $a->fresh()->decision_reason);
        $this->postJson(route('organizer.applications.accept', $a))->assertUnprocessable();
        $this->assertNull($a->fresh()->attendance);
    }

    public function test_overlap_rejected_and_adjacent_snapshot_intervals_allowed(): void
    {
        $a = $this->reviewApplication();
        $b = $this->reviewApplication();
        $b->forceFill(['volunteer_id' => $a->volunteer_id])->save();
        $service = app(SelectionService::class);
        $service->accept($a, $a->event->organizer);
        try {
            $service->accept($b, $b->event->organizer);
            $this->fail('Overlap must be rejected.');
        } catch (ValidationException) {
            $this->assertSame('under_review', $b->fresh()->status);
        }
        $snapshot = $b->snapshot_json;
        $end = CarbonImmutable::parse($a->snapshot_json['schedules'][0]['end']);
        $snapshot['schedules'] = [['start' => $end->toISOString(), 'end' => $end->addHour()->toISOString()]];
        $b->forceFill(['snapshot_json' => $snapshot])->save();
        $service->accept($b, $b->event->organizer);
        $this->assertSame('accepted', $b->fresh()->status);
    }

    public function test_other_owner_volunteer_and_admin_cannot_select_or_record_attendance(): void
    {
        $a = $this->reviewApplication();
        app(SelectionService::class)->accept($a, $a->event->organizer);
        foreach ([User::factory()->create(['role' => 'organizer', 'organizer_status' => 'active']), $a->volunteer,
            User::factory()->create(['role' => 'admin'])] as $actor) {
            $this->actingAs($actor)->postJson(route('organizer.applications.accept', $a))->assertForbidden();
            $this->postJson(route('organizer.applications.reject', $a), ['reason' => 'Tidak diizinkan.'])->assertForbidden();
            $this->patchJson(route('organizer.attendances.update', $a->attendance), ['status' => 'present', 'revision' => 0])->assertForbidden();
            $this->get(route('organizer.execution.show', $a->event))->assertForbidden();
        }
    }

    public function test_attendance_completion_history_and_reasoned_correction(): void
    {
        $a = $this->reviewApplication();
        app(SelectionService::class)->accept($a, $a->event->organizer);
        $event = $a->event;
        $this->actingAs($event->organizer)->patchJson(route('organizer.attendances.update', $a->attendance), ['status' => 'present', 'revision' => 0])->assertUnprocessable();
        $this->postJson(route('organizer.events.complete', $event), ['confirmed' => 1])->assertUnprocessable();
        $this->travelTo($event->ends_at->addMinute());
        $this->postJson(route('organizer.events.complete', $event), ['confirmed' => 1])->assertUnprocessable();
        $this->assertSame('unrecorded', $a->attendance->fresh()->status);
        $this->patch(route('organizer.attendances.update', $a->attendance), ['status' => 'present', 'revision' => 0])->assertRedirect();
        $this->post(route('organizer.events.complete', $event), ['confirmed' => 1])->assertRedirect();
        $completed = $event->fresh()->completed_at;
        $this->post(route('organizer.events.complete', $event), ['confirmed' => 1])->assertRedirect();
        $this->assertTrue($event->fresh()->completed_at->equalTo($completed));
        $this->actingAs($a->volunteer)->get(route('volunteer.history.index'))->assertOk()->assertSee('Partisipasi berhasil');
        $this->actingAs($event->organizer)->patchJson(route('organizer.attendances.update', $a->attendance), ['status' => 'absent', 'revision' => 1])->assertUnprocessable();
        $this->patch(route('organizer.attendances.update', $a->attendance), ['status' => 'absent', 'revision' => 1, 'reason' => 'Koreksi daftar kehadiran lapangan.'])->assertRedirect();
        $this->actingAs($a->volunteer)->get(route('volunteer.history.index'))->assertOk()->assertSee('Tidak hadir')->assertDontSee('Partisipasi berhasil');
        $this->assertSame('completed', $event->fresh()->lifecycle_status);
        $this->travelBack();
    }

    public function test_close_selection_cancels_attempt_then_zero_participant_completion_requires_confirmation(): void
    {
        $a = $this->reviewApplication();
        $a->forceFill(['status' => 'assessment_pending'])->save();
        $a->assessmentAttempt->forceFill(['status' => 'in_progress'])->save();
        $this->travelTo($a->event->ends_at->addMinute());
        $this->actingAs($a->event->organizer)->postJson(route('organizer.events.complete', $a->event), ['confirmed' => 1, 'confirm_zero' => 1])->assertUnprocessable();
        $this->post(route('organizer.events.close-selection', $a->event), ['reason' => 'Rekrutmen telah berakhir.', 'confirmed' => 1])->assertRedirect();
        $this->assertSame('rejected', $a->fresh()->status);
        $this->assertSame('cancelled', $a->assessmentAttempt->fresh()->status);
        $this->postJson(route('organizer.events.complete', $a->event), ['confirmed' => 1])->assertUnprocessable();
        $this->post(route('organizer.events.complete', $a->event), ['confirmed' => 1, 'confirm_zero' => 1])->assertRedirect();
        $this->assertSame('completed', $a->event->fresh()->lifecycle_status);
        $this->travelBack();
    }

    public function test_withdrawal_stops_attempt_and_accepted_requires_organizer_reason(): void
    {
        $a = $this->reviewApplication();
        $a->forceFill(['status' => 'assessment_pending'])->save();
        $a->assessmentAttempt->forceFill(['status' => 'in_progress'])->save();
        $counter = $a->event->entitlement->submitted_applications;
        $this->actingAs($a->event->organizer)->postJson(route('organizer.applications.withdraw', $a), ['reason' => 'Bukan jalur penolakan.'])->assertForbidden();
        $this->actingAs($a->volunteer)->post(route('volunteer.applications.withdraw', $a))->assertRedirect();
        $this->post(route('volunteer.applications.withdraw', $a))->assertRedirect();
        $this->assertSame('cancelled', $a->assessmentAttempt->fresh()->status);
        app(AssessmentService::class)->expire($a);
        $this->assertSame('withdrawn', $a->fresh()->status);
        $this->assertSame($counter, $a->event->entitlement->fresh()->submitted_applications);
        $b = $this->reviewApplication();
        app(SelectionService::class)->accept($b, $b->event->organizer);
        $this->actingAs($b->volunteer)->postJson(route('volunteer.applications.withdraw', $b))->assertForbidden();
        $this->actingAs($b->event->organizer)->postJson(route('organizer.applications.withdraw', $b))->assertUnprocessable();
        $this->post(route('organizer.applications.withdraw', $b), ['reason' => 'Peserta meminta pengunduran diri.'])->assertRedirect();
        $this->assertSame('withdrawn', $b->fresh()->status);
        $this->assertNull($b->fresh()->attendance);
    }

    public function test_real_cancellation_is_atomic_and_late_attempt_cannot_revive_application(): void
    {
        $a = $this->reviewApplication();
        $a->forceFill(['status' => 'assessment_pending'])->save();
        $a->assessmentAttempt->forceFill(['status' => 'in_progress'])->save();
        try {
            DB::transaction(function () use ($a) {
                app(EventPublicationService::class)->cancel($a->event, $a->event->organizer, 'Kegiatan dibatalkan panitia.');
                throw new \RuntimeException('Rollback probe');
            });
        } catch (\RuntimeException) {
            $this->assertSame('assessment_pending', $a->fresh()->status);
            $this->assertSame('in_progress', $a->assessmentAttempt->fresh()->status);
        }
        app(EventPublicationService::class)->cancel($a->event, $a->event->organizer, 'Kegiatan dibatalkan panitia.');
        app(EventPublicationService::class)->cancel($a->event, $a->event->organizer, 'Kegiatan dibatalkan panitia.');
        app(AssessmentService::class)->expire($a);
        $this->assertSame('cancelled', $a->fresh()->status);
        $this->assertSame('cancelled', $a->assessmentAttempt->fresh()->status);
        $this->actingAs($a->volunteer)->get(route('volunteer.history.index'))->assertOk()->assertSee('Dibatalkan')->assertDontSee('Partisipasi berhasil');
    }

    public function test_ready_tab_contains_only_under_review_and_uses_score_order(): void
    {
        $a = $this->reviewApplication();
        $b = $this->secondCandidate($a);
        $b->forceFill(['status' => 'accepted'])->save();
        $this->actingAs($a->event->organizer)->get(route('organizer.applications.index', [$a->event, 'tab' => 'ready']))
            ->assertOk()->assertSee($a->volunteer->name)->assertDontSee($b->volunteer->name);
        $this->get(route('organizer.execution.show', $a->event))->assertOk()->assertSee('Pelaksanaan');
    }

    public function test_audit_failure_rolls_back_acceptance_and_attendance(): void
    {
        $a = $this->reviewApplication();
        $this->mock(AuditService::class)->shouldReceive('record')->andThrow(new \RuntimeException('Audit failed'));
        try {
            app(SelectionService::class)->accept($a, $a->event->organizer);
            $this->fail('Must roll back');
        } catch (\RuntimeException) {
            $this->assertSame('under_review', $a->fresh()->status);
            $this->assertNull($a->fresh()->attendance);
        }
    }

    public function test_selection_deadline_inactive_candidate_and_stale_attendance_are_rejected(): void
    {
        $a = $this->reviewApplication();
        $this->actingAs($a->event->organizer);
        $a->volunteer->update(['is_active' => false]);
        $this->postJson(route('organizer.applications.accept', $a))->assertUnprocessable();
        $a->volunteer->update(['is_active' => true]);
        $this->travelTo($a->event->decision_deadline);
        $this->postJson(route('organizer.applications.accept', $a))->assertUnprocessable();
        $this->travelBack();
        app(SelectionService::class)->accept($a, $a->event->organizer);
        $this->travelTo($a->event->starts_at);
        $this->patch(route('organizer.attendances.update', $a->attendance), ['status' => 'present', 'revision' => 0])->assertRedirect();
        $this->patchJson(route('organizer.attendances.update', $a->attendance), ['status' => 'absent', 'revision' => 0])->assertUnprocessable();
        $this->assertSame('present', $a->attendance->fresh()->status);
        $this->travelBack();
    }

    public function test_ready_candidates_order_matching_then_assessment_then_submission(): void
    {
        $a = $this->reviewApplication();
        $b = $this->secondCandidate($a);
        $b->matchResult()->create(['skill_score' => 100, 'availability_score' => 100, 'location_score' => 100,
            'total_score' => 100, 'rule_version' => 'match-v1.4', 'breakdown_json' => []]);
        $c = $this->secondCandidate($a);
        $this->actingAs($a->event->organizer)->get(route('organizer.applications.index', [$a->event, 'tab' => 'ready']))
            ->assertOk()->assertViewHas('applications', fn ($rows) => $rows->pluck('id')->all() === [$b->id, $a->id, $c->id]);
    }

    public function test_notification_replay_preserves_transition_key_and_reports_missing_a4(): void
    {
        $a = $this->reviewApplication();
        $after = AuditLog::max('id');
        app(SelectionService::class)->accept($a, $a->event->organizer);
        $this->artisan('a3:retry-notifications', ['--after' => $after])->assertFailed();
        $adapter = new class
        {
            public array $keys = [];

            public function enqueue(string $key, User $recipient, array $payload): void
            {
                $this->keys[$key] = $payload;
            }
        };
        $this->app->instance('App\\Services\\NotificationService', $adapter);
        $this->artisan('a3:retry-notifications', ['--after' => $after])->assertSuccessful();
        $this->artisan('a3:retry-notifications', ['--after' => $after])->assertSuccessful();
        $this->assertCount(1, $adapter->keys);
        $this->assertArrayHasKey('application.accepted:'.$a->id.':'.$a->fresh()->revision.':'.$a->volunteer_id, $adapter->keys);
    }
}
