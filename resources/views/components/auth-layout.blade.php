@props([
    'title',
    'intro' => null,
    'register' => false,
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} - SkillMatch</title>
    @include('layouts.fonts')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="h-screen overflow-hidden bg-slate-50">
    <div class="flex h-screen w-full flex-col overflow-hidden lg:flex-row">

        <section class="hidden h-full w-[46%] flex-col justify-between overflow-hidden bg-[#211e54] px-10 py-8 text-white lg:flex xl:px-14">
            <div>
                <a href="{{ url('/') }}" class="inline-flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-500 text-lg font-bold">
                        S
                    </span>
                    <span class="text-xl font-bold tracking-tight">
                        SkillMatch
                    </span>
                </a>
            </div>

            <div class="max-w-xl">
                <p class="mb-4 text-xs font-bold uppercase tracking-[0.25em] text-indigo-300">
                    KETERAMPILAN MENJADI KONTRIBUSI
                </p>

                <h1 class="max-w-lg text-4xl font-bold leading-tight xl:text-5xl">
                    Ruang untuk kemampuan Anda bertemu kesempatan.
                </h1>

                <p class="mt-5 max-w-lg text-base leading-7 text-indigo-100">
                    Jelajahi kegiatan relawan, kenali kebutuhan posisi,
                    dan persiapkan kontribusi bersama SkillMatch.
                </p>

                <div class="relative mt-10 h-32">
                    <div class="absolute left-16 top-8 h-20 w-20 rounded-full border border-indigo-400/30"></div>
                    <div class="absolute left-24 top-2 h-20 w-20 rounded-full border border-indigo-400/30"></div>

                    <div class="absolute left-0 top-4 rounded-xl bg-indigo-500 px-6 py-3 text-sm font-semibold shadow-lg">
                        Keterampilan
                    </div>

                    <div class="absolute left-24 top-20 rounded-xl bg-indigo-700 px-6 py-3 text-sm font-semibold shadow-lg">
                        Kontribusi
                    </div>

                    <div class="absolute left-48 top-10 rounded-xl bg-white px-6 py-3 text-sm font-semibold text-[#211e54] shadow-lg">
                        Kesempatan
                    </div>
                </div>
            </div>

            <div>
                <div class="mb-5 h-px w-full bg-white/15"></div>

                <p class="text-sm font-semibold text-white">
                    Skill, waktu, dan lokasi.
                </p>

                <p class="mt-1 text-sm text-indigo-200">
                    Tiga hal untuk membantu Anda memahami kegiatan yang ingin diikuti.
                </p>

                <a href="{{ url('/') }}" class="mt-5 inline-flex items-center gap-2 text-sm font-semibold text-white hover:text-indigo-200">
                    Jelajahi event terlebih dahulu
                    <span>→</span>
                </a>
            </div>
        </section>

        <main class="flex h-full min-h-0 flex-1 flex-col overflow-hidden">
            <header class="flex h-20 shrink-0 items-center justify-between px-6 sm:px-8 xl:px-12">
                <a href="{{ url('/') }}" class="flex items-center gap-3 lg:hidden">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-600 text-sm font-bold text-white">
                        S
                    </span>
                    <span class="text-lg font-bold text-indigo-700">
                        SkillMatch
                    </span>
                </a>

                <div class="ml-auto">
                    <a href="{{ url('/') }}" class="text-sm font-medium text-slate-700 hover:text-indigo-700">
                        ← Kembali ke beranda
                    </a>
                </div>
            </header>

            <div class="flex min-h-0 flex-1 items-center justify-center overflow-hidden px-5 pb-5 sm:px-8 lg:px-12">
                <div class="w-full max-w-2xl">
                    <div class="mb-5">
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-indigo-600">
                            {{ $register ? 'MULAI BERSAMA SKILLMATCH' : 'SELAMAT DATANG KEMBALI' }}
                        </p>

                        <h2 class="mt-2 text-3xl font-bold tracking-tight text-slate-950">
                            {{ $title }}
                        </h2>

                        @if($intro)
                            <p class="mt-2 text-sm leading-6 text-slate-600">
                                {{ $intro }}
                            </p>
                        @endif
                    </div>

                    {{ $slot }}
                </div>
            </div>
        </main>
    </div>
</body>
</html>
