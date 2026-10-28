<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\AssessmentAttempt;
use App\Models\Event;
use App\Models\MatchResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ApplicationController extends Controller
{
    public function index(Request $request, Event $event)
    {
        Gate::authorize('update', $event);

        $query = $event->applications()
            ->whereNotNull('submitted_at')
            ->with([
                'volunteer.volunteerProfile.cityRecord',
                'position',
                'documents',
                'screeningResult',
                'matchResult',
                'assessmentAttempt',
            ]);

        if ($request->filled('position_id')) {
            $query->where('event_position_id', $request->input('position_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->input('tab') === 'ready') {
            $query->where('status', 'under_review');
        }
        $applications = $query
            ->orderByDesc(MatchResult::select('total_score')->whereColumn('application_id', 'applications.id')->limit(1))
            ->orderByDesc(AssessmentAttempt::select('score')->whereColumn('application_id', 'applications.id')->limit(1))
            ->orderBy('submitted_at')->orderBy('id')->paginate(15)->withQueryString();
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
            'screeningResult',
            'matchResult',
            'assessmentAttempt',
        ]);

        return view('organizer.applications.show', compact('application'));
    }
}
