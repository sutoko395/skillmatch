@props(['event'])
<div {{ $attributes->class(['aspect-video w-full overflow-hidden rounded-xl border border-slate-200 bg-blue-50']) }}>
    @if($event->cover_image_path)
        <img src="{{ route('events.cover', $event) }}" alt="Foto sampul {{ $event->title }}" class="h-full w-full object-cover" loading="lazy">
    @else
        <div class="flex h-full flex-col items-center justify-center gap-3 text-blue-600">
            <svg class="h-12 w-12" viewBox="0 0 48 48" fill="none" aria-hidden="true"><rect x="5" y="8" width="38" height="32" rx="6" stroke="currentColor" stroke-width="2"/><circle cx="32" cy="18" r="4" fill="#FB923C"/><path d="m9 35 11-13 9 10 5-6 6 9" stroke="currentColor" stroke-width="2"/></svg>
            <span class="text-sm text-slate-500">Kegiatan relawan</span>
        </div>
    @endif
</div>
