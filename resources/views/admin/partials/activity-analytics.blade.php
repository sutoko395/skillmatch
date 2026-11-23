@php
    $applicationStats = $activityStats['applications'];
    $eventStats = $activityStats['events'];
    $attendanceStats = $activityStats['attendance'];
    $cards = [
        [
            'title' => 'Lamaran kegiatan', 'scope' => $activityPeriod, 'tone' => 'text-blue-700',
            'total' => $applicationStats['submitted'], 'label' => 'Lamaran terkirim',
            'rows' => [
                'Diterima' => $applicationStats['accepted'],
                'Ditolak' => $applicationStats['rejected'],
                'Menunggu keputusan' => $applicationStats['pending'],
                'Status lainnya' => $applicationStats['other'],
            ],
        ],
        [
            'title' => 'Pelaksanaan kegiatan', 'scope' => 'Kondisi saat ini', 'tone' => 'text-emerald-700',
            'total' => $eventStats['ongoing'], 'label' => 'Sedang berlangsung',
            'rows' => [
                'Aktif terpublikasi' => $eventStats['active'],
                'Menunggu penyelesaian' => $eventStats['awaiting_completion'],
                'Selesai' => $eventStats['completed'],
            ],
        ],
        [
            'title' => 'Kehadiran peserta', 'scope' => 'Peserta diterima pada kegiatan yang sudah dimulai', 'tone' => 'text-purple-700',
            'total' => $attendanceStats['present'], 'label' => 'Hadir seluruh tugas',
            'rows' => [
                'Ada ketidakhadiran' => $attendanceStats['absent'],
                'Belum lengkap dicatat' => $attendanceStats['unrecorded'],
            ],
        ],
    ];
@endphp

<section aria-labelledby="admin-activity-analytics-title" class="space-y-4">
    <div>
        <h2 id="admin-activity-analytics-title" class="text-lg font-bold text-gray-900">Analitik kegiatan</h2>
        <p class="mt-1 text-sm text-gray-500">Lamaran dihitung berdasarkan tanggal kirim pada periode di atas. Status seleksi mengikuti kondisi terkini.</p>
    </div>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach ($cards as $card)
            <article class="admin-stat rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                <h3 class="font-semibold text-gray-900">{{ $card['title'] }}</h3>
                <p class="mt-1 text-xs leading-5 text-gray-500">{{ $card['scope'] }}</p>
                <dl class="mt-3">
                    <div class="flex flex-col-reverse">
                        <dt class="mt-1 text-sm text-gray-600">{{ $card['label'] }}</dt>
                        <dd class="text-3xl font-bold tabular-nums {{ $card['tone'] }}">{{ number_format($card['total']) }}</dd>
                    </div>
                    <div class="mt-4 space-y-2 border-t border-slate-200 pt-3">
                        @foreach ($card['rows'] as $label => $value)
                            <div class="flex justify-between gap-3 text-sm">
                                <dt class="text-gray-600">{{ $label }}</dt>
                                <dd class="font-semibold tabular-nums text-gray-900">{{ number_format($value) }}</dd>
                            </div>
                        @endforeach
                    </div>
                </dl>
            </article>
        @endforeach
    </div>
</section>
