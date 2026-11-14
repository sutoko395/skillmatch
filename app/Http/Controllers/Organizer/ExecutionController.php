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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class ExecutionController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('create', Event::class);

        $events = Event::where('organizer_id', $request->user()->id)
            ->withCount(['applications as accepted_applications_count' => fn ($query) => $query->where('status', 'accepted')])
            ->latest('created_at')->orderByDesc('id')->paginate(12);

        return view('organizer.execution.index', compact('events'));
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
            $query->whereHas('volunteer', fn ($q) => $q->where('name', 'like', '%'.$request->string('search')->limit(100).'%'));
        }
        $applications = $query->orderBy('id')->paginate(20)->withQueryString();
        $sessionSummary = AttendanceSession::whereHas('attendance.application', fn ($q) => $q->where('event_id', $event->id)->where('status', 'accepted'))
            ->selectRaw("COUNT(*) AS total, COALESCE(SUM(status = 'present'), 0) AS present, COALESCE(SUM(status = 'absent'), 0) AS absent, COALESCE(SUM(status = 'unrecorded'), 0) AS unrecorded")->first();

        return view('organizer.execution.show', compact('event', 'applications', 'summary', 'sessionSummary'));
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

    public function recordSession(Request $request, AttendanceSession $session, AttendanceSessionService $service)
    {
        $service->record($session, $request->user(), $request->all());

        return back()->with('success', 'Kehadiran sesi disimpan.');
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
