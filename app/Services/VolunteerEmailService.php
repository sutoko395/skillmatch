<?php

namespace App\Services;

use App\Models\Application;
use App\Models\Assessment;
use App\Models\NotificationOutbox;
use Carbon\CarbonImmutable;

class VolunteerEmailService
{
    public const FAILED = 'screening.failed';

    public const AVAILABLE = 'assessment.available';

    public const REMINDER = 'assessment.reminder';

    public const DECISION = 'selection.result';

    // Called inside the business transaction. No SMTP or network activity here.
    public function enqueue(Application $application, string $type): ?NotificationOutbox
    {
        if (! in_array($type, [self::FAILED, self::AVAILABLE, self::REMINDER, self::DECISION], true)) {
            throw new \InvalidArgumentException('Unsupported volunteer email type');
        }
        if (! $this->eligible($application, $type)) {
            return null;
        }
        $version = $application->snapshot_json['assessment_version'] ?? 0;
        $suffix = $type === self::DECISION ? $application->revision : $version;

        return NotificationOutbox::firstOrCreate(['dedupe_key' => 'volunteer-mail:'.$type.':'.$application->id.':'.$suffix], [
            'recipient_id' => $application->volunteer_id, 'application_id' => $application->id, 'channel' => 'mail',
            'type' => $type, 'data' => ['version' => $version, 'revision' => $application->revision,
                'decision' => $type === self::DECISION ? $application->status : null],
            'status' => 'pending', 'available_at' => now(),
        ]);
    }

    public function deadline(Application $application): ?CarbonImmutable
    {
        $value = $application->snapshot_json['assessment_deadline'] ?? null;
        if (! is_string($value) || ! preg_match('/(?:Z|[+-]\d{2}:\d{2})$/', $value)) {
            return null;
        }
        try {
            return CarbonImmutable::parse($value)->utc()->min($application->event->starts_at);
        } catch (\Throwable) {
            return null;
        }
    }

    public function assessment(Application $application): ?Assessment
    {
        return Assessment::where('event_position_id', $application->event_position_id)
            ->where('version', $application->snapshot_json['assessment_version'] ?? null)
            ->where('is_published', true)->withCount('questions')->first();
    }

    public function eligible(Application $application, string $type, ?NotificationOutbox $outbox = null): bool
    {
        $recipient = $application->volunteer;
        $event = $application->event;
        if (! $recipient || ! $recipient->is_active || $recipient->role !== 'volunteer' || ! $recipient->hasVerifiedEmail()
            || ! $event || in_array($event->lifecycle_status, ['cancelled', 'completed'], true)
            || ($outbox && $outbox->recipient_id !== $recipient->id)) {
            return false;
        }
        if ($type === self::FAILED) {
            return $application->status === 'screening_failed' && $application->screeningResult?->passed === false;
        }
        if ($type === self::DECISION) {
            return in_array($application->status, ['accepted', 'rejected'], true) && $application->decision_at && $application->decision_by
                && (! $outbox || ($outbox->data['decision'] ?? null) === $application->status);
        }
        if (! in_array($type, [self::AVAILABLE, self::REMINDER], true)) {
            return false;
        }
        $deadline = $this->deadline($application);
        if ($application->status !== 'assessment_pending' || ! $application->screeningResult?->passed || ! $application->matchResult
            || $application->assessmentAttempt || $event->lifecycle_status !== 'upcoming'
            || $event->publication_status !== 'published' || ! $event->published_at
            || ! $deadline || now()->gte($deadline) || ! $this->assessment($application)) {
            return false;
        }
        if ($outbox && (int) ($outbox->data['version'] ?? 0) !== (int) ($application->snapshot_json['assessment_version'] ?? 0)) {
            return false;
        }
        if ($type === self::REMINDER) {
            $cutoff = $deadline->subDay();
            $available = NotificationOutbox::where('application_id', $application->id)->where('type', self::AVAILABLE)
                ->where('status', 'sent')->first();

            return now()->gte($cutoff) && $available && $available->created_at->lt($cutoff)
                && $available->delivered_at?->lte($cutoff);
        }

        return true;
    }

    public function reminders(): int
    {
        $queued = 0;
        // Only applications whose availability email was delivered, never a historical blast.
        NotificationOutbox::where('channel', 'mail')->where('type', self::AVAILABLE)->where('status', 'sent')
            ->whereHas('application', fn ($query) => $query->where('status', 'assessment_pending'))
            ->with('application')->chunkById(100, function ($rows) use (&$queued) {
                foreach ($rows as $row) {
                    $reminder = $this->enqueue($row->application, self::REMINDER);
                    if ($reminder?->wasRecentlyCreated) {
                        $queued++;
                    }
                }
            });

        return $queued;
    }
}
