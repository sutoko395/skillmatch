@extends('volunteer.layouts.sidebar')

@section('title', 'Dashboard Volunteer')

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
                Temukan event yang sesuai dengan skill, lokasi, dan ketersediaan Anda melalui SkillMatch.
            </p>

        </div>

    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-5">

        <div class="bg-white p-4 md:p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4 transition-all duration-200 hover:shadow-md group">

            <div class="w-10 h-10 flex-shrink-0 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center transition-transform duration-300 group-hover:scale-110 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 01-3.586-3.586l5.654-4.654m0 0l3.03-2.496c.384-.317.74-.626.766-1.208" />
                </svg>
            </div>

            <div class="space-y-1 min-w-0">
                <p class="text-[10px] md:text-xs font-semibold text-gray-400 tracking-wider truncate">
                    Skill Saya
                </p>

                <h3 class="text-xl md:text-2xl font-bold text-gray-800 group-hover:text-indigo-600">
                    {{ $totalSkills }}
                </h3>
            </div>

        </div>

        <div class="bg-white p-4 md:p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4 transition-all duration-200 hover:shadow-md group">

            <div class="w-10 h-10 flex-shrink-0 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center transition-transform duration-300 group-hover:scale-110 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v18m10.5-18v18M3 6.75h18M3 17.25h18M7.5 9h9m-9 6h9" />
                </svg>
            </div>

            <div class="space-y-1 min-w-0">
                <p class="text-[10px] md:text-xs font-semibold text-gray-400 tracking-wider truncate">
                    Event Tersedia
                </p>

                <h3 class="text-xl md:text-2xl font-bold text-gray-800 group-hover:text-emerald-600">
                    {{ $totalEvents }}
                </h3>
            </div>

        </div>

        <div class="bg-white p-4 md:p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4 transition-all duration-200 hover:shadow-md group">

            <div class="w-10 h-10 flex-shrink-0 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center transition-transform duration-300 group-hover:scale-110 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M12 21a9 9 0 100-18 9 9 0 000 18z" />
                </svg>
            </div>

            <div class="space-y-1 min-w-0">
                <p class="text-[10px] md:text-xs font-semibold text-gray-400 tracking-wider truncate">
                    Status Profil
                </p>

                <h3 class="text-sm md:text-base font-bold text-gray-800 group-hover:text-blue-600">
                    {{ $profileCompleted ? 'Lengkap' : 'Belum Lengkap' }}
                </h3>
            </div>

        </div>

        <div class="bg-white p-4 md:p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4 transition-all duration-200 hover:shadow-md group">

            <div class="w-10 h-10 flex-shrink-0 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center transition-transform duration-300 group-hover:scale-110 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m5-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>

            <div class="space-y-1 min-w-0">
                <p class="text-[10px] md:text-xs font-semibold text-gray-400 tracking-wider truncate">
                    Ketersediaan
                </p>

                <h3 class="text-sm md:text-base font-bold text-gray-800 group-hover:text-purple-600 truncate">
                    {{ $volunteerProfile?->availability ?? 'Belum diatur' }}
                </h3>
            </div>

        </div>

    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">

            <div class="flex items-center justify-between mb-5">

                <div>
                    <h3 class="text-base font-bold text-gray-900">
                        Mulai Perjalanan Volunteer
                    </h3>

                    <p class="text-xs text-gray-500 mt-1">
                        Pastikan profil Anda siap sebelum mendaftar event.
                    </p>
                </div>

                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15.75a3.75 3.75 0 100-7.5 3.75 3.75 0 000 7.5z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12a7.5 7.5 0 11-15 0 7.5 7.5 0 0115 0z" />
                    </svg>
                </div>

            </div>

            <div class="flex flex-wrap gap-3">

                <a
                    href="{{ route('volunteer.profile.edit') }}"
                    class="inline-flex items-center gap-2 bg-indigo-600 text-white px-5 py-2.5 rounded-xl font-semibold text-xs md:text-sm hover:bg-indigo-700 transition shadow-sm"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0" />
                    </svg>

                    Lengkapi Profil & Skill
                </a>

                <a
                    href="{{ url('/') }}"
                    class="inline-flex items-center gap-2 bg-gray-100 text-gray-700 px-5 py-2.5 rounded-xl font-semibold text-xs md:text-sm hover:bg-gray-200 transition"
                >
                    Lihat Event
                </a>

            </div>

        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">

            <div class="flex items-center gap-3 mb-5">

                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m6.75.75a9.75 9.75 0 11-19.5 0 9.75 9.75 0 0119.5 0z" />
                    </svg>
                </div>

                <div>
                    <h3 class="text-base font-bold text-gray-900">
                        Kesiapan Profil
                    </h3>

                    <p class="text-xs text-gray-400">
                        Data volunteer
                    </p>
                </div>

            </div>

            <div class="space-y-3">

                <div class="flex items-center justify-between text-xs">
                    <span class="text-gray-500">Profil</span>

                    @if($profileCompleted)
                        <span class="px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 font-semibold">
                            Lengkap
                        </span>
                    @else
                        <span class="px-2.5 py-1 rounded-full bg-amber-50 text-amber-700 font-semibold">
                            Perlu dilengkapi
                        </span>
                    @endif
                </div>

                <div class="flex items-center justify-between text-xs">
                    <span class="text-gray-500">Skill terdaftar</span>

                    <span class="font-semibold text-gray-800">
                        {{ $totalSkills }} Skill
                    </span>
                </div>

                <div class="flex items-center justify-between text-xs">
                    <span class="text-gray-500">Lokasi</span>

                    <span class="font-semibold text-gray-800 truncate ml-4">
                        {{ $volunteerProfile?->city ?? 'Belum diatur' }}
                    </span>
                </div>

            </div>

        </div>

    </div>

</div>

@endsection