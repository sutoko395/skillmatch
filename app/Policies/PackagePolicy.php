<?php

namespace App\Policies;

use App\Models\User;

class PackagePolicy
{
    public function manage(User $user): bool
    {
        return $user->is_active && $user->hasVerifiedEmail() && $user->role === 'admin';
    }
}
