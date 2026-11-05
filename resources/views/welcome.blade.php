@extends('layouts.public')
@section('title', 'Relawan, keterampilan, dan kontribusi')
@section('description', 'Temukan kegiatan relawan berdasarkan keterampilan, waktu, dan lokasi. Mulai kontribusi bersama SkillMatch.')
@section('content')
<section class="eb-hero" aria-labelledby="hero-title">
    <div class="eb-container eb-hero-inner">
        <p class="eb-eyebrow">KETERAMPILANMU PUNYA TEMPAT.</p>
        <h1 id="hero-title">Punya skill.<br>Bikin berarti.</h1>
        <p class="eb-hero-description">Temukan kegiatan yang membutuhkan apa yang kamu bisa.<br class="hidden sm:block"> Sesuaikan dengan skill, waktu, dan kotamu.</p>
        <a href="{{ route('events.index') }}" class="eb-button">Jelajahi event <span aria-hidden="true">↗︎</span></a>
        <a href="#cara-kerja" class="eb-hero-link">Kenali cara kerjanya ↓</a>
        <img class="eb-character eb-character-plant" src="{{ asset('images/electric-blue/volunteer-plant.svg') }}" alt="" width="280" height="518">
        <img class="eb-character eb-character-megaphone" src="{{ asset('images/electric-blue/volunteer-megaphone.svg') }}" alt="" width="393" height="515">
        <span class="eb-skill-stamp" aria-hidden="true">BAWA<br>SKILLMU!</span>
    </div>
</section>
<div class="eb-ribbon" aria-label="Beragam keterampilan untuk berkontribusi">KOMUNIKASI ✳︎ DESAIN ✳︎ DOKUMENTASI ✳︎ LOGISTIK ✳︎ PEDULI</div>
<div class="eb-container">
    <form method="GET" action="{{ route('events.index') }}" class="eb-home-search">
        <div><label class="eb-eyebrow" for="home-search">KEGIATAN APA YANG INGIN KAMU IKUTI?</label><input id="home-search" name="search" type="search" maxlength="100" placeholder="Cari judul kegiatan..."></div>
        <button class="eb-button" type="submit">Temukan event <span aria-hidden="true">→</span></button>
    </form>
    <section id="event-tersedia" class="eb-section" aria-labelledby="events-title">
        <div class="eb-section-heading"><div><p class="eb-eyebrow">PILIH RUANG KONTRIBUSIMU</p><h2 id="events-title" class="eb-display">Banyak kegiatan.<br>Satu niat baik.</h2></div><a class="eb-text-link" href="{{ route('events.index') }}">Lihat semua event ↗︎</a></div>
        <div class="eb-event-grid">
            @forelse($events as $event)<x-event-card :event="$event" />
            @empty<div class="eb-empty"><h3>Peluang berikutnya sedang dipersiapkan</h3><p>Belum ada kegiatan publik dengan pendaftaran terbuka. Kunjungi katalog untuk melihat kegiatan lainnya.</p><a class="eb-text-link" href="{{ route('events.index') }}">Jelajahi katalog →</a></div>@endforelse
        </div>
    </section>
</div>
<section id="cara-kerja" class="eb-how eb-section" aria-labelledby="how-title">
    <div class="eb-container eb-two-column">
        <div><p class="eb-eyebrow">MULAI DARI APA YANG KAMU BISA.</p><h2 id="how-title" class="eb-display">Bukan harus hebat.<br>Cukup mau terlibat.</h2><p class="eb-how-intro">Pilih peranmu. Kenali kebutuhannya. Siapkan kontribusimu.</p><div class="eb-skill-circle" aria-hidden="true"><strong>SKILL</strong><span>WAKTU · KOTA</span></div></div>
        <ol class="eb-steps">
            @foreach([['Kenalkan kemampuanmu', 'Isi profil, kota, skill, level, dan jadwal ketersediaanmu.'], ['Temukan kegiatanmu', 'Baca posisi, persyaratan, lokasi, serta jadwal tugas sebelum memilih kegiatan.'], ['Siapkan langkah berikutnya', 'Lengkapi dokumen dan assessment sesuai posisi, lalu pantau perkembangan lamaran.']] as [$heading, $copy])
            <li><span class="eb-step-index">0{{ $loop->iteration }}</span><div><h3>{{ $heading }}</h3><p>{{ $copy }}</p></div></li>
            @endforeach
        </ol>
    </div>
