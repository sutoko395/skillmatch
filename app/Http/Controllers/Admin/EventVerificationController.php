<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\User;
use App\Services\AuditService;
use App\Services\EntitlementService;
use App\Services\EventConfiguration;
use App\Services\EventPublicationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EventVerificationController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();

        $events = Event::with(['organizer', 'category'])
            ->where('status', 'pending')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%")
                        ->orWhereHas('organizer', function ($query) use ($search) {
                            $query->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $pendingEvents = Event::where('status', 'pending')->count();
        $approvedEvents = Event::where('status', 'approved')->count();
        $rejectedEvents = Event::where('status', 'rejected')->count();

        return view('admin.event-verification.index', compact(
            'events',
            'pendingEvents',
            'approvedEvents',
            'rejectedEvents',
            'search'
        ));
    }

    public function show(Event $event): View
    {
        $event->load([
            'organizer',
            'category',
            'positions.positionSkills.skill',
            'positions.requirements',
            'positions.schedules',
        ]);

        return view('admin.event-verification.show', compact('event'));
    }

    public function approve(Event $event): RedirectResponse
    {
        DB::transaction(function () use ($event) {
            User::lockForUpdate()->findOrFail($event->organizer_id);
            $event = Event::lockForUpdate()->findOrFail($event->id);
            abort_unless($event->status === 'pending', 409, 'Event sudah diproses.');
            app(EventConfiguration::class)->assertValid($event);
            $event->update(['status' => 'approved', 'verification_note' => null, 'verified_at' => now()]);
            app(AuditService::class)->record(auth()->user(), 'event.approved', $event, ['after' => ['status' => 'approved']]);
            app(EntitlementService::class)->activate($event);
        }, 3);
        try {
            app(EventPublicationService::class)->publish($event->fresh(), auth()->user());
        } catch (ValidationException) { /* Approval retained; paid package still awaits verified payment. */
        }

        return redirect()
            ->route('admin.event-verification.index')
            ->with('success', 'Event disetujui. Publikasi tetap mengikuti kelengkapan dan hak paket.');
    }

    public function reject(Request $request, Event $event): RedirectResponse
    {
        $validated = $request->validate([
            'verification_note' => [
                'required',
                'string',
                'max:1000',
            ],
        ]);

        DB::transaction(function () use ($event, $validated) {
            User::lockForUpdate()->findOrFail($event->organizer_id);
            $event = Event::lockForUpdate()->findOrFail($event->id);
            abort_unless($event->status === 'pending', 409, 'Event sudah diproses.');
            $event->update(['status' => 'rejected', 'verification_note' => $validated['verification_note'], 'verified_at' => now()]);
            app(AuditService::class)->record(auth()->user(), 'event.rejected', $event, ['after' => ['status' => 'rejected']], 'revision_required');
        }, 3);

        return redirect()
            ->route('admin.event-verification.index')
            ->with('success', 'Event berhasil ditolak.');
    }
}
