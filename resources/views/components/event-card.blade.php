@props(['event'])
<article class="group flex h-full flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:border-indigo-300 hover:shadow-md">
    <div class="flex items-center justify-between gap-3 bg-indigo-50 px-6 py-5">
        <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-indigo-700">{{ $event->category?->name ?? 'Kegiatan relawan' }}</span>
        <span class="text-sm font-semibold text-indigo-800">{{ $event->positions_count }} posisi</span>
    </div>
    <div class="flex flex-1 flex-col p-6">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $event->organizer->organizerProfile?->organization_name ?? $event->organizer->name }}</p>
        <h3 class="mt-2 text-xl font-bold leading-snug text-slate-900"><a href="{{ route('events.show', $event) }}" class="hover:text-indigo-700">{{ $event->title }}</a></h3>
        <p class="mt-3 text-sm leading-relaxed text-slate-600">{{ Str::limit($event->description, 140) }}</p>
        <dl class="mt-5 space-y-2 text-sm text-slate-600">
            <div class="flex gap-2"><dt class="font-medium text-slate-900">Kota</dt><dd>{{ $event->cityRecord?->name ?? $event->city }}</dd></div>
            <div><dt class="font-medium text-slate-900">Pelaksanaan</dt><dd>{{ $event->starts_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</dd></div>
            <div><dt class="font-medium text-slate-900">Batas pendaftaran</dt><dd>{{ $event->registration_deadline->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</dd></div>
        </dl>
        <a href="{{ route('events.show', $event) }}" class="mt-6 inline-flex min-h-[44px] items-center justify-between gap-3 border-t border-slate-100 pt-4 text-sm font-semibold text-indigo-700">Lihat posisi dan persyaratan <span aria-hidden="true">&rarr;</span></a>
    </div>
</article>
