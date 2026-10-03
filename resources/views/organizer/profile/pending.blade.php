<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pengajuan Berhasil - SkillMatch Volunteer</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-50">

    <div class="min-h-screen flex items-center justify-center px-4 py-10">

        <div class="w-full max-w-2xl">

            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">

                <div class="p-8 md:p-10 text-center">

                    <div class="mx-auto w-20 h-20 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center">

                        <svg xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.8"
                            stroke="currentColor"
                            class="w-10 h-10">

                            <path stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M9 12.75L11.25 15 15 9.75" />

                            <path stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />

                        </svg>

                    </div>

                    <p class="mt-6 text-sm font-semibold text-indigo-600">
                        SkillMatch Volunteer
                    </p>

                    <h1 class="mt-2 text-2xl md:text-3xl font-bold text-slate-800">
                        Pengajuan Berhasil Dikirim
                    </h1>

                    <p class="mt-4 text-sm md:text-base leading-7 text-slate-500">
                        Terima kasih. Data profil dan dokumen Organizer Anda
                        telah berhasil kami terima dan sedang dalam proses
                        verifikasi oleh Admin.
                    </p>

                    <div class="mt-8 rounded-2xl border border-amber-200 bg-amber-50 p-6 text-left">

                        <div class="flex gap-4">

                            <div class="w-11 h-11 flex-shrink-0 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center">

                                <svg xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="1.8"
                                    stroke="currentColor"
                                    class="w-6 h-6">

                                    <path stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M12 6v6l4 2" />

                                    <path stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />

                                </svg>

                            </div>

                            <div>

                                <h2 class="font-bold text-amber-800">
                                    Menunggu Proses Verifikasi
                                </h2>

                                <p class="mt-2 text-sm leading-6 text-amber-700">
                                    Admin akan melakukan pemeriksaan terhadap
                                    data dan dokumen yang Anda kirimkan.
                                </p>

                                <p class="mt-2 text-sm font-semibold leading-6 text-amber-800">
                                    Proses verifikasi maksimal 2 hari kerja.
                                </p>

                            </div>

                        </div>

                    </div>

                    <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-5 text-left">

                        <h3 class="text-sm font-bold text-slate-700">
                            Apa yang harus dilakukan?
                        </h3>

                        <div class="mt-4 space-y-3">

                            <div class="flex gap-3">

                                <div class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-xs font-bold">
                                    1
                                </div>

                                <p class="text-sm text-slate-600">
                                    Tunggu proses pemeriksaan dari Admin.
                                </p>

                            </div>

                            <div class="flex gap-3">

                                <div class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-xs font-bold">
                                    2
                                </div>

                                <p class="text-sm text-slate-600">
                                    Admin akan menentukan apakah data Anda
                                    valid dan memenuhi persyaratan.
                                </p>

                            </div>

                            <div class="flex gap-3">

                                <div class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-xs font-bold">
                                    3
                                </div>

                                <p class="text-sm text-slate-600">
                                    Setelah disetujui, status akun berubah menjadi
                                    <span class="font-semibold text-emerald-600">
                                        Active
                                    </span>
                                    dan Dashboard Organizer dapat digunakan.
                                </p>

                            </div>

                        </div>

                    </div>

                    <div class="mt-7 flex items-center justify-center gap-2">

                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>

                        <span class="text-sm font-semibold text-slate-600">
                            Status Pengajuan:
                        </span>

                        <span class="px-3 py-1 rounded-full bg-amber-100 text-amber-700 text-xs font-bold">
                            Pending
                        </span>

                    </div>

                    <div class="mt-8">

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <button type="submit"
                                class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl border border-slate-200 bg-white text-slate-600 text-sm font-semibold hover:bg-slate-50 transition">

                                <svg xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="1.8"
                                    stroke="currentColor"
                                    class="w-4 h-4">

                                    <path stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15" />

                                    <path stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M18 12H9m0 0l3-3m-3 3l3 3" />

                                </svg>

                                Keluar

                            </button>

                        </form>

                    </div>

                </div>

            </div>

            <p class="mt-6 text-center text-xs text-slate-400">
                Silakan kembali setelah proses verifikasi selesai.
            </p>

        </div>

    </div>

</body>
</html>