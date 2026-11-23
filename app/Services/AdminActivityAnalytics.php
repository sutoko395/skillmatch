<?php

namespace App\Services;

use App\Models\Application;
use App\Models\Event;
use Carbon\CarbonInterface;

class AdminActivityAnalytics
{
    public function summarize(CarbonInterface $start, CarbonInterface $end): array
    {
        $selection = Application::whereBetween('submitted_at', [$start, $end])
            ->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');
        $submitted = (int) $selection->sum();
        $accepted = (int) $selection->get('accepted', 0);
        $rejected = (int) $selection->get('rejected', 0);
        $pending = (int) $selection->only(SelectionService::PENDING)->sum();

        $now = now();
        $published = Event::whereNotNull('published_at');
        $open = (clone $published)->whereIn('lifecycle_status', ['upcoming', 'ongoing']);
        $ongoing = (clone $open)->where('starts_at', '<=', $now)->where('ends_at', '>', $now)->count();
        $awaitingCompletion = (clone $open)->where('ends_at', '<=', $now)->count();
        $completed = (clone $published)->where('lifecycle_status', 'completed')->count();

        $attendance = Application::where('applications.status', 'accepted')
            ->join('events', 'events.id', '=', 'applications.event_id')
            ->leftJoin('attendances', 'attendances.application_id', '=', 'applications.id')
            ->whereNotNull('events.published_at')
            ->where('events.lifecycle_status', '!=', 'cancelled')
            ->where('events.starts_at', '<=', $now)
            ->selectRaw('attendances.status as attendance_status, COUNT(*) as total')
            ->groupBy('attendances.status')->pluck('total', 'attendance_status');
        $present = (int) $attendance->get('present', 0);
        $absent = (int) $attendance->get('absent', 0);

        return [
            'applications' => ['submitted' => $submitted, 'accepted' => $accepted, 'rejected' => $rejected,
                'pending' => $pending, 'other' => $submitted - $accepted - $rejected - $pending],
            'events' => ['ongoing' => $ongoing, 'active' => Event::publiclyVisible()->count(),
                'awaiting_completion' => $awaitingCompletion, 'completed' => $completed],
            'attendance' => ['present' => $present, 'absent' => $absent,
                'unrecorded' => (int) $attendance->sum() - $present - $absent],
        ];
    }
}
