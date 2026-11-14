@extends('organizer.layouts.sidebar')
@section('title', 'Pelaksanaan & Kehadiran')
@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    <x-page-header title="Pelaksanaan & Kehadiran" description="Pilih kegiatan untuk mengelola kehadiran peserta dan penyelesaian kegiatan." />
    <section class="organizer-event-list" aria-label="Daftar pelaksanaan kegiatan">
        @forelse($events as $event)
            <article class="flex flex-col gap-4 border-b border-slate-100 p-5 last:border-b-0 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <h2 class="break-words font-semibold text-slate-900">{{ $event->title }}</h2>
                    <p class="mt-2 text-sm text-slate-500">{{ $event->starts_at?->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') ?? 'Jadwal belum diisi' }}{{ $event->starts_at ? ' WIB' : '' }} &middot; {{ number_format($event->accepted_applications_count) }} peserta diterima</p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <x-organizer-status :status="$event->publication_status" context="Publikasi" />
                        <x-organizer-status :status="$event->lifecycle_status" context="Pelaksanaan" />
                    </div>
                </div>
                <a href="{{ route('organizer.execution.show', $event) }}" class="organizer-action-button organizer-event-manage shrink-0 self-start sm:self-auto" aria-label="Kelola pelaksanaan {{ $event->title }}">Kelola pelaksanaan</a>
            </article>
        @empty
            <div class="p-6">
                <x-empty-state title="Belum ada kegiatan" description="Kegiatan Anda akan tampil di sini untuk pengelolaan pelaksanaan dan kehadiran." />
            </div>
        @endforelse
    </section>
    @if($events->hasPages())
        {{ $events->links() }}
    @endif
</div>
@endsection
