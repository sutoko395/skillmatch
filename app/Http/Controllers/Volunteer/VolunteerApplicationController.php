<?php

namespace App\Http\Controllers\Volunteer;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\EventPosition;
use App\Services\ApplicationService;
use App\Services\ProfileEligibilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class VolunteerApplicationController extends Controller
{
    public function __construct(
        protected ApplicationService $applicationService,
        protected ProfileEligibilityService $profileEligibilityService
    ) {}

    public function store(Request $request, EventPosition $position)
    {
        $draft = $this->applicationService->storeDraft(Auth::user(), $position);

        return redirect()
            ->route('volunteer.applications.show', $draft)
            ->with('success', 'Draft lamaran berhasil dibuat.');
    }

    public function index(Request $request)
    {
        Gate::authorize('viewAny', Application::class);

        $applications = Application::with(['event.cityRecord', 'position'])
            ->where('volunteer_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('volunteer.applications.index', compact('applications'));
    }

    public function show(Application $application)
    {
        Gate::authorize('view', $application);

        $application->load([
            'event.organizer.organizerProfile',
            'event.cityRecord',
            'position.positionSkills.skill',
            'position.schedules',
            'position.requirements',
        ]);

        $eligibility = $this->profileEligibilityService->check(Auth::user());

        return view('volunteer.applications.show', compact('application', 'eligibility'));
    }

    public function submit(Request $request, Application $application)
    {
        Gate::authorize('submit', $application);

        $submittedDraft = $this->applicationService->submit($application, Auth::user());

        return redirect()
            ->route('volunteer.applications.show', $submittedDraft)
            ->with('success', 'Lamaran berhasil dikirimkan.');
    }
}
