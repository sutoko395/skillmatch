@extends('organizer.layouts.sidebar')

@section('title', 'Dashboard Organizer')

@section('content')
<div class="w-full max-w-7xl mx-auto space-y-6">

    <div>
        <h1 class="text-2xl font-bold text-slate-800">
            Dashboard Organizer
        </h1>

        <p class="mt-1 text-sm text-slate-500">
            Kelola organisasi dan kegiatan Anda melalui panel Organizer.
        </p>
    </div>

    <x-section-card class="p-6 md:p-7">
        <p class="text-sm font-medium text-indigo-700">
            Selamat datang kembali
        </p>

        <h2 class="mt-2 text-2xl font-bold text-slate-800 md:text-3xl">
            {{ $user->name }}
        </h2>

        <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600">
            Kelola organisasi, event, posisi, dan kebutuhan relawan Anda dari satu tempat.
        </p>
    </x-section-card>

    <div class="grid gap-5 lg:grid-cols-3">

        <x-section-card class="p-6 lg:col-span-2">
            <h2 class="text-lg font-bold text-slate-800">
                Profil Organisasi
            </h2>

            @if($profile)
                <div class="mt-5">
                    <p class="break-words text-xl font-bold text-indigo-700">
                        {{ $profile->organization_name }}
                    </p>

                    <dl class="mt-5 grid gap-x-8 gap-y-5 text-sm sm:grid-cols-2">
                        @foreach([
                            'contact_person' => 'Penanggung jawab',
                            'city' => 'Kota',
                            'phone' => 'Telepon',
                            'email' => 'Email',
                        ] as $field => $label)
                            <div>
                                <dt class="text-slate-500">
                                    {{ $label }}
                                </dt>

                                <dd class="mt-1 break-words font-semibold text-slate-800">
                                    {{ $profile->$field ?: 'Belum diisi' }}
                                </dd>
                            </div>
                        @endforeach
                    </dl>

                    @if($profile->description)
                        <div class="mt-6 border-t border-slate-100 pt-5">
                            <p class="whitespace-pre-line break-words text-sm leading-6 text-slate-600">
                                {{ $profile->description }}
                            </p>
                        </div>
                    @endif
                </div>
            @else
                <div class="mt-5 rounded-xl bg-slate-50 p-5">
                    <p class="text-sm leading-6 text-slate-600">
                        Profil organisasi belum diisi. Lengkapi identitas dan kontak organisasi Anda.
                    </p>
                </div>
            @endif
        </x-section-card>

        <x-section-card class="p-6">
            <h2 class="text-lg font-bold text-slate-800">
                Status Organizer
            </h2>

            <dl class="mt-5 space-y-5 text-sm">
                <div>
                    <dt class="mb-2 text-slate-500">
                        Status akun
                    </dt>

                    <dd>
                        <x-status-badge :status="$user->is_active ? 'success' : 'inactive'">
                            {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                        </x-status-badge>
                    </dd>
                </div>

                <div>
                    <dt class="mb-2 text-slate-500">
                        Verifikasi organisasi
                    </dt>

                    <dd>
                        <x-status-badge :status="$user->organizer_status">
                            {{ [
                                'active' => 'Terverifikasi',
                                'pending' => 'Menunggu verifikasi',
                                'inactive' => 'Belum terverifikasi',
                            ][$user->organizer_status] }}
                        </x-status-badge>
                    </dd>
                </div>
            </dl>
        </x-section-card>
    </div>

    <x-section-card class="p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-bold text-slate-800">
                    Aksi Cepat
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Akses fitur utama Organizer.
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                <a
                    href="{{ route('organizer.events.index') }}"
                    class="inline-flex items-center rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700"
                >
                    Kelola Event
                </a>

                <a
                    href="{{ route('organizer.profile.edit') }}"
                    class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                >
                    Profil Organisasi
                </a>
            </div>
        </div>
    </x-section-card>

</div>
@endsection