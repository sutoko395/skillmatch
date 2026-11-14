@extends('volunteer.layouts.sidebar')
@section('title', 'Riwayat Partisipasi')
@section('page-class', 'volunteer-history')
@section('content')
<div class="vh-history mx-auto max-w-7xl space-y-6">
    <x-page-header title="Riwayat Partisipasi" description="Riwayat kegiatan dan hasil kehadiran Anda." />
    @forelse($applications as $application)
        @include('volunteer.history.partials.participation-card')
    @empty <x-section-card>Belum ada riwayat kegiatan selesai atau dibatalkan.</x-section-card> @endforelse
    {{ $applications->links() }}
</div>
@endsection
