@extends('organizer.layouts.sidebar')

@section('title', 'Dashboard Organizer')

@section('content')
<div class="mx-auto w-full max-w-7xl space-y-8">

    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <p class="text-sm font-medium text-indigo-600">
                Organizer Dashboard
            </p>

            <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
                Halo, {{ $user->name }}!
            </h1>

            <p class="mt-2 text-sm text-slate-500">
                Pantau kegiatan dan perkembangan rekrutmen relawan Anda.
            </p>
        </div>

        <a href="{{ route('organizer.events.create') }}"
           class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Buat Kegiatan
        </a>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
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

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
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

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-slate-500">Total Pelamar</span>
                <span class="rounded-xl bg-sky-50 p-2 text-sky-600">
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
            <p class="mt-1 text-xs text-slate-500">Berstatus submitted</p>
        </div>

    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm xl:col-span-2">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-5 sm:px-6">
                <div>
                    <h2 class="font-bold text-slate-900">Pelamar Terbaru</h2>
                    <p class="mt-1 text-sm text-slate-500">Lamaran terbaru pada kegiatan Anda.</p>
                </div>

                <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">
                    {{ $latestApplications->count() }} data
                </span>
            </div>

            @forelse ($latestApplications as $application)
                <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-4 last:border-b-0 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <div class="flex min-w-0 items-center gap-3">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-indigo-50 font-bold text-indigo-700">
                            {{ strtoupper(substr($application->volunteer?->name ?? 'R', 0, 1)) }}
                        </div>

                        <div class="min-w-0">
                            <p class="truncate font-semibold text-slate-800">
                                {{ $application->volunteer?->name ?? 'Relawan' }}
                            </p>
                            <p class="mt-1 truncate text-sm text-slate-500">
                                {{ $application->event?->title ?? 'Kegiatan' }}
                            </p>
                            <p class="mt-1 text-xs text-slate-400">
                                {{ $application->position?->title ?? $application->position?->name ?? 'Posisi relawan' }}
                                ·
                                {{ $application->submitted_at?->format('d M Y') ?? 'Tanggal belum tersedia' }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-3 sm:justify-end">
                        <span class="rounded-full px-3 py-1 text-xs font-semibold
                            {{ $application->status === 'submitted' ? 'bg-amber-50 text-amber-700' : 'bg-slate-100 text-slate-600' }}">
                            {{ ucfirst(str_replace('_', ' ', $application->status)) }}
                        </span>

                        <a href="{{ route('organizer.applications.show', $application) }}"
                           class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">
                            Detail
                        </a>
                    </div>
                </div>
            @empty
                <div class="px-6 py-12 text-center">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-500">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2m6-10a4 4 0 100-8 4 4 0 000 8z"/>
                        </svg>
                    </div>
                    <p class="mt-3 font-semibold text-slate-700">Belum ada pelamar</p>
                    <p class="mt-1 text-sm text-slate-500">Lamaran yang masuk akan muncul di sini.</p>
                </div>
            @endforelse
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-5">
                <h2 class="font-bold text-slate-900">Kegiatan Saya</h2>
                <p class="mt-1 text-sm text-slate-500">Ringkasan kegiatan terbaru.</p>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse ($myEvents as $event)
                    <div class="space-y-3 p-5">
                        <div class="flex items-start justify-between gap-3">
                            <h3 class="font-semibold leading-5 text-slate-800">
                                {{ $event->title }}
                            </h3>

                            <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-medium
                                {{ $event->publication_status === 'published' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                {{ ucfirst(str_replace('_', ' ', $event->publication_status ?? $event->status)) }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between text-sm text-slate-500">
                            <span>{{ number_format($event->applicants_count) }} pelamar</span>
                            <a href="{{ route('organizer.applications.index', $event) }}"
                               class="font-semibold text-indigo-600 hover:text-indigo-800">
                                Kelola pelamar
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-10 text-center">
                        <p class="font-semibold text-slate-700">Belum ada kegiatan</p>
                        <p class="mt-1 text-sm text-slate-500">Mulai dengan membuat kegiatan pertama Anda.</p>
                        <a href="{{ route('organizer.events.create') }}"
                           class="mt-4 inline-flex rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                            Buat Kegiatan
                        </a>
                    </div>
                @endforelse
            </div>

            @if ($myEvents->isNotEmpty())
                <div class="border-t border-slate-100 p-5">
                    <a href="{{ route('organizer.events.index') }}"
                       class="flex items-center justify-center gap-2 text-sm font-semibold text-indigo-600 hover:text-indigo-800">
                        Lihat semua kegiatan
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-6-6l6 6-6 6"/>
                        </svg>
                    </a>
                </div>
            @endif
        </section>

    </div>

</div>
@endsection