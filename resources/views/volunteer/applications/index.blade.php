@extends('volunteer.layouts.sidebar')
@section('title', 'Lamaran Saya')
@section('page-class', 'volunteer-applications')
@section('content')
<div class="vl-applications mx-auto max-w-7xl space-y-6">
    <x-page-header title="Lamaran Saya" description="Daftar pengajuan lamaran kegiatan relawan yang Anda ikuti." />
    @forelse($applications as $app)
        @include('volunteer.applications.partials.list-card', ['application' => $app])
    @empty
        <x-empty-state title="Belum Ada Lamaran" description="Anda belum membuat atau mengirimkan lamaran kegiatan. Silakan jelajahi event untuk memilih posisi." />
    @endforelse
    <div class="mt-4">{{ $applications->links() }}</div>
</div>
@endsection
