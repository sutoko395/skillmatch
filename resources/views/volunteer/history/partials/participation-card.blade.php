@php
    $participation = $application->event->lifecycle_status === 'cancelled'
        ? 'Dibatalkan'
        : ($application->status === 'accepted'
            ? ($application->attendance?->participationLabel() ?? 'Belum dicatat')
            : 'Tidak berpartisipasi');
    $resultTone = match ($participation) {
        'Partisipasi berhasil' => 'success',
        'Partisipasi tidak lengkap', 'Belum dicatat' => 'pending',
        default => 'neutral',
    };
@endphp
<article class="vh-card" aria-labelledby="history-application-{{ $application->id }}">
    <header class="vh-card-heading">
        <div class="min-w-0">
            <h2 id="history-application-{{ $application->id }}"><a href="{{ route('volunteer.applications.show', $application) }}">{{ $application->event->title }}</a></h2>
            <p class="vh-position">Posisi: {{ $application->position->name }}</p>
        </div>
        <div class="vh-status"><x-application-status :application="$application" /></div>
    </header>
    <dl class="vh-summary">
        <div class="vh-time">
            <dt>Waktu kegiatan (WIB)</dt>
            <dd>{{ $application->event->starts_at?->copy()->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') ?? 'Belum tersedia' }}@if($application->event->starts_at) WIB @endif</dd>
        </div>
        <div class="vh-result vh-result-{{ $resultTone }}">
            <dt>Hasil partisipasi</dt>
            <dd>{{ $participation }}
                @if($participation === 'Belum dicatat')<span class="mt-2 block text-xs font-normal leading-relaxed text-slate-500">Kehadiran belum dicatat. Status ini berbeda dengan tidak hadir.</span>@endif
            </dd>
        </div>
    </dl>
    <div class="vh-attendance"><x-attendance-session-summary :attendance="$application->attendance" /></div>
    <div class="volunteer-card-actions"><a href="{{ route('volunteer.applications.show', $application) }}" class="volunteer-action volunteer-action--blue"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6" /></svg> Lihat detail</a></div>
</article>
