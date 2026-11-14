<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50" id="html-root">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Organizer') - SkillMatch</title>

    @include('layouts.fonts')

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])

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
    class="organizer-panel min-h-screen overflow-x-hidden bg-slate-50 text-slate-900 antialiased"
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
    class="organizer-sidebar fixed inset-y-0 left-0 z-50 flex h-screen w-64 -translate-x-full flex-col bg-slate-900 text-white transition-transform duration-300 ease-in-out lg:translate-x-0"
>
    <div class="flex h-16 shrink-0 items-center justify-between border-b border-white/10 px-6">
        <a
            href="{{ route('organizer.activity.index') }}"
            class="group flex items-center gap-3"
        >
            <x-public-brand :mark-only="true" class="organizer-brand-mark" />

            <div>
                <h2 class="text-base font-bold leading-none tracking-wider text-white group-hover:text-indigo-300">
                    SKILLMATCH
                </h2>
                <p class="mt-1 text-[10px] font-medium uppercase tracking-widest text-slate-400">
                    Panel Organisasi
                </p>
            </div>
        </a>

        <button
            type="button"
            aria-label="Tutup navigasi organizer"
            @click="mobileSidebarOpen = false"
            class="rounded-lg p-1 text-slate-400 hover:bg-white/5 hover:text-white lg:hidden"
        >
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="h-5 w-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <nav class="hierarchy-scroll flex-1 space-y-1 overflow-y-auto p-3" aria-label="Navigasi organizer">

        <div class="organizer-sidebar-account flex items-center gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-600 text-sm font-bold text-white" aria-hidden="true">
                {{ mb_strtoupper(mb_substr(Auth::user()->name ?? 'OR', 0, 2)) }}
            </div>
            <div class="min-w-0">
                <p class="truncate text-sm font-semibold text-white" title="{{ Auth::user()->name }}">{{ Auth::user()->name ?? 'Organizer' }}</p>
                <p class="mt-1 text-[10px] font-medium uppercase tracking-wider text-slate-400">Organizer</p>
            </div>
        </div>

        <a
            href="{{ route('organizer.activity.index') }}"
            aria-current="{{ Request::routeIs('organizer.activity.*') ? 'page' : 'false' }}"
            class="flex items-center gap-3 rounded-xl border px-3 py-2.5 text-sm font-medium transition-all duration-200
            {{ Request::routeIs('organizer.activity.*')
                ? 'border-blue-600 bg-blue-600 text-white'
                : 'border-transparent text-slate-300 hover:bg-white/5 hover:text-white' }}"
        >
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-5 w-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.5h6V21H3v-7.5Zm6-10.5h6v18H9V3Zm6 6h6v12h-6V9Z"/>
            </svg>
            <span>Ringkasan Aktivitas</span>
        </a>

        <a
            href="{{ route('organizer.events.index') }}"
            aria-current="{{ Request::routeIs('organizer.events.*') ? 'page' : 'false' }}"
            class="flex items-center gap-3 rounded-xl border px-3 py-2.5 text-sm font-medium transition-all duration-200
            {{ Request::routeIs('organizer.events.*')
                ? 'border-blue-600 bg-blue-600 text-white'
                : 'border-transparent text-slate-300 hover:bg-white/5 hover:text-white' }}"
        >
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-5 w-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3.75h10.5A2.25 2.25 0 0 1 19.5 6v14.25l-4.5-2.25-3 2.25-3-2.25-4.5 2.25V6a2.25 2.25 0 0 1 2.25-2.25Z"/>
            </svg>
            <span>Kegiatan Saya</span>
        </a>

        <a
            href="{{ route('organizer.applications.overview') }}"
            aria-current="{{ Request::routeIs('organizer.applications.*') ? 'page' : 'false' }}"
            class="flex items-center gap-3 rounded-xl border px-3 py-2.5 text-sm font-medium transition-all duration-200
            {{ Request::routeIs('organizer.applications.*')
                ? 'border-blue-600 bg-blue-600 text-white'
                : 'border-transparent text-slate-300 hover:bg-white/5 hover:text-white' }}"
        >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2m14-14a4 4 0 0 1 0 8m6 6v-2a4 4 0 0 0-3-3.87M13 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z" />
            </svg>
            <span>Kelola Pelamar</span>
        </a>

        <a
            href="{{ route('organizer.execution.index') }}"
            aria-current="{{ Request::routeIs('organizer.execution.*') ? 'page' : 'false' }}"
            class="flex items-center gap-3 rounded-xl border px-3 py-2.5 text-sm font-medium transition-all duration-200
            {{ Request::routeIs('organizer.execution.*')
                ? 'border-blue-600 bg-blue-600 text-white'
                : 'border-transparent text-slate-300 hover:bg-white/5 hover:text-white' }}"
        >
            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H6a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-3M9 5V3h6v2H9Zm0 9 2 2 4-4" />
            </svg>
            <span class="min-w-0 leading-5">Pelaksanaan &amp; Kehadiran</span>
        </a>

        <a
            href="{{ route('organizer.profile.edit') }}"
            aria-current="{{ Request::routeIs('organizer.profile.*') ? 'page' : 'false' }}"
            class="flex items-center gap-3 rounded-xl border px-3 py-2.5 text-sm font-medium transition-all duration-200
            {{ Request::routeIs('organizer.profile.*')
                ? 'border-blue-600 bg-blue-600 text-white'
                : 'border-transparent text-slate-300 hover:bg-white/5 hover:text-white' }}"
        >
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-5 w-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6.75a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.25a7.5 7.5 0 0 1 15 0"/>
            </svg>
            <span>Profil Organisasi</span>
        </a>

        <div class="organizer-sidebar-divider border-t border-white/10"></div>

        <a
            href="{{ url('/') }}"
            class="flex items-center gap-3 rounded-xl border border-transparent px-4 py-2.5 text-sm font-medium text-slate-300 transition-all duration-150 hover:bg-white/5 hover:text-white"
        >
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-5 w-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Zm0-18c2.485 2.485 4 6.514 4 9s-1.515 6.515-4 9m0-18C9.515 5.485 8 9.514 8 12s1.515 6.515 4 9m-8.25-9h16.5"/>
            </svg>
            <span>Lihat situs utama</span>
        </a>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="organizer-sidebar-logout flex w-full items-center gap-3 rounded-xl px-4 py-2.5 text-left text-sm font-semibold">
                <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h4m6-14 7 7-7 7m7-7H9" />
                </svg>
                <span>Keluar</span>
            </button>
        </form>
    </nav>
</aside>

<div class="organizer-workspace flex min-h-screen flex-1 flex-col lg:pl-64">

    <header class="organizer-topbar lg:hidden fixed left-0 right-0 top-0 z-30 h-16 border-b border-slate-100 bg-white lg:left-64">
        <div class="flex h-full items-center justify-between px-4 sm:px-6 lg:px-8">

            <button
                type="button"
                aria-label="Buka navigasi organizer"
                :aria-expanded="mobileSidebarOpen"
                @click="mobileSidebarOpen = true"
                class="rounded-xl p-2 text-slate-600 hover:bg-slate-50 hover:text-slate-900 lg:hidden"
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-6 w-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
                </svg>
            </button>

            <p class="min-w-0 truncate pl-3 text-sm font-medium text-slate-500">
                @hasSection('topbar-title')
                    @yield('topbar-title')
                @else
                    @yield('title', 'Panel Organisasi')
                @endif
            </p>

        </div>
    </header>

    <main class="organizer-main min-w-0 flex-1 px-4 pb-8 pt-24 sm:px-6 lg:px-8 lg:pt-8">
        <x-organizer-flash />
        @yield('content')
    </main>
</div>

</body>
</html>
