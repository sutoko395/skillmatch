@extends('layouts.user')
@section('title', 'Aktivitas Organizer')
@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    <x-section-card class="md:p-8">
        <p class="mb-2 text-sm font-medium text-indigo-700">Aktivitas Organizer</p>
        <h1 class="text-2xl font-semibold text-gray-900 md:text-3xl">Selamat Datang Kembali, <span class="font-bold text-indigo-600">{{ $user->name }}</span>!</h1>
        <p class="mt-3 text-slate-600">Tinjau informasi organisasi dan kontak untuk mempersiapkan kegiatan Anda.</p>
    </x-section-card>
    <div class="grid gap-6 lg:grid-cols-3">
        <x-section-card class="lg:col-span-2">
            <h2 class="text-lg font-bold">Profil Organisasi</h2>
            @if($profile)
                <p class="mt-4 break-words text-xl font-semibold text-indigo-700">{{ $profile->organization_name }}</p>
                <dl class="mt-4 grid gap-4 text-sm sm:grid-cols-2">
                    @foreach(['contact_person' => 'Penanggung jawab', 'city' => 'Kota', 'phone' => 'Telepon', 'email' => 'Email kontak'] as $field => $label)
                        <div><dt class="text-slate-600">{{ $label }}</dt><dd class="mt-1 break-words font-medium">{{ $profile->$field ?: 'Belum diisi' }}</dd></div>
                    @endforeach
                </dl>
                @if($profile->description)<p class="mt-5 whitespace-pre-line break-words text-sm text-slate-700">{{ $profile->description }}</p>@endif
            @else
                <p class="mt-4 text-slate-600">Profil organisasi belum diisi. Lengkapi identitas dan kontak Anda.</p>
            @endif
        </x-section-card>
        <x-section-card>
            <h2 class="text-lg font-bold">Status Organizer</h2>
            <dl class="mt-4 space-y-4 text-sm">
                <div><dt class="mb-2 text-slate-600">Akun</dt><dd><x-status-badge :status="$user->is_active ? 'success' : 'inactive'">{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</x-status-badge></dd></div>
                <div><dt class="mb-2 text-slate-600">Verifikasi organisasi</dt><dd><x-status-badge :status="$user->organizer_status">{{ ['active' => 'Terverifikasi', 'pending' => 'Menunggu verifikasi', 'inactive' => 'Belum terverifikasi'][$user->organizer_status] }}</x-status-badge></dd></div>
            </dl>
            <p class="mt-4 text-sm text-slate-600">Status organisasi tidak menandakan semua fitur pengelolaan kegiatan sudah tersedia.</p>
        </x-section-card>
    </div>
    <x-section-card>
        <h2 class="text-lg font-bold">Aksi Cepat Organizer</h2>
        <a href="{{ route('organizer.profile.edit') }}" class="mt-4 inline-flex rounded-xl bg-indigo-600 px-5 py-3 font-semibold text-white hover:bg-indigo-700">Perbarui Profil Organisasi</a>
    </x-section-card>
    <x-empty-state title="Pengelolaan kegiatan belum tersedia" description="Pembuatan event, daftar pelamar, seleksi dan pelaksanaan kegiatan sedang disiapkan. Informasi dan tindakan terkait akan tersedia setelah modul tersebut terintegrasi." />
</div>
@endsection
