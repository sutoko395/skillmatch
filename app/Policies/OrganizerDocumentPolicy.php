<?php

namespace App\Policies;

use App\Models\OrganizerDocument;
use App\Models\User;

class OrganizerDocumentPolicy
{
    public function create(User $user, User $owner): bool
    {
        return $user->is_active && $user->role === 'organizer' && $owner->id === $user->id;
    }

    public function download(User $user, OrganizerDocument $document): bool
    {
        return $user->is_active && ($user->role === 'admin'
            || ($user->role === 'organizer' && $document->user_id === $user->id));
    }
}
