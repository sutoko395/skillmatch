<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class AuditService
{
    // Only structured operational data. Never accept whole request/model payloads or document bodies.
    private const FIELDS = ['is_active', 'organizer_status', 'status', 'publication_status', 'lifecycle_status', 'city_id', 'skill_id', 'level', 'starts_at', 'ends_at', 'revision', 'changed_fields', 'price', 'max_positions', 'max_applications', 'max_registration_days'];

    public function record(?User $actor, string $action, Model $subject, array $changes = [], ?string $reason = null): AuditLog
    {
        if (! $subject->exists || ! preg_match('/\A[a-z][a-z0-9_.-]{0,99}\z/', $action)) {
            throw new \InvalidArgumentException('Persisted subject and structured action required.');
        }
        // Module-supplied reason is a short operational code, never untrusted free text.
        if ($reason !== null && ! preg_match('/\A[a-z][a-z0-9_.-]{0,99}\z/', $reason)) {
            throw new \InvalidArgumentException('Use a non-sensitive reason code.');
        }
        $safe = function (array $data): array {
            $result = [];
            foreach (Arr::only($data, self::FIELDS) as $key => $value) {
                if ($key === 'changed_fields') {
                    $result[$key] = array_values(array_intersect((array) $value, ['title', 'name', 'phone', 'birth_date', 'gender', 'address', 'bio', 'city_id', 'skills', 'availability_slots', 'organization_name', 'contact_person', 'email', 'city', 'website', 'description']));
                } elseif (is_bool($value) || is_int($value) || $value === null || (is_string($value) && strlen($value) <= 64 && preg_match('/\A[a-zA-Z0-9_. :+\-]+\z/', $value))) {
                    $result[$key] = $value;
                }
            }

            return $result;
        };

        return AuditLog::on($subject->getConnectionName())->create([
            'actor_id' => $actor?->id, 'action' => $action,
            'subject_type' => $subject->getMorphClass(), 'subject_id' => $subject->getKey(),
            'before' => $safe($changes['before'] ?? []), 'after' => $safe($changes['after'] ?? []),
            'reason' => $reason, 'created_at' => now(),
        ]);
    }
}
