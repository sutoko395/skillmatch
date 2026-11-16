<!DOCTYPE html>
<html lang="id" class="h-full bg-bg-light overflow-y-scroll" id="html-root">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'Dashboard') - SkillMatch Admin</title>

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
            background: rgba(255,255,255,.15);
            border-radius: 999px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(255,255,255,.3);
        }
    </style>




</head>
<body class="admin-panel h-full overflow-x-hidden font-sans bg-bg-light text-txt-light-primary antialiased" x-data="{ mobileSidebarOpen: false, desktop: window.innerWidth >= 1024 }" @resize.window="desktop = window.innerWidth >= 1024" @keydown.escape.window="if (mobileSidebarOpen) { mobileSidebarOpen = false; $refs.menu.focus(); }">

    <!-- Overlay Mobile -->
    <div x-show="mobileSidebarOpen"
         x-cloak
         x-transition:opacity.duration.200ms
         @click="mobileSidebarOpen = false; $refs.menu.focus()"
         class="fixed inset-0 bg-black/40 backdrop-blur-sm z-40 lg:hidden">
    </div>

    <!-- Sidebar Utama -->
    <aside :inert="!desktop && !mobileSidebarOpen"
        :class="mobileSidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
        class="admin-sidebar -translate-x-full lg:translate-x-0 fixed inset-y-0 left-0 w-64 bg-bg-brand text-txt-dark-primary flex flex-col h-screen select-none z-50 transition-transform duration-300 ease-in-out">
        
        <!-- Logo Sidebar -->
        <div class="p-6 h-16 border-b border-white/10 flex items-center justify-between shrink-0">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-3 group cursor-pointer transition-opacity hover:opacity-90">
                <x-public-brand :mark-only="true" class="admin-brand-mark" />
                <div>
                    <h2 class="text-base font-bold tracking-wider leading-none text-white transition-colors group-hover:text-indigo-300">SKILLMATCH</h2>
                    <p class="text-[10px] text-txt-dark-secondary font-medium tracking-widest uppercase mt-1">Panel Admin</p>
                </div>
            </a>

            <button aria-label="Tutup navigasi admin" @click="mobileSidebarOpen = false; $refs.menu.focus()" class="text-txt-dark-secondary hover:text-white lg:hidden focus:outline-none p-1 rounded-lg hover:bg-white/5 cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="admin-account flex shrink-0 items-center gap-3 border-b border-white/10 px-6 py-5">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-600 text-sm font-bold text-white">{{ mb_strtoupper(mb_substr(Auth::user()->name ?? 'Admin', 0, 2)) }}</span>
            <div class="min-w-0"><p class="truncate text-sm font-semibold text-white">{{ Auth::user()->name ?? 'Administrator' }}</p><p class="mt-1 text-xs text-slate-400">Administrator</p></div>
        </div>
        <!-- Navigation Menu -->
        <nav aria-label="Navigasi admin" class="flex-1 p-4 space-y-1.5 overflow-y-auto hierarchy-scroll">
            
            <a href="{{ route('admin.dashboard') }}" 
            class="flex items-center space-x-3 px-4 py-3 rounded-xl font-medium text-sm transition-all duration-200 {{ Request::routeIs('admin.dashboard') ? 'bg-blue-600 text-white border border-transparent' : 'text-txt-dark-secondary hover:bg-white/5 hover:text-white border border-transparent' }} group">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 opacity-90 transition-transform group-hover:scale-105">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                </svg>
                <span>Dashboard</span>
            </a>

            <div
                x-data="{ open: {{ Request::routeIs('admin.organizers.*', 'admin.volunteers.*') ? 'true' : 'false' }} }"
                class="space-y-1"
            >
                <button
                    type="button"
                    @click="open = !open"
                    class="w-full flex items-center justify-between px-4 py-3 rounded-xl font-medium text-sm transition-all duration-200 {{ Request::routeIs('admin.organizers.*', 'admin.volunteers.*') ? 'bg-blue-600 text-white border border-transparent' : 'text-txt-dark-secondary hover:bg-white/5 hover:text-white border border-transparent' }}"
                >
                    <span class="flex items-center space-x-3">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.375 9.375 0 0 0 2.625-.372M15 19.128v-3.375m0 3.375a9.375 9.375 0 0 1-7.5 0m7.5 0v-3.375m-7.5 3.375v-3.375m0 0a9.375 9.375 0 0 1 7.5 0M12 10.5a3.375 3.375 0 1 0 0-6.75 3.375 3.375 0 0 0 0 6.75Z" />
                        </svg>

                        <span>Pengguna</span>
                    </span>

                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="2"
                        stroke="currentColor"
                        class="w-4 h-4 transition-transform duration-300"
                        :class="open ? 'rotate-180' : ''"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                    </svg>
                </button>

                <div
                    x-show="open"
                    x-cloak
                    x-transition:enter="transition-all ease-out duration-300"
                    x-transition:enter-start="opacity-0 -translate-y-2 max-h-0"
                    x-transition:enter-end="opacity-100 translate-y-0 max-h-40"
                    x-transition:leave="transition-all ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 max-h-40"
                    x-transition:leave-end="opacity-0 -translate-y-2 max-h-0"
                    class="pl-6 pr-2 overflow-hidden"
                >

                    <a
                        href="{{ route('admin.organizers.index') }}"
                        class="block px-4 py-2.5 text-sm transition {{ Request::routeIs('admin.organizers.*') ? 'text-white font-bold' : 'text-txt-dark-secondary hover:text-white' }}"
                    >
                        Organizer
                    </a>

                    <a
                        href="{{ route('admin.volunteers.index') }}"
                        class="block px-4 py-2.5 text-sm transition {{ Request::routeIs('admin.volunteers.*') ? 'text-white font-bold' : 'text-txt-dark-secondary hover:text-white' }}"
                    >
                        Volunteer
                    </a>

                </div>
            </div>

            <a
                href="{{ route('admin.event-verification.index') }}"
                class="flex items-center space-x-3 px-4 py-3 rounded-xl font-medium text-sm transition-all duration-200 {{ Request::routeIs('admin.event-verification.*') ? 'bg-blue-600 text-white border border-transparent' : 'text-txt-dark-secondary hover:bg-white/5 hover:text-white border border-transparent' }}"
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>

                <span>Verifikasi Kegiatan</span>
            </a>

            <div
                x-data="{ open: {{ Request::routeIs('admin.master-data.*') ? 'true' : 'false' }} }"
                class="space-y-1"
            >
                <button
                    @click="open = !open"
                    class="w-full flex items-center justify-between px-4 py-3 rounded-xl font-medium text-sm transition-all duration-200 focus:outline-none cursor-pointer {{ Request::routeIs('admin.master-data.*') ? 'bg-blue-600 text-white border border-transparent' : 'text-txt-dark-secondary hover:bg-white/5 hover:text-white border border-transparent' }}"
                >
                    <div class="flex items-center space-x-3">
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
                                d="M4.5 6.75A2.25 2.25 0 016.75 4.5h10.5a2.25 2.25 0 012.25 2.25v10.5a2.25 2.25 0 01-2.25 2.25H6.75A2.25 2.25 0 014.5 17.25V6.75z"
                            />
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M8.25 8.25h7.5M8.25 12h7.5M8.25 15.75h4.5"
                            />
                        </svg>

                        <span>Master Data</span>
                    </div>

                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="2"
                        stroke="currentColor"
                        class="w-4 h-4 transition-transform duration-200"
                        :class="open ? 'rotate-180' : ''"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M19.5 8.25l-7.5 7.5-7.5-7.5"
                        />
                    </svg>
                </button>

                <div
                    x-show="open"
                    x-cloak
                    x-transition:enter="transition-all ease-out duration-300"
                    x-transition:enter-start="opacity-0 -translate-y-2 max-h-0"
                    x-transition:enter-end="opacity-100 translate-y-0 max-h-40"
                    x-transition:leave="transition-all ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 max-h-40"
                    x-transition:leave-end="opacity-0 -translate-y-2 max-h-0"
                    class="pl-6 pr-2 overflow-hidden"
                >
                    <div class="ml-5 pl-3 border-l-2 border-white/10 space-y-1">

                        <a
                            href="{{ route('admin.master-data.skills.index') }}"
                            class="flex items-center px-3 py-2.5 text-sm transition-all duration-200 {{ Request::routeIs('admin.master-data.skills.*') ? 'text-white font-bold' : 'text-txt-dark-secondary hover:text-white font-medium' }}"
                        >
                            Keahlian
                        </a>

                        <a
                            href="{{ route('admin.master-data.event-categories.index') }}"
                            class="flex items-center px-3 py-2.5 text-sm transition-all duration-200 {{ Request::routeIs('admin.master-data.event-categories.*') ? 'text-white font-bold' : 'text-txt-dark-secondary hover:text-white font-medium' }}"
                        >
                            Kategori Kegiatan
                        </a>

                    </div>
                </div>
            </div>
            <a href="{{ route('admin.packages.index') }}"
               @if(Request::routeIs('admin.packages.*')) aria-current="page" @endif
               class="flex items-center space-x-3 px-4 py-3 rounded-xl font-medium text-sm transition-all duration-200 {{ Request::routeIs('admin.packages.*') ? 'bg-blue-600 text-white border border-transparent' : 'text-txt-dark-secondary hover:bg-white/5 hover:text-white border border-transparent' }}">
                <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m12 3 9 5-9 5-9-5 9-5Zm-9 5v8l9 5 9-5V8M12 13v8" />
                </svg>
                <span>Kelola Paket</span>
            </a>

            <a href="{{ route('admin.orders.index') }}"
               @if(Request::routeIs('admin.orders.*')) aria-current="page" @endif
               class="flex items-center space-x-3 px-4 py-3 rounded-xl font-medium text-sm transition-all duration-200 {{ Request::routeIs('admin.orders.*') ? 'bg-blue-600 text-white border border-transparent' : 'text-txt-dark-secondary hover:bg-white/5 hover:text-white border border-transparent' }}">
                <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 7a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Zm0 3h18M7 15h3" />
                </svg>
                <span>Transaksi</span>
            </a>

            <!-- Kembali ke Beranda -->
            <a href="{{ url('/') }}" 
            class="flex items-center space-x-3 px-4 py-2.5 rounded-xl font-medium text-sm transition-all duration-150 text-txt-dark-secondary hover:bg-white/5 hover:text-white border border-transparent group">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-txt-dark-secondary group-hover:text-white transition-colors">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918" />
                </svg>
                <span>Lihat situs utama</span>
            </a>

            <form action="{{ route('logout') }}" method="POST" class="pt-1">
                @csrf
                <button type="submit" class="admin-sidebar-logout flex w-full items-center gap-3 rounded-xl px-4 py-3 text-left text-sm font-semibold">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h4m6-14 7 7-7 7m7-7H9"/></svg>Keluar
                </button>
            </form>
        </nav>
    </aside>

    <!-- Content Wrapper -->
    <div class="flex-1 lg:pl-64 flex flex-col min-h-screen">
        
        <!-- Navbar Top Bar -->
        <header class="admin-topbar lg:hidden fixed top-0 right-0 left-0 lg:left-64 h-16 bg-white border-b border-slate-100 z-30 select-none">
            <div class="w-full h-full px-4 sm:px-6 lg:px-8 flex justify-between lg:justify-between items-center">
                
                <button x-ref="menu" aria-label="Buka navigasi admin" :aria-expanded="mobileSidebarOpen" @click="mobileSidebarOpen = true" class="p-2 text-slate-600 hover:text-slate-900 lg:hidden focus:outline-none rounded-xl hover:bg-slate-50 cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>

                <p class="truncate pl-3 text-sm font-medium text-slate-500">@yield('title', 'Panel Admin')</p>
            </div>
        </header>

        <main class="admin-main min-w-0 flex-1 pt-24 px-4 sm:px-6 lg:px-8 pb-8 lg:pt-8">
            <x-admin-flash />
            @yield('content')
        </main>
        
    </div>

</body>
</html>
