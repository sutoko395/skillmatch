<?php

namespace App\Services;

use App\Models\Application;
use App\Models\Event;
use App\Models\EventPosition;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ApplicationService
{
    public function storeDraft(User $volunteer, EventPosition $position): Application
    {
        if ($volunteer->role !== 'volunteer' || ! $volunteer->is_active || ! $volunteer->hasVerifiedEmail()) {
            throw new AuthorizationException('Hanya volunteer aktif yang dapat membuat draft lamaran.');
        }

        return DB::transaction(function () use ($volunteer, $position) {
            $volunteer = User::whereKey($volunteer->id)->lockForUpdate()->firstOrFail();
            if ($volunteer->role !== 'volunteer' || ! $volunteer->is_active || ! $volunteer->hasVerifiedEmail()) {
                throw new AuthorizationException;
            }
            $event = Event::whereKey($position->event_id)->lockForUpdate()->firstOrFail();
            $position = EventPosition::whereKey($position->id)->where('event_id', $event->id)->firstOrFail();
            $position->setRelation('event', $event);
            $event = $position->event;

            if (! $event) {
                throw ValidationException::withMessages(['event' => 'Event tidak ditemukan.']);
            }

            $eligibility = app(ProfileEligibilityService::class)->check($volunteer);
            if (! ($eligibility['complete'] ?? false)) {
                throw ValidationException::withMessages([
                    'profile' => 'Profil Anda belum lengkap. Silakan lengkapi profil terlebih dahulu.',
                ]);
            }

            if (! Event::publiclyVisible()->whereKey($event->id)->exists()
                || now() < $event->registration_opens_at
                || now() >= $event->registration_deadline) {
                throw ValidationException::withMessages([
                    'event' => 'Pendaftaran untuk kegiatan ini tidak sedang dibuka.',
                ]);
            }

            $existing = Application::where('volunteer_id', $volunteer->id)
                ->where('event_id', $event->id)
                ->first();

            if ($existing) {
                if ($existing->status === 'draft') {
                    if ($existing->event_position_id !== $position->id) {
                        foreach ($existing->documents()->where('status', 'ready')->get() as $document) {
                            app(DocumentRetentionService::class)->queue($document, 'draft_position_changed');
                        }
                        $existing->event_position_id = $position->id;
                        $existing->save();
                    }

                    return $existing;
                }

                throw ValidationException::withMessages([
                    'application' => $existing->status === 'withdrawn'
                        ? 'Lamaranmu sudah ditarik dan tidak dapat diajukan ulang pada event ini.'
                        : 'Anda sudah memiliki lamaran yang dikirim untuk kegiatan ini.',
                ]);
            }

            return Application::create([
                'event_id' => $event->id,
                'event_position_id' => $position->id,
                'volunteer_id' => $volunteer->id,
                'status' => 'draft',
                'revision' => 0,
            ]);
        }, 3);
    }

    public function submit(Application $draft, User $actor): Application
    {
        if ($draft->volunteer_id !== $actor->id) {
            throw new AuthorizationException('Anda tidak berhak mengirimkan lamaran ini.');
        }

        return DB::transaction(function () use ($draft, $actor) {
            $volunteer = User::where('id', $draft->volunteer_id)->lockForUpdate()->firstOrFail();
            if ($actor->id !== $volunteer->id || $volunteer->role !== 'volunteer' || ! $volunteer->is_active || ! $volunteer->hasVerifiedEmail()) {
                throw new AuthorizationException;
            }
            $event = Event::where('id', $draft->event_id)->lockForUpdate()->firstOrFail();

            $lockedDraft = Application::where('id', $draft->id)->lockForUpdate()->firstOrFail();
            if ($lockedDraft->submitted_at !== null) {
                return $lockedDraft;
            }
            if ($lockedDraft->status !== 'draft') {
                throw ValidationException::withMessages(['application' => 'Lamaran ini tidak dapat dikirim.']);
            }

            $now = now();
            if (! Event::publiclyVisible()->whereKey($event->id)->exists()
                || $now < $event->registration_opens_at
                || $now >= $event->registration_deadline) {
                throw ValidationException::withMessages([
                    'event' => 'Pendaftaran untuk kegiatan ini tidak sedang dibuka.',
                ]);
            }

            $position = EventPosition::where('id', $lockedDraft->event_position_id)
                ->where('event_id', $event->id)
                ->firstOrFail();

            $snapshot = app(PositionSnapshotService::class)->build($position);

            $eligibility = app(ProfileEligibilityService::class)->check($volunteer);
            $snapshot['profile_eligibility'] = [
                'complete' => $eligibility['complete'],
                'missing_fields' => $eligibility['missing_fields'],
            ];
            $snapshot['profile'] = [
                'user_id' => $volunteer->id,
                'name' => $volunteer->name,
                'email' => $volunteer->email,
                'city_id' => $volunteer->volunteerProfile?->city_id,
                'city_name' => $volunteer->volunteerProfile?->cityRecord?->name,
                'phone' => $volunteer->volunteerProfile?->phone,
                'bio' => $volunteer->volunteerProfile?->bio,
                'skills' => $volunteer->volunteerSkills->map(fn ($vs) => [
                    'id' => $vs->skill_id,
                    'name' => $vs->skill?->name,
                    'level' => $vs->level,
                ])->values()->all(),
                'availability_slots' => $volunteer->availabilitySlots->map(fn ($slot) => [
                    'starts_at' => $slot->starts_at->toIso8601String(),
                    'ends_at' => $slot->ends_at->toIso8601String(),
                ])->values()->all(),
            ];

            $snapshot['documents'] = $lockedDraft->documents()
                ->where('status', 'ready')
                ->get()
                ->map(fn ($doc) => [
                    'id' => $doc->id,
                    'document_type' => $doc->document_type,
                    'original_name' => $doc->original_name,
                    'size_bytes' => $doc->size_bytes,
                    'checksum_sha256' => $doc->checksum_sha256,
                    'ready_at' => $doc->ready_at?->toIso8601String(),
                ])->values()->all();

            app(EntitlementService::class)->consumeApplication($event);

            $beforeStatus = $lockedDraft->status;
            $lockedDraft->forceFill([
                'snapshot_json' => $snapshot,
                'status' => 'submitted',
                'submitted_at' => now(),
                'revision' => $lockedDraft->revision + 1,
            ])->save();

            app(AuditService::class)->record(
                $actor,
                'application.submitted',
                $lockedDraft,
                [
                    'before' => ['status' => $beforeStatus],
                    'after' => ['status' => 'submitted'],
                ],
                'volunteer-submitted'
            );
            app(A2Dependencies::class)->notify('application.submitted:'.$lockedDraft->id.':'.$lockedDraft->revision.':'.$event->organizer_id,
                $event->organizer, ['type' => 'application.submitted', 'application_id' => $lockedDraft->id]);

            DB::afterCommit(fn () => $this->triggerEvaluation($lockedDraft));

            return $lockedDraft;
        }, 3);
    }

    /**
     * Trigger evaluation if evaluator service is registered/available.
     * If evaluator is not available, status remains 'submitted' (Menunggu evaluasi).
     */
    protected function triggerEvaluation(Application $application): void
    {
        // When A4 screening service exists, dispatch it here.
        // Fallback: remains 'submitted' awaiting evaluation.
        if (class_exists('App\Services\ScreeningService')) {
            try {
                app('App\Services\ScreeningService')->evaluate($application);
            } catch (\Throwable $e) {
                // Recovery uses a4:retry-screening; never log snapshot or exception contents.
                logger()->warning('Screening belum selesai; retry diperlukan.', ['application_id' => $application->id]);
            }
        }
    }
}
