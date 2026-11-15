@extends('organizer.layouts.sidebar')
@section('title', 'Pelaksanaan & Kehadiran')
@section('content')
<div class="organizer-execution-detail mx-auto max-w-7xl space-y-6" x-data="attendanceSessionSetup(@js(['url' => route('organizer.attendance-sessions.initialize', $event), 'applications' => $pendingSessionApplications]))">
    <div><a href="{{ route('organizer.execution.index') }}" class="organizer-action-button organizer-detail-back">Kembali</a></div>
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Pelaksanaan &amp; Kehadiran</h1>
        <div class="organizer-execution-heading">
            <h2>{{ $event->title }}</h2>
            <x-organizer-status :status="$event->executionPhase()" />
        </div>
        <p class="organizer-candidate-meta"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M7 3v4m10-4v4M3 11h18"/></svg><span>{{ $event->starts_at?->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }} &ndash; {{ $event->ends_at?->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</span></p>
    </div>
    @if($event->lifecycle_status === 'completed')
        <p class="organizer-execution-notice"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/></svg><span>Absensi sudah final dan tidak dapat diubah.</span></p>
    @endif
    <div x-show="error" x-cloak role="alert" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
        <p x-text="error"></p>
        <button type="button" @click="prepare()" :disabled="busy" class="mt-2 font-semibold underline">Coba lagi</button>
    </div>
    @if($pendingSessionApplications)
        <noscript><form method="POST" action="{{ route('organizer.attendance-sessions.initialize', $event) }}">@csrf
            @foreach($pendingSessionApplications as $id)<input type="hidden" name="applications[]" value="{{ $id }}">@endforeach
            <button type="submit" class="organizer-action-button">Tampilkan sesi peserta</button>
        </form></noscript>
    @endif
    <section aria-labelledby="attendance-participants-heading" data-live-attendance>
        <h2 id="attendance-participants-heading" class="sr-only">Kehadiran peserta</h2>
        <form method="GET" action="{{ route('organizer.execution.show', $event) }}" class="organizer-event-filters organizer-attendance-filters">
            <div class="organizer-checkin-search">
                <label for="attendance-search" class="sr-only">Cari peserta</label>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="10.5" cy="10.5" r="7.5"/><path stroke-linecap="round" d="m16 16 5 5"/></svg>
                <input id="attendance-search" type="search" name="search" value="{{ request('search') }}" maxlength="100" placeholder="Cari nama peserta...">
                <button type="submit" class="sr-only">Cari peserta</button>
            </div>
            <details class="organizer-checkin-filters" @if(request()->filled('position_id') || request()->filled('status') || request()->filled('session_date')) open @endif>
                <summary>Filter peserta</summary>
                <div class="organizer-event-filters mt-4">
            <div><label for="attendance-position">Posisi</label><select id="attendance-position" name="position_id"><option value="">Semua posisi</option>@foreach($event->positions as $position)<option value="{{ $position->id }}" @selected(request('position_id') == $position->id)>{{ $position->name }}</option>@endforeach</select></div>
            <div><label for="attendance-status">Kehadiran</label><select id="attendance-status" name="status"><option value="">Semua status</option>@foreach(['unrecorded' => 'Belum dicatat', 'present' => 'Hadir', 'absent' => 'Tidak hadir'] as $key => $label)<option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>@endforeach</select></div>
            <div><label for="attendance-date">Hari tugas (WIB)</label><input id="attendance-date" type="date" name="session_date" value="{{ request('session_date') }}"></div>
            <button type="submit" class="organizer-action-button">Terapkan</button>
            @if(request()->filled('search') || request()->filled('position_id') || request()->filled('status') || request()->filled('session_date'))<a href="{{ route('organizer.execution.show', $event) }}" class="organizer-event-filter-reset">Reset</a>@endif
                </div>
        <dl class="organizer-session-recap" aria-label="Rekap kehadiran per sesi">
            @foreach(['present' => 'Hadir', 'absent' => 'Tidak hadir', 'unrecorded' => 'Belum dicatat'] as $key => $label)
                <div><dt><span class="organizer-session-recap-dot organizer-session-recap-dot--{{ $key }}" aria-hidden="true"></span>{{ $label }}</dt><dd>{{ $sessionSummary->$key }}</dd></div>
            @endforeach
        </dl>
            </details>
        </form>
        <div data-attendance-results><div class="space-y-4">
            @forelse($applications as $application)
                @php($attendance = $application->attendance)
                <article class="organizer-attendance-participant" x-data="{ expanded: {{ $errors->any() || (int) session('attendance_application_id') === $application->id ? 'true' : 'false' }} }">
                    <header class="organizer-attendance-participant-header">
                        <div class="organizer-candidate-identity">
                            <span class="organizer-candidate-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($application->volunteer->name, 0, 1)) }}</span>
                            <div class="min-w-0">
                                <h3 class="organizer-candidate-name">{{ $application->volunteer->name }}</h3>
                                <p class="organizer-checkin-position">{{ $application->position->name }}</p>
                            </div>
                        </div>
                        <div class="organizer-attendance-participant-progress">
                            @if($attendance?->uses_sessions)
                                <span>{{ $attendance->sessions->where('status', 'present')->count() }} dari {{ $attendance->sessions->count() }} sesi hadir</span>
                            @else
                                <span>{{ ['unrecorded' => 'Belum dicatat', 'present' => 'Hadir', 'absent' => 'Tidak hadir'][$attendance?->status ?? 'unrecorded'] }}</span>
                            @endif
                        </div>
                        <button type="button" class="organizer-secondary-button organizer-info-edit" @click="expanded = !expanded" :aria-expanded="expanded.toString()" aria-controls="participant-sessions-{{ $application->id }}">
                            <span x-text="expanded ? 'Tutup sesi' : 'Lihat sesi'">Lihat sesi</span>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" :class="{ 'rotate-180': expanded }"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
                        </button>
                    </header>
                    <div id="participant-sessions-{{ $application->id }}" class="organizer-attendance-participant-body" x-show="expanded" x-cloak>
                        @if($attendance?->uses_sessions)
                            @include('organizer.execution.session-forms', ['attendance' => $attendance, 'application' => $application])
                        @elseif(!in_array($event->lifecycle_status, ['completed', 'cancelled']) && (!$attendance || $attendance->status === 'unrecorded'))
                            <p class="text-sm text-slate-500" role="status">Memuat sesi peserta...</p>
                        @elseif($attendance && $event->lifecycle_status !== 'cancelled')
                        <p class="mb-3 text-sm font-semibold text-amber-800">Catatan lama per kegiatan</p>
                        @if($event->lifecycle_status === 'completed' && $attendance->note)<p class="mb-3 whitespace-pre-line text-sm text-slate-600">Catatan: {{ $attendance->note }}</p>@endif
                        @if(!in_array($event->lifecycle_status, ['completed', 'cancelled']))
                            <form method="POST" action="{{ route('organizer.attendances.prepare-sessions', $attendance) }}" class="mb-5 space-y-3 rounded-xl border border-indigo-200 p-4">
                                @csrf
                                <input type="hidden" name="revision" value="{{ $attendance->revision }}">
                                <p class="text-sm">Sesi baru berstatus Belum dicatat. Catatan lama tetap diarsipkan.</p>
                                @if($attendance->status !== 'unrecorded')<label class="block">Alasan pengalihan<textarea name="reason" required minlength="5" maxlength="1000" class="block w-full rounded-lg border-slate-300"></textarea></label>@endif
                                <label class="block text-sm"><input type="checkbox" name="confirmed" value="1" required> Saya akan mencatat setiap sesi sesuai kehadiran sebenarnya.</label>
                                <x-primary-button>Siapkan absensi per sesi</x-primary-button>
                            </form>
                        @endif
                        @if($attendance->status !== 'unrecorded' && $event->lifecycle_status !== 'completed')
                        <form method="POST" action="{{ route('organizer.attendances.update', $attendance) }}" class="space-y-3" x-data="{busy:false}" @submit="if (busy) { $event.preventDefault(); } else { $nextTick(() => busy = true); }" @pageshow.window="busy=false">
                            @csrf @method('PATCH')
                            <input type="hidden" name="revision" value="{{ $attendance->revision }}">
                            <label class="block">Kehadiran<select name="status" class="ml-2 rounded-lg border-slate-300" required><option value="">Pilih</option><option value="present" @selected($attendance->status === 'present')>Hadir</option><option value="absent" @selected($attendance->status === 'absent')>Tidak hadir</option></select></label>
                            <label class="block">Catatan<textarea name="note" maxlength="1000" class="block w-full rounded-lg border-slate-300">{{ $attendance->note }}</textarea></label>
                            <x-primary-button x-bind:disabled="busy">Simpan kehadiran</x-primary-button>
                        </form>
                        @endif
                        @elseif(!$attendance && !in_array($event->lifecycle_status, ['cancelled', 'completed']))
                            <form method="POST" action="{{ route('organizer.applications.prepare-attendance-sessions', $application) }}" class="space-y-3">
                                @csrf
                                <label class="block text-sm"><input type="checkbox" name="confirmed" value="1" required> Siapkan absensi peserta dari jadwal tugas saat lamaran dikirim.</label>
                                <x-primary-button>Siapkan absensi per sesi</x-primary-button>
                            </form>
                        @else <p>Kehadiran tidak dapat diubah.</p> @endif
                    </div>
                </article>
            @empty
                <div class="organizer-attendance-participant p-6"><x-empty-state title="Belum ada peserta" description="Tidak ada peserta diterima yang sesuai filter." /></div>
            @endforelse
        </div>
        <p class="mt-5 text-sm text-slate-500">Menampilkan {{ $applications->count() }} peserta.</p>
        @if($applications->hasPages())<div class="mt-4">{{ $applications->links() }}</div>@endif
        </div>
    </section>
    @if(!in_array($event->lifecycle_status, ['cancelled', 'completed']))
    <x-section-card>
        <h2 class="mb-3 text-lg font-semibold">Selesaikan kegiatan</h2>
        <p class="mb-3 text-sm text-slate-600">Selesaikan setelah seluruh sesi berakhir, absensi tercatat, dan seleksi final.</p>
        @if($hasPendingSelection)
            <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 p-4">
                <p class="text-sm text-amber-800">Masih ada lamaran atau asesmen tertunda. Selesaikan seleksi di Kelola Pelamar terlebih dahulu.</p>
                <a href="{{ route('organizer.applications.index', $event) }}#tutup-seleksi" class="organizer-secondary-button organizer-info-edit mt-3">Ke Kelola Pelamar</a>
            </div>
        @endif
        @if($completionIssues)
            <ul class="mb-3 list-disc pl-5 text-sm text-amber-800">@foreach($completionIssues as $issue)<li>{{ $issue }}</li>@endforeach</ul>
        @elseif(!$hasPendingSelection)<p class="mb-3 text-sm text-emerald-700">Semua syarat terpenuhi. Kegiatan siap diselesaikan.</p>@endif
        <form method="POST" action="{{ route('organizer.events.complete', $event) }}" class="space-y-3">
            @csrf
            <label class="block"><input type="checkbox" name="confirmed" value="1" required> Saya sudah memeriksa rekap kegiatan. Setelah selesai, absensi dikunci dan tidak dapat diubah.</label>
            @if($acceptedCount === 0)<label class="block"><input required type="checkbox" name="confirm_zero" value="1"> Jika tidak ada peserta diterima, saya mengonfirmasi jumlah nol.</label>@endif
            <x-primary-button :disabled="$hasPendingSelection || count($completionIssues) > 0">Selesaikan kegiatan</x-primary-button>
        </form>
    </x-section-card>
    @endif
</div>
@endsection
