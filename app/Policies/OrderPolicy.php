<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function view(User $user, Order $order): bool
    {
        if (! $user->is_active) {
            return false;
        }

        if ($user->role === 'admin') {
            return true;
        }

        return $user->role === 'organizer'
            && $user->organizer_status === 'active'
            && $order->event->organizer_id === $user->id;
    }
}