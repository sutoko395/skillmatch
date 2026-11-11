@php
    $steps = [
        [
            'key' => 'submission',
            'label' => $application->submitted_at ? 'Lamaran dikirim' : 'Lamaran belum dikirim',
            'state' => $application->submitted_at ? 'done' : ($status === 'draft' ? 'active' : 'waiting'),
            'description' => $status === 'draft' ? 'Lengkapi profil dan dokumen, lalu kirim lamaran.' : null,
            'date' => $application->submitted_at,
        ],
        [
            'key' => 'screening',
            'label' => $screening ? ($screening->passed ? 'Screening' : 'Gagal screening') : 'Screening',
            'state' => $screening ? ($screening->passed ? 'done' : 'failed') : ($status === 'submitted' && !$terminal ? 'active' : 'waiting'),
            'description' => $screening ? ($screening->passed ? null : 'Belum memenuhi syarat wajib.') : ($status === 'draft' ? 'Dilakukan setelah lamaran dikirim.' : 'Hasil screening belum tersedia.'),
            'date' => $screening?->evaluated_at,
        ],
        [
            'key' => 'assessment',
            'label' => $attempt?->status === 'cancelled' ? 'Assessment dihentikan' : 'Assessment',
            'state' => $assessmentFinished ? 'done' : ($status === 'assessment_pending' && !$terminal ? 'active' : 'waiting'),
            'description' => match (true) {
                $assessmentFinished => $attempt->status === 'expired' ? 'Waktu pengerjaan habis. Jawaban tersimpan sudah dinilai.' : null,
                $attempt?->status === 'cancelled' => 'Pengerjaan assessment telah dihentikan.',
                $terminal => in_array($status, ['accepted', 'rejected'], true) ? 'Informasi pengerjaan assessment belum tersedia.' : 'Tahap ini tidak dilanjutkan.',
                $attempt?->status === 'in_progress' => 'Sedang dikerjakan. Waktu pengerjaan tetap berjalan.',
                $canOpenAssessment => 'Belum dikerjakan. Selesaikan sebelum tenggat.',
                $status === 'under_review' => 'Informasi pengerjaan belum tersedia.',
                default => 'Menunggu tahap assessment.',
            },
            'date' => $assessmentFinished ? $attempt->submitted_at : $attempt?->started_at,
        ],
        [
            'key' => 'decision',
            'label' => match ($status) {
                'accepted' => 'Diterima', 'rejected' => 'Belum terpilih',
                'screening_failed' => 'Seleksi tidak dilanjutkan',
                'withdrawn' => 'Lamaran ditarik', 'cancelled' => 'Dibatalkan',
                default => 'Keputusan seleksi',
            },
            'state' => match (true) {
                $status === 'accepted' => 'done',
                $status === 'rejected' => 'failed',
                $status === 'under_review' && !$terminal => 'active',
                default => 'waiting',
            },
            'description' => match ($status) {
                'accepted', 'rejected' => 'Keputusan seleksi telah diumumkan.',
                'screening_failed' => 'Lamaran belum dapat diteruskan ke tahap berikutnya.',
                'withdrawn', 'cancelled' => 'Proses seleksi lamaran ini telah berakhir.',
                default => 'Menunggu proses seleksi selesai.',
            },
            'date' => $application->decision_at,
        ],
    ];
@endphp
<x-section-card class="vd-progress">
    <div class="-mx-6 -mt-6 rounded-t-2xl border-b border-indigo-100 bg-indigo-50/80 px-6 py-4">
        <h2 class="text-lg font-bold text-indigo-950">Perkembangan lamaran</h2>
    </div>
    <ol class="mt-6">
        @foreach ($steps as $step)
            @php
                $stepColor = match ($step['state']) {
                    'done' => 'border-indigo-600 bg-indigo-600 text-white',
                    'active' => 'border-indigo-600 bg-indigo-50 text-indigo-700',
                    'failed' => 'border-rose-600 bg-rose-50 text-rose-700',
                    default => 'border-slate-300 bg-white text-slate-400',
                };
            @endphp
            <li class="relative flex gap-4 {{ !$loop->last ? 'pb-7' : '' }}" @if ($step['state'] === 'active') aria-current="step" @endif>
                @unless ($loop->last)
                    <span aria-hidden="true" class="absolute bottom-0 left-4 top-8 w-px bg-slate-200"></span>
                @endunless
                <span aria-hidden="true" class="relative flex h-8 w-8 shrink-0 items-center justify-center rounded-full border-2 {{ $stepColor }}">
                    @if ($step['state'] === 'done')
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" /></svg>
                    @elseif ($step['state'] === 'active')
                        <span class="h-3 w-3 rounded-full bg-indigo-600"></span>
                    @elseif ($step['state'] === 'failed')
                        <span class="text-sm font-bold">!</span>
                    @endif
                </span>
                <div class="min-w-0 flex-1 pt-0.5">
                    <h3 class="font-semibold {{ $step['state'] === 'failed' ? 'text-rose-800' : 'text-slate-900' }}">{{ $step['label'] }}</h3>
                    @if ($step['description'])<p class="mt-1 text-sm leading-relaxed text-slate-600">{{ $step['description'] }}</p>@endif
                    @if ($step['date'])<p class="mt-1 text-xs text-slate-500">{{ $step['date']->copy()->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</p>@endif

                    @if ($step['key'] === 'screening' && $status !== 'draft')
                        <details class="mt-3" @if (in_array($status, ['screening_failed', 'submitted', 'under_review', 'rejected', 'accepted'], true)) open @endif>
                            <summary class="w-fit cursor-pointer rounded text-sm font-semibold text-indigo-700 hover:text-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500">Lihat rincian screening</summary>
                            <div class="mt-3"><x-application-screening :application="$application" :show-evaluation-info="false" /></div>
                        </details>
                    @elseif ($step['key'] === 'assessment' && $status !== 'draft')
                        @if ($canOpenAssessment)
                            <a href="#assessment-summary" class="mt-3 inline-block rounded text-sm font-semibold text-indigo-700 underline underline-offset-4 focus:outline-none focus:ring-2 focus:ring-indigo-500">Lihat informasi assessment &uarr;</a>
                        @else
                            <details class="mt-3" @if (in_array($status, ['assessment_pending', 'submitted', 'under_review', 'screening_failed', 'rejected', 'accepted'], true)) open @endif>
                                <summary class="w-fit cursor-pointer rounded text-sm font-semibold text-indigo-700 hover:text-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500">Lihat rincian assessment</summary>
                                <div class="mt-3"><x-volunteer-application-assessment :application="$application" :assessment="$assessmentSummary" :deadline="$recruitmentDates['assessment_deadline']" /></div>
                            </details>
                        @endif
                    @endif
                </div>
            </li>
        @endforeach
    </ol>
</x-section-card>
