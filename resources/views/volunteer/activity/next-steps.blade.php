<section class="va-next-steps rounded-2xl border border-slate-200 bg-white p-6" aria-label="Tindakan Volunteer">
    <h2 class="text-lg font-bold text-slate-900">Langkah berikutnya</h2>
    <p class="mt-2 text-sm text-slate-500">Domisili: {{ $volunteerProfile?->cityRecord?->name ?? $volunteerProfile?->city ?? 'Belum diisi' }}@if($volunteerProfile?->cityRecord?->province), {{ $volunteerProfile->cityRecord->province->name }}@endif</p>
    <div class="mt-4 space-y-3">
        @foreach($assessmentApplications as $pending)
            @php($deadline = app(\App\Services\RecruitmentScheduleService::class)->deadlineFor($pending))
            <article class="va-action-row rounded-xl border border-slate-200 bg-slate-50 p-5">
                <div>
                    <h3 class="text-sm font-semibold text-slate-900">{{ $pending->event->title }} — {{ $pending->position->name }}</h3>
                    @if($deadline && $deadline->isFuture() && $pending->event->publication_status === 'published')
                        <p class="mt-4 text-sm text-slate-600">Asesmen sampai {{ $deadline->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</p>
                    @else
                        <p class="mt-3 text-sm text-amber-800">{{ !$deadline ? 'Tenggat kegiatan lama perlu dilengkapi Organizer.' : ($deadline->isPast() ? 'Tenggat asesmen telah berakhir.' : 'Asesmen sementara tidak tersedia karena kegiatan belum dipublikasikan atau ditangguhkan.') }}</p>
                    @endif
                </div>
                @if($deadline && $deadline->isFuture() && $pending->event->publication_status === 'published')
                    <a href="{{ route('volunteer.assessments.show', $pending) }}" class="va-button">Lanjutkan asesmen</a>
                @else
                    <a href="{{ route('volunteer.applications.show', $pending) }}" class="va-button">Lihat lamaran</a>
                @endif
            </article>
        @endforeach
        @foreach($acceptedApplications as $accepted)
            <article class="va-action-row rounded-xl border border-slate-200 bg-slate-50 p-5">
                <div>
                    <h3 class="text-sm font-semibold text-slate-900">Jadwal diterima: {{ $accepted->event->title }}</h3>
                    @forelse(($accepted->snapshot_json['schedules'] ?? []) as $schedule)
                        <p class="mt-3 text-sm text-slate-600">{{ \Carbon\CarbonImmutable::parse($schedule['start'])->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }} - {{ \Carbon\CarbonImmutable::parse($schedule['end'])->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</p>
                    @empty
                        <p class="mt-3 text-sm text-slate-500">Jadwal tugas belum tercatat dalam snapshot; periksa detail partisipasi.</p>
                    @endforelse
                </div>
                <a href="{{ route('volunteer.applications.show', $accepted) }}" class="volunteer-action volunteer-action--blue va-button"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6" /></svg> Detail partisipasi</a>
            </article>
        @endforeach
        @if($assessmentApplications->isEmpty() && $acceptedApplications->isEmpty())
            <p class="rounded-xl border border-slate-200 bg-slate-50 p-5 text-sm text-slate-500">Belum ada asesmen atau jadwal diterima yang perlu ditindaklanjuti.</p>
        @endif
    </div>
</section>
