<!DOCTYPE html>
<html lang="id" class="h-full bg-bg-light overflow-y-scroll" id="html-root">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>@yield('title', 'Dashboard') - SkillMatch Admin</title>

    <!-- Font Inter Standar Industri -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])
    
    <style>
        html { 
            scrollbar-gutter: stable; 
            font-family: 'Poppins', sans-serif;
        }

        body:not(.ready) {
            display: none !important;
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

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        document.body.classList.add('ready');
    });
    </script>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
</head>
<body class="h-full overflow-x-hidden font-sans bg-bg-light text-txt-light-primary antialiased" x-data="{ userDropdownOpen: false, mobileSidebarOpen: false }">

    <!-- Overlay Mobile -->
    <div x-show="mobileSidebarOpen" 
         x-cloak
         x-transition:opacity.duration.200ms
         @click="mobileSidebarOpen = false"
         class="fixed inset-0 bg-black/40 backdrop-blur-sm z-40 lg:hidden">
    </div>

    <!-- Sidebar Utama -->
    <aside x-cloak
        :class="mobileSidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
        class="fixed inset-y-0 left-0 w-64 bg-bg-brand text-txt-dark-primary flex flex-col h-screen shadow-xl select-none z-50 transition-transform duration-300 ease-in-out">
        
        <!-- Logo Sidebar -->
        <div class="p-6 h-16 border-b border-white/10 flex items-center justify-between shrink-0">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-3 group cursor-pointer transition-opacity hover:opacity-90">
                <div class="p-1.5 bg-indigo-600 rounded-lg shadow-inner flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-white">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a5.97 5.97 0 00-.942 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-base font-bold tracking-wider leading-none text-white transition-colors group-hover:text-indigo-300">SKILLMATCH</h2>
                    <p class="text-[10px] text-txt-dark-secondary font-medium tracking-widest uppercase mt-1">Management Panel</p>
                </div>
            </a>

            <button @click="mobileSidebarOpen = false" class="text-txt-dark-secondary hover:text-white lg:hidden focus:outline-none p-1 rounded-lg hover:bg-white/5 cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Navigation Menu -->
        <nav class="flex-1 p-4 space-y-1.5 overflow-y-auto hierarchy-scroll">
            
            <a href="{{ route('admin.dashboard') }}" 
            class="flex items-center space-x-3 px-4 py-3 rounded-xl font-medium text-sm transition-all duration-200 {{ Request::routeIs('admin.dashboard') ? 'bg-white/10 text-white shadow-sm border border-white/5' : 'text-txt-dark-secondary hover:bg-white/5 hover:text-white border border-transparent' }} group">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 opacity-90 transition-transform group-hover:scale-105">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                </svg>
                <span>Dashboard</span>
            </a>

            <!-- Master Data -->
            <a href="{{ route('admin.master-data') }}" 
            class="flex items-center space-x-3 px-4 py-2.5 rounded-xl font-medium text-sm transition-all duration-150 {{ Request::routeIs('admin.master-data') ? 'bg-white/10 text-white shadow-sm border border-white/5' : 'text-txt-dark-secondary hover:bg-white/5 hover:text-white border border-transparent' }} group">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-txt-dark-secondary group-hover:text-white transition-colors">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 5.625c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125" />
                </svg>
                <span>Master Data Skill & Event</span>
            </a>

            <!-- Kembali ke Beranda -->
            <a href="{{ url('/') }}" 
            class="flex items-center space-x-3 px-4 py-2.5 rounded-xl font-medium text-sm transition-all duration-150 text-txt-dark-secondary hover:bg-white/5 hover:text-white border border-transparent group">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-txt-dark-secondary group-hover:text-white transition-colors">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918" />
                </svg>
                <span>Lihat Website Utama</span>
            </a>

        </nav>
    </aside>

    <!-- Content Wrapper -->
    <div class="flex-1 lg:pl-64 flex flex-col min-h-screen">    
        
        <!-- Navbar Top Bar -->
        <header class="fixed top-0 right-0 left-0 lg:left-64 h-16 bg-white/80 backdrop-blur-md border-b border-slate-100 z-30 select-none">
            <div class="w-full h-full px-4 sm:px-6 lg:px-8 flex justify-between lg:justify-end items-center">
                
                <button @click="mobileSidebarOpen = true" class="p-2 text-slate-600 hover:text-slate-900 lg:hidden focus:outline-none rounded-xl hover:bg-slate-50 cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>

                <!-- Profile Dropdown -->
                <div class="relative">
                    <button @click="userDropdownOpen = !userDropdownOpen" @click.away="userDropdownOpen = false"
                            class="flex items-center space-x-3 hover:bg-slate-50 p-1.5 pr-3 rounded-xl transition duration-200 focus:outline-none group cursor-pointer">
                        
                        <div class="w-8 h-8 shrink-0 rounded-full bg-indigo-600 flex items-center justify-center font-bold text-xs text-white tracking-wider shadow-sm group-hover:bg-indigo-700 transition duration-200">
                            {{ strtoupper(substr(Auth::user()->name ?? 'AD', 0, 2)) }}
                        </div>
                        
                        <div class="text-left hidden sm:block min-w-[80px]">
                            <p class="text-xs font-semibold text-slate-700 group-hover:text-slate-900 truncate">{{ Auth::user()->name ?? 'Administrator' }}</p>
                            <p class="text-[10px] font-medium text-indigo-600 -mt-0.5 uppercase tracking-wider">Administrator</p>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" 
                             class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" :class="userDropdownOpen ? 'rotate-180' : ''">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>

                    <div x-show="userDropdownOpen"
                         x-cloak
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                         x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
                         class="absolute right-0 mt-2 w-48 bg-white border border-slate-100 rounded-xl shadow-xl py-1.5 z-40">
                        
                        <form action="{{ route('logout') }}" method="POST" class="w-full">
                            @csrf
                            <button type="submit" 
                                    class="w-full flex items-center space-x-2.5 px-4 py-2 text-left text-xs font-semibold text-red-500 hover:bg-red-50/60 transition duration-150 cursor-pointer">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                                </svg>
                                <span>Keluar Aplikasi</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 pt-24 px-4 sm:px-6 lg:px-8 pb-8">
            @yield('content')
        </main>
        
    </div>

</body>
</html>