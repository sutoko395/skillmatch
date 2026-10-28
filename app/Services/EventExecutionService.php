<?php

namespace App\Services;

use App\Models\Application;
use App\Models\Attendance;
use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class EventExecutionService
{
    public function record(Attendance $attendance, User $actor, array $input): Attendance
    {
        $data = Validator::make($input, ['status' => 'required|in:present,absent', 'note' => 'nullable|string|max:1000',
            'reason' => 'nullable|string|min:5|max:1000', 'revision' => 'required|integer|min:0'])->validate();

        return DB::transaction(function () use ($attendance, $actor, $data) {
            $application = $attendance->application;
            $event = Event::whereKey($application->event_id)->lockForUpdate()->firstOrFail();
            $application = Application::whereKey($application->id)->lockForUpdate()->firstOrFail();
            $attendance = Attendance::whereKey($attendance->id)->lockForUpdate()->firstOrFail();
            Gate::forUser($actor->fresh())->authorize('update', $attendance);
            if ($application->status !== 'accepted' || $event->lifecycle_status === 'cancelled'
                || ! $event->published_at || ! $event->starts_at || now()->lt($event->starts_at)) {
                app(SelectionService::class)->fail('Kehadiran hanya untuk peserta diterima sejak awal kegiatan.');
            }
            if ($attendance->status === $data['status'] && $attendance->note === ($data['note'] ?? null)) {
                return $attendance;
            }
            if ((int) $attendance->revision !== (int) $data['revision']) {
                app(SelectionService::class)->fail('Kehadiran telah berubah. Muat ulang sebelum menyimpan.');
            }
            if ($event->lifecycle_status === 'completed') {
                app(SelectionService::class)->reason($data['reason'] ?? '');
            } elseif ($event->lifecycle_status === 'upcoming') {
                $event->forceFill(['lifecycle_status' => 'ongoing', 'revision' => $event->revision + 1])->save();
                app(AuditService::class)->record($actor, 'event.begun', $event, ['after' => ['lifecycle_status' => 'ongoing']]);
            }
            $before = $attendance->status;
            $attendance->forceFill(['status' => $data['status'], 'note' => $data['note'] ?? null,
                'correction_reason' => $data['reason'] ?? null, 'recorded_by' => $actor->id, 'recorded_at' => now(), 'revision' => $attendance->revision + 1])->save();
            app(AuditService::class)->record($actor, 'attendance.recorded', $attendance,
                ['before' => ['status' => $before], 'after' => ['status' => $attendance->status, 'revision' => $attendance->revision]],
                $event->lifecycle_status === 'completed' ? 'post_completion_correction' : 'organizer_recorded');
            app(A2Dependencies::class)->notify('attendance.recorded:'.$attendance->id.':'.$attendance->revision.':'.$application->volunteer_id,
                $application->volunteer, ['type' => 'attendance.recorded', 'application_id' => $application->id]);

            return $attendance;
        }, 3);
    }

    public function complete(Event $event, User $actor, bool $confirmZero = false): Event
    {
        return DB::transaction(function () use ($event, $actor, $confirmZero) {
            $event = Event::whereKey($event->id)->lockForUpdate()->firstOrFail();
            Gate::forUser($actor->fresh())->authorize('update', $event);
            if ($event->lifecycle_status === 'completed') {
                return $event;
            }
            $fail = app(SelectionService::class);
            if ($event->lifecycle_status === 'cancelled' || ! $event->published_at || ! $event->ends_at || now()->lt($event->ends_at)) {
                $fail->fail('Kegiatan hanya dapat diselesaikan setelah jadwal akhir.');
            }
            if ($event->applications()->whereIn('status', SelectionService::PENDING)->exists()
                || $event->applications()->whereHas('assessmentAttempt', fn ($q) => $q->where('status', 'in_progress'))->exists()) {
                $fail->fail('Masih ada seleksi atau assessment tertunda. Tutup seleksi dengan alasan terlebih dahulu.');
            }
            $accepted = $event->applications()->where('status', 'accepted')->with('attendance')->get();
            if ($accepted->isEmpty() && ! $confirmZero) {
                $fail->fail('Konfirmasikan penyelesaian dengan nol peserta.');
            }
            foreach ($accepted as $application) {
                if (! in_array($application->attendance?->status, ['present', 'absent'], true)) {
                    $fail->fail('Seluruh peserta harus memiliki kehadiran final; Belum dicatat bukan Tidak hadir.');
                }
            }
            $event->forceFill(['lifecycle_status' => 'completed', 'completed_at' => now(), 'revision' => $event->revision + 1])->save();
            app(AuditService::class)->record($actor, 'event.completed', $event, ['after' => ['lifecycle_status' => 'completed', 'revision' => $event->revision]]);
            foreach ($accepted as $application) {
                app(A2Dependencies::class)->notify('event.completed:'.$event->id.':'.$event->revision.':'.$application->volunteer_id,
                    $application->volunteer, ['type' => 'event.completed', 'event_id' => $event->id]);
            }

            return $event;
        }, 3);
    }
}
