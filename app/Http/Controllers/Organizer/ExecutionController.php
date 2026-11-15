<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\Event;
use App\Services\AttendanceSessionService;
use App\Services\EventExecutionService;
use App\Services\SelectionService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class ExecutionController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('create', Event::class);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'phase' => ['nullable', 'string', 'in:today,ongoing,history'],
        ]);
        $search = trim($filters['search'] ?? '');
        $phase = $filters['phase'] ?? '';
        $now = CarbonImmutable::now('UTC');
        $tomorrowWib = $now->setTimezone('Asia/Jakarta')->startOfDay()->addDay()->utc();
        $query = Event::where('organizer_id', $request->user()->id)
            ->whereNotNull('published_at')
            ->where(fn ($query) => $query->where('starts_at', '<', $tomorrowWib)
                ->orWhereIn('lifecycle_status', ['completed', 'cancelled']))
            ->when($search !== '', function ($query) use ($search) {
                $literal = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $search);
                $query->whereRaw("title LIKE ? ESCAPE '!'", ['%'.$literal.'%']);
            })
            ->withCount(['applications as accepted_applications_count' => fn ($query) => $query->where('status', 'accepted')]);
        if ($phase === 'history') {
            $query->where(fn ($query) => $query->whereIn('lifecycle_status', ['completed', 'cancelled'])
                ->orWhere('publication_status', '!=', 'published')->orWhere('ends_at', '<=', $now));
        } elseif (in_array($phase, ['today', 'ongoing'], true)) {
            $query->whereNotIn('lifecycle_status', ['completed', 'cancelled'])
                ->where('publication_status', 'published')
                ->where(fn ($query) => $query->whereNull('ends_at')->orWhere('ends_at', '>', $now))
                ->where('starts_at', $phase === 'today' ? '>' : '<=', $now);
        }
        $events = $query
            ->orderByRaw("CASE WHEN lifecycle_status IN ('completed', 'cancelled') OR publication_status <> 'published' OR ends_at <= ? THEN 1 ELSE 0 END", [$now])
            ->orderByDesc('starts_at')->orderByDesc('id')->paginate(12)
            ->appends(['search' => $search, 'phase' => $phase]);
        $executionGroups = $events->getCollection()->groupBy(function ($event) use ($now) {
            if (in_array($event->lifecycle_status, ['completed', 'cancelled'], true)
                || $event->publication_status !== 'published'
                || ($event->ends_at && $event->ends_at->lte($now))) {
                return 'history';
            }

            return $event->starts_at && $event->starts_at->gt($now) ? 'today' : 'ongoing';
        });

        return view('organizer.execution.index', compact('events', 'executionGroups', 'now', 'search', 'phase'));
    }

    public function show(Request $request, Event $event)
    {
        Gate::authorize('update', $event);
        $summary = $event->applications()->where('applications.status', 'accepted')
            ->leftJoin('attendances', 'attendances.application_id', '=', 'applications.id')
            ->selectRaw("COUNT(*) AS accepted, COALESCE(SUM(attendances.status = 'present'), 0) AS present, COALESCE(SUM(attendances.status = 'absent'), 0) AS absent, COALESCE(SUM(attendances.status = 'unrecorded' OR attendances.id IS NULL), 0) AS unrecorded")
            ->first();
        $query = $event->applications()->where('status', 'accepted')->with(['volunteer', 'position', 'attendance.sessions']);
        $filters = $request->validate(['session_date' => 'nullable|date_format:Y-m-d', 'position_id' => 'nullable|integer',
            'status' => 'nullable|in:unrecorded,present,absent', 'search' => 'nullable|string|max:100']);
        if (! empty($filters['session_date'])) {
            $query->whereHas('attendance.sessions', fn ($q) => $q->where('session_date', $filters['session_date']));
        }
        if ($request->filled('position_id')) {
            $query->where('event_position_id', $request->input('position_id'));
        }
        if ($request->filled('status')) {
            $query->whereHas('attendance', function ($q) use ($filters) {
                $q->where(fn ($q) => $q->where('uses_sessions', false)->where('status', $filters['status']))
                    ->orWhere(fn ($q) => $q->where('uses_sessions', true)->whereHas('sessions', function ($q) use ($filters) {
                        $q->where('status', $filters['status']);
                        if (! empty($filters['session_date'])) {
                            $q->where('session_date', $filters['session_date']);
                        }
                    }));
            });
        }
        if ($request->filled('search')) {
             $literal = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], trim($request->input('search')));
            $query->whereHas('volunteer', fn ($q) => $q->whereRaw("name LIKE ? ESCAPE '!'", ['%'.$literal.'%']));
        }
        $applications = $query->orderBy('id')->paginate(20)->withQueryString();
        $sessionSummary = AttendanceSession::whereHas('attendance.application', fn ($q) => $q->where('event_id', $event->id)->where('status', 'accepted'))
            ->selectRaw("COUNT(*) AS total, COALESCE(SUM(status = 'present'), 0) AS present, COALESCE(SUM(status = 'absent'), 0) AS absent, COALESCE(SUM(status = 'unrecorded'), 0) AS unrecorded")->first();

        $pendingSessionApplications = in_array($event->lifecycle_status, ['completed', 'cancelled'], true) ? []
            : $event->applications()->where('status', 'accepted')
                ->where(fn ($query) => $query->whereDoesntHave('attendance')
                    ->orWhereHas('attendance', fn ($query) => $query->where('uses_sessions', false)->where('status', 'unrecorded')))
                ->orderBy('id')->limit(100)->pluck('id')->all();

        $hasPendingSelection = $event->applications()->whereIn('status', SelectionService::PENDING)->exists()
            || $event->applications()->whereHas('assessmentAttempt', fn ($query) => $query->where('status', 'in_progress'))->exists();

        $completionIssues = [];
        if (!$event->published_at) $completionIssues[] = 'Kegiatan belum pernah dipublikasikan.';
        if (!$event->ends_at || now()->lt($event->ends_at)) $completionIssues[] = 'Jadwal kegiatan belum berakhir.';
        $accepted = $event->applications()->where('status', 'accepted')->with('attendance.sessions')->get();
        $unfinished = 0;
        foreach ($accepted as $application) {
            $attendance = $application->attendance;
            if ($attendance?->uses_sessions) {
                try {
                    $expected = collect(app(AttendanceSessionService::class)->definitions($application))->pluck('session_key')->sort()->values()->all();
                } catch (ValidationException) {
                    $unfinished++;
                    continue;
                }
                if ($attendance->sessions->pluck('session_key')->sort()->values()->all() !== $expected
                    || $attendance->sessions->contains(fn ($session) => !in_array($session->status, ['present', 'absent'], true) || $session->ends_at->isFuture())) $unfinished++;
            } elseif (!in_array($attendance?->status, ['present', 'absent'], true)) $unfinished++;
        }
        if ($unfinished) $completionIssues[] = $unfinished.' peserta masih memiliki sesi yang belum berakhir atau absensi belum lengkap.';
        $acceptedCount = $accepted->count();

        return view('organizer.execution.show', compact('event', 'applications', 'summary', 'sessionSummary', 'pendingSessionApplications', 'hasPendingSelection', 'completionIssues', 'acceptedCount'));
    }

    public function accept(Request $request, Application $application, SelectionService $service)
    {
        try {
            $service->accept($application, $request->user());
        } catch (ValidationException $exception) {
            if ($request->expectsJson()) {
                throw $exception;
            }

            return redirect()->route('organizer.applications.show', $application)
                ->with('error', collect($exception->errors())->flatten()->first());
        }

        return redirect()->route('organizer.applications.show', $application)
            ->with('success', 'Pelamar diterima. Kehadiran belum dicatat.');
    }

    public function reject(Request $request, Application $application, SelectionService $service)
    {
        $data = $request->validate(['reason' => 'required|string|min:5|max:1000']);
        $service->reject($application, $request->user(), $data['reason']);

        return back()->with('success', 'Keputusan penolakan disimpan.');
    }

    public function withdraw(Request $request, Application $application, SelectionService $service)
    {
        $data = $request->validate(['reason' => 'nullable|string|max:1000']);
        $service->withdraw($application, $request->user(), $data['reason'] ?? null);

        return back()->with('success', 'Lamaran ditarik.');
    }

    public function close(Request $request, Event $event, SelectionService $service)
    {
        $data = $request->validate(['reason' => 'required|string|min:5|max:1000', 'confirmed' => 'accepted']);
        $service->close($event, $request->user(), $data['reason']);

        return back()->with('success', 'Lamaran tertunda ditutup dengan alasan.');
    }

    public function record(Request $request, Attendance $attendance, EventExecutionService $service)
    {
        $service->record($attendance, $request->user(), $request->all());

        return back()->with('success', 'Kehadiran disimpan.');
    }

    public function prepareSessions(Request $request, Attendance $attendance, AttendanceSessionService $service)
    {
        $service->prepare($attendance, $request->user(), $request->all());

        return back()->with('success', 'Sesi tugas disiapkan. Setiap sesi harus dicatat sesuai kehadiran sebenarnya.');
    }

    public function initializeSessions(Request $request, Event $event, AttendanceSessionService $service)
    {
        Gate::authorize('update', $event);
        $data = $request->validate(['applications' => 'required|array|min:1|max:100',
            'applications.*' => 'required|integer|distinct|min:1']);
        $prepared = $service->initializeUnrecorded($event, $request->user(), $data['applications']);

        if ($request->expectsJson()) {
            return response()->json(['prepared' => $prepared]);
        }

        return back();
    }

    public function recordSession(Request $request, AttendanceSession $session, AttendanceSessionService $service)
    {
        $service->record($session, $request->user(), $request->all());

        return back()->with('success', 'Kehadiran sesi disimpan.')
            ->with('attendance_application_id', $session->attendance->application_id);
    }

    public function updateSessionNote(Request $request, AttendanceSession $session, AttendanceSessionService $service)
    {
        $service->updateNote($session, $request->user(), $request->all());

        return back()->with('success', 'Catatan sesi disimpan.')
            ->with('attendance_application_id', $session->attendance->application_id);
    }

    public function prepareApplicationSessions(Request $request, Application $application, AttendanceSessionService $service)
    {
        $service->prepareApplication($application, $request->user(), $request->all());

        return back()->with('success', 'Absensi sesi peserta disiapkan dari snapshot jadwal tugas.');
    }

    public function complete(Request $request, Event $event, EventExecutionService $service)
    {
        $request->validate(['confirmed' => 'accepted', 'confirm_zero' => 'sometimes|boolean']);
        $service->complete($event, $request->user(), $request->boolean('confirm_zero'));

        return back()->with('success', 'Kegiatan selesai. Riwayat peserta diperbarui.');
    }
}
