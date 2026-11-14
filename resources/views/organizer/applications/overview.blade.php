@extends('organizer.layouts.sidebar')
@section('title', 'Kelola Pelamar')
@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Kelola Pelamar</h1>
        <p class="mt-1 text-sm text-slate-500">Pilih kegiatan untuk melihat dan meninjau pelamarnya.</p>
    </div>
    <section class="organizer-event-list" aria-label="Kegiatan untuk pengelolaan pelamar">
        @forelse($events as $event)
            <article class="organizer-event-row">
                <x-event-cover :event="$event" class="organizer-event-thumbnail" />
                <div class="min-w-0">
                    <a href="{{ route('organizer.applications.index', $event) }}" class="organizer-event-title">{{ $event->title }}</a>
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
                        <span><strong>{{ number_format($event->submitted_applications_count) }}</strong> lamaran terkirim</span>
                    </p>
                    <div class="organizer-event-badges mt-3 flex flex-wrap gap-2">
                        <x-organizer-status :status="$event->publication_status" context="Publikasi" class="organizer-list-status--blue" />
                        <x-organizer-status :status="$event->lifecycle_status" context="Pelaksanaan" class="organizer-list-status--yellow" />
                    </div>
                </div>
                <a href="{{ route('organizer.applications.index', $event) }}" class="organizer-action-button organizer-event-manage" aria-label="Lihat pelamar {{ $event->title }}">Lihat pelamar</a>
            </article>
        @empty
            <div class="p-6">
                <x-empty-state title="Belum ada kegiatan" description="Kegiatan Anda akan tampil di sini untuk pengelolaan pelamar." />
            </div>
        @endforelse
    </section>
    @if($events->hasPages())
        {{ $events->links() }}
    @endif
</div>
@endsection
