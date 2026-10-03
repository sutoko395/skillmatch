<!DOCTYPE html>
<html lang="id" class="h-full bg-bg-light overflow-y-scroll">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Dashboard') - SkillMatch Organizer</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

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
            background: rgba(100, 116, 139, .25);
            border-radius: 999px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: rgba(100, 116, 139, .4);
        }
    </style>
</head>

<body
    class="h-full overflow-x-hidden font-sans bg-bg-light text-txt-light-primary antialiased"
    x-data="{
        mobileSidebarOpen: false,
        userDropdownOpen: false
    }"
>

    <div
        x-show="mobileSidebarOpen"
        x-cloak
        x-transition:opacity.duration.200ms
        @click="mobileSidebarOpen = false"
        class="fixed inset-0 bg-black/40 backdrop-blur-sm z-40 lg:hidden"
    ></div>

    <aside
        x-cloak
        :class="mobileSidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
        class="fixed inset-y-0 left-0 w-72 bg-bg-brand text-txt-dark-primary flex flex-col h-screen shadow-xl select-none z-50 transition-transform duration-300 ease-in-out"
    >

        <div class="p-6 h-16 border-b border-white/10 flex items-center justify-between shrink-0">

            <a
                href="{{ route('organizer.dashboard') }}"
                class="flex items-center space-x-3 group cursor-pointer transition-opacity hover:opacity-90"
            >

                <div class="p-1.5 bg-indigo-600 rounded-lg shadow-inner flex items-center justify-center shrink-0">

                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="2"
                        stroke="currentColor"
                        class="w-5 h-5 text-white"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.375 9.375 0 0 0 2.625-.372M15 19.128v-3.375m0 3.375a9.375 9.375 0 0 1-7.5 0m7.5 0v-3.375m-7.5 3.375v-3.375m0 0a9.375 9.375 0 0 1 7.5 0M12 10.5a3.375 3.375 0 1 0 0-6.75 3.375 3.375 0 0 0 0 6.75Z"
                        />
                    </svg>

                </div>

                <div>
                    <h2 class="text-base font-bold tracking-wider leading-none text-white">
                        SKILLMATCH
                    </h2>

                    <p class="text-[10px] text-txt-dark-secondary font-medium tracking-widest uppercase mt-1">
                        Organizer Panel
                    </p>
                </div>

            </a>

            <button
                @click="mobileSidebarOpen = false"
                class="text-txt-dark-secondary hover:text-white lg:hidden focus:outline-none p-1 rounded-lg hover:bg-white/5 cursor-pointer"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="2.5"
                    stroke="currentColor"
                    class="w-5 h-5"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M6 18L18 6M6 6l12 12"
                    />
                </svg>
            </button>

        </div>

        <nav class="flex-1 p-4 space-y-1.5 overflow-y-auto hierarchy-scroll">

            <a
                href="{{ route('organizer.dashboard') }}"
                class="flex items-center space-x-3 px-4 py-3 rounded-xl font-medium text-sm transition-all duration-200 {{ Request::routeIs('organizer.dashboard') ? 'bg-white/10 text-white shadow-sm border border-white/5' : 'text-txt-dark-secondary hover:bg-white/5 hover:text-white border border-transparent' }} group"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.8"
                    stroke="currentColor"
                    class="w-5 h-5 opacity-90 transition-transform group-hover:scale-105"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75"
                    />
                </svg>

                <span>Dashboard</span>
            </a>

            <div
                x-data="{ open: {{ Request::routeIs('organizer.events.*') ? 'true' : 'false' }} }"
                class="space-y-1"
            >

                <button
                    type="button"
                    @click="open = !open"
                    class="w-full flex items-center justify-between px-4 py-3 rounded-xl font-medium text-sm transition-all duration-200 {{ Request::routeIs('organizer.events.*') ? 'bg-white/10 text-white shadow-sm border border-white/5' : 'text-txt-dark-secondary hover:bg-white/5 hover:text-white border border-transparent' }}"
                >

                    <span class="flex items-center space-x-3">

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.8"
                            stroke="currentColor"
                            class="w-5 h-5"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M6.75 3v2.25M17.25 3v2.25M3.75 9h16.5M5.25 5.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25A2.25 2.25 0 0 1 18.75 21H5.25A2.25 2.25 0 0 1 3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25Z"
                            />
                        </svg>

                        <span>Event</span>

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
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="m19.5 8.25-7.5 7.5-7.5-7.5"
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
                            href="#"
                            class="flex items-center px-3 py-2.5 text-sm transition-all duration-200 text-txt-dark-secondary hover:text-white font-medium"
                        >
                            Event Saya
                        </a>

                        <a
                            href="#"
                            class="flex items-center px-3 py-2.5 text-sm transition-all duration-200 text-txt-dark-secondary hover:text-white font-medium"
                        >
                            Buat Event
                        </a>

                    </div>

                </div>

            </div>

            <div
                x-data="{ open: {{ Request::routeIs('organizer.recruitment.*') ? 'true' : 'false' }} }"
                class="space-y-1"
            >

                <button
                    type="button"
                    @click="open = !open"
                    class="w-full flex items-center justify-between px-4 py-3 rounded-xl font-medium text-sm transition-all duration-200 {{ Request::routeIs('organizer.recruitment.*') ? 'bg-white/10 text-white shadow-sm border border-white/5' : 'text-txt-dark-secondary hover:bg-white/5 hover:text-white border border-transparent' }}"
                >

                    <span class="flex items-center space-x-3">

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.8"
                            stroke="currentColor"
                            class="w-5 h-5"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.375 9.375 0 0 0 2.625-.372M15 19.128v-3.375m0 3.375a9.375 9.375 0 0 1-7.5 0m7.5 0v-3.375m-7.5 3.375v-3.375m0 0a9.375 9.375 0 0 1 7.5 0M12 10.5a3.375 3.375 0 1 0 0-6.75 3.375 3.375 0 0 0 0 6.75Z"
                            />
                        </svg>

                        <span>Rekrutmen</span>

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
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="m19.5 8.25-7.5 7.5-7.5-7.5"
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
                            href="#"
                            class="flex items-center px-3 py-2.5 text-sm transition-all duration-200 text-txt-dark-secondary hover:text-white font-medium"
                        >
                            Posisi Volunteer
                        </a>

                        <a
                            href="#"
                            class="flex items-center px-3 py-2.5 text-sm transition-all duration-200 text-txt-dark-secondary hover:text-white font-medium"
                        >
                            Pendaftar
                        </a>

                        <a
                            href="#"
                            class="flex items-center px-3 py-2.5 text-sm transition-all duration-200 text-txt-dark-secondary hover:text-white font-medium"
                        >
                            Screening
                        </a>

                        <a
                            href="#"
                            class="flex items-center px-3 py-2.5 text-sm transition-all duration-200 text-txt-dark-secondary hover:text-white font-medium"
                        >
                            Assessment
                        </a>

                        <a
                            href="#"
                            class="flex items-center px-3 py-2.5 text-sm transition-all duration-200 text-txt-dark-secondary hover:text-white font-medium"
                        >
                            Volunteer Terpilih
                        </a>

                    </div>

                </div>

            </div>

            <div
                x-data="{ open: {{ Request::routeIs('organizer.execution.*') ? 'true' : 'false' }} }"
                class="space-y-1"
            >

                <button
                    type="button"
                    @click="open = !open"
                    class="w-full flex items-center justify-between px-4 py-3 rounded-xl font-medium text-sm transition-all duration-200 {{ Request::routeIs('organizer.execution.*') ? 'bg-white/10 text-white shadow-sm border border-white/5' : 'text-txt-dark-secondary hover:bg-white/5 hover:text-white border border-transparent' }}"
                >

                    <span class="flex items-center space-x-3">

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.8"
                            stroke="currentColor"
                            class="w-5 h-5"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M6.75 3v2.25M17.25 3v2.25M3.75 9h16.5M5.25 5.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25A2.25 2.25 0 0 1 18.75 21H5.25A2.25 2.25 0 0 1 3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25Z"
                            />
                        </svg>

                        <span>Pelaksanaan</span>

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
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="m19.5 8.25-7.5 7.5-7.5-7.5"
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
                            href="#"
                            class="flex items-center px-3 py-2.5 text-sm transition-all duration-200 text-txt-dark-secondary hover:text-white font-medium"
                        >
                            Jadwal Event
                        </a>

                        <a
                            href="#"
                            class="flex items-center px-3 py-2.5 text-sm transition-all duration-200 text-txt-dark-secondary hover:text-white font-medium"
                        >
                            Absensi
                        </a>

                        <a
                            href="#"
                            class="flex items-center px-3 py-2.5 text-sm transition-all duration-200 text-txt-dark-secondary hover:text-white font-medium"
                        >
                            Performance
                        </a>

                    </div>

                </div>

            </div>

            <a
                href="{{ route('organizer.profile.edit') }}"
                class="flex items-center space-x-3 px-4 py-3 rounded-xl font-medium text-sm transition-all duration-200 {{ Request::routeIs('organizer.profile.*') ? 'bg-white/10 text-white shadow-sm border border-white/5' : 'text-txt-dark-secondary hover:bg-white/5 hover:text-white border border-transparent' }} group"
            >

                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.8"
                    stroke="currentColor"
                    class="w-5 h-5"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.375 9.375 0 0 0 2.625-.372M15 19.128v-3.375m0 3.375a9.375 9.375 0 0 1-7.5 0m7.5 0v-3.375m-7.5 3.375v-3.375m0 0a9.375 9.375 0 0 1 7.5 0M12 10.5a3.375 3.375 0 1 0 0-6.75 3.375 3.375 0 0 0 0 6.75Z"
                    />
                </svg>

                <span>Profil Organisasi</span>

            </a>

            <a
                href="{{ url('/') }}"
                class="flex items-center space-x-3 px-4 py-2.5 rounded-xl font-medium text-sm transition-all duration-150 text-txt-dark-secondary hover:bg-white/5 hover:text-white border border-transparent group"
            >

                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.8"
                    stroke="currentColor"
                    class="w-5 h-5"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918"
                    />
                </svg>

                <span>Lihat Website Utama</span>

            </a>

        </nav>

    </aside>

    <div class="flex-1 lg:pl-72 flex flex-col min-h-screen">

        <header class="fixed top-0 right-0 left-0 lg:left-72 h-16 bg-white/80 backdrop-blur-md border-b border-slate-100 z-30 select-none">

            <div class="w-full h-full px-4 sm:px-6 lg:px-8 flex justify-between lg:justify-end items-center">

                <button
                    @click="mobileSidebarOpen = true"
                    class="p-2 text-slate-600 hover:text-slate-900 lg:hidden focus:outline-none rounded-xl hover:bg-slate-50 cursor-pointer"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="2"
                        stroke="currentColor"
                        class="w-6 h-6"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"
                        />
                    </svg>
                </button>

                <div class="relative">

                    <button
                        @click="userDropdownOpen = !userDropdownOpen"
                        @click.away="userDropdownOpen = false"
                        class="flex items-center space-x-3 hover:bg-slate-50 p-1.5 pr-3 rounded-xl transition duration-200 focus:outline-none group cursor-pointer"
                    >

                        <div class="w-8 h-8 shrink-0 rounded-full bg-indigo-600 flex items-center justify-center font-bold text-xs text-white tracking-wider shadow-sm group-hover:bg-indigo-700 transition duration-200">
                            {{ strtoupper(substr($user->name ?? 'OR', 0, 2)) }}
                        </div>

                        <div class="text-left hidden sm:block min-w-[100px]">

                            <p class="text-xs font-semibold text-slate-700 group-hover:text-slate-900 truncate">
                                {{ $user->name ?? 'Organizer' }}
                            </p>

                            <p class="text-[10px] font-medium text-indigo-600 -mt-0.5 uppercase tracking-wider">
                                Organizer
                            </p>

                        </div>

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="2.5"
                            stroke="currentColor"
                            class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200"
                            :class="userDropdownOpen ? 'rotate-180' : ''"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M19.5 8.25 12 15.75 4.5 8.25"
                            />
                        </svg>

                    </button>

                    <div
                        x-show="userDropdownOpen"
                        x-cloak
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                        x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
                        class="absolute right-0 mt-2 w-48 bg-white border border-slate-100 rounded-xl shadow-xl py-1.5 z-40"
                    >

                        <a
                            href="{{ route('organizer.profile.edit') }}"
                            class="flex items-center space-x-2.5 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition"
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
                                    d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.375 9.375 0 0 0-2.625-.372M15 19.128v-3.375m0 3.375a9.375 9.375 0 0 1-7.5 0m7.5 0v-3.375m-7.5 3.375v-3.375m0 0a9.375 9.375 0 0 1 7.5 0M12 10.5a3.375 3.375 0 1 0 0-6.75 3.375 3.375 0 0 0 0 6.75Z"
                                />
                            </svg>

                            <span>Profil Organisasi</span>
                        </a>

                        <form
                            action="{{ route('logout') }}"
                            method="POST"
                            class="w-full"
                        >
                            @csrf

                            <button
                                type="submit"
                                class="w-full flex items-center space-x-2.5 px-4 py-2 text-left text-xs font-semibold text-red-500 hover:bg-red-50/60 transition duration-150 cursor-pointer"
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
                                        d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"
                                    />
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