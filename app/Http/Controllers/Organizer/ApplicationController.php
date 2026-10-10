<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ApplicationController extends Controller
{
    public function index(Request $request, Event $event)
    {
        if ($event->organizer_id !== Auth::id()) {
            abort(403, 'Anda tidak berhak melihat pelamar pada kegiatan ini.');
        }

        $query = $event->applications()
            ->where('status', '!=', 'draft')
            ->with([
                'volunteer.volunteerProfile.cityRecord',
                'position',
                'documents',
            ]);

        if ($request->filled('position_id')) {
            $query->where('event_position_id', $request->input('position_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $applications = $query->latest('submitted_at')->paginate(15);
        $positions = $event->positions()->get();

        return view('organizer.applications.index', compact('event', 'applications', 'positions'));
    }

    public function show(Request $request, Application $application)
    {
        Gate::authorize('view', $application);

        $application->load([
            'event.cityRecord',
            'position.positionSkills.skill',
            'position.schedules',
            'position.requirements',
            'volunteer.volunteerProfile.cityRecord',
            'volunteer.volunteerSkills.skill',
            'volunteer.availabilitySlots',
            'documents',
        ]);

        return view('organizer.applications.show', compact('application'));
    }
}
