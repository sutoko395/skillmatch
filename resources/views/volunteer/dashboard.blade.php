@extends('volunteer.layouts.sidebar')

@section('title', 'Aktivitas Volunteer')

@section('content')

<div class="mx-auto max-w-7xl space-y-6">

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm md:p-8">
        <p class="text-sm font-semibold text-indigo-600">
            Aktivitas Volunteer
        </p>

        <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900 md:text-3xl">
            Selamat Datang Kembali,
            <span class="text-indigo-600">{{ $user->name }}</span>!
        </h1>

        <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600 md:text-base">
            Tinjau profil, skill, lokasi, dan waktu ketersediaan Anda sebelum mengikuti kegiatan volunteer.
        </p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-medium text-slate-500">
                Skill Saya
            </p>

            <p class="mt-2 text-3xl font-bold text-indigo-600">
                {{ $skills->count() }}
            </p>

            <p class="mt-1 text-sm text-slate-500">
                {{ count($eligibility['skills'] ?? []) }} skill aktif pada profil.
            </p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-medium text-slate-500">
                Status Profil
            </p>

            @if($eligibility['complete'] ?? false)
                <div class="mt-3 inline-flex items-center rounded-full bg-emerald-50 px-3 py-1.5 text-sm font-semibold text-emerald-700">
                    Profil Lengkap
                </div>
            @else
                <div class="mt-3 inline-flex items-center rounded-full bg-amber-50 px-3 py-1.5 text-sm font-semibold text-amber-700">
                    Perlu Dilengkapi
                </div>
            @endif
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:col-span-2 lg:col-span-1">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-slate-500">
                    Kegiatan Diikuti
                </p>

                <span class="rounded-xl bg-indigo-50 p-2 text-indigo-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M8 7V3m8 4V3M4 11h16M5 5h14a1 1 0 011 1v13a1 1 0 01-1 1H5a1 1 0 01-1-1V6a1 1 0 011-1z"/>
                    </svg>
                </span>
            </div>

            <p class="mt-2 text-3xl font-bold text-indigo-600">
                {{ $applications->count() }}
            </p>

            <p class="mt-1 text-sm text-slate-500">
                Kegiatan yang sudah Anda lamar.
            </p>
        </div>

    </div>

    <div class="grid gap-6 lg:grid-cols-3">

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2 md:p-8">

            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h2 class="text-xl font-bold text-slate-900">
                        Mulai Perjalanan Volunteer
                    </h2>

                    <p class="mt-2 text-sm leading-6 text-slate-600">
                        Pastikan seluruh data profil dan skill sudah lengkap sebelum mengikuti kegiatan.
                    </p>
                </div>

                <a
                    href="{{ route('volunteer.profile.edit') }}"
                    class="inline-flex shrink-0 items-center justify-center rounded-xl bg-indigo-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-indigo-700"
                >
                    {{ ($eligibility['complete'] ?? false) ? 'Perbarui Profil' : 'Lengkapi Profil' }}
                </a>
            </div>

            @unless($eligibility['complete'] ?? false)

                @php
                    $labels = [
                        'name' => 'Nama',
                        'email' => 'Email',
                        'phone' => 'Nomor telepon',
                        'birth_date' => 'Tanggal lahir',
                        'gender' => 'Jenis kelamin',
                        'address' => 'Alamat',
                        'bio' => 'Bio',
                        'city_id' => 'Kota aktif',
                        'skills' => 'Skill aktif',
                        'availability_slots' => 'Tanggal dan jam ketersediaan',
                    ];
                @endphp

                <div class="mt-6 rounded-xl border border-amber-200 bg-amber-50 p-4">
                    <p class="text-sm font-semibold text-amber-900">
                        Data yang perlu dilengkapi
                    </p>

                    <ul class="mt-3 space-y-2 text-sm text-amber-800">
                        @forelse($eligibility['missing_fields'] ?? [] as $field)
                            <li class="flex items-start gap-2">
                                <span class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-amber-500"></span>
                                <span>{{ $labels[$field] ?? 'Profil Volunteer' }}</span>
                            </li>
                        @empty
                            <li>Profil masih perlu diperiksa.</li>
                        @endforelse
                    </ul>
                </div>

            @else

                <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                    <p class="text-sm font-semibold text-emerald-800">
                        Profil Anda sudah lengkap dan siap digunakan.
                    </p>

                    <p class="mt-1 text-sm text-emerald-700">
                        Silakan perbarui profil apabila ada perubahan data.
                    </p>
                </div>

            @endunless

        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

            <h2 class="text-lg font-bold text-slate-900">
                Skill dan Level
            </h2>

            <div class="mt-4 space-y-3">

                @forelse($skills as $row)

                    <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="break-words text-sm font-semibold text-slate-900">
                                    {{ $row->skill?->name ?? 'Skill tidak tersedia' }}
                                </p>

                                @if(!$row->skill?->is_active)
                                    <p class="mt-1 text-xs font-medium text-amber-700">
                                        Skill sedang nonaktif
                                    </p>
                                @endif
                            </div>

                            <span class="shrink-0 rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700">
                                {{ ucfirst($row->level) }}
                            </span>
                        </div>
                    </div>

                @empty

                    <div class="rounded-xl border border-dashed border-slate-300 p-4">
                        <p class="text-sm text-slate-500">
                            Belum ada skill tersimpan.
                        </p>
                    </div>

                @endforelse

            </div>

        </div>

    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm md:p-8">

        <div class="flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
            <div>
                <h2 class="text-lg font-bold text-slate-900">
                    Ketersediaan
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Waktu ketersediaan Anda dalam zona waktu WIB.
                </p>
            </div>

            <a
                href="{{ route('volunteer.profile.edit') }}"
                class="text-sm font-semibold text-indigo-600 hover:text-indigo-700"
            >
                Kelola profil
            </a>
        </div>

        <div class="mt-5 overflow-hidden rounded-xl border border-slate-200">

            @forelse($slots->take(5) as $slot)

                <div class="flex flex-col gap-2 border-b border-slate-100 p-4 last:border-0 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-semibold text-slate-900">
                            {{ $slot->starts_at->setTimezone('Asia/Jakarta')->format('d/m/Y') }}
                        </p>

                        <p class="mt-1 text-sm text-slate-500">
                            {{ $slot->starts_at->setTimezone('Asia/Jakarta')->format('H:i') }}
                            -
                            {{ $slot->ends_at->setTimezone('Asia/Jakarta')->format('H:i') }}
                            WIB
                        </p>
                    </div>

                    <span class="inline-flex w-fit rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                        Tersedia
                    </span>
                </div>

            @empty

                <div class="p-6 text-center">
                    <p class="text-sm text-slate-500">
                        Belum ada tanggal dan jam ketersediaan.
                    </p>
                </div>

            @endforelse

        </div>

        @if($slots->count() > 5)
            <p class="mt-3 text-sm text-slate-500">
                Menampilkan 5 dari {{ $slots->count() }} interval ketersediaan.
            </p>
        @endif

        @if($volunteerProfile?->availability)
            <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-4">
                <p class="text-sm text-amber-800">
                    Preferensi lama:
                    <span class="font-semibold">{{ $volunteerProfile->availability }}</span>
                </p>
            </div>
        @endif

    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm md:p-8">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-bold text-slate-900">
                    Kegiatan Saya
                </h2>
                <p class="mt-1 text-sm text-slate-500">
                    Daftar kegiatan yang sudah Anda lamar.
                </p>
            </div>

            <span class="inline-flex w-fit rounded-full bg-indigo-50 px-3 py-1.5 text-xs font-semibold text-indigo-700">
                {{ $applications->count() }} kegiatan
            </span>
        </div>

        <div class="mt-5 space-y-3">
            @forelse($applications as $application)
                <div class="flex flex-col gap-3 rounded-xl border border-slate-200 p-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="min-w-0">
                        <h3 class="font-semibold text-slate-900">
                            {{ $application->event?->title ?? 'Kegiatan tidak tersedia' }}
                        </h3>

                        <p class="mt-1 text-sm text-slate-500">
                            Posisi:
                            {{ $application->position?->title ?? $application->position?->name ?? 'Tidak tersedia' }}
                        </p>

                        <p class="mt-1 text-xs text-slate-400">
                            Dilamar:
                            {{ $application->submitted_at?->format('d M Y') ?? 'Tanggal tidak tersedia' }}
                        </p>
                    </div>

                    <span class="inline-flex w-fit shrink-0 rounded-full px-3 py-1.5 text-xs font-semibold
                        {{ $application->status === 'accepted'
                            ? 'bg-emerald-50 text-emerald-700'
                            : ($application->status === 'rejected'
                                ? 'bg-red-50 text-red-700'
                                : 'bg-amber-50 text-amber-700') }}">
                        {{ ucfirst(str_replace('_', ' ', $application->status)) }}
                    </span>
                </div>
            @empty
                <div class="rounded-xl border border-dashed border-slate-300 p-8 text-center">
                    <p class="font-semibold text-slate-700">
                        Belum ada kegiatan yang dilamar
                    </p>
                    <p class="mt-2 text-sm text-slate-500">
                        Kegiatan yang sudah Anda kirim lamarannya akan muncul di sini.
                    </p>
                </div>
            @endforelse
        </div>
    </div>

</div>

@endsection