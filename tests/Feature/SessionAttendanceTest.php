<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\NotificationOutbox;
use App\Models\User;
use App\Services\AttendanceSessionService;
use App\Services\AuditService;
use App\Services\SelectionService;
use Tests\DatabaseTestCase;
use Tests\Support\A3ExecutionFixture;

class SessionAttendanceTest extends DatabaseTestCase
{
    use A3ExecutionFixture;

    private function participant(bool $accept = true): Application
    {
        $application = $this->reviewApplication();
        $start = $application->event->starts_at->setTimezone('Asia/Jakarta');
        $snapshot = $application->snapshot_json;
        $snapshot['schedules'] = [
            ['start' => $start->toIso8601String(), 'end' => $start->addHours(3)->toIso8601String()],
            ['start' => $start->addHours(5)->toIso8601String(), 'end' => $start->addHours(7)->toIso8601String()],
            ['start' => $start->setTime(21, 0)->toIso8601String(), 'end' => $start->addDay()->setTime(3, 0)->toIso8601String()],
        ];
        $application->forceFill(['snapshot_json' => $snapshot])->save();
        $application->event->forceFill(['ends_at' => $start->addDay()->setTime(17, 0)->utc()])->save();
        if ($accept) {
            app(SelectionService::class)->accept($application, $application->event->organizer);
        }

        return $application->fresh(['event', 'attendance.sessions']);
    }

    public function test_notes_save_independently_from_attendance_and_reject_stale_changes(): void
    {
        $application = $this->participant();
        $slot = $application->attendance->sessions->first();
        $this->travelTo($slot->starts_at->addMinute());
        $this->actingAs($application->event->organizer);
        $url = route('organizer.attendance-sessions.note', $slot);
        $before = array_intersect_key($slot->fresh()->getAttributes(), array_flip(['status', 'recorded_at', 'recorded_by']));
        $outbox = NotificationOutbox::count();
        $audit = AuditLog::count();
        $this->patch($url, ['revision' => 0, 'note' => 'Izin datang terlambat.'])->assertRedirect()
            ->assertSessionHas('attendance_application_id', $application->id);
        $this->assertSame($before, array_intersect_key($slot->fresh()->getAttributes(), array_flip(['status', 'recorded_at', 'recorded_by'])));
        $this->assertSame('unrecorded', $application->attendance->fresh()->status);
        $this->assertSame('upcoming', $application->event->fresh()->lifecycle_status);
        $this->assertSame(1, $slot->fresh()->revision);
        $this->assertSame($audit + 1, AuditLog::count());
        $this->assertSame($outbox, NotificationOutbox::count());
        $this->patch($url, ['revision' => 0, 'note' => 'Izin datang terlambat.'])->assertRedirect();
        $this->assertSame($audit + 1, AuditLog::count());
        $this->patchJson($url, ['revision' => 0, 'note' => 'Menimpa catatan baru.'])->assertUnprocessable();
        $this->patchJson(route('organizer.attendance-sessions.update', $slot), ['revision' => 0, 'status' => 'present'])->assertUnprocessable();
        $this->patch(route('organizer.attendance-sessions.update', $slot), ['revision' => 1, 'status' => 'present', 'note' => $slot->fresh()->note])->assertRedirect();
        $recorded = array_intersect_key($slot->fresh()->getAttributes(), array_flip(['status', 'recorded_at', 'recorded_by']));
        $this->patch($url, ['revision' => 2, 'note' => 'Tiba di meja panitia.'])->assertRedirect();
        $this->assertSame($recorded, array_intersect_key($slot->fresh()->getAttributes(), array_flip(['status', 'recorded_at', 'recorded_by'])));
        $this->actingAs($application->volunteer)->get(route('volunteer.applications.show', $application))->assertOk()->assertSee('Tiba di meja panitia.');
        $this->actingAs($application->event->organizer)->patch($url, ['revision' => 3, 'note' => ''])->assertRedirect();
        $this->assertNull($slot->fresh()->note);
        $this->assertSame('present', $slot->fresh()->status);
    }

