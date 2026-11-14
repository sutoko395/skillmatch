@extends('organizer.layouts.sidebar')

@section('title', 'Ringkasan Aktivitas')

@section('content')
<div class="mx-auto w-full max-w-7xl space-y-6 organizer-dashboard">

    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <p class="text-sm font-medium text-indigo-600">
                Ruang kerja organisasi
            </p>

            <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">
                Halo, {{ $user->name }}!
            </h1>

            <p class="mt-2 text-sm text-slate-500">
                Pantau kegiatan dan perkembangan rekrutmen relawan Anda.
            </p>
        </div>

    </div>

    <div class="organizer-metrics grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        <div class="organizer-metric--blue rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-slate-500">Total Kegiatan</span>
                <span class="rounded-xl bg-indigo-50 p-2 text-indigo-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3M4 11h16M5 5h14a1 1 0 011 1v13a1 1 0 01-1 1H5a1 1 0 01-1-1V6a1 1 0 011-1z"/>
                    </svg>
                </span>
            </div>
            <p class="mt-4 text-3xl font-bold text-slate-900">{{ number_format($totalEvents) }}</p>
            <p class="mt-1 text-xs text-slate-500">Seluruh kegiatan Anda</p>
        </div>

        <div class="organizer-metric--green rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-slate-500">Kegiatan Aktif</span>
                <span class="rounded-xl bg-emerald-50 p-2 text-emerald-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
            </div>
            <p class="mt-4 text-3xl font-bold text-slate-900">{{ number_format($activeEvents) }}</p>
            <p class="mt-1 text-xs text-slate-500">Dipublikasikan dan belum selesai</p>
        </div>

        <div class="organizer-metric--purple rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-slate-500">Total Pelamar</span>
                <span class="rounded-xl bg-purple-50 p-2 text-purple-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2m6-10a4 4 0 100-8 4 4 0 000 8zm10 10v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
                    </svg>
                </span>
            </div>
            <p class="mt-4 text-3xl font-bold text-slate-900">{{ number_format($totalApplicants) }}</p>
            <p class="mt-1 text-xs text-slate-500">Lamaran yang sudah dikirim</p>
        </div>

        <div class="rounded-2xl border border-amber-200 bg-amber-50/50 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-amber-800">Lamaran Belum Ditinjau</span>
                <span class="rounded-xl bg-amber-100 p-2 text-amber-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
            </div>
            <p class="mt-4 text-3xl font-bold text-slate-900">{{ number_format($pendingApplications) }}</p>
            <p class="mt-1 text-xs text-slate-500">Kandidat siap diputuskan (Siap Ditinjau)</p>
        </div>

    </div>

    @include('activity.organizer')

</div>
@include('notifications.summary')
@endsection
