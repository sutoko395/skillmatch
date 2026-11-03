<?php

namespace App\Services;

use App\Models\Application;
use App\Models\Event;
use App\Models\NotificationOutbox;
use App\Models\Order;
use App\Models\User;

class ActivityReadService
{

    public function notificationDestination(NotificationOutbox $row, User $user): string
    {
        abort_unless($row->recipient_id === $user->id, 403);
        if ($row->application_id && ($application = Application::with('event')->find($row->application_id))) {
            if ($user->role === 'volunteer' && $application->volunteer_id === $user->id) {
                return route('volunteer.applications.show', $application);
            }
            if ($user->role === 'organizer' && $application->event->organizer_id === $user->id) {
                return route('organizer.applications.show', $application);
            }
        }
        if ($user->role === 'organizer' && isset($row->data['order_id'])
            && ($order = Order::whereKey($row->data['order_id'])->whereHas('event', fn ($q) => $q->where('organizer_id', $user->id))->first())) {
            return route('organizer.orders.show', $order);
        }
        if ($user->role === 'organizer' && isset($row->data['event_id'])
            && ($event = Event::whereKey($row->data['event_id'])->where('organizer_id', $user->id)->first())) {
            return route('organizer.events.show', $event);
        }

        return match ($user->role) {
            'admin' => route('admin.organizers.index'),
            'organizer' => route('organizer.profile.edit'),
            default => route('volunteer.activity.index'),
        };
    }
}
