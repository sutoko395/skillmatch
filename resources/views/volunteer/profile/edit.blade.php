@extends('layouts.user')
@section('title', 'Profil Volunteer')
@section('content')
<div class="mx-auto max-w-3xl">
<x-page-header title="Profil Volunteer" description="Lengkapi identitas, kota, skill dan tanggal ketersediaan Anda." />
<form method="POST" action="{{ route('volunteer.profile.update') }}" class="space-y-6" data-saving-form>
@csrf
<x-section-card class="space-y-5">
    <h2 class="text-lg font-semibold">Identitas dan kontak</h2>
    <x-form-field name="name" label="Nama" :value="$user->name" required />
    <x-form-field name="phone" label="Nomor telepon" :value="$volunteerProfile?->phone" required />
    <x-form-field name="city_id" label="Kota domisili" required>
        <select id="city_id" name="city_id" required class="mt-2 w-full rounded-xl border-slate-300">
            <option value="">Pilih kota aktif</option>
            @foreach($cities as $city)<option value="{{ $city->id }}" @selected(old('city_id', $volunteerProfile?->city_id) == $city->id)>{{ $city->name }}</option>@endforeach
        </select>
        @if($volunteerProfile?->city && !$volunteerProfile?->city_id)<p class="mt-2 text-sm text-amber-800">Kota lama: {{ $volunteerProfile->city }}. Pilih kota dari daftar agar profil dapat digunakan.</p>@endif
    </x-form-field>
    <div class="grid gap-5 sm:grid-cols-2">
        <x-form-field name="birth_date" label="Tanggal lahir" type="date" :value="$volunteerProfile?->birth_date?->format('Y-m-d')" required />
        <x-form-field name="gender" label="Jenis kelamin" required>
            <select id="gender" name="gender" class="mt-2 w-full rounded-xl border-slate-300" required>
                <option value="">Pilih</option>
                <option value="male" @selected(old('gender', $volunteerProfile?->gender) === 'male')>Laki-laki</option>
                <option value="female" @selected(old('gender', $volunteerProfile?->gender) === 'female')>Perempuan</option>
            </select>
        </x-form-field>
    </div>
    <x-form-field name="address" label="Alamat" :value="$volunteerProfile?->address" required />
    <x-form-field name="bio" label="Bio singkat" helper="Maksimal 100 kata / 700 karakter." required>
        <textarea id="bio" name="bio" required maxlength="700" rows="4" class="mt-2 w-full rounded-xl border-slate-300">{{ old('bio', $volunteerProfile?->bio) }}</textarea>
    </x-form-field>
</x-section-card>
<x-section-card>
    <h2 class="mb-4 text-lg font-semibold">Skill dan level</h2>
    <div data-repeater="skills" class="space-y-4">
        <div data-rows class="space-y-4">
        @foreach(old('skills', collect($userSkills)->map(fn ($level, $id) => ['skill_id' => $id, 'level' => $level])->values()->all() ?: [['skill_id' => '', 'level' => 'beginner']]) as $index => $row)
            @include('volunteer.profile.skill-row')
        @endforeach
        </div>
        <template data-template>@include('volunteer.profile.skill-row', ['index' => '__INDEX__', 'row' => ['skill_id' => '', 'level' => 'beginner']])</template>
        <x-secondary-button data-add>Tambah skill</x-secondary-button>
    </div>
</x-section-card>
<x-section-card>
    <h2 class="text-lg font-semibold">Ketersediaan (WIB)</h2>
    <p class="mb-4 mt-2 text-sm text-slate-600">Masukkan tanggal dan jam mulai/akhir. Interval yang tumpang tindih tetap disimpan; perhitungan durasi akan menggabungkannya.</p>
    @if($volunteerProfile?->availability)<p class="mb-4 text-sm text-amber-800">Preferensi lama: {{ $volunteerProfile->availability }}. Lengkapi tanggal dan jam ketersediaan; label lama tidak dipakai sebagai interval.</p>@endif
    <div data-repeater="availability" class="space-y-4">
        <div data-rows class="space-y-4">
        @foreach(old('availability_slots', $slots->map(fn ($slot) => ['starts_at' => $slot->starts_at->setTimezone('Asia/Jakarta')->format('Y-m-d\TH:i'), 'ends_at' => $slot->ends_at->setTimezone('Asia/Jakarta')->format('Y-m-d\TH:i')])->all() ?: [['starts_at' => '', 'ends_at' => '']]) as $index => $row)
            @include('volunteer.profile.slot-row')
        @endforeach
        </div>
        <template data-template>@include('volunteer.profile.slot-row', ['index' => '__INDEX__', 'row' => ['starts_at' => '', 'ends_at' => '']])</template>
        <x-secondary-button data-add>Tambah interval</x-secondary-button>
    </div>
</x-section-card>
<x-button type="submit">Simpan profil</x-button><span data-saving-status role="status" class="ml-3 text-sm"></span>
</form>
</div>
@endsection
