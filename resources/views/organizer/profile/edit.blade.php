@extends('organizer.layouts.sidebar')

@section('title', 'Profil Organisasi')

@section('content')
<div
    class="w-full max-w-7xl mx-auto space-y-6"
    x-data="{ editProfile: false, viewKtp: false }"
>

    @if (!$profile)

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
                    Menunggu pengajuan
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

                    <x-form-field
                        name="organization_name"
                        label="Nama organisasi"
                        :value="old('organization_name')"
                        required
                    />

                    <div class="grid gap-6 md:grid-cols-2">
                        <x-form-field
                            name="contact_person"
                            label="Penanggung jawab"
                            :value="old('contact_person')"
                            required
                        />

                        <x-form-field
                            name="phone"
                            label="Nomor telepon"
                            :value="old('phone')"
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
                        :value="old('address')"
                        required
                    />

                    <div class="grid gap-6 md:grid-cols-2">
                        <x-form-field
                            name="city"
                            label="Kota"
                            :value="old('city')"
                            required
                        />

                        <x-form-field
                            name="website"
                            label="Website"
                            type="url"
                            :value="old('website')"
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
                        >{{ old('description') }}</textarea>
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
                            required
                            class="mt-2 block w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"
                        >

                        <p class="mt-2 text-xs text-slate-500">
                            KTP wajib diupload untuk proses verifikasi.
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

    @else

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">
                    Profil Organisasi
                </h1>

                <p class="mt-1 text-sm text-slate-500">
                    Informasi organisasi dan data penanggung jawab.
                </p>
            </div>

            <button
                type="button"
                @click="editProfile = true"
                class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700"
            >
                Edit Profil
            </button>
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
                        'inactive' => 'Akun Anda Dinonaktifkan',
                    ][$user->organizer_status] }}
                </x-status-badge>
            </div>
        </div>

        <x-section-card class="p-6 md:p-7">
            <div class="space-y-6">

                <div>
                    <p class="text-sm text-slate-500">
                        Nama organisasi
                    </p>

                    <p class="mt-1 break-words text-xl font-bold text-slate-800">
                        {{ $profile->organization_name }}
                    </p>
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <p class="text-sm text-slate-500">
                            Penanggung jawab
                        </p>

                        <p class="mt-1 break-words font-semibold text-slate-800">
                            {{ $profile->contact_person }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-slate-500">
                            Nomor telepon
                        </p>

                        <p class="mt-1 break-words font-semibold text-slate-800">
                            {{ $profile->phone }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-slate-500">
                            Email
                        </p>

                        <p class="mt-1 break-words font-semibold text-slate-800">
                            {{ $user->email }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-slate-500">
                            Kota
                        </p>

                        <p class="mt-1 break-words font-semibold text-slate-800">
                            {{ $profile->city }}
                        </p>
                    </div>
                </div>

                <div>
                    <p class="text-sm text-slate-500">
                        Alamat
                    </p>

                    <p class="mt-1 whitespace-pre-line break-words font-semibold text-slate-800">
                        {{ $profile->address }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-slate-500">
                        Website
                    </p>

                    @if ($profile->website)
                        <a
                            href="{{ $profile->website }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="mt-1 inline-block break-all font-semibold text-indigo-700 hover:underline"
                        >
                            {{ $profile->website }}
                        </a>
                    @else
                        <p class="mt-1 font-semibold text-slate-800">
                            Belum diisi
                        </p>
                    @endif
                </div>

                <div>
                    <p class="text-sm text-slate-500">
                        Deskripsi organisasi
                    </p>

                    <p class="mt-1 whitespace-pre-line break-words text-sm leading-6 text-slate-700">
                        {{ $profile->description }}
                    </p>
                </div>

                <div class="border-t border-slate-100 pt-5">
                    <p class="text-sm text-slate-500">
                        Dokumen identitas
                    </p>

                    @forelse ($documents as $document)
                        <div class="mt-3 flex flex-wrap items-center justify-between gap-4 rounded-xl bg-slate-50 px-4 py-3">
                            <div>
                                <p class="text-sm font-semibold text-slate-800">
                                    {{ $document->document_name }}
                                </p>

                                <p class="mt-1 text-xs text-slate-500">
                                    {{ strtoupper($document->document_type) }}
                                </p>
                            </div>

                            <button
                                type="button"
                                @click="viewKtp = true"
                                class="inline-flex items-center rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700"
                            >
                                Lihat KTP
                            </button>
                        </div>
                    @empty
                        <p class="mt-1 text-sm text-slate-500">
                            Dokumen belum tersedia.
                        </p>
                    @endforelse
                </div>

            </div>
        </x-section-card>

        <template x-teleport="body">
            <div
                x-show="editProfile"
                x-cloak
                x-transition.opacity
                class="fixed inset-0 z-[99999] bg-slate-900/50 backdrop-blur-sm"
            >
                <div
                    class="flex min-h-screen items-center justify-center overflow-y-auto p-4"
                    @click.self="editProfile = false"
                >
                    <div
                        x-show="editProfile"
                        x-transition
                        class="w-full max-w-3xl overflow-hidden rounded-2xl bg-white shadow-2xl"
                        @click.stop
                    >

                        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
                            <div>
                                <h2 class="text-lg font-bold text-slate-800">
                                    Edit Profil Organisasi
                                </h2>

                                <p class="mt-1 text-sm text-slate-500">
                                    Perbarui informasi organisasi Anda.
                                </p>
                            </div>

                            <button
                                type="button"
                                @click="editProfile = false"
                                class="rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-5 w-5"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18 18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>

                        <form
                            method="POST"
                            action="{{ route('organizer.profile.update') }}"
                            enctype="multipart/form-data"
                            class="max-h-[80vh] overflow-y-auto"
                        >
                            @csrf

                            <div class="space-y-6 px-6 py-6">

                                <x-form-field
                                    name="organization_name"
                                    label="Nama organisasi"
                                    :value="$profile->organization_name"
                                    required
                                />

                                <div class="grid gap-6 md:grid-cols-2">
                                    <x-form-field
                                        name="contact_person"
                                        label="Penanggung jawab"
                                        :value="$profile->contact_person"
                                        required
                                    />

                                    <x-form-field
                                        name="phone"
                                        label="Nomor telepon"
                                        :value="$profile->phone"
                                        required
                                    />
                                </div>

                                <x-form-field
                                    name="address"
                                    label="Alamat lengkap"
                                    :value="$profile->address"
                                    required
                                />

                                <div class="grid gap-6 md:grid-cols-2">
                                    <x-form-field
                                        name="city"
                                        label="Kota"
                                        :value="$profile->city"
                                        required
                                    />

                                    <x-form-field
                                        name="website"
                                        label="Website"
                                        type="url"
                                        :value="$profile->website"
                                    />
                                </div>

                                <x-form-field
                                    name="description"
                                    label="Deskripsi organisasi"
                                    required
                                >
                                    <textarea
                                        id="edit-description"
                                        name="description"
                                        rows="5"
                                        required
                                        maxlength="1000"
                                        class="mt-2 w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >{{ $profile->description }}</textarea>
                                </x-form-field>

                                <div>
                                    <label
                                        for="ktp"
                                        class="block text-sm font-semibold text-slate-700"
                                    >
                                        Ganti KTP / Dokumen Identitas
                                    </label>

                                    <input
                                        id="ktp"
                                        name="ktp"
                                        type="file"
                                        accept=".jpg,.jpeg,.png,.pdf"
                                        class="mt-2 block w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"
                                    >

                                    <p class="mt-2 text-xs text-slate-500">
                                        Kosongkan jika tidak ingin mengganti dokumen.
                                        Format JPG, JPEG, PNG, atau PDF. Maksimal 5 MB.
                                    </p>

                                    @error('ktp')
                                        <p class="mt-2 text-sm text-red-600">
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                            </div>

                            <div class="flex justify-end gap-3 border-t border-slate-100 px-6 py-4">
                                <button
                                    type="button"
                                    @click="editProfile = false"
                                    class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                                >
                                    Batal
                                </button>

                                <x-button type="submit">
                                    Simpan Perubahan
                                </x-button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </template>
    @endif

    <template x-teleport="body">
        <div
            x-show="viewKtp"
            x-cloak
            x-transition.opacity
            class="fixed inset-0 z-[99999] bg-slate-900/70 backdrop-blur-sm"
        >
            <div
                class="flex min-h-screen items-center justify-center p-4"
                @click.self="viewKtp = false"
            >
                <div
                    x-show="viewKtp"
                    x-transition
                    class="relative flex max-h-[92vh] w-full max-w-5xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl"
                    @click.stop
                >

                    <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                        <div>
                            <h2 class="text-lg font-bold text-slate-800">
                                Dokumen KTP
                            </h2>

                            <p class="mt-1 text-sm text-slate-500">
                                Dokumen identitas Organizer
                            </p>
                        </div>

                        <button
                            type="button"
                            @click="viewKtp = false"
                            class="rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M6 18 18 6M6 6l12 12"
                                />
                            </svg>
                        </button>
                    </div>

                    @php
                        $ktpDocument = $documents->firstWhere('document_type', 'ktp');
                        $ktpExtension = $ktpDocument
                            ? strtolower(pathinfo($ktpDocument->file_path, PATHINFO_EXTENSION))
                            : null;
                    @endphp

                    <div class="flex-1 overflow-auto bg-slate-100 p-4 md:p-6">
                        @if ($ktpDocument)

                            @if (in_array($ktpExtension, ['jpg', 'jpeg', 'png']))
                                <div class="flex min-h-[60vh] items-center justify-center">
                                    <img
                                        src="{{ route('organizer.profile.documents.view', $ktpDocument) }}"
                                        alt="KTP Organizer"
                                        class="max-h-[75vh] max-w-full rounded-xl object-contain shadow-lg"
                                    >
                                </div>
                            @elseif ($ktpExtension === 'pdf')
                                <iframe
                                    src="{{ route('organizer.profile.documents.view', $ktpDocument) }}"
                                    class="h-[75vh] w-full rounded-xl border-0 bg-white"
                                ></iframe>
                            @else
                                <div class="flex min-h-[60vh] items-center justify-center">
                                    <p class="text-sm text-slate-500">
                                        Dokumen tidak dapat ditampilkan.
                                    </p>
                                </div>
                            @endif

                        @else
                            <div class="flex min-h-[60vh] items-center justify-center">
                                <p class="text-sm text-slate-500">
                                    Dokumen KTP belum tersedia.
                                </p>
                            </div>
                        @endif
                    </div>

                </div>
            </div>
        </div>
    </template>

</div>
@endsection