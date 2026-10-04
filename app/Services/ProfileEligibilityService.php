<?php

namespace App\Services;

use App\Models\User;

class ProfileEligibilityService
{
    public function check(User $user): array
    {
        $user->load(['volunteerProfile.cityRecord', 'volunteerSkills.skill', 'availabilitySlots']);
        $profile = $user->volunteerProfile;
        $missing = [];
        if ($user->role !== 'volunteer') {
            $missing[] = 'role';
        }
        foreach (['name', 'email'] as $field) {
            if (blank($user->$field)) {
                $missing[] = $field;
            }
        }
        // Same identity fields as the existing profile form; account/email access is checked separately.
        foreach (['phone', 'birth_date', 'gender', 'address', 'bio'] as $field) {
            if (blank($profile?->$field)) {
                $missing[] = $field;
            }
        }
        if (! $profile?->cityRecord?->is_active) {
            $missing[] = 'city_id';
        }
        $skills = $user->volunteerSkills->filter(fn ($row) => $row->skill?->is_active && in_array($row->level, ['beginner', 'intermediate', 'advanced', 'expert'], true))
            ->map(fn ($row) => ['id' => $row->skill_id, 'level' => $row->level])->values()->all();
        if (! $skills) {
            $missing[] = 'skills';
        }
        $slots = $user->availabilitySlots->filter(fn ($slot) => $slot->ends_at > $slot->starts_at)
            ->sortBy('starts_at')->map(fn ($slot) => ['start' => $slot->starts_at->utc()->toIso8601String(), 'end' => $slot->ends_at->utc()->toIso8601String()])->values()->all();
        if (! $slots) {
            $missing[] = 'availability_slots';
        }

        return ['complete' => ! $missing, 'missing_fields' => $missing, 'city_id' => $profile?->city_id, 'skills' => $skills, 'availability_slots' => $slots];
    }
}
