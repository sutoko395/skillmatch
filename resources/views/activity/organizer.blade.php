<section class="organizer-followups" aria-labelledby="organizer-followup-title">
    <div class="organizer-action-heading">
        <h2 id="organizer-followup-title" class="text-lg font-semibold text-slate-900">Perlu Ditindaklanjuti</h2>
    </div>

        <section class="organizer-action-group" aria-labelledby="organizer-recruitment-title">
            <h3 id="organizer-recruitment-title" class="organizer-action-group-title">Persiapan dan rekrutmen <span class="organizer-action-count">{{ $actionEvents->count() }} tindakan</span></h3>
            @forelse($actionEvents as $actionEvent)
                @php
                    $destination = route('organizer.events.show', $actionEvent);
                    $actionLabel = 'Lanjutkan';
                    if (!$actionEvent->assessment_deadline && in_array($actionEvent->status, ['pending', 'approved'])) {
                        $description = 'Lengkapi tenggat rekrutmen kegiatan.';
                    } elseif ($actionEvent->status === 'rejected') {
                        $description = 'Perbaiki kegiatan sesuai catatan admin.';
                    } elseif ($actionEvent->status === 'pending') {
                        $description = 'Menunggu hasil moderasi admin.';
                        $actionLabel = 'Lihat status';
                    } elseif ($actionEvent->status === 'approved' && $actionEvent->publication_status !== 'published') {
                        $description = 'Periksa pembayaran, hak paket, dan syarat publikasi.';
                    } elseif ($actionEvent->status === 'draft') {
                        $description = 'Lengkapi draf kegiatan sebelum diajukan.';
                    } elseif ($actionEvent->decision_deadline && $actionEvent->decision_deadline->isPast()) {
                        $description = 'Tutup seleksi tertunda dengan alasan.';
                        $destination = route('organizer.execution.show', $actionEvent);
                        $actionLabel = 'Kelola';
                    } else {
                        $description = 'Tinjau kandidat dan periksa batas keputusan.';
                        $destination = route('organizer.applications.index', $actionEvent);
                        $actionLabel = 'Tinjau pelamar';
                    }
                @endphp
                <div class="organizer-action-row">
                    <div class="min-w-0">
                        <p class="organizer-action-name">{{ $actionEvent->title }}</p>
                        <p class="organizer-action-description">{{ $description }}</p>
                    </div>
                    <a href="{{ $destination }}" class="organizer-action-button" aria-label="{{ $actionLabel }}: {{ $actionEvent->title }}">{{ $actionLabel }}</a>
                </div>
            @empty
                <p class="organizer-action-empty">Tidak ada persiapan atau rekrutmen yang perlu ditindaklanjuti.</p>
            @endforelse
        </section>

        <section class="organizer-action-group" aria-labelledby="organizer-execution-title">
            <h3 id="organizer-execution-title" class="organizer-action-group-title">Pelaksanaan <span class="organizer-action-count">{{ $attendanceEvents->count() }} tindakan</span></h3>
            @forelse($attendanceEvents as $attendanceEvent)
                <div class="organizer-action-row">
                    <div class="min-w-0">
                        <p class="organizer-action-name">{{ $attendanceEvent->title }}</p>
                        <p class="organizer-action-description">Kelola kehadiran dan penyelesaian kegiatan.</p>
                    </div>
                    <a href="{{ route('organizer.execution.show', $attendanceEvent) }}" class="organizer-action-button" aria-label="Kelola pelaksanaan: {{ $attendanceEvent->title }}">Kelola</a>
                </div>
            @empty
                <p class="organizer-action-empty">Tidak ada pelaksanaan yang perlu ditindaklanjuti.</p>
            @endforelse
        </section>
</section>
