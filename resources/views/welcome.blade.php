@extends('layouts.public')
@section('title', 'Relawan, keterampilan, dan kontribusi')
@section('description', 'Kenali SkillMatch: platform rekrutmen relawan berbasis keterampilan, waktu, dan lokasi. Jelajahi event dan persiapkan kontribusi Anda.')
@section('content')
<section aria-labelledby="hero-title" class="relative overflow-hidden rounded-3xl bg-indigo-950 px-6 py-12 text-white sm:px-10 lg:px-12 lg:py-16">
    <div aria-hidden="true" class="pointer-events-none absolute -right-20 -top-32 h-96 w-96 rounded-full bg-indigo-700/40 blur-3xl"></div>
    <div class="relative grid items-center gap-12 lg:grid-cols-5">
        <div class="lg:col-span-3">
            <p class="inline-flex items-center gap-2 rounded-full border border-indigo-400/40 bg-indigo-900 px-3 py-2 text-xs font-semibold tracking-wide text-indigo-100"><span aria-hidden="true" class="h-2 w-2 rounded-full bg-emerald-300"></span>RUANG BERTEMU RELAWAN DAN ORGANIZER</p>
            <h1 id="hero-title" class="mt-6 max-w-2xl text-4xl font-extrabold leading-tight tracking-tight sm:text-5xl">Hubungkan Talent Relawan dengan <span class="text-indigo-300">Event Terbaik.</span></h1>
            <p class="mt-5 max-w-xl text-base leading-relaxed text-indigo-100 sm:text-lg">SkillMatch membantu Anda mengenali peluang kontribusi dari keterampilan, waktu, dan lokasi. Jelajahi kegiatan, kenali kebutuhan posisi, dan persiapkan profil relawan Anda.</p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ route('events.index') }}" class="inline-flex min-h-[48px] items-center justify-center gap-3 rounded-xl bg-white px-6 py-3 font-semibold text-indigo-900 transition hover:bg-indigo-100">Jelajahi Event <span aria-hidden="true">&rarr;</span></a>
                @auth
                    <a href="{{ route('dashboard') }}" class="inline-flex min-h-[48px] items-center justify-center rounded-xl border border-indigo-400 px-6 py-3 font-semibold text-white hover:bg-indigo-900">{{ auth()->user()->role === 'admin' ? 'Buka Panel Admin' : 'Buka Aktivitas Saya' }}</a>
                @else
                    <a href="{{ route('register') }}" class="inline-flex min-h-[48px] items-center justify-center rounded-xl border border-indigo-400 px-6 py-3 font-semibold text-white hover:bg-indigo-900">Mulai Berkontribusi</a>
                @endauth
            </div>
            <p class="mt-5 text-sm text-indigo-200">Untuk individu yang ingin berkontribusi dan organisasi yang membutuhkan relawan.</p>
        </div>
        <div class="lg:col-span-2">
            <svg viewBox="0 0 420 320" role="img" aria-labelledby="community-title" class="mx-auto w-full max-w-sm">
                <title id="community-title">Ilustrasi relawan dengan beragam keterampilan terhubung dalam satu komunitas</title>
                <circle cx="210" cy="160" r="135" fill="#312e81"/>
                <circle cx="210" cy="160" r="105" fill="none" stroke="#818cf8" stroke-width="2" stroke-dasharray="6 10"/>
                <path d="M100 95L210 160L330 100M95 245L210 160L330 245" fill="none" stroke="#a5b4fc" stroke-width="3"/>
                <rect x="148" y="98" width="124" height="124" rx="30" fill="#eef2ff"/>
                <path d="M178 156l22 22 43-46" fill="none" stroke="#4f46e5" stroke-width="12" stroke-linecap="round" stroke-linejoin="round"/>
                <g fill="#c7d2fe"><circle cx="100" cy="76" r="23"/><path d="M59 132c0-42 82-42 82 0v10H59z"/></g>
                <g fill="#6ee7b7"><circle cx="330" cy="80" r="23"/><path d="M289 136c0-42 82-42 82 0v10h-82z"/></g>
                <g fill="#fcd34d"><circle cx="95" cy="224" r="23"/><path d="M54 280c0-42 82-42 82 0v10H54z"/></g>
                <g fill="#f9a8d4"><circle cx="330" cy="224" r="23"/><path d="M289 280c0-42 82-42 82 0v10h-82z"/></g>
            </svg>
            <p class="mt-3 text-center text-sm font-medium text-indigo-100">Beragam keterampilan. Satu tujuan: berkontribusi.</p>
        </div>
    </div>
