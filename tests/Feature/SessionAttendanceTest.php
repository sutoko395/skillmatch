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
            ->assertOk()->assertSee($last->session_date->format('d/m/Y'))->assertDontSee('Siapkan absensi per sesi');
        $this->getJson(route('organizer.execution.show', [$application->event, 'session_date' => 'invalid']))->assertUnprocessable();
        $this->travelBack();
    }

    public function test_every_session_is_required_for_completion_partial_participation_and_reasoned_correction(): void
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
            $this->patch(route('organizer.attendance-sessions.update', $session), ['status' => $index === 1 ? 'absent' : 'present', 'revision' => 0])->assertRedirect();
        }
        $this->assertSame('absent', $application->attendance->fresh()->status);
        $this->post($complete, ['confirmed' => 1])->assertRedirect();
        $this->actingAs($application->volunteer)->get(route('volunteer.history.index'))->assertOk()->assertSee('Partisipasi tidak lengkap')->assertSee('3/4 sesi hadir');
        $absent = $sessions[1];
        $this->actingAs($application->event->organizer)->patchJson(route('organizer.attendance-sessions.update', $absent), ['status' => 'present', 'revision' => 1])->assertUnprocessable();
        $input = ['status' => 'present', 'revision' => 1, 'reason' => 'Koreksi berdasarkan daftar hadir sesi'];
        $this->patch(route('organizer.attendance-sessions.update', $absent), $input)->assertRedirect();
        $this->patch(route('organizer.attendance-sessions.update', $absent), $input)->assertRedirect();
        $this->assertSame(2, $absent->fresh()->revision);
        $this->assertSame('present', $application->attendance->fresh()->status);
        $this->assertSame(2, AuditLog::where('action', 'attendance.session_recorded')->where('subject_id', $absent->id)->count());
        $this->artisan('a3:retry-notifications', ['--after' => 0, '--limit' => 1000])->assertSuccessful();
        $this->assertSame(1, NotificationOutbox::where('dedupe_key', 'attendance.session_recorded:'.$absent->id.':2:'.$application->volunteer_id)->count());
        $this->actingAs($application->volunteer)->get(route('volunteer.history.index'))->assertOk()->assertSee('Partisipasi berhasil')->assertSee('4/4 sesi hadir');
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
        $attendance = Attendance::create(['application_id' => $legacyApplication->id, 'status' => 'present']);
        $legacyApplication->event->forceFill(['lifecycle_status' => 'completed', 'completed_at' => now()])->save();
        $this->actingAs($legacyApplication->volunteer)->get(route('volunteer.history.index'))->assertOk()
            ->assertSee('Partisipasi berhasil')->assertSee('Catatan lama per kegiatan');
        $this->actingAs($legacyApplication->event->organizer)->postJson(route('organizer.attendances.prepare-sessions', $attendance), ['confirmed' => 1, 'revision' => 0])->assertUnprocessable();
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
