<?php

namespace App\Policies;

use App\Models\Application;
use App\Models\User;

class ApplicationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active && $user->hasVerifiedEmail() && $user->role === 'volunteer';
    }

    public function view(User $user, Application $application): bool
    {
        if (! $user->is_active || ! $user->hasVerifiedEmail()) {
            return false;
        }

        if ($user->role === 'volunteer' && $application->volunteer_id === $user->id) {
            return true;
        }

        if ($user->role === 'organizer' && $application->event?->organizer_id === $user->id && $application->status !== 'draft') {
            return true;
        }

        return false;
    }

    public function update(User $user, Application $application): bool
    {
        return $user->is_active && $user->hasVerifiedEmail() && $user->role === 'volunteer' && $application->volunteer_id === $user->id && $application->status === 'draft';
    }

    public function submit(User $user, Application $application): bool
    {
        return $this->update($user, $application);
    }
}
