<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AdminResourcePolicy
{
    public function manage(User $actor, Model $subject): bool
    {
        return $actor->is_active
            && $actor->role === 'admin';
    }
}