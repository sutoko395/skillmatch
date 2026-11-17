<section class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6" aria-label="Informasi kegiatan">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <span class="rounded-lg bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">{{ match($event->status) { 'pending' => 'Menunggu verifikasi', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', default => 'Draf' } }}</span>
        <span class="text-xs text-slate-500">Diajukan {{ $event->submitted_at ? $event->submitted_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i').' WIB' : 'Belum tercatat' }}</span>
    </div>
    <h2 class="mt-4 break-words">{{ $event->title }}</h2>
    <p class="mt-2 whitespace-pre-line break-words text-sm leading-relaxed text-slate-600">{{ $event->description }}</p>
    <dl class="mt-5 grid gap-4 rounded-xl bg-slate-50 p-4 sm:grid-cols-2">
        <div><dt class="text-xs text-slate-500">Kategori</dt><dd class="mt-1 text-sm font-medium">{{ $event->category?->name ?? 'Belum tersedia' }}</dd></div>
        <div><dt class="text-xs text-slate-500">Lokasi</dt><dd class="mt-1 text-sm font-medium">{{ $event->location }} &middot; {{ $event->cityRecord?->name ?? $event->city }}@if($event->cityRecord?->province), {{ $event->cityRecord->province->name }}@endif</dd></div>
    </dl>
    <div class="mt-5 border-t border-slate-100 pt-4">
        <h3 class="flex items-center gap-2"><svg class="h-4 w-4 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M7 3v4m10-4v4M3 11h18"/></svg>Jadwal kegiatan dan pendaftaran</h3>
        <dl class="mt-4 grid gap-4 sm:grid-cols-2">
            @foreach(['starts_at' => 'Mulai kegiatan', 'ends_at' => 'Selesai kegiatan', 'registration_opens_at' => 'Pendaftaran dibuka', 'registration_deadline' => 'Pendaftaran ditutup', 'assessment_deadline' => 'Tenggat asesmen', 'decision_deadline' => 'Batas keputusan seleksi'] as $field => $label)
                <div><dt class="text-xs text-slate-500">{{ $label }}</dt><dd class="mt-1 text-sm">{{ $event->$field ? $event->$field->setTimezone('Asia/Jakarta')->format('d/m/Y H:i').' WIB' : 'Belum tersedia' }}</dd></div>
            @endforeach
        </dl>
    </div>
</section>
