<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

// Baseline admin objects only. Document access uses a separate A1/A3 policy.
class AdminResourcePolicy
{
    public function manage(User $actor, Model $subject): bool
    {
        return $actor->is_active && $actor->role === 'admin' && $actor->hasVerifiedEmail();
    }
}
