<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function view(User $user, Order $order): bool
    {
        return $user->is_active && $user->hasVerifiedEmail() && ($user->role === 'admin' || ($user->role === 'organizer' && $user->organizer_status === 'active' && $order->event->organizer_id === $user->id));
    }
}
