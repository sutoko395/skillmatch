<?php

namespace App\Policies;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class AttendancePolicy
{
    public function update(User $user, Attendance $attendance): bool
    {
        return Gate::forUser($user)->allows('select', $attendance->application);
    }
}
