@props(['application', 'assessment', 'deadline', 'featured' => false])
<section class="rounded-2xl border p-5 sm:p-6 {{ $featured ? 'border-indigo-200 bg-indigo-50' : 'border-slate-200 bg-white' }}" aria-label="Informasi assessment">
    <h2 class="text-lg font-bold text-slate-900">{{ $featured ? ($application->assessmentAttempt?->status === 'in_progress' ? 'Assessment sedang dikerjakan' : 'Assessment tersedia') : 'Assessment' }}</h2>
    @if ($featured)<p class="mt-1 text-sm text-slate-600">Selesaikan assessment untuk melanjutkan proses seleksi.</p>@endif
    <dl class="mt-3 grid gap-4 text-sm sm:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_max-content]">
        <div>
            <dt class="text-slate-500">Jumlah soal</dt>
            <dd class="mt-1 font-semibold text-slate-900">{{ $assessment ? $assessment->questions_count . ' soal' : 'Belum tersedia' }}</dd>
        </div>
        <div>
            <dt class="text-slate-500">Durasi maksimal</dt>
            <dd class="mt-1 font-semibold text-slate-900">{{ $assessment?->duration_minutes ? $assessment->duration_minutes . ' menit' : 'Belum tersedia' }}</dd>
        </div>
        <div>
            <dt class="text-slate-500">Tenggat assessment (WIB)</dt>
            <dd class="mt-1 font-semibold text-slate-900">{{ $deadline?->format('d/m/Y H:i') ?? 'Belum tersedia' }}</dd>
        </div>
    </dl>

    @php
        $showSubmittedConfirmation = $application->assessmentAttempt?->status === 'submitted'
            && !in_array($application->status, ['screening_failed', 'cancelled'], true)
            && $application->event->lifecycle_status !== 'cancelled';
    @endphp
    @if ($showSubmittedConfirmation)
        <div class="mt-4 flex items-center gap-3 border-t border-slate-200 pt-4">
            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-indigo-600 text-white" aria-hidden="true">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" /></svg>
            </span>
            <p class="text-sm font-semibold text-slate-900">Jawabanmu sudah dikirim.</p>
        </div>
    @else
    <div class="mt-4 text-sm text-slate-700">
        @if ($application->status === 'screening_failed')
            <p>Assessment tidak tersedia karena lamaranmu belum memenuhi syarat wajib screening.</p>
        @elseif ($application->status === 'cancelled' || $application->event->lifecycle_status === 'cancelled')
            <p>Assessment tidak tersedia karena event telah dibatalkan.</p>
        @elseif (in_array($application->status, ['accepted', 'rejected', 'withdrawn']))
            <p>{{ match ($application->assessmentAttempt?->status) {
                'expired' => 'Waktu pengerjaan telah habis. Jawaban yang tersimpan sudah dinilai.',
                default => 'Informasi pengerjaan assessment belum tersedia.',
            } }}</p>
        @elseif ($application->assessmentAttempt?->status === 'expired')
            <p>Waktu pengerjaan telah habis. Jawaban yang tersimpan sudah dinilai.</p>
        @elseif ($application->assessmentAttempt?->status === 'cancelled')
            <p>Pengerjaan assessment telah dihentikan.</p>
        @elseif ($application->status === 'under_review')
            <p>Lamaranmu sedang ditinjau EO. Tunggu hasil seleksi pada halaman ini.</p>
        @elseif (!$deadline)
            <p>Tenggat assessment belum tersedia. Assessment belum dapat dimulai.</p>
        @elseif (now()->greaterThanOrEqualTo($deadline))
            <p>Tenggat assessment telah berakhir. Assessment tidak dapat dimulai.</p>
        @elseif ($application->status === 'submitted')
            <p>{{ $application->screeningResult?->passed ? 'Lamaranmu menunggu proses pencocokan sebelum assessment.' : 'Lamaranmu menunggu hasil screening sebelum assessment.' }}</p>
        @elseif ($application->event->lifecycle_status !== 'upcoming' || now()->greaterThanOrEqualTo($application->event->starts_at))
            <p>Assessment tidak tersedia karena kegiatan sudah dimulai atau berakhir.</p>
        @elseif ($application->status === 'assessment_pending' && $assessment)
            @unless ($featured)<p>Pencocokan selesai. Tahap berikutnya adalah mengerjakan assessment.</p>@endunless
            <a href="{{ route('volunteer.assessments.show', $application) }}" class="inline-flex rounded-xl bg-indigo-600 px-5 py-3 font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 {{ $featured ? '' : 'mt-3' }}">{{ $application->assessmentAttempt?->status === 'in_progress' ? 'Lanjutkan Assessment' : 'Buka Assessment' }}</a>
            <p class="mt-3 text-xs leading-relaxed text-slate-600">{{ $application->assessmentAttempt?->status === 'in_progress' ? 'Waktu pengerjaan tetap berjalan meskipun halaman ditutup.' : 'Waktu pengerjaan dimulai setelah kamu menekan Mulai Assessment pada halaman konfirmasi.' }}</p>
        @else
            <p>Informasi assessment belum tersedia untuk lamaran ini.</p>
        @endif
    </div>
    @endif
</section>