</section>
<form method="GET" action="{{ route('events.index') }}" class="mx-auto flex max-w-4xl flex-col gap-3 rounded-b-2xl border border-slate-200 bg-white p-5 shadow-sm sm:flex-row sm:items-end sm:p-6">
    <div class="flex-1"><label for="home-search" class="block text-sm font-semibold text-slate-900">Kegiatan apa yang ingin Anda ikuti?</label><input id="home-search" name="search" type="search" maxlength="100" placeholder="Cari judul event..." class="mt-2 w-full rounded-xl border-slate-300 placeholder:text-slate-500"></div>
    <button type="submit" class="min-h-[44px] rounded-xl bg-indigo-600 px-7 py-3 text-sm font-semibold text-white hover:bg-indigo-700">Temukan Event</button>
</form>
<section aria-labelledby="about-title" class="py-16 sm:py-20">
    <div class="grid gap-8 lg:grid-cols-2 lg:gap-16">
        <div><p class="text-xs font-bold uppercase tracking-widest text-indigo-700">KENALI SKILLMATCH</p><h2 id="about-title" class="mt-3 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">Kontribusi yang dimulai dari apa yang Anda bisa.</h2></div>
        <div class="space-y-4 text-base leading-relaxed text-slate-600"><p>Setiap kegiatan membutuhkan orang dengan keterampilan dan waktu yang berbeda. SkillMatch mempertemukan informasi kebutuhan Organizer dengan profil Volunteer agar peluang kontribusi lebih mudah dipahami.</p><p>Anda bisa melihat posisi, persyaratan, lokasi, dan jadwal sebelum menentukan kegiatan yang ingin diikuti. Organizer dapat menyiapkan kebutuhan relawan secara terstruktur dalam satu tempat.</p></div>
    </div>
    <div class="mt-10 grid gap-5 md:grid-cols-3">
        @foreach([
            ['01', 'Keterampilan yang relevan', 'Kenalkan skill dan level Anda melalui profil. Lihat keterampilan yang dibutuhkan setiap posisi di detail event.'],
            ['02', 'Waktu yang jelas', 'Isi jadwal ketersediaan dan baca interval tugas. Persiapkan komitmen waktu sebelum memilih kegiatan.'],
            ['03', 'Lokasi yang terjangkau', 'Lengkapi kota pada profil dan gunakan filter kota untuk menjelajahi kegiatan di lokasi yang Anda pilih.'],
        ] as [$number, $title, $description])
            <article class="rounded-2xl border border-slate-200 bg-white p-7"><span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-sm font-bold text-indigo-700">{{ $number }}</span><h3 class="mt-5 text-lg font-bold text-slate-900">{{ $title }}</h3><p class="mt-3 text-sm leading-relaxed text-slate-600">{{ $description }}</p></article>
        @endforeach
    </div>
</section>
<section id="event-tersedia" aria-labelledby="events-title" class="border-t border-slate-200 py-14">
    <div class="flex flex-wrap items-end justify-between gap-5"><div><p class="text-xs font-bold uppercase tracking-widest text-indigo-700">TEMUKAN PELUANG KONTRIBUSI</p><h2 id="events-title" class="mt-3 text-3xl font-bold text-slate-900">Event untuk dijelajahi</h2><p class="mt-3 max-w-2xl text-slate-600">Kegiatan yang telah dipublikasikan, dengan periode pendaftaran dan batas lamaran yang masih tersedia.</p></div><a href="{{ route('events.index') }}" class="inline-flex min-h-[44px] items-center gap-3 font-semibold text-indigo-700">Lihat semua event <span aria-hidden="true">&rarr;</span></a></div>
    @if($events->isNotEmpty())
        <div class="mt-8 grid gap-5 md:grid-cols-2 lg:grid-cols-3">@foreach($events as $event)<x-event-card :event="$event" />@endforeach</div>
    @else
        <div class="mt-8 rounded-2xl border border-dashed border-indigo-200 bg-indigo-50/50 px-6 py-12 text-center"><span aria-hidden="true" class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-white text-xl text-indigo-700">&rarr;</span><h3 class="mt-5 text-xl font-bold text-slate-900">Peluang berikutnya sedang dipersiapkan</h3><p class="mx-auto mt-3 max-w-lg text-sm leading-relaxed text-slate-600">Belum ada event dengan pendaftaran terbuka saat ini. Anda tetap bisa menjelajahi katalog dan menyiapkan profil untuk kegiatan berikutnya.</p><a href="{{ route('events.index') }}" class="mt-5 inline-flex min-h-[44px] items-center rounded-xl bg-white px-5 py-3 text-sm font-semibold text-indigo-700 shadow-sm hover:bg-indigo-100">Buka Katalog Event</a></div>
    @endif
    <p class="mt-5 text-sm leading-relaxed text-slate-600">Detail kegiatan sudah dapat dijelajahi. Pengajuan lamaran, assessment, dan pencocokan relawan masih dalam pengembangan.</p>
