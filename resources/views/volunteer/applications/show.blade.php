@extends('volunteer.layouts.sidebar')
@section('title', 'Detail Lamaran - ' . $application->event->title)
@section('content')
@php
    $status = $application->status;
    $screening = $application->screeningResult;
    $attempt = $application->assessmentAttempt;
    $assessmentFinished = in_array($attempt?->status, ['submitted', 'expired'], true);
    $eventCancelled = $application->event->lifecycle_status === 'cancelled';
    $terminal = in_array($status, ['screening_failed', 'accepted', 'rejected', 'withdrawn', 'cancelled'], true) || $eventCancelled;
    $canOpenAssessment = $status === 'assessment_pending' && $assessmentSummary
        && !in_array($attempt?->status, ['submitted', 'expired', 'cancelled'], true)
        && $recruitmentDates['assessment_deadline'] && now()->lt($recruitmentDates['assessment_deadline'])
        && $application->event->lifecycle_status === 'upcoming' && now()->lt($application->event->starts_at);
    [$bannerTitle, $bannerText] = match (true) {
        $eventCancelled || $status === 'cancelled' => ['Kegiatan dibatalkan', 'Proses lamaran ini telah dihentikan karena event dibatalkan.'],
        $status === 'accepted' => ['Selamat, kamu DITERIMA!', 'Kamu terpilih untuk posisi ' . $application->position->name . '. Periksa jadwal tugasmu di bawah.'],
        $status === 'rejected' => ['Maaf, kamu BELUM TERPILIH', 'Terima kasih sudah mengikuti proses seleksi untuk posisi ' . $application->position->name . '.'],
        $status === 'screening_failed' => ['Lamaran belum memenuhi syarat wajib', 'Lamaranmu belum bisa dilanjutkan ke tahap selanjutnya. Lihat alasan pada hasil screening.'],
        $status === 'withdrawn' => ['Lamaran telah ditarik', 'Proses seleksi lamaran ini telah berakhir.'],
        $status === 'draft' => ['Lengkapi lamaranmu', 'Periksa profil dan dokumen sebelum mengirimkan lamaran.'],
        $assessmentFinished || $status === 'under_review' => ['Menunggu hasil seleksi', 'Lamaranmu sedang ditinjau penyelenggara. Hasil seleksi akan ditampilkan pada halaman ini.'],
        $status === 'submitted' => ['Lamaran sedang diproses', $screening?->passed ? 'Lamaranmu menunggu proses pencocokan sebelum assessment.' : 'Lamaranmu menunggu hasil screening sebelum assessment.'],
        $status === 'assessment_pending' && $recruitmentDates['assessment_deadline'] && now()->gte($recruitmentDates['assessment_deadline']) => ['Tenggat assessment telah berakhir', 'Assessment tidak dapat dimulai. Periksa informasi tahap seleksi di bawah.'],
        default => ['Periksa tahap assessment', 'Lihat informasi assessment dan tenggat pengerjaan pada perkembangan lamaran.'],
    };
    $bannerColor = match (true) {
        $eventCancelled || in_array($status, ['withdrawn', 'cancelled'], true) => 'border-slate-200 bg-slate-100 text-slate-800',
        $status === 'accepted' => 'border-emerald-200 bg-emerald-50 text-emerald-900',
        in_array($status, ['rejected', 'screening_failed'], true) => 'border-rose-200 bg-rose-50 text-rose-900',
        default => 'border-indigo-200 bg-indigo-50 text-indigo-900',
    };
@endphp
<div class="mx-auto max-w-7xl space-y-6">
    <header class="space-y-4">
        <a href="{{ route('volunteer.applications.index') }}" class="inline-flex w-fit items-center gap-2 rounded-xl border border-indigo-200 bg-white px-3 py-2 text-sm font-semibold text-indigo-700 shadow-sm transition hover:border-indigo-300 hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
            <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5m7 7-7-7 7-7" /></svg>
            Lamaran Saya
        </a>
        <x-page-header title="Detail Lamaran" :description="$application->event->title . ' · Posisi: ' . $application->position->name" />
    </header>

    {{-- The panel layout already renders success and validation messages. --}}
    @if ($canOpenAssessment)
        <div id="assessment-summary" class="scroll-mt-24">
            <x-volunteer-application-assessment :application="$application" :assessment="$assessmentSummary" :deadline="$recruitmentDates['assessment_deadline']" featured />
        </div>
    @else
        <section aria-label="Status lamaran" class="rounded-2xl border p-5 sm:p-6 {{ $bannerColor }}">
            <div class="flex items-start gap-4">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-white/70" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-6 w-6">
                        @if ($status === 'accepted' && !$eventCancelled)
                            <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
                        @else
                            <circle cx="12" cy="12" r="9" /><path stroke-linecap="round" d="M12 11v5m0-9v.01" />
                        @endif
                    </svg>
                </span>
                <div class="min-w-0 flex-1">
                    <h2 class="text-xl font-bold">{{ $bannerTitle }}</h2>
                    <p class="mt-1 text-sm leading-relaxed">{{ $bannerText }}</p>
                    @if ($status !== 'accepted')
                        <div class="mt-3"><x-application-status :application="$application" /></div>
                    @endif
                    @if ($status === 'draft')
                        <form method="POST" action="{{ route('volunteer.applications.submit', $application) }}" class="mt-4">
                            @csrf
                            <x-button type="submit">Kirimkan Lamaran</x-button>
                        </form>
                    @endif
                </div>
            </div>
        </section>
    @endif

    @if ($status === 'draft' && !($eligibility['complete'] ?? true))
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
            <p class="font-semibold">Profil Anda belum lengkap!</p>
            <p class="mt-1">Silakan lengkapi data profil dan skill Anda sebelum mengirimkan lamaran.</p>
            <a href="{{ route('volunteer.profile.edit') }}" class="mt-2 inline-block rounded font-semibold underline focus:ring-2 focus:ring-amber-600">Lengkapi Profil &rarr;</a>
        </div>
    @endif

    <div class="flex flex-col gap-6 xl:grid xl:items-start xl:grid-cols-[minmax(0,1.6fr)_minmax(0,1fr)]">
        <div class="order-2 min-w-0 space-y-6 xl:order-1">
            @include('volunteer.applications.partials.progress')
            @include('volunteer.applications.partials.documents')

            @if (in_array($status, ['submitted', 'assessment_pending', 'under_review'], true))
                <details class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <summary class="cursor-pointer rounded-2xl p-5 text-sm font-semibold text-slate-600 focus:outline-none focus:ring-2 focus:ring-indigo-500">Kelola penarikan lamaran</summary>
                    <div class="px-4 pb-4"><x-application-selection :application="$application" /></div>
                </details>
            @endif
        </div>
        <aside class="contents xl:order-2 xl:block xl:min-w-0 xl:space-y-6" aria-label="Informasi seleksi dan posisi">
            @include('volunteer.applications.partials.coordination')
            @include('volunteer.applications.partials.information')
        </aside>
    </div>
</div>
@endsection
