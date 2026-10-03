@extends('organizer.layouts.sidebar')

@section('title', 'Dashboard Organizer')

@section('content')

<div class="w-full max-w-7xl mx-auto space-y-6">

    <div class="bg-white p-6 md:p-8 rounded-2xl shadow-sm border border-gray-100 relative overflow-hidden group">

        <div class="absolute top-0 right-0 -mt-4 -mr-4 w-32 h-32 bg-indigo-500/10 rounded-full blur-xl transition-all duration-300 group-hover:bg-indigo-500/20"></div>

        <div class="relative z-10 space-y-2">

            <h1 class="text-xl md:text-2xl font-semibold text-gray-900 tracking-tight">
                Selamat Datang Kembali,
                <span class="text-indigo-600 font-bold">
                    {{ $user->name }}
                </span>!
            </h1>

            <p class="text-xs md:text-sm text-gray-500 font-normal leading-relaxed">
                Kelola event, rekrutmen volunteer, proses seleksi, serta pelaksanaan kegiatan Anda melalui sistem SkillMatch.
            </p>

        </div>

    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-5">

        <div class="bg-white p-4 md:p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4 transition-all duration-200 hover:shadow-md group">

            <div class="w-10 h-10 flex-shrink-0 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center transition-transform duration-300 group-hover:scale-110 shadow-sm">

                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="2"
                    stroke="currentColor"
                    class="w-5 h-5"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M6.75 3v2.25M17.25 3v2.25M3.75 9h16.5M5.25 5.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25A2.25 2.25 0 0 1 18.75 21H5.25A2.25 2.25 0 0 1 3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25Z"
                    />
                </svg>

            </div>

            <div class="space-y-1 min-w-0">

                <p class="text-[10px] md:text-xs font-semibold text-gray-400 tracking-wider truncate">
                    Total Event
                </p>

                <h3 class="text-xl md:text-2xl font-bold text-gray-800 transition-all group-hover:text-indigo-600 truncate">
                    0
                </h3>

            </div>

        </div>

        <div class="bg-white p-4 md:p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4 transition-all duration-200 hover:shadow-md group">

            <div class="w-10 h-10 flex-shrink-0 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center transition-transform duration-300 group-hover:scale-110 shadow-sm">

                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="2"
                    stroke="currentColor"
                    class="w-5 h-5"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.375 9.375 0 0 0 2.625-.372M15 19.128v-3.375m0 3.375a9.375 9.375 0 0 1-7.5 0m7.5 0v-3.375m-7.5 3.375v-3.375m0 0a9.375 9.375 0 0 1 7.5 0M12 10.5a3.375 3.375 0 1 0 0-6.75 3.375 3.375 0 0 0 0 6.75Z"
                    />
                </svg>

            </div>

            <div class="space-y-1 min-w-0">

                <p class="text-[10px] md:text-xs font-semibold text-gray-400 tracking-wider truncate">
                    Total Pendaftar
                </p>

                <h3 class="text-xl md:text-2xl font-bold text-gray-800 transition-all group-hover:text-emerald-600 truncate">
                    0
                </h3>

            </div>

        </div>

        <div class="bg-white p-4 md:p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4 transition-all duration-200 hover:shadow-md group">

            <div class="w-10 h-10 flex-shrink-0 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center transition-transform duration-300 group-hover:scale-110 shadow-sm">

                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="2"
                    stroke="currentColor"
                    class="w-5 h-5"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M12 9v3.75m0 3.75h.007v.008H12V16.5zM9.75 3.75h4.5l6 6v4.5l-6 6h-4.5l-6-6v-4.5l6-6z"
                    />
                </svg>

            </div>

            <div class="space-y-1 min-w-0">

                <p class="text-[10px] md:text-xs font-semibold text-gray-400 tracking-wider truncate">
                    Screening
                </p>

                <h3 class="text-xl md:text-2xl font-bold text-gray-800 transition-all group-hover:text-amber-600 truncate">
                    0
                </h3>

            </div>

        </div>

        <div class="bg-white p-4 md:p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4 transition-all duration-200 hover:shadow-md group">

            <div class="w-10 h-10 flex-shrink-0 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center transition-transform duration-300 group-hover:scale-110 shadow-sm">

                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="2"
                    stroke="currentColor"
                    class="w-5 h-5"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
                    />
                </svg>

            </div>

            <div class="space-y-1 min-w-0">

                <p class="text-[10px] md:text-xs font-semibold text-gray-400 tracking-wider truncate">
                    Volunteer Terpilih
                </p>

                <h3 class="text-xl md:text-2xl font-bold text-gray-800 transition-all group-hover:text-purple-600 truncate">
                    0
                </h3>

            </div>

        </div>

    </div>

    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 space-y-4">

        <h3 class="text-base font-bold text-gray-900">
            Aksi Cepat Organizer
        </h3>

        <div class="flex flex-wrap gap-4">

            <a
                href="#"
                class="inline-flex items-center gap-2 bg-indigo-600 text-white px-5 py-2.5 rounded-xl font-semibold text-xs md:text-sm hover:bg-indigo-700 transition shadow-sm"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="2"
                    stroke="currentColor"
                    class="w-4 h-4"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M12 4.5v15m7.5-7.5h-15"
                    />
                </svg>

                Buat Event
            </a>

            <a
                href="{{ route('organizer.profile.edit') }}"
                class="inline-flex items-center gap-2 bg-slate-100 text-slate-700 px-5 py-2.5 rounded-xl font-semibold text-xs md:text-sm hover:bg-slate-200 transition shadow-sm"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="2"
                    stroke="currentColor"
                    class="w-4 h-4"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.375 9.375 0 0 0 2.625-.372M15 19.128v-3.375m0 3.375a9.375 9.375 0 0 1-7.5 0m7.5 0v-3.375m-7.5 3.375v-3.375m0 0a9.375 9.375 0 0 1 7.5 0M12 10.5a3.375 3.375 0 1 0 0-6.75 3.375 3.375 0 0 0 0 6.75Z"
                    />
                </svg>

                Profil Organisasi
            </a>

        </div>

    </div>

    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">

        <div class="flex items-center justify-between gap-4">

            <div>

                <h3 class="text-base font-bold text-gray-900">
                    Status Akun Organizer
                </h3>

                <p class="mt-1 text-xs md:text-sm text-gray-500">
                    Akun Anda telah diverifikasi dan dapat menggunakan seluruh fitur Organizer.
                </p>

            </div>

            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-600 text-xs font-bold whitespace-nowrap">

                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>

                Aktif

            </span>

        </div>

    </div>

</div>

@endsection