<?php

namespace App\Http\Controllers\Volunteer;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Assessment;
use App\Models\EventPosition;
use App\Services\ApplicationService;
use App\Services\ProfileEligibilityService;
use Carbon\CarbonImmutable;
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

        $applications = Application::with(['event.cityRecord', 'position', 'screeningResult'])
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
            'documents',
            'screeningResult',
        ]);

        $eligibility = $this->profileEligibilityService->check(Auth::user());

        $snapshot = $application->status === 'draft' ? [] : ($application->snapshot_json ?? []);
        $recruitmentDates = [];
        foreach (['assessment_deadline', 'decision_deadline'] as $field) {
            $value = array_key_exists($field, $snapshot) ? $snapshot[$field] : $application->event->$field;
            $recruitmentDates[$field] = $value ? CarbonImmutable::parse($value)->setTimezone('Asia/Jakarta') : null;
        }

        // Only fetch published metadata for the version recorded when this application was submitted.
        $assessmentSummary = null;
        if ($application->status !== 'draft' && isset($snapshot['assessment_version'])) {
            $assessmentSummary = Assessment::query()
                ->where('event_position_id', $application->event_position_id)
                ->where('version', $snapshot['assessment_version'])
                ->where('is_published', true)
                ->select(['id', 'duration_minutes'])
                ->withCount('questions')
                ->first();
        }

        return view('volunteer.applications.show', compact('application', 'eligibility', 'recruitmentDates', 'assessmentSummary'));
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
