<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    public function create(User $user): bool
    {
        return $user->is_active && $user->hasVerifiedEmail() && $user->role === 'organizer' && $user->organizer_status === 'active';
    }

    public function update(User $user, Event $event): bool
    {
        return $this->create($user) && $event->organizer_id === $user->id;
    }

    public function view(User $user, Event $event): bool
    {
        return $this->update($user, $event) || $this->manage($user, $event);
    }

    public function manage(User $user, Event $event): bool
    {
        return $user->is_active && $user->hasVerifiedEmail() && $user->role === 'admin';
    }
}
