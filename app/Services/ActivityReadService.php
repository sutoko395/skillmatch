<?php

namespace App\Services;

use App\Models\Application;
use App\Models\Event;
use App\Models\NotificationOutbox;
use App\Models\Order;
use App\Models\User;

class ActivityReadService
{
    public function forVolunteer(User $user): array
    {
        abort_unless($user->is_active && $user->role === 'volunteer', 403);
        $query = Application::where('volunteer_id', $user->id)->whereNotNull('submitted_at');

        return [
            'applicationCount' => (clone $query)->count(),
            'applications' => (clone $query)->with(['event', 'position'])->latest('submitted_at')->paginate(10),
            'assessmentApplications' => (clone $query)->where('status', 'assessment_pending')
                ->whereHas('event', fn ($q) => $q->where('lifecycle_status', 'upcoming')->where('starts_at', '>', now()))
                ->with(['event', 'position'])->orderBy('id')->limit(6)->get(),
            'acceptedApplications' => (clone $query)->where('status', 'accepted')
                ->whereHas('event', fn ($q) => $q->whereNotIn('lifecycle_status', ['completed', 'cancelled'])->where('ends_at', '>', now()))
                ->with(['event', 'position'])->orderBy('id')->limit(6)->get(),
        ];
    }

    public function forOrganizer(User $user): array
    {
        abort_unless($user->is_active && $user->role === 'organizer', 403);
        $events = Event::where('organizer_id', $user->id);
        $applications = Application::whereHas('event', fn ($q) => $q->where('organizer_id', $user->id))->whereNotNull('submitted_at');

        return [
            'totalEvents' => (clone $events)->count(),
            'activeEvents' => (clone $events)->where('status', 'approved')->where('publication_status', 'published')
                ->whereIn('lifecycle_status', ['upcoming', 'ongoing'])->where('ends_at', '>', now())->count(),
            'totalApplicants' => (clone $applications)->count(),
            'pendingApplications' => (clone $applications)->where('status', 'under_review')
                ->whereHas('event', fn ($q) => $q->where('lifecycle_status', 'upcoming')->where('starts_at', '>', now())
                    ->where(fn ($q) => $q->whereNull('decision_deadline')->orWhere('decision_deadline', '>', now())))->count(),
            'latestApplications' => (clone $applications)->with(['event', 'volunteer', 'position'])->latest('submitted_at')->limit(6)->get(),
            'myEvents' => (clone $events)->withCount(['applications as applicants_count' => fn ($q) => $q->whereNotNull('submitted_at')])->latest()->limit(5)->get(),
            'actionEvents' => (clone $events)->whereNotIn('lifecycle_status', ['completed', 'cancelled'])
                ->where(fn ($q) => $q->whereIn('status', ['pending', 'rejected'])->orWhere('publication_status', 'unpublished')
                    ->orWhereNull('assessment_deadline')->orWhereHas('applications', fn ($q) => $q->where('status', 'under_review')))
                ->orderBy('starts_at')->limit(6)->get(),
            'attendanceEvents' => (clone $events)->whereNotIn('lifecycle_status', ['completed', 'cancelled'])
                ->whereNotNull('published_at')->where('starts_at', '<=', now())->orderBy('starts_at')->limit(6)->get(),
        ];
    }

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
