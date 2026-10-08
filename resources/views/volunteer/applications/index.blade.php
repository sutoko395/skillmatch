@extends('layouts.user')
@section('title', 'Lamaran Saya')
@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    <x-page-header title="Lamaran Saya" description="Daftar pengajuan lamaran kegiatan relawan yang Anda ikuti." />

    @forelse($applications as $app)
        <x-section-card class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
            <div class="space-y-1">
                <div class="flex items-center gap-3">
                    <h2 class="text-xl font-bold text-gray-900">{{ $app->event->title }}</h2>
                    <x-status-badge :status="$app->status === 'submitted' || $app->status === 'accepted' ? 'success' : ($app->status === 'draft' ? 'pending' : 'default')">
                        {{ ucfirst(str_replace('_', ' ', $app->status)) }}
                    </x-status-badge>
                </div>
                <p class="text-sm font-medium text-indigo-700">Posisi: {{ $app->position->name }}</p>
                <p class="text-sm text-slate-600">
                    {{ $app->event->location }} &middot; {{ $app->event->cityRecord?->name }}
                </p>
                <p class="text-xs text-slate-500">
                    Dibuat: {{ $app->created_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB
                    @if($app->submitted_at)
                        &middot; Dikirim: {{ $app->submitted_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB
                    @endif
                </p>
            </div>
            <div>
                <a href="{{ route('volunteer.applications.show', $app) }}" class="inline-flex items-center rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                    Lihat Detail
                </a>
            </div>
        </x-section-card>
    @empty
        <x-empty-state title="Belum Ada Lamaran" description="Anda belum membuat atau mengirimkan lamaran kegiatan. Silakan jelajahi event untuk memilih posisi." />
    @endforelse

    <div class="mt-4">
        {{ $applications->links() }}
    </div>
</div>
@endsection
