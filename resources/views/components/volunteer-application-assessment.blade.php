@props(['application', 'assessment', 'deadline'])
<section class="rounded-xl border border-slate-200 bg-white p-5" aria-label="Informasi assessment">
    <h2 class="text-lg font-bold text-slate-900">Assessment</h2>
    <dl class="mt-3 grid gap-4 text-sm sm:grid-cols-3">
        <div>
            <dt class="text-slate-500">Jumlah soal</dt>
            <dd class="mt-1 font-semibold text-slate-900">{{ $assessment ? $assessment->questions_count . ' soal' : 'Belum tersedia' }}</dd>
        </div>
        <div>
            <dt class="text-slate-500">Durasi</dt>
            <dd class="mt-1 font-semibold text-slate-900">{{ $assessment?->duration_minutes ? $assessment->duration_minutes . ' menit' : 'Belum tersedia' }}</dd>
        </div>
        <div>
            <dt class="text-slate-500">Tenggat assessment (WIB)</dt>
            <dd class="mt-1 font-semibold text-slate-900">{{ $deadline?->format('d/m/Y H:i') ?? 'Belum tersedia' }}</dd>
        </div>
    </dl>

    <div class="mt-4 rounded-lg bg-slate-50 p-4 text-sm text-slate-700">
        @if ($application->status === 'screening_failed')
            <p>Assessment tidak tersedia karena lamaranmu belum memenuhi syarat wajib screening.</p>
        @elseif ($application->status === 'cancelled' || $application->event->lifecycle_status === 'cancelled')
            <p>Assessment tidak tersedia karena event telah dibatalkan.</p>
        @elseif (in_array($application->status, ['accepted', 'rejected', 'withdrawn']))
            <p>Proses seleksi lamaran ini telah berakhir.</p>
        @elseif (in_array($application->assessmentAttempt?->status, ['submitted', 'expired']))
            <p>{{ $application->assessmentAttempt->status === 'expired' ? 'Waktu pengerjaan telah habis. Jawaban yang tersimpan sudah dinilai.' : 'Assessment selesai. Jawabanmu sudah dikirim.' }} Menunggu keputusan EO.</p>
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
            <p>Pencocokan selesai. Tahap berikutnya adalah mengerjakan assessment.</p>
            <a href="{{ route('volunteer.assessments.show', $application) }}" class="mt-3 inline-flex rounded-xl bg-indigo-600 px-4 py-2 font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">{{ $application->assessmentAttempt?->status === 'in_progress' ? 'Lanjutkan assessment' : 'Mulai assessment' }}</a>
        @else
            <p>Informasi assessment belum tersedia untuk lamaran ini.</p>
        @endif
    </div>
</section>
