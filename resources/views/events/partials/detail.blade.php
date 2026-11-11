<div class="ve-detail mx-auto max-w-7xl space-y-6">
    <a href="{{ route($catalogRoute) }}" class="inline-flex items-center rounded-xl border border-blue-200 bg-white px-4 py-3 text-sm font-semibold text-blue-600 hover:bg-blue-50">&larr; Jelajahi Event</a>
    <header>
        <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-blue-600">{{ $event->category?->name ?? 'Kegiatan volunteer' }}</p>
        <h1 class="break-words text-3xl font-bold text-slate-900">{{ $event->title }}</h1>
        <p class="mt-2 break-words text-sm text-slate-600">Diselenggarakan oleh {{ $event->organizer->organizerProfile?->organization_name ?? $event->organizer->name }}</p>
    </header>
    <div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1.7fr)_minmax(0,1fr)]">
        <div class="min-w-0 space-y-6">
            @if($event->cover_image_path)<x-event-cover :event="$event" />@endif
            <x-section-card>
                <h2 class="text-lg font-bold text-slate-900">Tentang kegiatan</h2>
                <p class="mt-4 whitespace-pre-line break-words text-sm leading-relaxed text-slate-600">{{ $event->description }}</p>
            </x-section-card>
            <section class="space-y-4" aria-labelledby="event-positions-heading">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h2 id="event-positions-heading" class="text-xl font-bold text-slate-900">Posisi volunteer</h2>
                    <span class="text-sm text-slate-500">{{ $event->positions->count() }} posisi</span>
                </div>
                @forelse($event->positions as $position)
                    @include('volunteer.events.partials.position')
                @empty
                    <x-section-card><p class="text-sm text-slate-500">Belum ada posisi yang tersedia.</p></x-section-card>
                @endforelse
            </section>
        </div>
        @include('volunteer.events.partials.summary')
    </div>
</div>
