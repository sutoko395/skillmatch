@extends('layouts.user')
@section('title', 'Detail Lamaran - ' . $application->event->title)
@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    <x-page-header :title="$application->event->title" :description="'Posisi: ' . $application->position->name" />

    <x-section-card>
        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 pb-4">
            <div>
                <p class="text-sm font-medium text-slate-500">Status Lamaran</p>
                <div class="mt-1 flex items-center gap-3">
                    <x-status-badge :status="$application->status === 'submitted' || $application->status === 'accepted' ? 'success' : ($application->status === 'draft' ? 'pending' : 'default')">
                        {{ ucfirst(str_replace('_', ' ', $application->status)) }}
                    </x-status-badge>
                    @if($application->status === 'submitted')
                        <span class="text-sm text-slate-600">Dikirim pada {{ $application->submitted_at?->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</span>
                    @endif
                </div>
            </div>

            @if($application->status === 'draft')
                <div>
                    <form method="POST" action="{{ route('volunteer.applications.submit', $application) }}">
                        @csrf
                        <x-button type="submit">Kirimkan Lamaran</x-button>
                    </form>
                </div>
            @endif
        </div>

        @if($application->status === 'draft' && !($eligibility['complete'] ?? true))
            <div class="mt-4 rounded-xl bg-amber-50 p-4 text-sm text-amber-800">
                <p class="font-semibold">Profil Anda belum lengkap!</p>
                <p class="mt-1">Silakan lengkapi data profil dan skill Anda sebelum mengirimkan lamaran.</p>
                <a href="{{ route('volunteer.profile.edit') }}" class="mt-2 inline-block font-semibold underline">Lengkapi Profil &rarr;</a>
            </div>
        @endif

        <div class="mt-6 space-y-4">
            <div>
                <h3 class="text-lg font-bold text-gray-900">Penyelenggara</h3>
                <p class="text-slate-700">{{ $application->event->organizer->organizerProfile?->organization_name ?? $application->event->organizer->name }}</p>
            </div>

            <div>
                <h3 class="text-lg font-bold text-gray-900">Lokasi & Waktu Event</h3>
                <p class="text-slate-700">{{ $application->event->location }} &middot; {{ $application->event->cityRecord?->name }}</p>
                <p class="text-slate-700">{{ $application->event->starts_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }} - {{ $application->event->ends_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</p>
            </div>

            <div>
                <h3 class="text-lg font-bold text-gray-900">Deskripsi Posisi ({{ $application->position->name }})</h3>
                <p class="whitespace-pre-line text-slate-700">{{ $application->position->description }}</p>
            </div>

            @if($application->position->positionSkills->isNotEmpty())
                <div>
                    <h4 class="font-semibold text-gray-900">Skill yang Dibutuhkan</h4>
                    <ul class="mt-1 list-inside list-disc text-slate-700">
                        @foreach($application->position->positionSkills as $s)
                            <li>{{ $s->skill->name }} - {{ ucfirst($s->minimum_level) }} ({{ $s->is_required ? 'Wajib' : 'Preferensi' }})</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if($application->position->schedules->isNotEmpty())
                <div>
                    <h4 class="font-semibold text-gray-900">Jadwal Tugas (WIB)</h4>
                    @foreach($application->position->schedules as $slot)
                        <p class="text-slate-700">{{ $slot->starts_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }} - {{ $slot->ends_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }}</p>
                    @endforeach
                </div>
            @endif
        </div>
    </x-section-card>
</div>
@endsection
