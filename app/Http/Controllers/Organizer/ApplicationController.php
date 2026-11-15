<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\AssessmentAttempt;
use App\Models\Event;
use App\Models\MatchResult;
use App\Services\SelectionService;
use Illuminate\Http\Request;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Gate;

class ApplicationController extends Controller
{
    public function overview(Request $request)
    {
        Gate::authorize('create', Event::class);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'recruitment' => ['nullable', 'in:open,closed,history'],
        ]);
        $search = trim($filters['search'] ?? '');
        $recruitment = $filters['recruitment'] ?? '';
        $now = now('UTC');
        $query = Event::where('organizer_id', $request->user()->id)
            ->whereNotNull('published_at')
            ->when($search !== '', function ($query) use ($search) {
                $literal = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $search);
                $query->whereRaw("title LIKE ? ESCAPE '!'", ['%'.$literal.'%']);
            });
        if ($recruitment === 'history') {
            $query->where(fn ($query) => $query->whereIn('lifecycle_status', ['completed', 'cancelled'])
                ->orWhere('publication_status', '!=', 'published'));
        } elseif (in_array($recruitment, ['open', 'closed'], true)) {
            $query->where('publication_status', 'published')->whereNotIn('lifecycle_status', ['completed', 'cancelled']);
            if ($recruitment === 'open') {
                $query->where('registration_opens_at', '<=', $now)->where('registration_deadline', '>', $now);
            } else {
                $query->where('registration_deadline', '<=', $now);
            }
        }
        $events = $query
            ->withCount(['applications as submitted_applications_count' => fn ($query) => $query->whereNotNull('submitted_at')])
            ->latest('created_at')->orderByDesc('id')->paginate(12)
            ->appends(['search' => $search, 'recruitment' => $recruitment]);

        return view('organizer.applications.overview', compact('events', 'search', 'recruitment'));
    }

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

        $pendingSelectionCount = $event->applications()->whereIn('status', SelectionService::PENDING)->count();

        return view('organizer.applications.index', compact('event', 'applications', 'positions', 'pendingSelectionCount'));
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

        $profile = $application->snapshot_json['profile'] ?? [];
        $ageFromCurrentProfile = !array_key_exists('birth_date', $profile);
        $birthDate = $ageFromCurrentProfile
            ? $application->volunteer->volunteerProfile?->birth_date?->format('Y-m-d')
            : $profile['birth_date'];
        $ageAtEvent = null;
        if (is_string($birthDate) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $birthDate) && $application->event->starts_at) {
            try {
                $birth = CarbonImmutable::createFromFormat('!Y-m-d', $birthDate, 'Asia/Jakarta');
                $eventDate = $application->event->starts_at->setTimezone('Asia/Jakarta')->startOfDay();
                if ($birth->format('Y-m-d') === $birthDate && $birth->lte($eventDate)) {
                    $ageAtEvent = (int) $birth->diffInYears($eventDate);
                }
            } catch (\Throwable) {
                // Invalid historical dates are shown as unavailable, never as an inferred age.
            }
        }

        return view('organizer.applications.show', compact('application', 'ageAtEvent', 'ageFromCurrentProfile'));
    }
}
