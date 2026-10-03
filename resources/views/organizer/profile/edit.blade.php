<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lengkapi Profil Organizer - SkillMatch Volunteer</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-50">

    <div class="min-h-screen py-10 px-4">
        <div class="w-full max-w-5xl mx-auto">

            <div class="mb-8">
                <p class="text-sm font-semibold text-indigo-600">
                    SkillMatch Volunteer
                </p>

                <h1 class="mt-1 text-3xl font-bold text-slate-800">
                    Lengkapi Profil Organizer
                </h1>

                <p class="mt-2 text-sm leading-6 text-slate-500">
                    Lengkapi data organizer dan dokumen yang diperlukan
                    untuk proses verifikasi oleh Admin.
                </p>
            </div>

            @if ($errors->any())
                <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-5">
                    <div class="flex gap-3">
                        <div class="flex-shrink-0 text-red-500">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="2"
                                stroke="currentColor"
                                class="w-5 h-5">
                                <path stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M12 9v3.75m0 3.75h.007v.008H12V16.5z" />
                                <path stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M10.29 3.86l-8.82 15a1.875 1.875 0 001.62 2.815h17.82a1.875 1.875 0 001.62-2.815l-8.82-15a1.875 1.875 0 00-3.24 0z" />
                            </svg>
                        </div>

                        <div>
                            <p class="font-semibold text-red-700">
                                Periksa kembali data Anda
                            </p>

                            <ul class="mt-2 list-disc list-inside space-y-1 text-sm text-red-600">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            @if (session('success'))
                <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
                    <p class="text-sm font-medium text-emerald-700">
                        {{ session('success') }}
                    </p>
                </div>
            @endif

            <form method="POST"
                action="{{ route('organizer.profile.update') }}"
                enctype="multipart/form-data">

                @csrf

                <div class="space-y-6">

                    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">

                        <div class="px-6 py-5 md:px-8 border-b border-slate-100">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke-width="1.8"
                                        stroke="currentColor"
                                        class="w-5 h-5">
                                        <path stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M3.75 21h16.5M4.5 21V5.25A2.25 2.25 0 016.75 3h10.5a2.25 2.25 0 012.25 2.25V21M8.25 7.5h1.5m-1.5 3h1.5m4.5-3h1.5m-1.5 3h1.5M8.25 21v-3.75A2.25 2.25 0 0110.5 15h3a2.25 2.25 0 012.25 2.25V21" />
                                    </svg>
                                </div>

                                <div>
                                    <h2 class="text-lg font-bold text-slate-800">
                                        Informasi Organizer
                                    </h2>

                                    <p class="text-sm text-slate-500">
                                        Informasi utama organisasi atau penyelenggara.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="p-6 md:p-8">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                                <div>
                                    <label for="organization_name"
                                        class="block text-sm font-semibold text-slate-700 mb-2">
                                        Nama Organizer
                                    </label>

                                    <input
                                        type="text"
                                        id="organization_name"
                                        name="organization_name"
                                        value="{{ old('organization_name', $profile?->organization_name) }}"
                                        placeholder="Contoh: Komunitas Pemuda Malang"
                                        required
                                        class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-700 placeholder-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none transition">
                                </div>

                                <div>
                                    <label for="contact_person"
                                        class="block text-sm font-semibold text-slate-700 mb-2">
                                        Nama Penanggung Jawab
                                    </label>

                                    <input
                                        type="text"
                                        id="contact_person"
                                        name="contact_person"
                                        value="{{ old('contact_person', $profile?->contact_person) }}"
                                        placeholder="Nama lengkap penanggung jawab"
                                        required
                                        class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-700 placeholder-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none transition">
                                </div>

                                <div>
                                    <label for="phone"
                                        class="block text-sm font-semibold text-slate-700 mb-2">
                                        Nomor Telepon
                                    </label>

                                    <input
                                        type="text"
                                        id="phone"
                                        name="phone"
                                        value="{{ old('phone', $profile?->phone) }}"
                                        placeholder="08xxxxxxxxxx"
                                        required
                                        class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-700 placeholder-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none transition">
                                </div>

                                <div>
                                    <label for="email"
                                        class="block text-sm font-semibold text-slate-700 mb-2">
                                        Email Organizer
                                    </label>

                                    <input
                                        type="email"
                                        id="email"
                                        name="email"
                                        value="{{ old('email', $profile?->email ?? $user->email) }}"
                                        placeholder="organizer@email.com"
                                        required
                                        class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-700 placeholder-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none transition">
                                </div>

                                <div>
                                    <label for="city"
                                        class="block text-sm font-semibold text-slate-700 mb-2">
                                        Kota / Kabupaten
                                    </label>

                                    <input
                                        type="text"
                                        id="city"
                                        name="city"
                                        value="{{ old('city', $profile?->city) }}"
                                        placeholder="Contoh: Malang"
                                        required
                                        class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-700 placeholder-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none transition">
                                </div>

                                <div>
                                    <label for="website"
                                        class="block text-sm font-semibold text-slate-700 mb-2">
                                        Website
                                        <span class="font-normal text-slate-400">(Opsional)</span>
                                    </label>

                                    <input
                                        type="text"
                                        id="website"
                                        name="website"
                                        value="{{ old('website', $profile?->website) }}"
                                        placeholder="https://contoh.com"
                                        class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-700 placeholder-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none transition">
                                </div>

                                <div class="md:col-span-2">
                                    <label for="address"
                                        class="block text-sm font-semibold text-slate-700 mb-2">
                                        Alamat Lengkap
                                    </label>

                                    <textarea
                                        id="address"
                                        name="address"
                                        rows="3"
                                        required
                                        placeholder="Masukkan alamat lengkap organizer"
                                        class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-700 placeholder-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none transition resize-none">{{ old('address', $profile?->address) }}</textarea>
                                </div>

                                <div class="md:col-span-2">
                                    <label for="description"
                                        class="block text-sm font-semibold text-slate-700 mb-2">
                                        Deskripsi Organizer
                                    </label>

                                    <textarea
                                        id="description"
                                        name="description"
                                        rows="4"
                                        required
                                        placeholder="Jelaskan secara singkat tentang organisasi, komunitas, atau lembaga Anda"
                                        class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-700 placeholder-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none transition resize-none">{{ old('description', $profile?->description) }}</textarea>

                                    <p class="mt-2 text-xs text-slate-400">
                                        Maksimal 1000 karakter.
                                    </p>
                                </div>

                            </div>
                        </div>

                    </div>

                    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">

                        <div class="px-6 py-5 md:px-8 border-b border-slate-100">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke-width="1.8"
                                        stroke="currentColor"
                                        class="w-5 h-5">
                                        <path stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5V6.375a3.375 3.375 0 00-3.375-3.375h-1.5a3.375 3.375 0 00-3.375 3.375V8.25h-1.5A3.375 3.375 0 001.5 11.625v2.625m18 0v4.125a3.375 3.375 0 01-3.375 3.375H5.625a3.375 3.375 0 01-3.375-3.375v-4.125m17.25 0h-3.75m-9.75 0H1.5m6.75 0h7.5" />
                                    </svg>
                                </div>

                                <div>
                                    <h2 class="text-lg font-bold text-slate-800">
                                        Dokumen Organizer
                                    </h2>

                                    <p class="text-sm text-slate-500">
                                        Upload dokumen untuk membantu proses verifikasi Admin.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="p-6 md:p-8">

                            <div class="rounded-2xl bg-indigo-50 border border-indigo-100 p-4 mb-6">
                                <p class="text-sm leading-6 text-indigo-700">
                                    Minimal satu dokumen wajib dilampirkan.
                                    Dokumen dapat berupa PDF, JPG, JPEG, atau PNG dengan ukuran maksimal 5 MB.
                                </p>
                            </div>

                            <div class="border border-slate-200 rounded-2xl p-5">

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                                    <div>
                                        <label for="document_type"
                                            class="block text-sm font-semibold text-slate-700 mb-2">
                                            Jenis Dokumen
                                        </label>

                                        <select
                                            id="document_type"
                                            name="documents[0][type]"
                                            required
                                            class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-700 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none transition">

                                            <option value="">
                                                Pilih jenis dokumen
                                            </option>

                                            <option value="Identitas Penanggung Jawab"
                                                @selected(old('documents.0.type') === 'Identitas Penanggung Jawab')>
                                                Identitas Penanggung Jawab
                                            </option>

                                            <option value="Legalitas Organisasi"
                                                @selected(old('documents.0.type') === 'Legalitas Organisasi')>
                                                Legalitas Organisasi
                                            </option>

                                            <option value="Dokumen Pendukung"
                                                @selected(old('documents.0.type') === 'Dokumen Pendukung')>
                                                Dokumen Pendukung
                                            </option>
                                        </select>
                                    </div>

                                    <div>
                                        <label for="document_name"
                                            class="block text-sm font-semibold text-slate-700 mb-2">
                                            Nama Dokumen
                                        </label>

                                        <input
                                            type="text"
                                            id="document_name"
                                            name="documents[0][name]"
                                            value="{{ old('documents.0.name') }}"
                                            placeholder="Contoh: KTP Penanggung Jawab"
                                            required
                                            class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-700 placeholder-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none transition">
                                    </div>

                                    <div class="md:col-span-2">
                                        <label for="document_file"
                                            class="block text-sm font-semibold text-slate-700 mb-2">
                                            File Dokumen
                                        </label>

                                        <input
                                            type="file"
                                            id="document_file"
                                            name="documents[0][file]"
                                            accept=".pdf,.jpg,.jpeg,.png"
                                            required
                                            class="block w-full rounded-xl border border-slate-200 bg-white text-sm text-slate-600 file:mr-4 file:border-0 file:bg-indigo-50 file:px-4 file:py-3 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100">

                                        <p class="mt-2 text-xs text-slate-400">
                                            PDF, JPG, JPEG, PNG · Maksimal 5 MB
                                        </p>
                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                    <div class="bg-white rounded-3xl border border-amber-200 shadow-sm p-6 md:p-8">

                        <div class="flex gap-4">

                            <div class="w-10 h-10 flex-shrink-0 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="1.8"
                                    stroke="currentColor"
                                    class="w-5 h-5">
                                    <path stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M12 6v6l4 2m6-2a10 10 0 11-20 0 10 10 0 0120 0z" />
                                </svg>
                            </div>

                            <div>
                                <h3 class="font-bold text-slate-800">
                                    Proses Verifikasi
                                </h3>

                                <p class="mt-1 text-sm leading-6 text-slate-500">
                                    Setelah data dan dokumen dikirim, Admin akan melakukan
                                    pemeriksaan. Proses verifikasi maksimal
                                    <span class="font-semibold text-slate-700">
                                        2 hari kerja
                                    </span>.
                                </p>

                                <p class="mt-2 text-sm leading-6 text-slate-500">
                                    Jika data dinyatakan valid, status akun akan berubah menjadi
                                    <span class="font-semibold text-emerald-600">
                                        Active
                                    </span>
                                    dan Anda dapat mengakses Dashboard Organizer.
                                </p>
                            </div>

                        </div>

                    </div>

                    <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3 pb-6">

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <button type="submit"
                                class="w-full sm:w-auto px-6 py-3 rounded-xl border border-slate-200 bg-white text-slate-600 text-sm font-semibold hover:bg-slate-50 transition">
                                Keluar
                            </button>
                        </form>

                        <button type="submit"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-7 py-3 rounded-xl bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 shadow-sm hover:shadow-md transition">

                            <svg xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="2"
                                stroke="currentColor"
                                class="w-4 h-4">
                                <path stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M12 16.5V7.5m0 0l-3.75 3.75M12 7.5l3.75 3.75M5.25 19.5h13.5" />
                            </svg>

                            Kirim untuk Verifikasi
                        </button>

                    </div>

                </div>

            </form>

        </div>
    </div>

</body>
</html>