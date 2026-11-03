<?php

namespace App\Services;

use App\Models\Application;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\Event;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AttendanceSessionService
{
    /** Intervals come only from the submitted immutable position snapshot. */
    public function definitions(Application $application): array
    {
        $rows = $application->snapshot_json['schedules'] ?? null;
        if (! is_array($rows) || $rows === []) {
            $this->fail('Snapshot jadwal tugas tidak tersedia. Absensi sesi tidak dapat disiapkan dengan menebak jadwal.');
        }
        $intervals = [];
        foreach ($rows as $row) {
            foreach (['start', 'end'] as $field) {
                if (! is_string($row[$field] ?? null) || ! preg_match('/(?:Z|[+-]\d{2}:\d{2})$/', $row[$field])) {
                    $this->fail('Snapshot jadwal tugas tidak valid.');
                }
            }
            try {
                $start = CarbonImmutable::parse($row['start'])->utc();
                $end = CarbonImmutable::parse($row['end'])->utc();
            } catch (\Throwable) {
                $this->fail('Snapshot jadwal tugas tidak valid.');
            }
            if ($start->gte($end)) {
                $this->fail('Snapshot jadwal tugas tidak valid.');
            }
            $intervals[] = [$start, $end];
        }
        usort($intervals, fn ($a, $b) => $a[0]->getTimestamp() <=> $b[0]->getTimestamp());
        $merged = [];
        foreach ($intervals as [$start, $end]) {
            $last = count($merged) - 1;
            // Overlap is one duty interval; adjacent, distinct shifts stay separate.
            if ($last >= 0 && $start->lt($merged[$last][1])) {
                $merged[$last][1] = $merged[$last][1]->max($end);
            } else {
                $merged[] = [$start, $end];
            }
        }
        $sessions = [];
        foreach ($merged as [$start, $end]) {
            for ($cursor = $start; $cursor->lt($end); $cursor = $until) {
                $until = $cursor->setTimezone('Asia/Jakarta')->startOfDay()->addDay()->utc()->min($end);
                $key = hash('sha256', $cursor->toIso8601String().'|'.$until->toIso8601String());
                $sessions[$key] = ['session_key' => $key, 'session_date' => $cursor->setTimezone('Asia/Jakarta')->toDateString(),
                    'starts_at' => $cursor, 'ends_at' => $until];
            }
        }

        return array_values($sessions);
    }

    public function prepare(Attendance $attendance, User $actor, array $input): Attendance
    {
        $data = Validator::make($input, ['confirmed' => 'accepted', 'revision' => 'required|integer|min:0',
            'reason' => 'nullable|string|min:5|max:1000'])->validate();

        return DB::transaction(function () use ($attendance, $actor, $data) {
            $event = Event::whereKey($attendance->application->event_id)->lockForUpdate()->firstOrFail();
            $application = Application::whereKey($attendance->application_id)->lockForUpdate()->firstOrFail();
            $attendance = Attendance::whereKey($attendance->id)->lockForUpdate()->firstOrFail();
            Gate::forUser($actor->fresh())->authorize('update', $attendance);
            if ($attendance->uses_sessions) {
                return $attendance;
            }
            if ($application->status !== 'accepted' || in_array($event->lifecycle_status, ['completed', 'cancelled'], true)) {
                $this->fail('Persiapan sesi hanya untuk peserta diterima pada kegiatan yang belum selesai atau dibatalkan.');
            }
            if ((int) $attendance->revision !== (int) $data['revision']) {
                $this->fail('Kehadiran telah berubah. Muat ulang sebelum menyiapkan sesi.');
            }
            if ($attendance->status !== 'unrecorded') {
                app(SelectionService::class)->reason($data['reason'] ?? '');
                $attendance->legacy_record = [...$attendance->only(['status', 'recorded_by', 'recorded_at', 'note', 'correction_reason', 'revision']),
                    'converted_by' => $actor->id, 'converted_at' => now()->toIso8601String(), 'conversion_reason' => $data['reason']];
            }

            return $this->prepareLocked($attendance, $application, $actor);
        }, 3);
    }

    public function prepareApplication(Application $application, User $actor, array $input): Attendance
    {
        Validator::make($input, ['confirmed' => 'accepted'])->validate();

        return DB::transaction(function () use ($application, $actor, $input) {
            $event = Event::whereKey($application->event_id)->lockForUpdate()->firstOrFail();
            $application = Application::whereKey($application->id)->lockForUpdate()->firstOrFail();
            Gate::forUser($actor->fresh())->authorize('select', $application);
            if ($application->status !== 'accepted' || in_array($event->lifecycle_status, ['cancelled', 'completed'], true)) {
                $this->fail('Persiapan sesi hanya untuk peserta diterima pada kegiatan yang belum terminal.');
            }
            $attendance = Attendance::firstOrCreate(['application_id' => $application->id], ['status' => 'unrecorded']);

            return $this->prepare($attendance, $actor, $input + ['revision' => 0]);
        }, 3);
    }

    /** Caller holds event/application/attendance locks, including acceptance. */
    public function prepareLocked(Attendance $attendance, Application $application, User $actor): Attendance
    {
        if (DB::transactionLevel() < 1 || $attendance->application_id !== $application->id || $application->status !== 'accepted') {
            throw new \LogicException('Session preparation requires the accepted application transaction.');
        }
        if ($attendance->uses_sessions) {
            return $attendance;
        }
        foreach ($this->definitions($application) as $definition) {
            $attendance->sessions()->create($definition);
        }
        $attendance->forceFill(['uses_sessions' => true, 'status' => 'unrecorded', 'recorded_by' => null,
            'recorded_at' => null, 'note' => null, 'correction_reason' => null,
            'revision' => (int) ($attendance->revision ?? 0) + ($attendance->wasRecentlyCreated ? 0 : 1)])->save();
        app(AuditService::class)->record($actor, 'attendance.sessions_prepared', $attendance, [], 'snapshot_schedule');

        return $attendance;
    }

    public function record(AttendanceSession $session, User $actor, array $input): AttendanceSession
    {
        $data = $this->validate($input);

        return DB::transaction(function () use ($session, $actor, $data) {
            $parent = $session->attendance;
            $event = Event::whereKey($parent->application->event_id)->lockForUpdate()->firstOrFail();
            $application = Application::whereKey($parent->application_id)->lockForUpdate()->firstOrFail();
            $parent = Attendance::whereKey($parent->id)->lockForUpdate()->firstOrFail();
            Gate::forUser($actor->fresh())->authorize('update', $parent);
            $session = AttendanceSession::whereKey($session->id)->where('attendance_id', $parent->id)->lockForUpdate()->firstOrFail();

            return $this->recordLocked($session, $parent, $application, $event, $actor, $data);
        }, 3);
    }

    public function validate(array $input): array
    {
        return Validator::make($input, ['status' => 'required|in:present,absent', 'note' => 'nullable|string|max:1000',
            'reason' => 'nullable|string|min:5|max:1000', 'revision' => 'required|integer|min:0'])->validate();
    }

    public function recordLocked(AttendanceSession $session, Attendance $parent, Application $application, Event $event, User $actor, array $data): AttendanceSession
    {
        if (DB::transactionLevel() < 1 || ! $parent->uses_sessions || $session->attendance_id !== $parent->id) {
            throw new \LogicException('Session record requires parent locks.');
        }
        if ($application->status !== 'accepted' || $event->lifecycle_status === 'cancelled' || ! $event->published_at
            || ! $event->starts_at || now()->lt($event->starts_at) || now()->lt($session->starts_at)) {
            $this->fail('Kehadiran hanya dapat dicatat untuk peserta diterima setelah sesi tugas dimulai.');
        }
        if ($session->status === $data['status'] && $session->note === ($data['note'] ?? null)) {
            return $session;
        }
        if ($session->revision !== (int) $data['revision']) {
            $this->fail('Kehadiran sesi telah berubah. Muat ulang sebelum menyimpan.');
        }
        if ($event->lifecycle_status === 'completed') {
            app(SelectionService::class)->reason($data['reason'] ?? '');
        } elseif ($event->lifecycle_status === 'upcoming') {
            $event->forceFill(['lifecycle_status' => 'ongoing', 'revision' => $event->revision + 1])->save();
            app(AuditService::class)->record($actor, 'event.begun', $event, ['after' => ['lifecycle_status' => 'ongoing']]);
        }
        $before = $session->status;
        $session->forceFill(['status' => $data['status'], 'note' => $data['note'] ?? null,
            'correction_reason' => $data['reason'] ?? null, 'recorded_by' => $actor->id,
            'recorded_at' => now(), 'revision' => $session->revision + 1])->save();
        $states = $parent->sessions()->lockForUpdate()->pluck('status');
        $summary = $states->contains('unrecorded') ? 'unrecorded' : ($states->contains('absent') ? 'absent' : 'present');
        $parent->forceFill(['status' => $summary, 'recorded_by' => $actor->id, 'recorded_at' => now(),
            'revision' => $parent->revision + 1])->save();
        app(AuditService::class)->record($actor, 'attendance.session_recorded', $session,
            ['before' => ['status' => $before], 'after' => ['status' => $session->status, 'revision' => $session->revision]],
            $event->lifecycle_status === 'completed' ? 'post_completion_correction' : 'organizer_recorded');
        app(A2Dependencies::class)->notify('attendance.session_recorded:'.$session->id.':'.$session->revision.':'.$application->volunteer_id,
            $application->volunteer, ['type' => 'attendance.session_recorded', 'application_id' => $application->id]);

        return $session;
    }

    private function fail(string $message): never
    {
        throw ValidationException::withMessages(['attendance' => $message]);
    }
}
