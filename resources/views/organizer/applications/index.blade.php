@extends('organizer.layouts.sidebar')
@section('title', 'Kelola Pelamar')
@section('content')
<div class="organizer-applicant-page mx-auto max-w-7xl space-y-6">
    <div><a href="{{ route('organizer.applications.overview') }}" class="organizer-action-button organizer-detail-back">Kembali</a></div>
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Daftar Pelamar</h1>
            <p class="mt-2 text-base font-medium text-slate-700">{{ $event->title }}</p>
        </div>
        <span class="organizer-applicant-total">{{ number_format($applications->total()) }} pelamar</span>
    </div>
    <form method="GET" action="{{ route('organizer.applications.index', $event) }}" class="organizer-event-filters">
        @if(request('tab') === 'ready')<input type="hidden" name="tab" value="ready">@endif
        <div class="organizer-applicant-position-filter">
            <label for="position_id">Posisi yang dilamar</label>
            <select name="position_id" id="position_id">
                <option value="">Semua posisi</option>
                @foreach($positions as $pos)
                    <option value="{{ $pos->id }}" @selected(request('position_id') == $pos->id)>{{ $pos->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="organizer-action-button">Terapkan</button>
        @if(request()->filled('position_id') || request()->filled('tab') || request()->filled('status'))
            <a href="{{ route('organizer.applications.index', $event) }}" class="organizer-event-filter-reset">Reset</a>
        @endif
    </form>
    <section class="organizer-event-list" aria-label="Daftar kandidat">
        @forelse($applications as $app)
            <article class="organizer-candidate-row">
                <div class="organizer-candidate-identity">
                    <span class="organizer-candidate-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($app->volunteer->name, 0, 1)) }}</span>
                    <div class="min-w-0">
                        <h2 class="organizer-candidate-name">{{ $app->volunteer->name }}</h2>
                        <p class="organizer-candidate-meta"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 10c0 5-7 11-7 11S5 15 5 10a7 7 0 1 1 14 0Z"/><circle cx="12" cy="10" r="2.5"/></svg><span>{{ $app->volunteer->volunteerProfile?->cityRecord?->name ?? 'Kota belum diisi' }}@if($app->volunteer->volunteerProfile?->cityRecord?->province), {{ $app->volunteer->volunteerProfile->cityRecord->province->name }}@endif</span></p>
                        <p class="organizer-candidate-meta"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="7" width="18" height="14" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M3 12a22 22 0 0 0 18 0M12 11v3"/></svg><span>{{ $app->position->name }}</span></p>
                        <div class="mt-3"><x-application-status :application="$app" /></div>
                    </div>
                </div>
                <div class="organizer-candidate-facts">
                    <div class="organizer-candidate-score">
                        <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 3v18h17M8 16v-4m5 4V8m5 8V5"/></svg> Skor kecocokan</span>
                        <strong>{{ $app->matchResult ? number_format($app->matchResult->total_score, 2, ',', '.') : 'Belum tersedia' }}</strong>
                    </div>
                    <p class="organizer-candidate-meta"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M7 3v4m10-4v4M3 11h18"/></svg><span><span class="sr-only">Dikirim: </span>{{ $app->submitted_at?->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') ?? '-' }} WIB</span></p>
                    <p class="organizer-candidate-meta"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z M14 2v6h6M8 13h8M8 17h5"/></svg><span>{{ $app->documents->count() }} dokumen</span></p>
                </div>
                <a href="{{ route('organizer.applications.show', $app) }}" class="organizer-action-button organizer-event-manage" aria-label="Tinjau lamaran {{ $app->volunteer->name }}">Tinjau lamaran</a>
            </article>
        @empty
            <div class="p-6">
                @if(request()->filled('position_id') || request()->filled('tab') || request()->filled('status'))
                    <x-empty-state title="Pelamar tidak ditemukan" description="Belum ada pelamar yang sesuai dengan filter ini." />
                @else
                    <x-empty-state title="Belum Ada Pelamar" description="Belum ada kandidat yang mengirimkan lamaran untuk kegiatan ini." />
                @endif
            </div>
        @endforelse
    </section>
    @if($applications->hasPages()) {{ $applications->links() }} @endif
    @if($pendingSelectionCount > 0 && !in_array($event->lifecycle_status, ['cancelled', 'completed']))
    <x-section-card id="tutup-seleksi">
        <h2 class="mb-3 text-lg font-semibold">Tutup seleksi tertunda</h2>
        <p class="mb-3 text-sm text-slate-600">Lamaran tertunda akan ditolak dengan alasan yang kamu isi.</p>
        <form method="POST" action="{{ route('organizer.events.close-selection', $event) }}" class="organizer-execution-close space-y-3">
            @csrf
            <label class="block">Alasan penutupan<textarea name="reason" required minlength="5" maxlength="1000" class="block w-full rounded-lg border-slate-300">{{ old('reason') }}</textarea></label>
            <label class="block"><input type="checkbox" name="confirmed" value="1" required> Saya mengonfirmasi penutupan seluruh lamaran tertunda.</label>
            @if(!$event->registration_deadline || now()->lt($event->registration_deadline))
                <p class="text-xs text-slate-500">Tersedia setelah tenggat pendaftaran{{ $event->registration_deadline ? ': '.$event->registration_deadline->setTimezone('Asia/Jakarta')->format('d/m/Y H:i').' WIB' : ' ditentukan' }}.</p>
            @endif
            <x-primary-button :disabled="!$event->registration_deadline || now()->lt($event->registration_deadline)">Tutup seleksi tertunda</x-primary-button>
        </form>
    </x-section-card>
    @endif
</div>
@endsection
