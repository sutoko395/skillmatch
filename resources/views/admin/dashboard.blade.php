@extends('admin.layouts.sidebar')

@section('title', 'Dashboard Utama Admin')

@section('content')
<div class="w-full max-w-7xl mx-auto space-y-6 px-4 sm:px-6 md:px-8 py-4">
    
    {{-- WELCOME BANNER --}}
    <div class="bg-white p-6 md:p-8 rounded-2xl shadow-sm border border-gray-100 relative overflow-hidden group">
        <div class="absolute top-0 right-0 -mt-4 -mr-4 w-32 h-32 bg-indigo-500/10 rounded-full blur-xl transition-all duration-300 group-hover:bg-indigo-500/20"></div>
        
        <div class="relative z-10 space-y-2">
            <h1 class="text-xl md:text-2xl font-semibold text-gray-900 tracking-tight">
                Selamat Datang Kembali, <span class="text-indigo-600 font-bold">{{ Auth::user()->name }}</span>!
            </h1>
            <p class="text-xs md:text-sm text-gray-500 font-normal leading-relaxed">
                Pantau akumulasi pendaftaran Volunteer, aktivitas Organizer, verifikasi event, serta ketersediaan Master Skill sistem SkillMatch.
            </p>
        </div>
    </div>

    {{-- METRICS CARD GRID --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-5">
        
        {{-- Total Volunteer --}}
        <div class="bg-white p-4 md:p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4 transition-all duration-200 hover:shadow-md group">
            <div class="w-10 h-10 flex-shrink-0 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center transition-transform duration-300 group-hover:scale-110 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                </svg>
            </div>
            <div class="space-y-1 min-w-0">
                <p class="text-[10px] md:text-xs font-semibold text-gray-400 tracking-wider truncate">Total Volunteer</p>
                <h3 class="text-xl md:text-2xl font-bold text-gray-800 transition-all group-hover:text-indigo-600 truncate">{{ $totalVolunteers }}</h3>
            </div>
        </div>

        {{-- Total Organizer --}}
        <div class="bg-white p-4 md:p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4 transition-all duration-200 hover:shadow-md group">
            <div class="w-10 h-10 flex-shrink-0 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center transition-transform duration-300 group-hover:scale-110 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5h.75m-.75 3h.75m-.75 3h.75" />
                </svg>
            </div>
            <div class="space-y-1 min-w-0">
                <p class="text-[10px] md:text-xs font-semibold text-gray-400 tracking-wider truncate">Total Organizer</p>
                <h3 class="text-xl md:text-2xl font-bold text-gray-800 transition-all group-hover:text-emerald-600 truncate">{{ $totalOrganizers }}</h3>
            </div>
        </div>

        {{-- Master Skill --}}
        <div class="bg-white p-4 md:p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4 transition-all duration-200 hover:shadow-md group">
            <div class="w-10 h-10 flex-shrink-0 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center transition-transform duration-300 group-hover:scale-110 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 01-3.586-3.586l5.654-4.654m0 0l3.03-2.496c.384-.317.626-.74.766-1.208" />
                </svg>
            </div>
            <div class="space-y-1 min-w-0">
                <p class="text-[10px] md:text-xs font-semibold text-gray-400 tracking-wider truncate">Master Skill</p>
                <h3 class="text-xl md:text-2xl font-bold text-gray-800 transition-all group-hover:text-blue-600 truncate">{{ $totalSkills }}</h3>
            </div>
        </div>

        {{-- Kategori Event --}}
        <div class="bg-white p-4 md:p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4 transition-all duration-200 hover:shadow-md group">
            <div class="w-10 h-10 flex-shrink-0 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center transition-transform duration-300 group-hover:scale-110 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.674.509a13.91 13.91 0 003.354-2.1c.78-.658.95-1.745.424-2.583L11.16 3.66a2.25 2.25 0 00-1.592-.661z" />
                </svg>
            </div>
            <div class="space-y-1 min-w-0">
                <p class="text-[10px] md:text-xs font-semibold text-gray-400 tracking-wider truncate">Kategori Event</p>
                <h3 class="text-xl md:text-2xl font-bold text-gray-800 transition-all group-hover:text-purple-600 truncate">{{ $totalCategories }}</h3>
            </div>
        </div>

    </div>

    {{-- QUICK ACTION PANEL --}}
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 space-y-4">
        <h3 class="text-base font-bold text-gray-900">Aksi Cepat Admin</h3>
        <div class="flex flex-wrap gap-4">
            <a href="{{ route('admin.master-data') }}" class="inline-flex items-center gap-2 bg-indigo-600 text-white px-5 py-2.5 rounded-xl font-semibold text-xs md:text-sm hover:bg-indigo-700 transition shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Kelola Master Data (Skill & Kategori)
            </a>
        </div>
    </div>

</div>
@endsection