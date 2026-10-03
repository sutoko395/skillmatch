<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            'positions.skills',
            'positions.requirements',
        ]);

        return view('admin.event-verification.show', compact('event'));
    }

    public function approve(Event $event): RedirectResponse
    {
        if ($event->status !== 'pending') {
            return back()->with('error', 'Event ini sudah diproses.');
        }

        $event->update([
            'status' => 'approved',
            'verification_note' => null,
            'verified_at' => now(),
        ]);

        return redirect()
            ->route('admin.event-verification.index')
            ->with('success', 'Event berhasil disetujui dan sekarang dapat ditampilkan kepada volunteer.');
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

        if ($event->status !== 'pending') {
            return back()->with('error', 'Event ini sudah diproses.');
        }

        $event->update([
            'status' => 'rejected',
            'verification_note' => $validated['verification_note'],
            'verified_at' => now(),
        ]);

        return redirect()
            ->route('admin.event-verification.index')
            ->with('success', 'Event berhasil ditolak.');
    }
}