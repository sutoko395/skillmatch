<section class="rounded-2xl border border-slate-200 bg-white p-5" aria-label="Identitas EO">
    <h2>EO / Organisasi</h2>
    <p class="mt-3 text-sm font-semibold">{{ $event->organizer?->organizerProfile?->organization_name ?? $event->organizer?->name ?? 'Tidak tersedia' }}</p>
    <p class="mt-1 break-all text-xs text-slate-500">{{ $event->organizer?->email }}</p>
    <p class="mt-3 text-xs text-slate-600">Status organisasi: {{ match($event->organizer?->organizer_status) { 'active' => 'Disetujui', 'inactive' => 'Perlu revisi', default => 'Menunggu verifikasi' } }}</p>
</section>
<section class="rounded-2xl border border-slate-200 bg-white p-5" aria-label="Paket pengajuan">
    <h2>Paket kegiatan</h2>
    @if($event->package_snapshot)
        <p class="mt-3 text-sm font-semibold">{{ $event->package_snapshot['name'] ?? 'Tidak tersedia' }}</p>
        <p class="mt-1 text-sm text-blue-600">Rp {{ number_format($event->package_snapshot['price'] ?? 0, 0, ',', '.') }}</p>
        <dl class="mt-3 space-y-2 text-xs text-slate-600">
            @foreach(['max_positions' => 'Maksimum posisi', 'max_applications' => 'Maksimum lamaran', 'max_registration_days' => 'Durasi pendaftaran (hari)'] as $key => $label)
                <div class="flex justify-between gap-3"><dt>{{ $label }}</dt><dd class="font-semibold text-slate-900">{{ $event->package_snapshot[$key] ?? '-' }}</dd></div>
            @endforeach
        </dl>
    @else<p class="mt-3 text-sm text-slate-500">Paket belum dipilih.</p>@endif
</section>
@if($event->verification_note || $event->verified_at)
<section id="verification-note" style="scroll-margin-top:96px" class="rounded-2xl border border-amber-200 bg-amber-50 p-5" aria-label="Keputusan pemeriksaan terakhir">
    <h2>Pemeriksaan terakhir</h2>
    <p class="mt-2 text-xs text-amber-800">{{ $event->verified_at ? $event->verified_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i').' WIB' : 'Waktu keputusan belum tercatat' }}</p>
    @if($event->verification_note)<p class="mt-3 whitespace-pre-line text-sm text-amber-900">{{ $event->verification_note }}</p>@endif
    @if($event->status === 'pending')<p class="mt-3 text-xs text-amber-800">Pengajuan sedang diperiksa kembali. Catatan sebelumnya tetap ditampilkan.</p>@endif
</section>
@endif
