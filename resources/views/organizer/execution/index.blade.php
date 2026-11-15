@extends('organizer.layouts.sidebar')
@section('title', 'Pelaksanaan & Kehadiran')
@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Pelaksanaan &amp; Kehadiran</h1>
        <p class="mt-1 text-sm text-slate-500">Kegiatan tampil sejak hari pelaksanaan pukul 00.00 WIB. Kehadiran mengikuti jadwal kegiatan.</p>
    </div>
    <form method="GET" action="{{ route('organizer.execution.index') }}" class="organizer-event-filters" role="search" aria-label="Cari dan filter pelaksanaan kegiatan">
        <div class="organizer-event-search">
            <label for="execution-event-search">Cari kegiatan</label>
            <input id="execution-event-search" type="search" name="search" value="{{ $search }}" maxlength="255" placeholder="Cari nama kegiatan...">
        </div>
        <div>
            <label for="execution-phase">Tahap pelaksanaan</label>
            <select id="execution-phase" name="phase">
                <option value="">Semua</option>
                @foreach(['today' => 'Hari ini', 'ongoing' => 'Berlangsung', 'history' => 'Riwayat'] as $value => $label)
                    <option value="{{ $value }}" @selected($phase === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="organizer-action-button">Terapkan</button>
        @if($search !== '' || $phase !== '')
            <a href="{{ route('organizer.execution.index') }}" class="organizer-event-filter-reset">Reset</a>
        @endif
    </form>
    @foreach(['today' => 'Hari ini', 'ongoing' => 'Berlangsung', 'history' => 'Riwayat'] as $group => $label)
        @if($executionGroups->has($group))
            <section aria-label="{{ $label }}">
                <h2 class="mb-3 text-base font-semibold text-slate-900">{{ $label }}</h2>
                <div class="organizer-event-list">
                    @foreach($executionGroups->get($group) as $event)
            <article class="organizer-event-row">
                <x-event-cover :event="$event" class="organizer-event-thumbnail" />
                <div class="min-w-0">
                    <a href="{{ route('organizer.execution.show', $event) }}" class="organizer-event-title">{{ $event->title }}</a>
                    <div class="organizer-event-metadata">
                        <div class="organizer-event-schedule">
                            <span class="organizer-event-meta-item">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 3v4m8-4v4M4 10h16M6 5h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z" /></svg>
                                <span><span class="sr-only">Tanggal kegiatan: </span>{{ $event->starts_at?->setTimezone('Asia/Jakarta')->format('d/m/Y') ?? 'Jadwal belum diisi' }}</span>
                            </span>
                            @if($event->starts_at)
                                <span class="organizer-event-meta-item">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><circle cx="12" cy="12" r="9" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 2" /></svg>
                                    <span><span class="sr-only">Jam kegiatan: </span>{{ $event->starts_at->setTimezone('Asia/Jakarta')->format('H:i') }} WIB</span>
                                </span>
                            @endif
                        </div>
                        <div class="organizer-event-meta-item organizer-event-location">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 10c0 5-7 11-7 11S5 15 5 10a7 7 0 1 1 14 0Z" /><circle cx="12" cy="10" r="2.5" /></svg>
                            <span><span class="sr-only">Lokasi kegiatan: </span>{{ $event->location }}{{ $event->location && $event->city ? ' · ' : '' }}{{ $event->city }}</span>
                        </div>
                    </div>
                    <p class="organizer-applicant-count">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2m14-14a4 4 0 0 1 0 8m6 6v-2a4 4 0 0 0-3-3.87M13 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z" /></svg>
                        <span><strong>{{ number_format($event->accepted_applications_count) }}</strong> peserta diterima</span>
                    </p>
                    @if($group === 'history' && !in_array($event->lifecycle_status, ['completed', 'cancelled']) && $event->ends_at && $event->ends_at->lte($now))
                        <p class="mt-2 text-xs text-amber-700">Menunggu penyelesaian kegiatan</p>
                    @endif
                </div>
                <a href="{{ route('organizer.execution.show', $event) }}" class="organizer-action-button organizer-event-manage" aria-label="Kelola pelaksanaan {{ $event->title }}">Kelola pelaksanaan</a>
            </article>
                    @endforeach
                </div>
            </section>
        @endif
    @endforeach
    @if($events->isEmpty())
        <section class="organizer-event-list p-6">
            @if($search !== '' || $phase !== '')
                <x-empty-state title="Kegiatan tidak ditemukan" description="Coba kata kunci atau tahap pelaksanaan lain." />
            @else
                <x-empty-state title="Belum ada kegiatan" description="Kegiatan yang telah dipublikasikan akan muncul mulai hari pelaksanaannya. Riwayat kegiatan selesai atau dibatalkan tetap dapat diakses." />
            @endif
        </section>
    @endif
    @if($events->hasPages())
        {{ $events->links() }}
    @endif
</div>
@endsection
