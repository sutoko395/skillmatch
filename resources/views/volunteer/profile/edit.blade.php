@extends('volunteer.layouts.sidebar')

@section('title', 'Profil Volunteer')

@section('content')

<div class="w-full max-w-7xl mx-auto space-y-6">

    <x-page-header
        title="Profil Volunteer"
        description="Lengkapi identitas, kota, skill dan tanggal ketersediaan Anda."
    />

    <form
        method="POST"
        action="{{ route('volunteer.profile.update') }}"
        class="space-y-6"
        data-saving-form
    >
        @csrf

        <x-section-card class="space-y-5">

            <div class="border-b border-slate-100 pb-4">
                <h2 class="text-lg font-bold text-slate-800">
                    Identitas dan Kontak
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Lengkapi informasi dasar dan kontak yang dapat digunakan.
                </p>
            </div>

            <div class="grid gap-5 md:grid-cols-2">

                <x-form-field
                    name="name"
                    label="Nama"
                    :value="$user->name"
                    required
                />

                <x-form-field
                    name="phone"
                    label="Nomor telepon"
                    :value="$volunteerProfile?->phone"
                    required
                />

            </div>

            <x-form-field
                name="city_id"
                label="Kota domisili"
                required
            >
                <select
                    id="city_id"
                    name="city_id"
                    required
                    class="mt-2 w-full rounded-xl border-slate-200 text-sm transition focus:border-orange-500 focus:ring-orange-500"
                >
                    <option value="">
                        Pilih kota aktif
                    </option>

                    @foreach($cities as $city)
                        <option
                            value="{{ $city->id }}"
                            @selected(old('city_id', $volunteerProfile?->city_id) == $city->id)
                        >
                            {{ $city->name }}
                        </option>
                    @endforeach
                </select>

                @if($volunteerProfile?->city && !$volunteerProfile?->city_id)
                    <p class="mt-2 text-sm text-amber-800">
                        Kota lama: {{ $volunteerProfile->city }}.
                        Pilih kota dari daftar agar profil dapat digunakan.
                    </p>
                @endif
            </x-form-field>

            <div class="grid gap-5 md:grid-cols-2">

                <x-form-field
                    name="birth_date"
                    label="Tanggal lahir"
                    type="date"
                    :value="$volunteerProfile?->birth_date?->format('Y-m-d')"
                    required
                />

                <x-form-field
                    name="gender"
                    label="Jenis kelamin"
                    required
                >
                    <select
                        id="gender"
                        name="gender"
                        required
                        class="mt-2 w-full rounded-xl border-slate-200 text-sm transition focus:border-orange-500 focus:ring-orange-500"
                    >
                        <option value="">
                            Pilih
                        </option>

                        <option
                            value="male"
                            @selected(old('gender', $volunteerProfile?->gender) === 'male')
                        >
                            Laki-laki
                        </option>

                        <option
                            value="female"
                            @selected(old('gender', $volunteerProfile?->gender) === 'female')
                        >
                            Perempuan
                        </option>
                    </select>
                </x-form-field>

            </div>

            <x-form-field
                name="address"
                label="Alamat"
                :value="$volunteerProfile?->address"
                required
            />

            <x-form-field
                name="bio"
                label="Bio singkat"
                helper="Maksimal 100 kata / 700 karakter."
                required
            >
                <textarea
                    id="bio"
                    name="bio"
                    required
                    maxlength="700"
                    rows="4"
                    class="mt-2 w-full rounded-xl border-slate-200 text-sm transition focus:border-orange-500 focus:ring-orange-500"
                >{{ old('bio', $volunteerProfile?->bio) }}</textarea>
            </x-form-field>

        </x-section-card>

        <x-section-card>

            <div class="border-b border-slate-100 pb-4">
                <h2 class="text-lg font-bold text-slate-800">
                    Skill dan Level
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Tambahkan skill yang Anda kuasai beserta level kemampuan.
                </p>
            </div>

            <div
                data-repeater="skills"
                class="space-y-4"
            >

                <div data-rows class="space-y-4">

                    @foreach(
                        old(
                            'skills',
                            collect($userSkills)
                                ->map(fn ($level, $id) => [
                                    'skill_id' => $id,
                                    'level' => $level,
                                ])
                                ->values()
                                ->all()
                            ?: [
                                [
                                    'skill_id' => '',
                                    'level' => 'beginner',
                                ],
                            ]
                        ) as $index => $row
                    )

                        @include('volunteer.profile.skill-row')

                    @endforeach

                </div>

                <template data-template>
                    @include('volunteer.profile.skill-row', [
                        'index' => '__INDEX__',
                        'row' => [
                            'skill_id' => '',
                            'level' => 'beginner',
                        ],
                    ])
                </template>

                <div>
                    <x-secondary-button data-add>
                        Tambah skill
                    </x-secondary-button>
                </div>

            </div>

        </x-section-card>

        <x-section-card>

            <div class="border-b border-slate-100 pb-4">
                <h2 class="text-lg font-bold text-slate-800">
                    Ketersediaan
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Atur tanggal dan jam ketika Anda tersedia untuk kegiatan volunteer.
                </p>
            </div>

            <div class="rounded-xl border border-orange-100 bg-orange-50 px-4 py-3 text-sm text-orange-800">
                Masukkan tanggal dan jam mulai serta akhir.
                Interval yang tumpang tindih tetap disimpan dan durasinya akan digabungkan saat perhitungan.
            </div>

            @if($volunteerProfile?->availability)
                <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    Preferensi lama:
                    <span class="font-semibold">
                        {{ $volunteerProfile->availability }}
                    </span>.
                    Lengkapi tanggal dan jam ketersediaan karena label lama tidak digunakan sebagai interval.
                </div>
            @endif

            <div
                data-repeater="availability"
                class="space-y-4"
            >

                <div data-rows class="space-y-4">

                    @foreach(
                        old(
                            'availability_slots',
                            $slots->map(
                                fn ($slot) => [
                                    'starts_at' => $slot->starts_at
                                        ->setTimezone('Asia/Jakarta')
                                        ->format('Y-m-d\TH:i'),

                                    'ends_at' => $slot->ends_at
                                        ->setTimezone('Asia/Jakarta')
                                        ->format('Y-m-d\TH:i'),
                                ]
                            )->all()
                            ?: [
                                [
                                    'starts_at' => '',
                                    'ends_at' => '',
                                ],
                            ]
                        ) as $index => $row
                    )

                        @include('volunteer.profile.slot-row')

                    @endforeach

                </div>

                <template data-template>
                    @include('volunteer.profile.slot-row', [
                        'index' => '__INDEX__',
                        'row' => [
                            'starts_at' => '',
                            'ends_at' => '',
                        ],
                    ])
                </template>

                <div>
                    <x-secondary-button data-add>
                        Tambah interval
                    </x-secondary-button>
                </div>

            </div>

        </x-section-card>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">

            <x-button type="submit">
                Simpan Profil
            </x-button>

            <span
                data-saving-status
                role="status"
                class="text-sm text-slate-500"
            ></span>

        </div>

    </form>

</div>

@endsection