<?php

namespace App\Policies;

use App\Models\Application;
use App\Models\User;

class ApplicationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active
            && $user->role === 'volunteer';
    }

    public function view(User $user, Application $application): bool
    {
        if (! $user->is_active) {
            return false;
        }

        if (
            $user->role === 'volunteer'
            && $application->volunteer_id === $user->id
        ) {
            return true;
        }

        if (
            $user->role === 'organizer'
            && $user->organizer_status === 'active'
            && $application->event?->organizer_id === $user->id
            && $application->submitted_at !== null
        ) {
            return true;
        }

        return false;
    }

    public function update(User $user, Application $application): bool
    {
        return $user->is_active
            && $user->role === 'volunteer'
            && $application->volunteer_id === $user->id
            && $application->status === 'draft';
    }

    public function submit(User $user, Application $application): bool
    {
        return $user->is_active && $user->role === 'volunteer'
            && $user->hasVerifiedEmail() && $application->volunteer_id === $user->id;
    }

    public function select(User $user, Application $application): bool
    {
        return $user->is_active && $user->role === 'organizer'
            && $user->organizer_status === 'active' && $application->submitted_at !== null
            && $application->event?->organizer_id === $user->id;
    }

    public function withdraw(User $user, Application $application): bool
    {
        return ($this->submit($user, $application) && $application->status !== 'accepted')
            || (in_array($application->status, ['accepted', 'withdrawn'], true) && $this->select($user, $application));
    }
}
