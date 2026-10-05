@extends('layouts.user')
@section('title', 'Aktivitas Volunteer')
@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    <x-section-card class="md:p-8">
        <p class="mb-2 text-sm font-medium text-indigo-700">Aktivitas Volunteer</p>
        <h1 class="text-2xl font-semibold text-gray-900 md:text-3xl">Selamat Datang Kembali, <span class="font-bold text-indigo-600">{{ $user->name }}</span>!</h1>
        <p class="mt-3 text-slate-600">Tinjau profil, skill, dan waktu yang Anda sediakan untuk berkontribusi.</p>
    </x-section-card>
    <div class="grid gap-4 sm:grid-cols-3">
        <x-section-card><p class="text-sm text-slate-600">Skill Saya</p><p class="mt-2 text-2xl font-bold text-indigo-700">{{ $skills->count() }}</p><p class="mt-1 text-sm text-slate-600">{{ count($eligibility['skills']) }} skill aktif untuk profil.</p></x-section-card>
        <x-section-card><p class="mb-3 text-sm text-slate-600">Status Profil</p><x-status-badge :status="$eligibility['complete'] ? 'success' : 'pending'">{{ $eligibility['complete'] ? 'Lengkap' : 'Perlu dilengkapi' }}</x-status-badge></x-section-card>
        <x-section-card><p class="text-sm text-slate-600">Lokasi</p><p class="mt-2 break-words text-lg font-semibold">{{ $volunteerProfile?->cityRecord?->name ?? 'Belum dipilih' }}</p>
            @if($volunteerProfile?->cityRecord && !$volunteerProfile->cityRecord->is_active)<p class="mt-2 text-sm text-amber-800">Kota ini nonaktif. Pilih kota aktif pada profil.</p>@endif
            @if(!$volunteerProfile?->city_id && $volunteerProfile?->city)<p class="mt-2 text-sm text-amber-800">Kota lama: {{ $volunteerProfile->city }}. Pilih kota dari daftar.</p>@endif
        </x-section-card>
    </div>
    <div class="grid gap-6 lg:grid-cols-3">
        <x-section-card class="lg:col-span-2">
            <h2 class="text-lg font-bold">Mulai Perjalanan Volunteer</h2>
            <p class="mt-2 text-slate-600">Pastikan profil Anda siap sebelum melanjutkan ke kegiatan.</p>
            @unless($eligibility['complete'])
                @php($labels = ['name' => 'Nama', 'email' => 'Email', 'phone' => 'Nomor telepon', 'birth_date' => 'Tanggal lahir', 'gender' => 'Jenis kelamin', 'address' => 'Alamat', 'bio' => 'Bio', 'city_id' => 'Kota aktif', 'skills' => 'Skill aktif', 'availability_slots' => 'Tanggal dan jam ketersediaan'])
                <p class="mt-4 font-medium">Perlu dilengkapi:</p><ul class="mt-2 list-inside list-disc text-sm text-slate-700">@foreach($eligibility['missing_fields'] as $field)<li>{{ $labels[$field] ?? 'Profil Volunteer' }}</li>@endforeach</ul>
            @endunless
            <a href="{{ route('volunteer.profile.edit') }}" class="mt-5 inline-flex rounded-xl bg-indigo-600 px-5 py-3 font-semibold text-white hover:bg-indigo-700">{{ $eligibility['complete'] ? 'Perbarui Profil & Skill' : 'Lengkapi Profil & Skill' }}</a>
        </x-section-card>
        <x-section-card>
            <h2 class="text-lg font-bold">Skill dan Level</h2>
            <ul class="mt-4 space-y-3 text-sm">@forelse($skills as $row)<li class="break-words"><span class="font-semibold">{{ $row->skill?->name ?? 'Skill tidak tersedia' }}</span> <span class="text-slate-600">{{ ucfirst($row->level) }}</span>@if(!$row->skill?->is_active)<span class="block text-amber-800">Nonaktif, perbarui pilihan profil.</span>@endif</li>@empty<li class="text-slate-600">Belum ada skill tersimpan.</li>@endforelse</ul>
        </x-section-card>
    </div>
    <x-section-card>
        <h2 class="text-lg font-bold">Ketersediaan (WIB)</h2>
        <p class="mt-2 text-sm text-slate-600">Interval waktu dari profil Anda, bukan jadwal penugasan event.</p>
        <ul class="mt-4 space-y-3 text-sm">@forelse($slots->take(5) as $slot)<li>{{ $slot->starts_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }} &ndash; {{ $slot->ends_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</li>@empty<li class="text-slate-600">Belum ada tanggal dan jam ketersediaan.</li>@endforelse</ul>
        @if($slots->count() > 5)<p class="mt-3 text-sm text-slate-600">Menampilkan 5 dari {{ $slots->count() }} interval. Seluruh interval dapat dilihat di profil.</p>@endif
        @if($volunteerProfile?->availability)<p class="mt-3 text-sm text-amber-800">Preferensi lama: {{ $volunteerProfile->availability }}. Label ini tidak menggantikan interval tanggal dan jam.</p>@endif
    </x-section-card>
    <x-empty-state title="Aktivitas kegiatan belum tersedia" description="Katalog event, lamaran, assessment, jadwal kegiatan dan notifikasi sedang disiapkan. Ringkasan di atas berasal dari profil Anda." />
</div>
@endsection
