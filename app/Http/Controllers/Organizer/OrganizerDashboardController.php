<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrganizerDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $eventsQuery = Event::where('organizer_id', $user->id);

        $applicationsQuery = Application::whereHas('event', function ($query) use ($user) {
            $query->where('organizer_id', $user->id);
        })->where('status', '!=', 'draft');

        $totalEvents = (clone $eventsQuery)->count();

        $activeEvents = (clone $eventsQuery)
            ->where('status', 'approved')
            ->where('publication_status', 'published')
            ->whereIn('lifecycle_status', ['upcoming', 'ongoing'])
            ->count();

        $totalApplicants = (clone $applicationsQuery)->count();

        $pendingApplications = (clone $applicationsQuery)
            ->where('status', 'submitted')
            ->count();

        $latestApplications = (clone $applicationsQuery)
            ->with(['event', 'volunteer', 'position'])
            ->latest('submitted_at')
            ->limit(6)
            ->get();

        $myEvents = (clone $eventsQuery)
            ->withCount([
                'applications as applicants_count' => function ($query) {
                    $query->where('status', '!=', 'draft');
                },
            ])
            ->latest()
            ->limit(5)
            ->get();

        return view('organizer.dashboard', [
            'user' => $user,
            'profile' => $user->organizerProfile,
            'totalEvents' => $totalEvents,
            'activeEvents' => $activeEvents,
            'totalApplicants' => $totalApplicants,
            'pendingApplications' => $pendingApplications,
            'latestApplications' => $latestApplications,
            'myEvents' => $myEvents,
        ]);
    }
}
