<aside class="min-w-0" aria-label="Ringkasan kegiatan">
    <x-section-card>
        <h2 class="text-lg font-bold text-slate-900">Informasi kegiatan</h2>
        <dl class="mt-5 space-y-5 divide-y divide-slate-100 text-sm">
            <div><dt class="font-medium text-slate-500">Lokasi kegiatan</dt><dd class="mt-2 break-words font-medium text-slate-900">{{ $event->location }} · {{ $event->cityRecord?->name }}</dd></div>
            <div class="pt-5"><dt class="font-medium text-slate-500">Pelaksanaan (WIB)</dt><dd class="mt-2 space-y-1 text-slate-900"><p>{{ $event->starts_at->copy()->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }}</p><p class="text-slate-500">sampai</p><p>{{ $event->ends_at->copy()->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }}</p></dd></div>
            <div class="pt-5"><dt class="font-medium text-slate-500">Pendaftaran dibuka (WIB)</dt><dd class="mt-2 text-slate-900">{{ $event->registration_opens_at?->copy()->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') ?? 'Belum tersedia' }}</dd></div>
            <div class="pt-5"><dt class="font-medium text-slate-500">Batas pendaftaran (WIB)</dt><dd class="mt-2 rounded-lg bg-blue-50 p-3 font-semibold text-blue-700">{{ $event->registration_deadline->copy()->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }}</dd></div>
        </dl>
    </x-section-card>
</aside>
