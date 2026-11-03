<section class="mt-6 space-y-4" aria-label="Tindakan Volunteer">
    <h2 class="text-lg font-bold text-slate-900">Langkah berikutnya</h2>
    <p class="text-sm text-slate-600">Domisili: {{ $volunteerProfile?->cityRecord?->name ?? $volunteerProfile?->city ?? 'Belum diisi' }}</p>
    @foreach($assessmentApplications as $pending)
        @php($deadline = app(\App\Services\RecruitmentScheduleService::class)->deadlineFor($pending))
        <div class="rounded-xl border border-indigo-200 bg-white p-4">
            <p class="font-semibold">{{ $pending->event->title }} — {{ $pending->position->name }}</p>
            @if($deadline && $deadline->isFuture() && $pending->event->publication_status === 'published')
                <p class="mt-1 text-sm">Assessment sampai {{ $deadline->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</p>
                <a href="{{ route('volunteer.assessments.show', $pending) }}" class="mt-2 inline-block font-semibold text-indigo-600">Lanjutkan assessment</a>
            @else
                <p class="mt-1 text-sm text-amber-800">{{ !$deadline ? 'Tenggat event lama perlu dilengkapi Organizer.' : ($deadline->isPast() ? 'Tenggat assessment telah berakhir.' : 'Assessment sementara tidak tersedia karena event belum dipublikasikan atau ditangguhkan.') }}</p>
                <a href="{{ route('volunteer.applications.show', $pending) }}" class="mt-2 inline-block font-semibold text-indigo-600">Lihat lamaran</a>
            @endif
        </div>
    @endforeach
    @foreach($acceptedApplications as $accepted)
        <div class="rounded-xl border border-emerald-200 bg-white p-4">
            <p class="font-semibold">Jadwal diterima: {{ $accepted->event->title }}</p>
            @forelse(($accepted->snapshot_json['schedules'] ?? []) as $schedule)
                <p class="mt-1 text-sm">{{ \Carbon\CarbonImmutable::parse($schedule['start'])->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }} - {{ \Carbon\CarbonImmutable::parse($schedule['end'])->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</p>
            @empty
                <p class="mt-1 text-sm">Jadwal tugas belum tercatat dalam snapshot; periksa detail partisipasi.</p>
            @endforelse
            <a href="{{ route('volunteer.applications.show', $accepted) }}" class="mt-2 inline-block font-semibold text-indigo-600">Detail partisipasi</a>
        </div>
    @endforeach
    @if($assessmentApplications->isEmpty() && $acceptedApplications->isEmpty())
        <p class="text-sm text-slate-500">Belum ada assessment atau jadwal diterima yang perlu ditindaklanjuti.</p>
    @endif
</section>
