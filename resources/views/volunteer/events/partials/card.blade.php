<x-section-card class="flex min-w-0 flex-col gap-4">
    @if($event->cover_image_path)<x-event-cover :event="$event" />@endif
    <div>
        <p class="text-xs font-medium text-slate-500">{{ $event->category?->name }}</p>
        <h2 class="mt-2 break-words text-xl font-bold text-slate-900">{{ $event->title }}</h2>
        <p class="mt-2 break-words text-sm text-slate-600">{{ $event->organizer->organizerProfile?->organization_name ?? $event->organizer->name }}</p>
    </div>
    <dl class="space-y-3 text-sm">
        <div><dt class="text-slate-500">Lokasi</dt><dd class="mt-1 break-words">{{ $event->location }} · {{ $event->cityRecord?->name }}</dd></div>
        <div><dt class="text-slate-500">Waktu kegiatan (WIB)</dt><dd class="mt-1">{{ $event->starts_at->copy()->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }}</dd></div>
        <div><dt class="text-slate-500">Posisi tersedia</dt><dd class="mt-1">{{ $event->positions_count }} posisi</dd></div>
    </dl>
    <a href="{{ route('events.show', $event) }}" class="mt-auto inline-flex justify-center rounded-lg bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700">Lihat Detail Event</a>
</x-section-card>
