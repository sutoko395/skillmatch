@extends('volunteer.layouts.sidebar')
@section('title', 'Detail Lamaran - ' . $application->event->title)
@section('page-class', 'volunteer-application-detail ' . match ($application->status) { 'draft' => 'volunteer-application-draft', 'submitted', 'under_review' => 'volunteer-application-submitted', 'screening_failed' => 'volunteer-application-screening-failed', 'rejected' => 'volunteer-application-rejected', 'withdrawn' => 'volunteer-application-withdrawn', 'cancelled' => 'volunteer-application-cancelled', 'accepted' => $application->event->lifecycle_status === 'cancelled' ? 'volunteer-application-cancelled' : 'volunteer-application-accepted', default => '' })
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
<div class="vd-detail mx-auto max-w-7xl space-y-6">
    @include('volunteer.applications.partials.detail-header')

    @include('volunteer.applications.partials.status-banner')

    @if ($status === 'draft' && !($eligibility['complete'] ?? true))
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
            <p class="font-semibold">Profil Anda belum lengkap!</p>
            <p class="mt-1">Silakan lengkapi data profil dan skill Anda sebelum mengirimkan lamaran.</p>
            <a href="{{ route('volunteer.profile.edit') }}" class="mt-2 inline-block rounded font-semibold underline focus:ring-2 focus:ring-amber-600">Lengkapi Profil &rarr;</a>
        </div>
    @endif

    <div class="vd-grid flex flex-col gap-6 xl:grid xl:items-start xl:grid-cols-[minmax(0,1.6fr)_minmax(0,1fr)]">
        <div class="order-2 min-w-0 space-y-6 xl:order-1">
            @include('volunteer.applications.partials.progress')
            @include('volunteer.applications.partials.documents')

            @if (in_array($status, ['submitted', 'assessment_pending', 'under_review'], true))
                @include('volunteer.applications.partials.withdrawal')
            @endif
        </div>
        <aside class="contents xl:order-2 xl:block xl:min-w-0 xl:space-y-6" aria-label="Informasi seleksi dan posisi">
            @include('volunteer.applications.partials.coordination')
            @include('volunteer.applications.partials.information')
        </aside>
    </div>
</div>
@endsection
