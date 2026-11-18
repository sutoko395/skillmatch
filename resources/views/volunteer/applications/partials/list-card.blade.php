<article class="vl-card" aria-labelledby="application-title-{{ $application->id }}">
    <div class="vl-card-heading">
        <div class="min-w-0">
            <h2 id="application-title-{{ $application->id }}">{{ $application->event->title }}</h2>
            <p class="vl-position"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M3 12a20 20 0 0 0 18 0M10 13h4M5 7h14a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2Z" /></svg><span>Posisi: {{ $application->position->name }}</span></p>
        </div>
        <div class="vl-status"><x-application-status :application="$application" /></div>
    </div>
    <dl class="vl-location">
        <dt><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M20 10c0 6-8 11-8 11S4 16 4 10a8 8 0 1 1 16 0Z" /><circle cx="12" cy="10" r="2.5" /></svg>Lokasi kegiatan</dt>
        <dd>{{ $application->event->location }} @if($application->event->cityRecord?->name) &middot; {{ $application->event->cityRecord->name }}@if($application->event->cityRecord->province), {{ $application->event->cityRecord->province->name }}@endif @endif</dd>
    </dl>
    @php
        $nextStep = match (true) {
            $application->event->lifecycle_status === 'cancelled' => 'Kegiatan dibatalkan. Proses lamaran telah dihentikan.',
            $application->status === 'draft' => 'Lengkapi profil dan dokumen, lalu kirim lamaran.',
            $application->status === 'submitted' => 'Menunggu proses penyaringan dan pencocokan.',
            $application->status === 'under_review' => 'Menunggu hasil seleksi dari penyelenggara.',
            $application->status === 'assessment_pending' => 'Periksa tenggat dan status pengerjaan asesmen di detail lamaran.',
            $application->status === 'accepted' => 'Periksa jadwal tugas dan koordinasi peserta di detail lamaran.',
            $application->status === 'screening_failed' => 'Lihat alasan pada hasil penyaringan di detail lamaran.',
            default => null,
        };
    @endphp
    @if($nextStep)<p class="vl-next-step">{{ $nextStep }}</p>@endif
    <div class="vl-card-footer">
        <dl class="vl-dates">
            <div>
                <dt><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="9" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 2" /></svg>Dibuat</dt>
                <dd>{{ $application->created_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</dd>
            </div>
            @if($application->submitted_at)
                <div>
                    <dt><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m21 3-7 18-4-7-7-4 18-7ZM10 14 21 3" /></svg>Dikirim</dt>
                    <dd>{{ $application->submitted_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</dd>
                </div>
            @endif
        </dl>
        <a href="{{ route('volunteer.applications.show', $application) }}" class="volunteer-action volunteer-action--blue vl-detail" aria-label="Lihat detail: {{ $application->event->title }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6" /></svg> Lihat detail</a>
    </div>
</article>