</section>
<section class="eb-section eb-container" aria-labelledby="role-title">
    <p class="eb-eyebrow">DUA PERAN, SATU RUANG KOLABORASI</p><h2 id="role-title" class="eb-display">Mulai dari peran Anda.</h2><p class="eb-intro">Volunteer membawa keterampilan. Organizer menyiapkan kegiatan dan kebutuhan relawan yang relevan.</p>
    <div class="eb-role-grid">
        @foreach([
            ['volunteer', 'UNTUK VOLUNTEER', 'Temukan ruang untuk kontribusi Anda.', [['Buat akun dan lengkapi profil', 'Tambahkan kota, skill, level, dan jadwal ketersediaan Anda.'], ['Jelajahi kebutuhan relawan', 'Baca detail posisi, persyaratan, serta waktu pelaksanaan kegiatan.'], ['Ajukan lamaran dan ikuti seleksi', 'Lengkapi dokumen dan assessment sesuai posisi, lalu pantau perkembangan lamaran Anda.']]],
            ['organizer', 'UNTUK ORGANIZER', 'Susun tim relawan dengan kebutuhan yang jelas.', [['Lengkapi profil organisasi', 'Daftarkan akun Organizer dan ikuti proses verifikasi organisasi.'], ['Rancang kegiatan dan posisi', 'Organizer aktif dapat menyiapkan event, skill, jadwal, persyaratan, dan paket.'], ['Ajukan ketika seluruh syarat siap', 'Penuhi moderasi, paket aktif, dan assessment yang sah sebelum memublikasikan kegiatan.']]],
        ] as [$role, $label, $title, $steps])
        <article class="eb-role-card"><p class="eb-eyebrow">{{ $label }}</p><h3>{{ $title }}</h3><ol>@foreach($steps as [$heading, $copy])<li><span>{{ $loop->iteration }}</span><div><h4>{{ $heading }}</h4><p>{{ $copy }}</p></div></li>@endforeach</ol><a class="eb-button" href="{{ auth()->check() ? route('dashboard') : route('register', ['role' => $role]) }}">{{ auth()->check() ? 'Lanjutkan ke akun saya' : 'Mulai sebagai '.ucfirst($role) }} ↗︎</a></article>
        @endforeach
    </div>
</section>
<section id="untuk-organizer" class="eb-container eb-section eb-organizer" aria-labelledby="organizer-title"><div><p class="eb-eyebrow">UNTUK ORGANIZER</p><h2 id="organizer-title" class="eb-display">Kegiatan hebat,<br>ketemu tim yang tepat.</h2><p class="eb-intro">Susun kebutuhan relawan, posisi, dan jadwal dalam satu tempat.</p></div><a class="eb-button" href="{{ auth()->check() ? route('dashboard') : route('register', ['role' => 'organizer']) }}">{{ auth()->check() ? 'Buka akun saya' : 'Mulai sebagai Organizer' }} ↗︎</a></section>
<section id="paket" class="eb-container eb-section" aria-labelledby="packages-title"><p class="eb-eyebrow">PILIH KAPASITAS UNTUK KEGIATANMU</p><h2 id="packages-title" class="eb-display">Ruang untuk rencanamu.</h2><p class="eb-intro">Paket aktif mengikuti harga dan kapasitas yang tersedia. Pembayaran menggunakan Midtrans Sandbox.</p><div class="eb-package-grid">
    @forelse($packages as $package)
    <x-package-card :package="$package"><a class="eb-button" href="{{ auth()->check() ? route('dashboard') : route('register', ['role' => 'organizer']) }}">{{ auth()->check() ? 'Buka akun saya' : 'Mulai sebagai Organizer' }} ↗︎</a></x-package-card>
    @empty<p class="eb-empty">Paket kegiatan belum tersedia. Silakan kunjungi kembali nanti.</p>@endforelse
</div></section>
<section class="eb-container eb-section eb-faq" aria-labelledby="faq-title"><h2 id="faq-title" class="eb-display">Sebelum<br>ikut terlibat.</h2><div>
    @foreach([
        ['Apa itu SkillMatch?', 'SkillMatch mempertemukan kebutuhan kegiatan Organizer dengan keterampilan, waktu, dan lokasi Volunteer. Kenali kebutuhan posisi sebelum menentukan kontribusi Anda.'],
        ['Perlu akun untuk melihat event?', 'Katalog dan detail event dapat dilihat tanpa login. Buat akun Volunteer untuk melengkapi profil, dokumen, dan melamar posisi.'],
        ['Bagaimana menjadi Organizer?', 'Daftar sebagai Organizer, lengkapi profil dan dokumen organisasi untuk verifikasi. Organizer aktif dapat menyiapkan kegiatan serta mengajukannya untuk moderasi.'],
        ['Bagaimana alur lamaran dan seleksi?', 'Pilih posisi, lengkapi persyaratan dan assessment yang diminta, lalu pantau status melalui Lamaran Saya. Keputusan seleksi ditentukan Organizer.'],
        ['Mengapa event belum muncul di katalog?', 'Event memerlukan moderasi, paket aktif, dan publikasi. Draft serta kegiatan yang belum dipublikasikan tidak ditampilkan kepada publik.'],
    ] as [$question, $answer])<details><summary>{{ $question }}<span aria-hidden="true">+</span></summary><p>{{ $answer }}</p></details>@endforeach
</div></section>
<section class="eb-final-cta" aria-labelledby="cta-title"><div class="eb-container"><h2 id="cta-title" class="eb-display">Skillmu bisa jadi<br>awal cerita baik.</h2><a class="eb-button eb-button-navy" href="{{ auth()->check() ? route('dashboard') : route('register') }}">{{ auth()->check() ? 'Buka akun saya' : 'Buat akun SkillMatch' }} ↗︎</a></div></section>
@endsection
