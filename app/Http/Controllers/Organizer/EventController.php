<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\City;
use App\Models\Event;
use App\Models\Package;
use App\Services\EventPublicationService;
use App\Services\EventService;
use App\Services\PackageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class EventController extends Controller
{
    public function text(Event $event)
    {
        Gate::authorize('update', $event);

        abort_unless(
            $event->published_at &&
            ! in_array($event->lifecycle_status, ['completed', 'cancelled']),
            409
        );

        return view('organizer.events.text', compact('event'));
    }

    public function correctText(Request $r, Event $event, EventService $service)
    {
        $service->correctText($event, $r->user(), $r->all());

        return redirect()
            ->route('organizer.events.show', $event)
            ->with('success', 'Koreksi teks tersimpan; aturan posisi, jadwal dan paket tetap.');
    }

    public function destroy(Request $r, Event $event, EventService $service)
    {
        $service->removeDraft($event, $r->user());

        return redirect()
            ->route('organizer.events.index')
            ->with('success', 'Draft awal dihapus.');
    }

    public function index(Request $r)
    {
        return view('organizer.events.index', [
            'events' => Event::where('organizer_id', $r->user()->id)
                ->latest()
                ->paginate(12),
            'cities' => City::where('is_active', true)->orderBy('name')->get(),
            'packages' => $this->formPackages(),
            'categories' => Category::where('is_active', true)
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function create()
    {
        Gate::authorize('create', Event::class);

        return $this->form(new Event);
    }

    private function form(Event $event)
    {
        return view('organizer.events.form', [
            'event' => $event,
            'packages' => $this->formPackages($event),
            'cities' => City::where('is_active', true)->orderBy('name')->get(),
            'categories' => Category::where('is_active', true)
                ->orderBy('name')
                ->get(),
        ]);
    }

    private function formPackages(?Event $event = null)
    {
        $packages = Package::where('is_active', true)->orderBy('price')->get()->map(fn ($package) => $package->snapshot());
        // Editing the same package keeps its historical price and limits, including inactive master rows.
        if ($event?->package_snapshot) {
            $snapshot = $event->package_snapshot;
            $packages = $packages->reject(fn ($package) => (int) $package['id'] === (int) $snapshot['id'])->prepend($snapshot);
        }

        return $packages->values();
    }

    public function store(Request $r, EventService $service)
    {
        Gate::authorize('create', Event::class);

        $event = $service->save($r->user(), $r->all());

        return redirect()
            ->route('organizer.events.index')
            ->with('success', 'Event berhasil dibuat.');
    }

    public function edit(Event $event, EventService $service)
    {
        Gate::authorize('update', $event);

        $service->editable($event);

        return $this->form($event);
    }

    public function update(Request $r, Event $event, EventService $service)
    {
        Gate::authorize('update', $event);

        $service->save($r->user(), $r->all(), $event);

        return redirect()
            ->route('organizer.events.show', $event)
            ->with('success', 'Event diperbarui.');
    }

    public function show(Event $event)
    {
        Gate::authorize('update', $event);

        return view('organizer.events.show', [
            'event' => $event->load([
                'positions.positionSkills.skill',
                'positions.schedules',
                'positions.requirements',
                'positions.assessments' => fn ($query) => $query->withCount('questions'),
                'entitlement',
                'orders',
            ]),
        ]);
    }

    public function submit(Request $r, Event $event, EventService $service)
    {
        $service->submit($event, $r->user());

        return back()->with('success', 'Event diajukan untuk moderasi.');
    }

    public function publish(
        Request $r,
        Event $event,
        EventPublicationService $service
    ) {
        $service->publish($event, $r->user());

        return back()->with('success', 'Event dipublikasikan.');
    }

    public function cancel(
        Request $r,
        Event $event,
        EventPublicationService $service
    ) {
        $data = $r->validate([
            'reason' => 'required|string|min:5|max:1000',
        ]);

        $service->cancel($event, $r->user(), $data['reason']);

        return back()->with('success', 'Event dibatalkan.');
    }

    public function package(Event $event)
    {
        Gate::authorize('update', $event);

        return view('organizer.events.package', [
            'event' => $event,
            'packages' => Package::where('is_active', true)
                ->orderBy('price')
                ->paginate(12),
        ]);
    }

    public function selectPackage(
        Request $r,
        Event $event,
        PackageService $service
    ) {
        Gate::authorize('update', $event);

        $data = $r->validate([
            'package_id' => 'required|integer',
        ]);

        $service->select(
            $event,
            $r->user(),
            $data['package_id']
        );

        return redirect()
            ->route('organizer.events.show', $event)
            ->with('success', 'Paket dipilih dan manfaat disnapshot.');
    }
}
