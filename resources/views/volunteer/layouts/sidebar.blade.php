<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Volunteer') - SkillMatch</title>

    @include('layouts.fonts')

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])

    @stack('styles')

    <style>
        html {
            scrollbar-gutter: stable;
            font-family: 'Inter', sans-serif;
        }

        [x-cloak] {
            display: none !important;
        }

        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: rgba(15, 23, 42, .15);
            border-radius: 999px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: rgba(15, 23, 42, .25);
        }
    </style>
</head>

<body
    class="volunteer-panel min-h-screen overflow-x-hidden bg-slate-50 text-slate-900 antialiased @yield('page-class')"
    x-data="{
        mobileSidebarOpen: false,
        desktop: window.innerWidth >= 1024
    }"
    @resize.window="desktop = window.innerWidth >= 1024"
    @keydown.escape.window="mobileSidebarOpen = false"
>

    <div
        x-show="mobileSidebarOpen"
        x-cloak
        x-transition:opacity.duration.200ms
        @click="mobileSidebarOpen = false"
        class="fixed inset-0 z-40 bg-black/40 backdrop-blur-sm lg:hidden"
    ></div>

    <aside
        :inert="!desktop && !mobileSidebarOpen"
        :class="mobileSidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
        class="volunteer-sidebar fixed inset-y-0 left-0 z-50 flex h-screen w-64 -translate-x-full flex-col bg-slate-900 text-white shadow-xl transition-transform duration-300 ease-in-out lg:translate-x-0"
    >

        <div class="flex h-16 shrink-0 items-center justify-between border-b border-white/10 px-6">

            <a
                href="{{ route('volunteer.activity.index') }}"
                class="group flex items-center gap-3"
            >

                <x-public-brand :mark-only="true" class="volunteer-brand-mark" />

                <div>
                    <h2 class="text-base font-bold leading-none tracking-wider text-white group-hover:text-blue-200">
                        SKILLMATCH
                    </h2>

                    <p class="mt-1 text-[10px] font-medium uppercase tracking-widest text-slate-400">
                        Panel Volunteer
                    </p>
                </div>

            </a>

            <button
                type="button"
                aria-label="Tutup navigasi volunteer"
                @click="mobileSidebarOpen = false"
                class="rounded-lg p-1 text-slate-400 hover:bg-white/5 hover:text-white lg:hidden"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="2.5"
                    stroke="currentColor"
                    class="h-5 w-5"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M6 18L18 6M6 6l12 12"
                    />
                </svg>
            </button>

        </div>

        <div class="volunteer-sidebar-account flex items-center gap-3 border-b border-white/10 px-6 py-5">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-600 text-sm font-bold text-white" aria-hidden="true">{{ mb_strtoupper(mb_substr(Auth::user()->name ?? 'RE', 0, 2)) }}</div>
            <div class="min-w-0"><p class="truncate text-sm font-semibold text-white" title="{{ Auth::user()->name }}">{{ Auth::user()->name }}</p><p class="mt-1 text-[10px] font-medium uppercase tracking-wider text-slate-400">Volunteer</p></div>
        </div>

        <nav
            class="hierarchy-scroll flex-1 space-y-1 overflow-y-auto p-3"
            aria-label="Navigasi volunteer"
        >

            <a
                href="{{ route('volunteer.activity.index') }}"
                @if(Request::routeIs('volunteer.activity.*')) aria-current="page" @endif
                class="flex items-center gap-3 rounded-xl border px-3 py-2.5 text-sm font-medium transition-all duration-200
                {{ Request::routeIs('volunteer.activity.*') ? 'va-current' : '' }}
                {{ Request::routeIs('volunteer.activity.*')
                    ? 'border-white/5 bg-white/10 text-white shadow-sm'
                    : 'border-transparent text-slate-300 hover:bg-white/5 hover:text-white' }}"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="2"
                    stroke="currentColor"
                    class="h-5 w-5 shrink-0"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M3.75 13.5h6V21h-6v-7.5Zm6-10.5h6v18h-6V3Zm6 6h6v12h-6V9Z"
                    />
                </svg>

                <span>
                    Ringkasan Aktivitas
                </span>
            </a>
            <a
                href="{{ route('volunteer.applications.index') }}"
                @if(Request::routeIs('volunteer.applications.*')) aria-current="page" @endif
                class="flex items-center gap-3 rounded-xl border px-3 py-2.5 text-sm font-medium transition-all duration-200
                {{ Request::routeIs('volunteer.applications.*')
                    ? 'border-white/5 bg-white/10 text-white shadow-sm'
                    : 'border-transparent text-slate-300 hover:bg-white/5 hover:text-white' }}"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="2"
                    stroke="currentColor"
                    class="h-5 w-5 shrink-0"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"
                    />
                </svg>

                <span>
                    Lamaran Saya
                </span>
            </a>

            <a
                href="{{ route('volunteer.history.index') }}"
                @if(Request::routeIs('volunteer.history.*')) aria-current="page" @endif
                class="flex items-center gap-3 rounded-xl border px-3 py-2.5 text-sm font-medium transition-all duration-200
                {{ Request::routeIs('volunteer.history.*')
                    ? 'border-white/5 bg-white/10 text-white shadow-sm'
                    : 'border-transparent text-slate-300 hover:bg-white/5 hover:text-white' }}"
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-5 w-5 shrink-0" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25V12l2.25 2.25M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span>Riwayat Partisipasi</span>
            </a>

            <a
                href="{{ route('volunteer.profile.edit') }}"
                @if(Request::routeIs('volunteer.profile.*')) aria-current="page" @endif
                class="flex items-center gap-3 rounded-xl border px-3 py-2.5 text-sm font-medium transition-all duration-200
                {{ Request::routeIs('volunteer.profile.*') ? 'vp-current' : '' }}
                {{ Request::routeIs('volunteer.profile.*')
                    ? 'border-white/5 bg-white/10 text-white shadow-sm'
                    : 'border-transparent text-slate-300 hover:bg-white/5 hover:text-white' }}"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="2"
                    stroke="currentColor"
                    class="h-5 w-5 shrink-0"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M15.75 6.75a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.25a7.5 7.5 0 0 1 15 0"
                    />
                </svg>

                <span>
                    Profil Volunteer
                </span>
            </a>

            <div class="my-4 border-t border-white/10"></div>

            <a
                href="{{ route('volunteer.events.index') }}"
                @if(Request::routeIs('volunteer.events.*')) aria-current="page" @endif
                class="flex items-center gap-3 rounded-xl border border-transparent px-3 py-2.5 text-sm font-medium transition-all duration-150 hover:bg-white/5 hover:text-white {{ Request::routeIs('volunteer.events.*') ? 'bg-blue-600 text-white' : 'text-slate-300' }}"
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-5 w-5 shrink-0" aria-hidden="true">
                    <circle cx="10.5" cy="10.5" r="6.5" />
                    <path stroke-linecap="round" d="m16 16 4 4" />
                </svg>
                <span>Jelajahi Kegiatan</span>
            </a>

            <a
                href="{{ url('/') }}"
                class="flex items-center gap-3 rounded-xl border border-transparent px-3 py-2.5 text-sm font-medium text-slate-300 transition-all duration-150 hover:bg-white/5 hover:text-white"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="2"
                    stroke="currentColor"
                    class="h-5 w-5 shrink-0"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Zm0-18c2.485 2.485 4 6.514 4 9s-1.515 6.515-4 9m0-18C9.515 5.485 8 9.514 8 12s1.515 6.515 4 9m-8.25-9h16.5"
                    />
                </svg>

                <span>
                    Lihat situs utama
                </span>
            </a>

            <form action="{{ route('logout') }}" method="POST" class="mt-2">
                @csrf
                <button type="submit" class="volunteer-logout flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4m7 14 5-5-5-5M21 12H9" /></svg>Keluar
                </button>
            </form>
        </nav>

    </aside>

    <div class="flex min-h-screen flex-1 flex-col lg:pl-64">

        <header class="volunteer-topbar fixed left-0 right-0 top-0 z-30 h-16 border-b border-slate-100 bg-white/80 backdrop-blur-md lg:left-64">

            <div class="flex h-full items-center justify-between px-4 sm:px-6 lg:justify-end lg:px-8">

                <button
                    type="button"
                    aria-label="Buka navigasi volunteer"
                    :aria-expanded="mobileSidebarOpen"
                    @click="mobileSidebarOpen = true"
                    class="rounded-xl p-2 text-slate-600 hover:bg-slate-50 hover:text-slate-900 lg:hidden"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="2"
                        stroke="currentColor"
                        class="h-6 w-6"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"
                        />
                    </svg>
                </button>

                <span class="text-sm font-semibold text-slate-900">Panel Volunteer</span>

            </div>

        </header>

        <main class="volunteer-main flex-1 px-4 pb-8 pt-24 sm:px-6 lg:px-8">
            @include('volunteer.partials.flash')

            @if(!auth()->user()->hasVerifiedEmail())
                <div role="status" class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                    Akun Anda aktif. Verifikasi email diperlukan sebelum mulai melamar.
                    <a href="{{ route('verification.notice') }}" class="font-semibold underline">Buka verifikasi email</a>
                </div>
            @endif

            @yield('content')
        </main>

    </div>

    @stack('scripts')

</body>
</html>