</section>
<section id="cara-kerja" aria-labelledby="journey-title" class="rounded-3xl bg-white p-6 ring-1 ring-slate-200 sm:p-10">
    <div class="max-w-2xl"><p class="text-xs font-bold uppercase tracking-widest text-indigo-700">DUA PERAN, SATU RUANG KOLABORASI</p><h2 id="journey-title" class="mt-3 text-3xl font-bold text-slate-900">Mulai dari peran Anda.</h2><p class="mt-3 leading-relaxed text-slate-600">Volunteer membawa keterampilan. Organizer menyiapkan kegiatan dan kebutuhan relawan.</p></div>
    <div class="mt-9 grid gap-8 lg:grid-cols-2">
        <article class="rounded-2xl bg-slate-50 p-6 sm:p-8"><p class="text-sm font-bold text-indigo-700">UNTUK VOLUNTEER</p><h3 class="mt-2 text-2xl font-bold text-slate-900">Temukan ruang untuk kontribusi Anda.</h3><ol class="mt-6 space-y-5">
            @foreach([['Buat akun dan lengkapi profil', 'Tambahkan kota, skill, level, dan jadwal ketersediaan Anda.'], ['Jelajahi kebutuhan relawan', 'Baca detail posisi, persyaratan, serta waktu pelaksanaan kegiatan.'], ['Persiapkan langkah berikutnya', 'Alur lamaran, assessment, dan hasil seleksi akan tersedia setelah integrasi selesai.']] as [$title, $description])
            <li class="flex gap-4"><span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-white text-xs font-bold text-indigo-700">{{ $loop->iteration }}</span><div><h4 class="font-semibold text-slate-900">{{ $title }}</h4><p class="mt-1 text-sm leading-relaxed text-slate-600">{{ $description }}</p></div></li>
            @endforeach
        </ol></article>
        <article class="rounded-2xl bg-indigo-50 p-6 sm:p-8"><p class="text-sm font-bold text-indigo-700">UNTUK ORGANIZER</p><h3 class="mt-2 text-2xl font-bold text-slate-900">Susun tim relawan dengan kebutuhan yang jelas.</h3><ol class="mt-6 space-y-5">
            @foreach([['Lengkapi profil organisasi', 'Daftarkan akun Organizer dan ikuti proses verifikasi organisasi.'], ['Rancang kegiatan dan posisi', 'Organizer aktif dapat menyiapkan event, skill, jadwal, persyaratan, dan paket.'], ['Ajukan ketika seluruh syarat siap', 'Publikasi memerlukan moderasi, paket yang aktif, dan assessment yang sah. Integrasi assessment masih dikembangkan.']] as [$title, $description])
            <li class="flex gap-4"><span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-white text-xs font-bold text-indigo-700">{{ $loop->iteration }}</span><div><h4 class="font-semibold text-slate-900">{{ $title }}</h4><p class="mt-1 text-sm leading-relaxed text-slate-600">{{ $description }}</p></div></li>
            @endforeach
        </ol></article>
    </div>
    <div class="mt-8 flex flex-wrap items-center justify-between gap-4 border-t border-slate-200 pt-6"><p class="text-sm text-slate-600">Satu akun, profil yang jelas, dan informasi kegiatan yang terstruktur.</p><a href="{{ auth()->check() ? route('dashboard') : route('register') }}" class="inline-flex min-h-[44px] items-center gap-3 font-semibold text-indigo-700">{{ auth()->check() ? 'Lanjutkan ke akun saya' : 'Daftar sebagai Volunteer atau Organizer' }} <span aria-hidden="true">&rarr;</span></a></div>
