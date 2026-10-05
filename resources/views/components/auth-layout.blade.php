@props(['title', 'intro', 'register' => false])
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} &middot; SkillMatch</title>
    @include('layouts.fonts')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 font-sans text-slate-900 antialiased">
    <a href="#auth-form" class="sr-only focus:not-sr-only">Lewati ke formulir</a>
    <div class="mx-auto flex min-h-screen max-w-7xl flex-col px-4 py-6 sm:px-8 sm:py-8">
        <header class="mb-6 flex flex-wrap items-center justify-between gap-4">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-3 text-xl font-bold tracking-tight text-indigo-700"><span aria-hidden="true" class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-600 text-base text-white">S</span>SkillMatch</a>
            <a href="{{ route('home') }}" class="inline-flex min-h-[44px] items-center gap-2 text-sm font-medium text-slate-600 hover:text-indigo-700"><span aria-hidden="true">&larr;</span> Kembali ke beranda</a>
        </header>
        <main class="my-auto grid overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-xl shadow-indigo-950/5 lg:grid-cols-2">
            <aside aria-label="Tentang SkillMatch" class="relative hidden flex-col justify-between overflow-hidden bg-indigo-950 p-12 text-white lg:flex">
                <div aria-hidden="true" class="absolute -bottom-40 -left-32 h-96 w-96 rounded-full bg-indigo-700/50 blur-3xl"></div>
                <div class="relative"><p class="text-xs font-semibold uppercase tracking-[0.2em] text-indigo-300">KETERAMPILAN MENJADI KONTRIBUSI</p><h2 class="mt-6 text-4xl font-bold leading-tight tracking-tight">{{ $register ? 'Mulai langkah kecil. Hadirkan kontribusi berarti.' : 'Ruang untuk kemampuan Anda bertemu kesempatan.' }}</h2><p class="mt-5 max-w-md text-base leading-relaxed text-indigo-100">{{ $register ? 'Kenalkan diri Anda sebagai Volunteer atau siapkan kegiatan bersama organisasi Anda.' : 'Jelajahi kegiatan relawan, kenali kebutuhan posisi, dan persiapkan kontribusi bersama SkillMatch.' }}</p></div>
                <div aria-hidden="true" class="relative my-10 flex h-36 items-center justify-center"><div class="absolute h-36 w-36 rounded-full border border-indigo-400/40"></div><div class="absolute h-24 w-24 rounded-full border border-indigo-400/40"></div><span class="absolute -translate-x-24 -translate-y-8 rounded-xl bg-indigo-500 px-5 py-3 text-sm font-semibold shadow-lg">Keterampilan</span><span class="absolute translate-x-20 translate-y-2 rounded-xl bg-white px-5 py-3 text-sm font-semibold text-indigo-900 shadow-lg">Kesempatan</span><span class="absolute -translate-x-6 translate-y-14 rounded-xl bg-indigo-800 px-5 py-3 text-sm font-semibold text-indigo-100">Kontribusi</span></div>
                <div class="relative border-t border-indigo-400/30 pt-6"><p class="text-sm font-semibold text-indigo-100">Skill, waktu, dan lokasi.</p><p class="mt-2 text-sm leading-relaxed text-indigo-200">Tiga hal untuk membantu Anda memahami kegiatan yang ingin diikuti.</p><a href="{{ route('events.index') }}" class="mt-5 inline-flex min-h-[44px] items-center gap-3 text-sm font-semibold text-white">Jelajahi event terlebih dahulu <span aria-hidden="true">&rarr;</span></a></div>
            </aside>
            <section id="auth-form" aria-labelledby="auth-title" class="px-6 py-9 sm:px-10 sm:py-12 lg:px-12">
                <p class="mb-3 text-xs font-bold uppercase tracking-widest text-indigo-700">{{ $register ? 'BERGABUNG DENGAN SKILLMATCH' : 'SELAMAT DATANG KEMBALI' }}</p>
                <h1 id="auth-title" class="text-3xl font-bold tracking-tight">{{ $title }}</h1>
                <p class="mb-8 mt-3 text-sm leading-relaxed text-slate-600">{{ $intro }}</p>
                {{ $slot }}
            </section>
        </main>
        <footer class="mt-6 flex flex-wrap justify-between gap-3 text-xs text-slate-500"><p>&copy; {{ date('Y') }} SkillMatch &middot; Kelompok 5 Politeknik Negeri Malang</p><p>Relawan dan Organizer dalam satu ruang kolaborasi.</p></footer>
    </div>
</body>
</html>
