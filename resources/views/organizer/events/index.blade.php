@extends('layouts.user')
@section('title', 'Event Saya')
@section('content')
<x-page-header title="Event Saya" description="Kelola draft, posisi, paket dan publikasi kegiatan Anda." />
<a class="inline-block mb-6 rounded-lg bg-indigo-600 px-4 py-2 text-white" href="{{ route('organizer.events.create') }}">Buat event</a>
<div class="grid gap-4 md:grid-cols-2">
@forelse($events as $event)
<x-section-card><h2 class="text-lg font-semibold"><a class="text-indigo-700" href="{{ route('organizer.events.show',$event) }}">{{ $event->title }}</a></h2><p class="mt-2">{{ $event->status }} / {{ $event->publication_status }} / {{ $event->lifecycle_status }}</p><p class="text-sm text-slate-600">{{ $event->starts_at?->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</p></x-section-card>
@empty<x-empty-state title="Belum ada event" description="Mulai dengan membuat draft kegiatan." />@endforelse
</div><div class="mt-6">{{ $events->links() }}</div>
@endsection