</section>
<section aria-labelledby="faq-title" class="grid gap-8 py-16 lg:grid-cols-3">
    <div><p class="text-xs font-bold uppercase tracking-widest text-indigo-700">SEBELUM MEMULAI</p><h2 id="faq-title" class="mt-3 text-3xl font-bold text-slate-900">Yang sering ditanyakan.</h2><p class="mt-4 text-sm leading-relaxed text-slate-600">Kenali apa yang sudah tersedia dan langkah yang dapat Anda lakukan sekarang.</p></div>
    <div class="space-y-3 lg:col-span-2">
        @foreach([
            ['Apa itu SkillMatch?', 'SkillMatch adalah platform rekrutmen relawan berbasis keterampilan. Volunteer dapat menyiapkan profil dan menjelajahi kebutuhan kegiatan, sedangkan Organizer mengelola informasi event serta posisi relawan.'],
            ['Apakah saya perlu akun untuk melihat event?', 'Katalog dan detail event dapat dilihat tanpa login. Buat akun untuk menyimpan profil skill, kota, dan jadwal ketersediaan.'],
            ['Apakah saya sudah bisa melamar atau mendapat rekomendasi otomatis?', 'Pengajuan lamaran, assessment, matching, dan notifikasi masih dalam pengembangan. Saat ini Anda dapat menyiapkan profil dan mempelajari posisi di katalog.'],
            ['Mengapa event saya belum muncul di katalog?', 'Event perlu memenuhi syarat moderasi, publikasi, paket, dan assessment. Organizer harus aktif dan terverifikasi. Draft dan event yang belum dipublikasikan hanya tersedia bagi pemilik serta admin yang berwenang.'],
            ['Apakah pembayaran sudah menggunakan uang nyata?', 'Pembayaran saat ini menggunakan Midtrans Sandbox untuk simulasi. Jangan melakukan pembayaran nyata melalui alur demo ini.'],
        ] as [$question, $answer])
        <details class="group rounded-xl border border-slate-200 bg-white"><summary class="flex min-h-[56px] cursor-pointer list-none items-center justify-between gap-4 p-5 font-semibold text-slate-900">{{ $question }}<span aria-hidden="true" class="text-xl text-indigo-600 group-open:rotate-45">+</span></summary><p class="px-5 pb-5 text-sm leading-relaxed text-slate-600">{{ $answer }}</p></details>
        @endforeach
    </div>
</section>
<section aria-labelledby="cta-title" class="flex flex-col items-start justify-between gap-6 rounded-3xl bg-indigo-600 p-8 text-white sm:p-10 lg:flex-row lg:items-center"><div><h2 id="cta-title" class="text-2xl font-bold sm:text-3xl">Kontribusi berikutnya dimulai dari Anda.</h2><p class="mt-3 max-w-xl leading-relaxed text-indigo-100">Kenalkan kemampuan Anda atau mulai menyiapkan kegiatan bersama organisasi Anda.</p></div><a href="{{ auth()->check() ? route('dashboard') : route('register') }}" class="inline-flex min-h-[48px] shrink-0 items-center justify-center rounded-xl bg-white px-6 py-3 font-semibold text-indigo-800 hover:bg-indigo-50">{{ auth()->check() ? 'Buka Akun Saya' : 'Buat Akun SkillMatch' }}</a></section>
<footer class="mt-12 border-t border-slate-200 py-8"><div class="flex flex-wrap items-start justify-between gap-6"><div><a href="{{ route('home') }}" class="text-xl font-bold text-indigo-700">SkillMatch</a><p class="mt-2 max-w-sm text-sm leading-relaxed text-slate-600">Keterampilan, waktu, dan kesempatan untuk berkontribusi.</p></div><nav aria-label="Navigasi footer" class="flex flex-wrap gap-x-6 gap-y-3 text-sm font-medium text-slate-600"><a href="{{ route('events.index') }}" class="hover:text-indigo-700">Katalog Event</a><a href="#cara-kerja" class="hover:text-indigo-700">Cara Kerja</a>@guest<a href="{{ route('login') }}" class="hover:text-indigo-700">Masuk</a><a href="{{ route('register') }}" class="hover:text-indigo-700">Daftar</a>@endguest</nav></div><p class="mt-8 text-xs text-slate-500">&copy; {{ date('Y') }} SkillMatch Volunteer &middot; Kelompok 5 Politeknik Negeri Malang</p></footer>
@endsection
