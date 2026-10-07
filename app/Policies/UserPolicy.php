<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function updateProfile(User $actor, User $subject): bool
    {
        return $actor->is_active && $actor->is($subject) && in_array($actor->role, ['volunteer', 'organizer'], true);
    }

    public function viewAdmin(User $actor): bool
    {
        return $actor->is_active && $actor->role === 'admin';
    }

    public function manageVolunteer(User $actor, User $subject): bool
    {
        return $this->viewAdmin($actor) && $subject->role === 'volunteer';
    }

    public function manageOrganizer(User $actor, User $subject): bool
    {
        return $this->viewAdmin($actor) && $subject->role === 'organizer';
    }

    public function delete(User $actor, User $subject): bool
    {
        return false;
    }
}