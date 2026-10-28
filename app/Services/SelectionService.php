<?php

namespace App\Services;

use App\Models\Application;
use App\Models\Attendance;
use App\Models\Event;
use App\Models\EventPosition;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class SelectionService
{
    public const PENDING = ['submitted', 'assessment_pending', 'under_review'];

    public function fail(string $message): never
    {
        throw ValidationException::withMessages(['selection' => $message]);
    }

    public function reason(string $reason): string
    {
        $reason = trim($reason);
        if (mb_strlen($reason) < 5 || mb_strlen($reason) > 1000) {
            $this->fail('Alasan wajib 5 sampai 1000 karakter.');
        }

        return $reason;
    }

    public function accept(Application $application, User $actor, ?string $reason = null): Application
    {
        return $this->decide($application, $actor, 'accepted', $reason);
    }

    public function reject(Application $application, User $actor, string $reason): Application
    {
        return $this->decide($application, $actor, 'rejected', $this->reason($reason));
    }

    private function decide(Application $application, User $actor, string $status, ?string $reason): Application
    {
        return DB::transaction(function () use ($application, $actor, $status, $reason) {
            $volunteer = User::whereKey($application->volunteer_id)->lockForUpdate()->firstOrFail();
            $event = Event::whereKey($application->event_id)->lockForUpdate()->firstOrFail();
            $position = EventPosition::whereKey($application->event_position_id)->where('event_id', $event->id)->lockForUpdate()->firstOrFail();
            $application = Application::whereKey($application->id)->lockForUpdate()->firstOrFail();
            Gate::forUser($actor->fresh())->authorize('select', $application);
            if ($application->status === $status) {
                return $application;
            }
            if ($application->status !== 'under_review' || ! $application->submitted_at) {
                $this->fail('Keputusan hanya tersedia untuk lamaran Siap Ditinjau.');
            }
            if ($event->lifecycle_status !== 'upcoming' || ! $event->starts_at || now()->gte($event->starts_at)) {
                $this->fail('Seleksi harus dilakukan sebelum kegiatan dimulai.');
            }
            if ($event->decision_deadline && now()->gte($event->decision_deadline)) {
                $this->fail('Batas keputusan seleksi telah berakhir. Gunakan penutupan seleksi beralasan untuk lamaran tertunda.');
            }
            if ($status === 'accepted') {
                if (! $volunteer->is_active || $volunteer->role !== 'volunteer' || ! $volunteer->hasVerifiedEmail()
                    || ! Event::publiclyVisible()->whereKey($event->id)->exists()) {
                    $this->fail('Akun atau event tidak memenuhi syarat penerimaan.');
                }
                // Locking reads see commits made while waiting for volunteer/event locks.
                $accepted = Application::where('event_position_id', $position->id)->where('status', 'accepted')->lockForUpdate()->get();
                if ($accepted->count() >= $position->quota) {
                    $this->fail('Kuota posisi sudah penuh.');
                }
                $schedules = $this->schedules($application);
                $others = Application::where('volunteer_id', $volunteer->id)->where('status', 'accepted')
                    ->where('event_id', '!=', $event->id)->lockForUpdate()->get();
                foreach ($others as $other) {
                    if ($other->event->lifecycle_status === 'cancelled') {
                        continue;
                    }
                    foreach ($this->schedules($other) as [$start, $end]) {
                        foreach ($schedules as [$candidateStart, $candidateEnd]) {
                            if ($candidateStart->lt($end) && $start->lt($candidateEnd)) {
                                $this->fail('Jadwal bertabrakan dengan kegiatan lain yang sudah diterima.');
                            }
                        }
                    }
                }
            }
            $this->transition($application, $actor, $status, $reason);
            if ($status === 'accepted') {
                Attendance::firstOrCreate(['application_id' => $application->id], ['status' => 'unrecorded']);
            }

            return $application;
        }, 3);
    }

    private function schedules(Application $application): array
    {
        $intervals = $application->snapshot_json['schedules'] ?? [];
        if (! is_array($intervals) || $intervals === []) {
            $this->fail('Snapshot jadwal tidak lengkap; penerimaan ditahan untuk pemeriksaan.');
        }
        $result = [];
        foreach ($intervals as $interval) {
            foreach (['start', 'end'] as $key) {
                if (! is_string($interval[$key] ?? null) || ! preg_match('/(?:Z|[+-]\d{2}:\d{2})$/', $interval[$key])) {
                    $this->fail('Snapshot jadwal tidak valid.');
                }
            }
            try {
                $start = CarbonImmutable::parse($interval['start']);
                $end = CarbonImmutable::parse($interval['end']);
            } catch (\Throwable) {
                $this->fail('Snapshot jadwal tidak valid.');
            }
            if ($start->gte($end)) {
                $this->fail('Snapshot jadwal tidak valid.');
            }
            $result[] = [$start, $end];
        }

        return $result;
    }

    // Caller holds event and application locks; audit/notification share the transaction.
    public function transition(Application $application, User $actor, string $status, ?string $reason): void
    {
        if (DB::transactionLevel() < 1) {
            throw new \LogicException('Transition requires a transaction.');
        }
        $before = $application->status;
        $application->forceFill(['status' => $status, 'decision_by' => $actor->id, 'decision_at' => now(),
            'decision_reason' => $reason, 'revision' => $application->revision + 1])->save();
        app(AuditService::class)->record($actor, 'application.'.$status, $application,
            ['before' => ['status' => $before], 'after' => ['status' => $status, 'revision' => $application->revision]], 'a3_transition');
        app(A2Dependencies::class)->notify('application.'.$status.':'.$application->id.':'.$application->revision.':'.$application->volunteer_id,
            $application->volunteer, ['type' => 'application.'.$status, 'application_id' => $application->id]);
    }

    public function withdraw(Application $application, User $actor, ?string $reason = null): Application
    {
        return DB::transaction(function () use ($application, $actor, $reason) {
            User::whereKey($application->volunteer_id)->lockForUpdate()->firstOrFail();
            $event = Event::whereKey($application->event_id)->lockForUpdate()->firstOrFail();
            $application = Application::whereKey($application->id)->lockForUpdate()->firstOrFail();
            Gate::forUser($actor->fresh())->authorize('withdraw', $application);
            if ($application->status === 'withdrawn') {
                return $application;
            }
            if (in_array($event->lifecycle_status, ['cancelled', 'completed'], true)) {
                $this->fail('Lamaran pada kegiatan terminal tidak dapat ditarik.');
            }
            if ($application->status === 'accepted') {
                $reason = $this->reason($reason ?? '');
                if (! $event->starts_at || now()->gte($event->starts_at)) {
                    $this->fail('Pengunduran peserta hanya sebelum kegiatan dimulai.');
                }
                $attendance = Attendance::where('application_id', $application->id)->lockForUpdate()->first();
                if ($attendance && $attendance->status !== 'unrecorded') {
                    $this->fail('Kehadiran sudah dicatat; pengunduran ditolak.');
                }
                $attendance?->delete();
            } elseif (! in_array($application->status, self::PENDING, true)) {
                $this->fail('Status lamaran tidak dapat ditarik.');
            }
            $this->transition($application, $actor, 'withdrawn', $reason);
            app(AssessmentService::class)->cancelLocked($application, $actor);

            return $application;
        }, 3);
    }

    public function close(Event $event, User $actor, string $reason): void
    {
        $reason = $this->reason($reason);
        DB::transaction(function () use ($event, $actor, $reason) {
            $event = Event::whereKey($event->id)->lockForUpdate()->firstOrFail();
            Gate::forUser($actor->fresh())->authorize('update', $event);
            if (in_array($event->lifecycle_status, ['cancelled', 'completed'], true) || now()->lt($event->registration_deadline)) {
                $this->fail('Penutupan hanya setelah pendaftaran berakhir pada kegiatan nonterminal.');
            }
            foreach ($event->applications()->whereIn('status', self::PENDING)->orderBy('id')->lockForUpdate()->get() as $application) {
                $this->transition($application, $actor, 'rejected', $reason);
                app(AssessmentService::class)->cancelLocked($application, $actor);
            }
        }, 3);
    }
}
