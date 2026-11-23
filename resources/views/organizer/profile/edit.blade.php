@extends('organizer.layouts.sidebar')

@section('title', 'Profil Organisasi')

@section('content')
<div
    class="w-full max-w-7xl mx-auto space-y-6"
    x-data="{ editProfile: {{ $errors->any() && $profile ? 'true' : 'false' }}, viewNib: false }"
>

    @if($user->organizer_verification_note)
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4"><strong>Catatan Admin:</strong> {{ $user->organizer_verification_note }}</div>
    @endif
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

                        <x-phone-field :value="old('phone')" required />
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
                        <x-form-field name="city_id" label="Kota/Kabupaten" required>
                            <select
                                id="city_id"
                                name="city_id"
                                required
                                class="mt-2 w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="">Pilih Kota/Kabupaten</option>
                                <x-city-options :cities="$cities" :selected="$profile?->city_id" />
                            </select>
                        </x-form-field>

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
                            for="nib"
                            class="block text-sm font-semibold text-slate-700"
                        >
                            Dokumen verifikasi (NIB atau KTP)
                        </label>

                        <input
                            id="nib"
                            name="nib"
                            type="file"
                            accept=".jpg,.jpeg,.png,.pdf"
                            required
                            class="mt-2 block w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"
                        >

                        <p class="mt-2 text-xs text-slate-500">
                            Unggah salah satu berkas untuk pengajuan pertama.
                            Format JPG, JPEG, PNG, atau PDF. Maksimal 5 MB.
                        </p>

                        @error('nib')
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

        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Profil Organisasi</h1>
                <p class="mt-1 text-sm text-slate-500">Informasi organisasi dan data penanggung jawab.</p>
            </div>
            <button type="button" @click="editProfile = true" class="organizer-secondary-button organizer-info-edit">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m16 3 5 5M4 20l4-1L21 6a2.8 2.8 0 0 0-4-4L4 15l-1 6Z"/></svg> Edit Profil
            </button>
        </div>

        <section class="organizer-profile-card" aria-labelledby="organization-heading">
            <div class="organizer-profile-identity">
                <span class="organizer-profile-symbol"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 21V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v16M16 9h3a1 1 0 0 1 1 1v11M8 7h4M8 11h4M8 15h4M2 21h20M9 21v-3h2v3"/></svg></span>
                <div class="min-w-0 flex-1">
                    <p class="organizer-profile-eyebrow">Nama organisasi</p>
                    <h2 id="organization-heading" class="organizer-profile-name">{{ $profile->organization_name }}</h2>
                </div>
                <div class="organizer-profile-verification">
                    <span class="sr-only">Status organisasi: </span>
                    <x-status-badge :status="$user->organizer_status">
                        {{ ['pending' => 'Menunggu verifikasi', 'active' => 'Terverifikasi', 'inactive' => 'Perlu revisi organisasi'][$user->organizer_status] }}
                    </x-status-badge>
                </div>
            </div>
            <div class="organizer-profile-inner">
                <h3>Tentang organisasi</h3>
                <p class="organizer-profile-description">{{ $profile->description }}</p>
            </div>
            <dl class="organizer-profile-details">
                <div class="organizer-profile-field"><span class="organizer-profile-field-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 10c0 5-7 11-7 11S5 15 5 10a7 7 0 1 1 14 0Z"/><circle cx="12" cy="10" r="2.5"/></svg></span><div><dt>Alamat lengkap</dt><dd>{{ $profile->address }}</dd></div></div>
                <div class="organizer-profile-field"><span class="organizer-profile-field-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 21V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v16M16 9h3a1 1 0 0 1 1 1v11M8 7h4M8 11h4M8 15h4M2 21h20M9 21v-3h2v3"/></svg></span><div><dt>Kota/Kabupaten</dt><dd>{{ $profile->cityRecord?->name ?? $profile->city }}@if($profile->cityRecord?->province), {{ $profile->cityRecord->province->name }}@endif</dd></div></div>
                <div class="organizer-profile-field"><span class="organizer-profile-field-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a18 18 0 0 1 0 18 18 18 0 0 1 0-18Z"/></svg></span><div><dt>Website</dt><dd>@if ($profile->website)
                    <a href="{{ $profile->website }}" target="_blank" rel="noopener noreferrer">{{ $profile->website }}</a>
                    @else <span class="text-slate-500">Belum diisi</span> @endif</dd></div></div>
            </dl>
        </section>

        <section class="organizer-profile-card" aria-labelledby="organization-contact-heading">
            <h2 id="organization-contact-heading" class="organizer-profile-section-title">Kontak penanggung jawab</h2>
            <dl class="organizer-profile-details organizer-profile-contacts">
                <div class="organizer-profile-field"><span class="organizer-profile-field-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21v-2a8 8 0 0 1 16 0v2"/></svg></span><div><dt>Penanggung jawab</dt><dd>{{ $profile->contact_person }}</dd></div></div>
                <div class="organizer-profile-field"><span class="organizer-profile-field-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6 19.8 19.8 0 0 1-3.1-8.7A2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 2 .7 2.9a2 2 0 0 1-.4 2.1L8.1 10a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.9.6 2.9.7a2 2 0 0 1 1.6 1.9Z"/></svg></span><div><dt>Nomor telepon</dt><dd>{{ $profile->phone }}</dd></div></div>
                <div class="organizer-profile-field"><span class="organizer-profile-field-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 6 9 7 9-7"/></svg></span><div><dt>Email akun</dt><dd>{{ $user->email }}</dd></div></div>
            </dl>
        </section>

        <section class="organizer-profile-card" aria-labelledby="organization-documents-heading">
            <h2 id="organization-documents-heading" class="organizer-profile-section-title">Dokumen verifikasi</h2>
            <p class="organizer-profile-section-note">Dokumen identitas untuk verifikasi organisasi.</p>
            @forelse ($documents as $document)
                <div class="organizer-profile-document">
                    <span class="organizer-profile-field-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z M14 2v6h6M8 13h8M8 17h5"/></svg></span>
                    <div class="min-w-0 flex-1">
                        <p class="organizer-profile-document-name">{{ $document->document_name }}</p>
                        <p class="organizer-profile-document-type">{{ $document->document_type === 'ktp' ? 'Dokumen verifikasi' : strtoupper($document->document_type) }}</p>
                    </div>
                    <button type="button" @click="viewNib = true" class="organizer-secondary-button organizer-info-edit">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg> Lihat dokumen
                    </button>
                </div>
            @empty
                <div class="organizer-profile-document">
                    <span class="organizer-profile-field-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z M14 2v6h6M8 13h8M8 17h5"/></svg></span>
                    <p class="text-sm text-slate-500">Dokumen belum tersedia.</p>
                </div>
            @endforelse
        </section>

        <template x-teleport="body">
            <div
                x-show="editProfile"
                x-cloak
                x-transition.opacity
                class="fixed inset-0 z-[99999] bg-slate-900/50 backdrop-blur-sm"
                @keydown.escape.window="editProfile = false"
            >
                <div
                    class="flex h-dvh items-center justify-center overflow-y-auto p-4"
                    @click.self="editProfile = false"
                >
                    <div
                        x-show="editProfile"
                        x-transition
                        class="organizer-panel organizer-profile-dialog w-full max-w-3xl overflow-hidden rounded-2xl bg-white shadow-2xl"
                        role="dialog" aria-modal="true" aria-label="Edit Profil Organisasi"
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
                            class="max-h-[70dvh] overflow-y-auto"
                        >
                            @csrf
                            @if($user->organizer_status === 'active')
                                <label class="m-6 flex gap-3 rounded-xl bg-amber-50 p-4 text-sm text-amber-900">
                                    <input type="checkbox" name="confirm_reverification" value="1" @checked(old('confirm_reverification'))>
                                    Saya memahami perubahan nama organisasi, alamat, kota atau dokumen verifikasi akan meminta persetujuan ulang Admin. Kegiatan sementara tidak tampil publik dan pengelolaannya tertunda sampai disetujui kembali. Perubahan kontak biasa tetap dapat disimpan tanpa persetujuan ulang.
                                </label>
                                @error('confirm_reverification')<p class="px-6 text-sm text-red-600">{{ $message }}</p>@enderror
                            @endif

                            <div class="space-y-6 px-6 py-6">

                                <x-form-field
                                    name="organization_name"
                                    label="Nama organisasi"
                                    :value="old('organization_name', $profile->organization_name)"
                                    required
                                />

                                <div class="grid gap-6 md:grid-cols-2">
                                    <x-form-field
                                        name="contact_person"
                                        label="Penanggung jawab"
                                        :value="old('contact_person', $profile->contact_person)"
                                        required
                                    />

                                    <x-phone-field :value="old('phone', $profile->phone)" required />
                                </div>

                                <x-form-field
                                    name="address"
                                    label="Alamat lengkap"
                                    :value="old('address', $profile->address)"
                                    required
                                />

                                <div class="grid gap-6 md:grid-cols-2">
                                    <x-form-field name="city_id" label="Kota/Kabupaten" required>
                                        <select
                                            id="city_id"
                                            name="city_id"
                                            required
                                            class="mt-2 w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        >
                                            <option value="">Pilih Kota/Kabupaten</option>
                                            <x-city-options :cities="$cities" :selected="$profile->city_id" />
                                        </select>
                                        @if($profile->city && !$profile->city_id)
                                            <p class="mt-2 text-sm text-amber-800">
                                                Kota lama: {{ $profile->city }}. Pilih kota/kabupaten dari daftar agar profil dapat diverifikasi.
                                            </p>
                                        @endif
                                    </x-form-field>

                                    <x-form-field
                                        name="website"
                                        label="Website"
                                        type="url"
                                        :value="old('website', $profile->website)"
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
                                        for="nib"
                                        class="block text-sm font-semibold text-slate-700"
                                    >
                                        Ganti dokumen verifikasi (NIB atau KTP)
                                    </label>

                                    <input
                                        id="nib"
                                        name="nib"
                                        type="file"
                                        accept=".jpg,.jpeg,.png,.pdf"
                                        class="mt-2 block w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"
                                    >

                                    <p class="mt-2 text-xs text-slate-500">
                                        Kosongkan jika tidak ingin mengganti dokumen.
                                        Format JPG, JPEG, PNG, atau PDF. Maksimal 5 MB.
                                    </p>

                                    @error('nib')
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
                                    class="organizer-secondary-button organizer-position-delete"
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
                x-show="viewNib"
                x-cloak
                x-transition.opacity
                class="fixed inset-0 z-[99999] bg-slate-900/70 backdrop-blur-sm"
                @keydown.escape.window="viewNib = false"
            >
            <div
                class="flex min-h-screen items-center justify-center p-4"
                @click.self="viewNib = false"
            >
                <div
                    x-show="viewNib"
                    x-transition
                    class="relative flex max-h-[92vh] w-full max-w-5xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl"
                    @click.stop
                >

                    <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                        <div>
                            <h2 class="text-lg font-bold text-slate-800">
                                Dokumen verifikasi
                            </h2>

                            <p class="mt-1 text-sm text-slate-500">
                                Dokumen identitas Organizer
                            </p>
                        </div>

                        <button
                            type="button"
                            @click="viewNib = false"
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
                        $verificationDocument = $documents->firstWhere('document_type', 'ktp');

                        $documentExtension = $verificationDocument
                            ? strtolower(pathinfo($verificationDocument->file_path, PATHINFO_EXTENSION))
                            : null;
                    @endphp

                    <div class="flex-1 overflow-auto bg-slate-100 p-4 md:p-6">
                        @if ($verificationDocument)
                            @if (in_array($documentExtension, ['jpg', 'jpeg', 'png']))
                                <div class="flex min-h-[60vh] items-center justify-center">
                                    <img
                                        src="{{ route('organizer.profile.documents.view', $verificationDocument) }}"
                                        alt="Dokumen verifikasi Organizer"
                                        class="max-h-[75vh] max-w-full rounded-xl object-contain shadow-lg"
                                    >
                                </div>
                            @elseif ($documentExtension === 'pdf')
                                <iframe
                                    src="{{ route('organizer.profile.documents.view', $verificationDocument) }}"
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
                                    Dokumen verifikasi belum tersedia.
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
