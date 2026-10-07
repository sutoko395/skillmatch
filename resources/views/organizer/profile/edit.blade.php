@extends('organizer.layouts.sidebar')

@section('title', 'Profil Organisasi')

@section('content')
<div class="w-full max-w-7xl mx-auto space-y-6">

    <div>
        <h1 class="text-2xl font-bold text-slate-800">
            Profil Organisasi
        </h1>

        <p class="mt-1 text-sm text-slate-500">
            Lengkapi informasi organisasi untuk proses verifikasi.
        </p>
    </div>

    <div class="rounded-xl border border-slate-100 bg-white px-5 py-4 shadow-sm">
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-sm font-semibold text-slate-700">
                Status organisasi:
            </span>

            <x-status-badge :status="$user->organizer_status">
                {{ [
                    'pending' => 'Menunggu verifikasi',
                    'active' => 'Terverifikasi',
                    'inactive' => 'Belum terverifikasi / perlu revisi',
                ][$user->organizer_status] }}
            </x-status-badge>
        </div>
    </div>

    <form
        method="POST"
        action="{{ route('organizer.profile.update') }}"
        enctype="multipart/form-data"
        class="space-y-6"
        data-saving-form
    >
        @csrf

        <x-section-card class="p-6 md:p-7">
            <div class="space-y-6">

                <div>
                    <x-form-field
                        name="organization_name"
                        label="Nama organisasi"
                        :value="$profile?->organization_name"
                        required
                    />
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    <x-form-field
                        name="contact_person"
                        label="Penanggung jawab"
                        :value="$profile?->contact_person"
                        required
                    />

                    <x-form-field
                        name="phone"
                        label="Nomor telepon"
                        :value="$profile?->phone"
                        required
                    />
                </div>

                <div>
                    <x-form-field
                        name="email"
                        label="Email"
                        type="email"
                        :value="$user->email"
                        readonly
                        required
                    />

                    <p class="mt-2 text-xs text-slate-500">
                        Email mengikuti akun yang digunakan saat pendaftaran.
                    </p>
                </div>

                <x-form-field
                    name="address"
                    label="Alamat lengkap"
                    :value="$profile?->address"
                    required
                />

                <div class="grid gap-6 md:grid-cols-2">
                    <x-form-field
                        name="city"
                        label="Kota"
                        :value="$profile?->city"
                        required
                    />

                    <x-form-field
                        name="website"
                        label="Website"
                        type="url"
                        :value="$profile?->website"
                    />
                </div>

                <x-form-field
                    name="description"
                    label="Deskripsi organisasi"
                    required
                >
                    <textarea
                        id="description"
                        name="description"
                        rows="5"
                        required
                        maxlength="1000"
                        class="mt-2 w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >{{ old('description', $profile?->description) }}</textarea>
                </x-form-field>

                <div>
                    <label
                        for="ktp"
                        class="block text-sm font-semibold text-slate-700"
                    >
                        KTP / Dokumen Identitas
                    </label>

                    <input
                        id="ktp"
                        name="ktp"
                        type="file"
                        accept=".jpg,.jpeg,.png,.pdf"
                        class="mt-2 block w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"
                    >

                    <p class="mt-2 text-xs text-slate-500">
                        Format JPG, JPEG, PNG, atau PDF. Maksimal 5 MB.
                    </p>

                    @error('ktp')
                        <p class="mt-2 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

            </div>
        </x-section-card>

        <div class="flex flex-wrap items-center gap-3">
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