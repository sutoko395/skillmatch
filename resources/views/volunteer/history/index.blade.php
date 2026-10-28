@extends('volunteer.layouts.sidebar')
@section('title', 'Riwayat Partisipasi')
@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    <x-page-header title="Riwayat Partisipasi" description="Hasil berasal dari keputusan dan kehadiran aktual. Belum dicatat berbeda dengan Tidak hadir." />
    @forelse($applications as $application)
        <x-section-card>
            <a class="text-lg font-semibold text-indigo-600" href="{{ route('volunteer.applications.show', $application) }}">{{ $application->event->title }}</a>
            <p>{{ $application->position->name }}</p>
            <p class="text-sm text-slate-600">{{ $application->event->starts_at?->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</p>
            <p class="mt-2 font-semibold">{{ $application->event->lifecycle_status === 'cancelled' ? 'Dibatalkan' : ($application->status === 'accepted' ? match($application->attendance?->status) {'present' => 'Partisipasi berhasil', 'absent' => 'Tidak hadir', default => 'Belum dicatat'} : 'Tidak berpartisipasi') }}</p>
            <x-application-status :application="$application" />
        </x-section-card>
    @empty <x-section-card>Belum ada riwayat kegiatan selesai atau dibatalkan.</x-section-card> @endforelse
    {{ $applications->links() }}
</div>
@endsection