    public function test_note_endpoint_retains_time_owner_validation_and_final_locks(): void
    {
        $application = $this->participant();
        $slot = $application->attendance->sessions->first();
        $url = route('organizer.attendance-sessions.note', $slot);
        $payload = ['revision' => 0, 'note' => 'Catatan uji.'];
        $this->patch($url, $payload)->assertRedirect(route('login'));
        foreach (['volunteer', 'admin', 'organizer'] as $role) {
            $actor = User::factory()->create(['role' => $role, 'organizer_status' => 'active']);
            $this->actingAs($actor)->patchJson($url, $payload)->assertForbidden();
        }
        $this->actingAs($application->event->organizer);
        $this->travelTo($slot->starts_at->subMinute());
        $this->patchJson($url, $payload)->assertUnprocessable();
        $this->travelTo($slot->starts_at->addMinute());
        $this->patchJson($url, ['revision' => -1, 'note' => str_repeat('a', 1001)])->assertUnprocessable()->assertJsonValidationErrors(['revision', 'note']);
        foreach (['completed', 'cancelled'] as $state) {
            $application->event->forceFill(['lifecycle_status' => $state])->save();
            $this->patchJson($url, $payload)->assertUnprocessable();
            $this->patchJson($url, ['revision' => 0, 'note' => null])->assertUnprocessable();
        }
        $this->assertNull($slot->fresh()->note);
        $this->assertSame(0, $slot->fresh()->revision);
        $this->assertSame('unrecorded', $slot->fresh()->status);
    }

    public function test_unrecorded_legacy_sessions_initialize_without_manual_confirmation_or_get_writes(): void
    {
        $application = $this->participant(false);
        $application->forceFill(['status' => 'accepted'])->save();
        $legacy = Attendance::create(['application_id' => $application->id, 'status' => 'unrecorded', 'note' => 'Catatan lama belum absen']);
        $missing = $this->secondCandidate($application);
        $missing->forceFill(['status' => 'accepted'])->save();
        $recorded = $this->secondCandidate($application);
        $recorded->forceFill(['status' => 'accepted'])->save();
        $recordedAttendance = Attendance::create(['application_id' => $recorded->id, 'status' => 'present', 'note' => 'Bukti lama']);
        $this->actingAs($application->event->organizer);
        $url = route('organizer.attendance-sessions.initialize', $application->event);
        $this->get(route('organizer.execution.show', $application->event))->assertOk()
            ->assertViewHas('pendingSessionApplications', [$application->id, $missing->id])
            ->assertSee('<meta name="csrf-token" content="'.csrf_token().'">', false)
            ->assertSee('Memuat sesi peserta...');
        $this->assertSame(0, $legacy->sessions()->count());
        $this->assertNull($missing->fresh()->attendance);
        $payload = ['applications' => [$application->id, $missing->id, $recorded->id]];
        $this->postJson($url, $payload)->assertOk()->assertJson(['prepared' => 2]);
        $this->assertSame(4, $legacy->sessions()->count());
        $this->assertSame(4, $missing->fresh()->attendance->sessions()->count());
        $this->assertSame('unrecorded', $legacy->fresh()->status);
        $this->assertSame('Catatan lama belum absen', $legacy->fresh()->legacy_record['note']);
        $this->assertFalse($recordedAttendance->fresh()->uses_sessions);
        $this->assertSame('present', $recordedAttendance->fresh()->status);
        $this->assertSame('Bukti lama', $recordedAttendance->fresh()->note);
        $audit = AuditLog::count();
        $this->postJson($url, $payload)->assertOk()->assertJson(['prepared' => 0]);
        $this->assertSame($audit, AuditLog::count());
        $this->get(route('organizer.execution.show', $application->event))->assertOk()->assertViewHas('pendingSessionApplications', []);
    }

