<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventPosition;
use App\Models\Skill;
use App\Services\EventService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PositionController extends Controller
{
    public function index(Event $event)
    {
        Gate::authorize('update', $event);

        return redirect()->route('organizer.events.show', $event);
    }

    public function show(Event $event, EventPosition $position)
    {
        Gate::authorize('update', $event);
        abort_unless($position->event_id === $event->id, 404);

        return redirect()->to(route('organizer.events.show', $event).'#position-'.$position->id);
    }

    public function create(Event $event, EventService $service)
    {
        Gate::authorize('update', $event);
        $service->editable($event);

        return $this->form($event, new EventPosition);
    }

    private function form(Event $event, EventPosition $position)
    {
        return view('organizer.positions.form', ['event' => $event, 'position' => $position->load(['positionSkills', 'schedules', 'requirements']), 'skills' => Skill::where('is_active', true)->orderBy('name')->get()]);
    }

    public function edit(Event $event, EventPosition $position, EventService $service)
    {
        Gate::authorize('update', $event);
        abort_unless($position->event_id === $event->id, 404);
        $service->editable($event);

        return $this->form($event, $position);
    }

    public function store(Request $r, Event $event, EventService $service)
    {
        $service->position($event, $r->user(), $r->all());

        return redirect()->route('organizer.events.show', $event)->with('success', 'Posisi tersimpan.');
    }

    public function update(Request $r, Event $event, EventPosition $position, EventService $service)
    {
        $service->position($event, $r->user(), $r->all(), $position);

        return redirect()->route('organizer.events.show', $event)->with('success', 'Posisi diperbarui.');
    }

    public function destroy(Request $r, Event $event, EventPosition $position, EventService $service)
    {
        $service->removePosition($event, $position, $r->user());

        return back()->with('success', 'Posisi draft dihapus.');
    }
}
