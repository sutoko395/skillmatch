<?php

namespace App\Services;

use App\Models\Application;
use App\Models\Event;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class RecruitmentScheduleService
{
    public function deadlineFor(Application $application, string $field = 'assessment_deadline'): ?CarbonImmutable
    {
        if (! in_array($field, ['assessment_deadline', 'decision_deadline'], true)) {
            throw new \InvalidArgumentException('Unknown recruitment deadline.');
        }
        $value = $application->snapshot_json[$field] ?? null;
        // Only an explicit, audited legacy repair can supplement absent snapshot metadata.
        // Existing snapshot dates always win and the snapshot itself is never changed.
        if (! $value && $application->event->recruitment_repaired_at) {
            $value = $application->event->$field?->toIso8601String();
        }
        if (! is_string($value) || ! preg_match('/(?:Z|[+-]\d{2}:\d{2})$/', $value)) {
            return null;
        }

        return CarbonImmutable::parse($value)->utc();
    }

    public function repair(Event $event, User $actor, array $input): void
    {
        $data = Validator::make($input, [
            'assessment_deadline' => 'required|date_format:Y-m-d\TH:i',
            'decision_deadline' => 'required|date_format:Y-m-d\TH:i',
            'reason' => 'required|string|min:5|max:1000', 'confirmed' => 'accepted',
        ])->validate();
        DB::transaction(function () use ($event, $actor, $data) {
            User::whereKey($event->organizer_id)->lockForUpdate()->firstOrFail();
            $event = Event::whereKey($event->id)->lockForUpdate()->firstOrFail();
            Gate::forUser($actor->fresh())->authorize('update', $event);
            $assessment = CarbonImmutable::createFromFormat('!Y-m-d\TH:i', $data['assessment_deadline'], 'Asia/Jakarta')->utc();
            $decision = CarbonImmutable::createFromFormat('!Y-m-d\TH:i', $data['decision_deadline'], 'Asia/Jakarta')->utc();
            if ($event->recruitment_repaired_at) {
                if ($event->assessment_deadline->equalTo($assessment) && $event->decision_deadline->equalTo($decision)) {
                    return;
                }
                throw ValidationException::withMessages(['assessment_deadline' => 'Pelengkapan tenggat sudah dicatat dan tidak dapat diubah.']);
            }
            if ($event->assessment_deadline || $event->decision_deadline
                || ! in_array($event->status, ['pending', 'approved'], true)
                || $event->lifecycle_status !== 'upcoming' || ! $event->registration_deadline
                || ! $event->starts_at || $assessment->lte(now())
                || $assessment->lte($event->registration_deadline) || $decision->lte($assessment) || $decision->gte($event->starts_at)) {
                throw ValidationException::withMessages(['assessment_deadline' => 'Hanya event lama tanpa kedua tenggat dapat dilengkapi. Tutup pendaftaran < assessment (masih akan datang) < keputusan < awal kegiatan.']);
            }
            $event->forceFill(['assessment_deadline' => $assessment, 'decision_deadline' => $decision,
                'recruitment_repaired_at' => now(), 'recruitment_repaired_by' => $actor->id,
                'recruitment_repair_reason' => trim($data['reason']), 'revision' => $event->revision + 1])->save();
            app(AuditService::class)->record($actor, 'event.recruitment_repaired', $event,
                ['after' => ['revision' => $event->revision]], 'legacy_deadlines_completed');
            foreach ($event->applications()->whereIn('status', ['submitted', 'assessment_pending', 'under_review'])->with('volunteer')->lazyById(100) as $application) {
                app(NotificationService::class)->enqueue('recruitment.repaired:'.$event->id.':'.$event->revision.':'.$application->volunteer_id,
                    $application->volunteer, ['type' => 'recruitment.repaired', 'application_id' => $application->id]);
            }
        }, 3);
    }
}