    public function test_automatic_preparation_preserves_ownership_terminal_and_snapshot_boundaries(): void
    {
        $application = $this->participant(false);
        $application->forceFill(['status' => 'accepted'])->save();
        $url = route('organizer.attendance-sessions.initialize', $application->event);
        $payload = ['applications' => [$application->id]];
        $this->post($url, $payload)->assertRedirect(route('login'));
        foreach (['volunteer', 'admin', 'organizer'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role, 'organizer_status' => 'active']))->postJson($url, $payload)->assertForbidden();
        }
        $this->actingAs($application->event->organizer);
        $foreign = $this->participant(false);
        $this->postJson($url, ['applications' => [$foreign->id]])->assertUnprocessable();
        $this->postJson($url, ['applications' => [$application->id, $application->id]])->assertUnprocessable();
        $this->postJson($url, ['applications' => []])->assertUnprocessable();
        $this->postJson($url, ['applications' => range(1, 101)])->assertUnprocessable();
        $pending = $this->secondCandidate($application);
        $this->postJson($url, ['applications' => [$pending->id]])->assertUnprocessable();
        foreach (['completed', 'cancelled'] as $state) {
            $application->event->forceFill(['lifecycle_status' => $state])->save();
            $this->postJson($url, $payload)->assertUnprocessable();
            $this->get(route('organizer.execution.show', $application->event))->assertOk()->assertViewHas('pendingSessionApplications', []);
        }
        $application->event->forceFill(['lifecycle_status' => 'upcoming'])->save();
        $snapshot = $application->snapshot_json;
        $snapshot['schedules'] = [];
        $application->forceFill(['snapshot_json' => $snapshot])->save();
        $this->postJson($url, $payload)->assertUnprocessable();
        $this->assertNull($application->fresh()->attendance);
        $this->assertNull($foreign->fresh()->attendance);
    }

    public function test_acceptance_creates_each_shift_and_splits_midnight_in_wib_once(): void
    {
        $application = $this->participant();
        $attendance = $application->attendance;
        $this->assertTrue($attendance->uses_sessions);
        $this->assertSame(4, $attendance->sessions->count());
        $this->assertSame(3, $attendance->sessions->where('session_date', $attendance->sessions->first()->session_date)->count());
        $this->assertSame('00:00', $attendance->sessions->last()->starts_at->setTimezone('Asia/Jakarta')->format('H:i'));
        $this->assertSame('03:00', $attendance->sessions->last()->ends_at->setTimezone('Asia/Jakarta')->format('H:i'));
        $this->assertSame(4, $attendance->sessions->where('status', 'unrecorded')->count());
        app(SelectionService::class)->accept($application, $application->event->organizer);
        $this->assertSame(4, $attendance->sessions()->count());
        $this->assertSame(1, AuditLog::where('action', 'attendance.sessions_prepared')->where('subject_id', $attendance->id)->count());
    }

    public function test_overlaps_are_merged_but_adjacent_shifts_and_midnight_end_do_not_create_duplicates(): void
    {
        $application = new Application(['snapshot_json' => ['schedules' => [
            ['start' => '2026-11-10T09:00:00+07:00', 'end' => '2026-11-10T12:00:00+07:00'],
            ['start' => '2026-11-10T10:00:00+07:00', 'end' => '2026-11-10T11:00:00+07:00'],
            ['start' => '2026-11-10T12:00:00+07:00', 'end' => '2026-11-11T00:00:00+07:00'],
        ]]]);
        $definitions = app(AttendanceSessionService::class)->definitions($application);
        $this->assertCount(2, $definitions);
        $this->assertSame(['2026-11-10', '2026-11-10'], array_column($definitions, 'session_date'));
        $this->assertSame('2026-11-10 17:00:00', $definitions[1]['ends_at']->toDateTimeString());
    }

    public function test_future_session_and_ambiguous_event_update_are_rejected_and_day_filter_is_specific(): void
    {
        $application = $this->participant();
        $first = $application->attendance->sessions->first();
        $last = $application->attendance->sessions->last();
        $this->actingAs($application->event->organizer);
        $this->patchJson(route('organizer.attendance-sessions.update', $first), ['status' => 'present', 'revision' => 0])->assertUnprocessable();
        $this->travelTo($first->starts_at);
        $this->patch(route('organizer.attendance-sessions.update', $first), ['status' => 'present', 'revision' => 0])->assertRedirect();
        $this->patchJson(route('organizer.attendance-sessions.update', $last), ['status' => 'present', 'revision' => 0,
            'starts_at' => now()->subDay()->toIso8601String()])->assertUnprocessable();
        $this->assertSame('unrecorded', $last->fresh()->status);
        $this->assertSame('unrecorded', $application->attendance->fresh()->status);
        $this->patchJson(route('organizer.attendances.update', $application->attendance), ['status' => 'present', 'revision' => 1])->assertUnprocessable();
        $this->get(route('organizer.execution.show', [$application->event, 'session_date' => $last->session_date->toDateString()]))
            ->assertOk()->assertSee($last->session_date->locale('id')->translatedFormat('d M Y'))->assertDontSee('Siapkan absensi per sesi');
        $this->getJson(route('organizer.execution.show', [$application->event, 'session_date' => 'invalid']))->assertUnprocessable();
        $this->travelBack();
    }

    public function test_every_session_is_required_for_completion_and_then_locked(): void
    {
        $application = $this->participant();
        $this->travelTo($application->event->ends_at->addMinute());
        $this->actingAs($application->event->organizer);
        $complete = route('organizer.events.complete', $application->event);
        // A cached participant summary cannot bypass unfinished session records.
        $application->attendance->forceFill(['status' => 'present'])->save();
        $this->postJson($complete, ['confirmed' => 1])->assertUnprocessable();
        $sessions = $application->attendance->sessions;
        foreach ($sessions as $index => $session) {
            $this->patch(route('organizer.attendance-sessions.update', $session), ['status' => $index === 1 ? 'absent' : 'present', 'revision' => 0, 'note' => 'Catatan final sesi '.$index])->assertRedirect();
        }
        $this->assertSame('absent', $application->attendance->fresh()->status);
        $this->post($complete, ['confirmed' => 1])->assertRedirect();
        $this->actingAs($application->volunteer)->get(route('volunteer.history.index'))->assertOk()->assertSee('Partisipasi tidak lengkap')->assertSee('3/4 sesi hadir');
        $absent = $sessions[1];
        $this->actingAs($application->event->organizer)->patchJson(route('organizer.attendance-sessions.update', $absent), ['status' => 'present', 'revision' => 1])->assertUnprocessable();
        $input = ['status' => 'present', 'revision' => 1, 'reason' => 'Koreksi berdasarkan daftar hadir sesi'];
        $before = $absent->fresh()->getAttributes();
        $auditCount = AuditLog::count();
        $outboxCount = NotificationOutbox::count();
        $this->patchJson(route('organizer.attendance-sessions.update', $absent), $input)->assertUnprocessable()->assertJsonValidationErrors('attendance');
        $this->patchJson(route('organizer.attendance-sessions.update', $absent), ['status' => 'absent', 'revision' => 1])->assertUnprocessable();
        $this->postJson(route('organizer.attendances.prepare-sessions', $application->attendance), ['confirmed' => 1, 'revision' => $application->attendance->fresh()->revision])->assertUnprocessable();
        $this->assertSame($before, $absent->fresh()->getAttributes());
        $this->assertSame('absent', $application->attendance->fresh()->status);
        $this->assertSame($auditCount, AuditLog::count());
        $this->assertSame($outboxCount, NotificationOutbox::count());
        $this->get(route('organizer.execution.show', $application->event))->assertOk()
            ->assertSee('Absensi sudah final')->assertSee('Catatan final sesi 1')->assertDontSee('Simpan kehadiran')->assertDontSee('Alasan koreksi');
        $this->actingAs($application->volunteer)->get(route('volunteer.history.index'))->assertOk()->assertSee('Partisipasi tidak lengkap')->assertSee('3/4 sesi hadir');
        $this->travelBack();
    }

    public function test_session_ownership_stale_revision_and_cancelled_event_are_checked_server_side(): void
    {
        $application = $this->participant();
        $session = $application->attendance->sessions->first();
        $url = route('organizer.attendance-sessions.update', $session);
        $this->travelTo($application->event->ends_at->addMinute());
        foreach ([User::factory()->create(['role' => 'organizer', 'organizer_status' => 'active']), $application->volunteer,
            User::factory()->create(['role' => 'admin'])] as $actor) {
            $this->actingAs($actor)->patchJson($url, ['status' => 'present', 'revision' => 0])->assertForbidden();
        }
        $this->actingAs($application->event->organizer)->patch($url, ['status' => 'present', 'revision' => 0])->assertRedirect();
        $this->patchJson($url, ['status' => 'absent', 'revision' => 0])->assertUnprocessable();
        $application->event->forceFill(['lifecycle_status' => 'cancelled'])->save();
        $this->patchJson($url, ['status' => 'absent', 'revision' => 1])->assertUnprocessable();
        $this->assertSame('present', $session->fresh()->status);
        $this->travelBack();
    }

    public function test_legacy_record_is_not_spread_across_days_and_explicit_conversion_preserves_original(): void
    {
        $application = $this->participant(false);
        $application->forceFill(['status' => 'accepted'])->save();
        $legacy = Attendance::create(['application_id' => $application->id, 'status' => 'present', 'recorded_at' => now(),
            'recorded_by' => $application->event->organizer_id, 'note' => 'Catatan kehadiran lama', 'revision' => 3]);
        $this->actingAs($application->event->organizer);
        $url = route('organizer.attendances.prepare-sessions', $legacy);
        $this->postJson($url, ['confirmed' => 1, 'revision' => 3])->assertUnprocessable();
        $this->assertSame('present', $legacy->fresh()->status);
        $this->assertSame(0, $legacy->sessions()->count());
        $input = ['confirmed' => 1, 'revision' => 3, 'reason' => 'Mulai pencatatan setiap sesi sesuai daftar hadir'];
        $this->post($url, $input)->assertRedirect();
        $this->post($url, $input)->assertRedirect();
        $this->assertSame('present', $legacy->fresh()->legacy_record['status']);
        $this->assertSame('Catatan kehadiran lama', $legacy->fresh()->legacy_record['note']);
        $this->assertSame('unrecorded', $legacy->fresh()->status);
        $this->assertSame(4, $legacy->sessions()->where('status', 'unrecorded')->count());
        $this->assertSame(1, AuditLog::where('action', 'attendance.sessions_prepared')->where('subject_id', $legacy->id)->count());
        $this->postJson(route('organizer.applications.withdraw', $application), ['reason' => 'Permintaan pengunduran peserta'])->assertUnprocessable();
        $this->assertSame('Catatan kehadiran lama', $legacy->fresh()->legacy_record['note']);
    }

    public function test_completed_legacy_history_stays_readable_and_unrecorded_missing_attendance_can_be_prepared(): void
    {
        $legacyApplication = $this->participant(false);
        $legacyApplication->forceFill(['status' => 'accepted'])->save();
        $attendance = Attendance::create(['application_id' => $legacyApplication->id, 'status' => 'present', 'note' => 'Catatan final lama']);
        $legacyApplication->event->forceFill(['lifecycle_status' => 'completed', 'completed_at' => now()])->save();
        $this->actingAs($legacyApplication->volunteer)->get(route('volunteer.history.index'))->assertOk()
            ->assertSee('Partisipasi berhasil')->assertSee('Catatan lama per kegiatan');
        $this->actingAs($legacyApplication->event->organizer)->postJson(route('organizer.attendances.prepare-sessions', $attendance), ['confirmed' => 1, 'revision' => 0])->assertUnprocessable();
        $before = $attendance->fresh()->getAttributes();
        $auditCount = AuditLog::count();
        $this->patchJson(route('organizer.attendances.update', $attendance), ['status' => 'absent', 'revision' => 0, 'reason' => 'Koreksi lama tidak diizinkan'])->assertUnprocessable();
        $this->patchJson(route('organizer.attendances.update', $attendance), ['status' => 'present', 'revision' => 0])->assertUnprocessable();
        $this->assertSame($before, $attendance->fresh()->getAttributes());
        $this->assertSame($auditCount, AuditLog::count());
        $this->get(route('organizer.execution.show', $legacyApplication->event))->assertOk()
            ->assertSee('Absensi sudah final')->assertSee('Catatan final lama')->assertDontSee('Simpan kehadiran')->assertDontSee('Alasan koreksi');

        $application = $this->participant(false);
        $application->forceFill(['status' => 'accepted'])->save();
        $this->actingAs($application->event->organizer)->post(route('organizer.applications.prepare-attendance-sessions', $application), ['confirmed' => 1])->assertRedirect();
        $this->assertSame(4, $application->fresh()->attendance->sessions()->count());
    }

    public function test_recording_rolls_back_on_audit_failure(): void
    {
        $application = $this->participant();
        $session = $application->attendance->sessions->first();
        $this->travelTo($application->event->ends_at->addMinute());
        $this->mock(AuditService::class, fn ($mock) => $mock->shouldReceive('record')->andThrow(new \RuntimeException('Synthetic audit failure')));
        try {
            app(AttendanceSessionService::class)->record($session, $application->event->organizer, ['status' => 'present', 'revision' => 0]);
            $this->fail('Expected rollback');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Synthetic audit failure', $exception->getMessage());
        }
        $this->assertSame('unrecorded', $session->fresh()->status);
        $this->assertSame(0, $session->fresh()->revision);
        $this->assertSame('unrecorded', $application->attendance->fresh()->status);
        $this->travelBack();
    }

    public function test_conversion_rolls_back_on_audit_failure_and_invalid_snapshot_never_creates_fake_sessions(): void
    {
        $application = $this->participant(false);
        $application->forceFill(['status' => 'accepted'])->save();
        $legacy = Attendance::create(['application_id' => $application->id, 'status' => 'present', 'note' => 'Original legacy attendance']);
        $this->mock(AuditService::class, fn ($mock) => $mock->shouldReceive('record')->andThrow(new \RuntimeException('Synthetic audit failure')));
        try {
            app(AttendanceSessionService::class)->prepare($legacy, $application->event->organizer,
                ['confirmed' => 1, 'revision' => 0, 'reason' => 'Konversi berdasarkan jadwal tugas']);
            $this->fail('Expected rollback');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Synthetic audit failure', $exception->getMessage());
        }
        $this->assertSame('present', $legacy->fresh()->status);
        $this->assertNull($legacy->fresh()->legacy_record);
        $this->assertSame(0, $legacy->sessions()->count());
        $this->app->forgetInstance(AuditService::class);
        $legacy->delete();
        $application->forceFill(['snapshot_json' => ['schedules' => []]])->save();
        $this->actingAs($application->event->organizer)->postJson(route('organizer.applications.prepare-attendance-sessions', $application), ['confirmed' => 1])->assertUnprocessable();
        $this->assertNull($application->fresh()->attendance);
    }
}
