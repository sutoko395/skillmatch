<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

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
    class="min-h-screen overflow-x-hidden bg-slate-50 text-slate-900 antialiased"
    x-data="{
        userDropdownOpen: false,
        mobileSidebarOpen: false,
        desktop: window.innerWidth >= 1024
    }"
    @resize.window="desktop = window.innerWidth >= 1024"
    @keydown.escape.window="mobileSidebarOpen = false; userDropdownOpen = false"
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
        class="fixed inset-y-0 left-0 z-50 flex h-screen w-72 -translate-x-full flex-col bg-[#9a3412] text-white shadow-xl transition-transform duration-300 ease-in-out lg:translate-x-0"
    >

        <div class="flex h-16 shrink-0 items-center justify-between border-b border-white/10 px-6">

            <a
                href="{{ route('volunteer.activity.index') }}"
                class="group flex items-center gap-3"
            >

                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-orange-500 shadow-inner">
                    <span class="text-sm font-bold text-white">
                        S
                    </span>
                </div>

                <div>
                    <h2 class="text-base font-bold leading-none tracking-wider text-white group-hover:text-orange-200">
                        SKILLMATCH
                    </h2>

                    <p class="mt-1 text-[10px] font-medium uppercase tracking-widest text-orange-200/70">
                        Volunteer Panel
                    </p>
                </div>

            </a>

            <button
                type="button"
                aria-label="Tutup navigasi volunteer"
                @click="mobileSidebarOpen = false"
                class="rounded-lg p-1 text-orange-200/70 hover:bg-white/5 hover:text-white lg:hidden"
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

        <div class="border-b border-white/10 px-6 py-5">

            <p class="text-[10px] font-medium uppercase tracking-widest text-orange-200/70">
                Akun Volunteer
            </p>

            <p class="mt-2 truncate text-sm font-semibold text-white">
                {{ Auth::user()->name ?? 'Volunteer' }}
            </p>

            <p class="mt-1 truncate text-xs text-orange-100/70">
                {{ Auth::user()->email ?? '-' }}
            </p>

        </div>

        <nav
            class="hierarchy-scroll flex-1 space-y-1.5 overflow-y-auto p-4"
            aria-label="Navigasi volunteer"
        >

            <a
                href="{{ route('volunteer.activity.index') }}"
                class="flex items-center gap-3 rounded-xl border px-4 py-3 text-sm font-medium transition-all duration-200
                {{ Request::routeIs('volunteer.activity.*')
                    ? 'border-white/5 bg-white/10 text-white shadow-sm'
                    : 'border-transparent text-orange-100/70 hover:bg-white/5 hover:text-white' }}"
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
                    Dashboard
                </span>
            </a>

            <a
                href="{{ route('volunteer.profile.edit') }}"
                class="flex items-center gap-3 rounded-xl border px-4 py-3 text-sm font-medium transition-all duration-200
                {{ Request::routeIs('volunteer.profile.*')
                    ? 'border-white/5 bg-white/10 text-white shadow-sm'
                    : 'border-transparent text-orange-100/70 hover:bg-white/5 hover:text-white' }}"
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
                    Profil & Skill
                </span>
            </a>

            <div class="my-4 border-t border-white/10"></div>

            <a
                href="{{ url('/') }}"
                class="flex items-center gap-3 rounded-xl border border-transparent px-4 py-2.5 text-sm font-medium text-orange-100/70 transition-all duration-150 hover:bg-white/5 hover:text-white"
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
                    Lihat Website Utama
                </span>
            </a>

        </nav>

    </aside>

    <div class="flex min-h-screen flex-1 flex-col lg:pl-72">

        <header class="fixed left-0 right-0 top-0 z-30 h-16 border-b border-slate-100 bg-white/80 backdrop-blur-md lg:left-72">

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

                <div class="relative">

                    <button
                        type="button"
                        @click="userDropdownOpen = !userDropdownOpen"
                        @click.away="userDropdownOpen = false"
                        class="group flex items-center gap-3 rounded-xl p-1.5 pr-3 transition hover:bg-slate-50"
                    >

                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-orange-500 text-xs font-bold tracking-wider text-white">
                            {{ strtoupper(substr(Auth::user()->name ?? 'VO', 0, 2)) }}
                        </div>

                        <div class="hidden min-w-[100px] text-left sm:block">

                            <p class="truncate text-xs font-semibold text-slate-700">
                                {{ Auth::user()->name ?? 'Volunteer' }}
                            </p>

                            <p class="text-[10px] font-medium uppercase tracking-wider text-orange-600">
                                Volunteer
                            </p>

                        </div>

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="2.5"
                            stroke="currentColor"
                            class="h-3.5 w-3.5 text-slate-400 transition-transform"
                            :class="userDropdownOpen ? 'rotate-180' : ''"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="m19.5 8.25-7.5 7.5-7.5-7.5"
                            />
                        </svg>

                    </button>

                    <div
                        x-show="userDropdownOpen"
                        x-cloak
                        x-transition
                        class="absolute right-0 z-40 mt-2 w-48 rounded-xl border border-slate-100 bg-white py-1.5 shadow-xl"
                    >

                        <a
                            href="{{ route('volunteer.profile.edit') }}"
                            class="flex items-center px-4 py-2.5 text-xs font-medium text-slate-700 hover:bg-slate-50"
                        >
                            Profil & Skill
                        </a>

                        <form action="{{ route('logout') }}" method="POST">
                            @csrf

                            <button
                                type="submit"
                                class="flex w-full items-center px-4 py-2.5 text-left text-xs font-semibold text-red-500 hover:bg-red-50"
                            >
                                Keluar Aplikasi
                            </button>
                        </form>

                    </div>

                </div>

            </div>

        </header>

        <main class="flex-1 px-4 pb-8 pt-24 sm:px-6 lg:px-8">
            @include('components.flash')

            @yield('content')
        </main>

    </div>

    @stack('scripts')

</body>
</html>