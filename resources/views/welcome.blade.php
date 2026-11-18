@extends('layouts.public')
@section('title', 'Volunteer, keterampilan, dan kontribusi')
@section('description', 'Temukan kegiatan volunteer berdasarkan keterampilan, waktu, dan lokasi. Mulai kontribusi bersama SkillMatch.')
@section('content')
<section class="eb-hero" aria-labelledby="hero-title">
    <div class="eb-container eb-hero-inner">
        <p class="eb-eyebrow">KETERAMPILANMU PUNYA TEMPAT.</p>
        <h1 id="hero-title">Punya skill.<br>Bikin berarti.</h1>
        <p class="eb-hero-description">Temukan kegiatan yang membutuhkan apa yang kamu bisa.<br class="hidden sm:block"> Sesuaikan dengan skill, waktu, dan kotamu.</p>
        <a href="{{ route('events.index') }}" class="eb-button">Jelajahi event <x-arrow-icon direction="up-right" /></a>
        <a href="#cara-kerja" class="eb-hero-link">Kenali cara kerjanya <x-arrow-icon direction="down" /></a>
        <img class="eb-character eb-character-plant" src="{{ asset('images/electric-blue/volunteer-plant.svg') }}" alt="" width="280" height="518">
        <img class="eb-character eb-character-megaphone" src="{{ asset('images/electric-blue/volunteer-megaphone.svg') }}" alt="" width="393" height="515">
    </div>
</section>
<div class="eb-ribbon" aria-label="Beragam keterampilan untuk berkontribusi">KOMUNIKASI ✳︎ DESAIN ✳︎ DOKUMENTASI ✳︎ LOGISTIK ✳︎ PEDULI</div>
<section class="eb-search-band" aria-label="Pencarian kegiatan">
    <form method="GET" action="{{ route('events.index') }}" class="eb-container eb-home-search">
        <div><label class="eb-eyebrow" for="home-search">KEGIATAN APA YANG INGIN KAMU IKUTI?</label><input id="home-search" name="search" type="search" maxlength="100" placeholder="Cari judul kegiatan..."></div>
        <button class="eb-button" type="submit">Temukan event <x-arrow-icon direction="right" /></button>
    </form>
</section>
<div class="eb-catalog-preview">
    <section id="event-tersedia" class="eb-container eb-section" aria-labelledby="events-title">
        <div class="eb-section-heading"><div><p class="eb-eyebrow">PILIH RUANG KONTRIBUSIMU</p><h2 id="events-title" class="eb-display">Banyak kegiatan.<br>Satu niat baik.</h2></div><a class="eb-text-link" href="{{ route('events.index') }}">Lihat semua event <x-arrow-icon direction="up-right" /></a></div>
        <div class="eb-event-grid">
            @forelse($events as $event)<x-event-card :event="$event" />
            @empty<div class="eb-empty"><h3>Peluang berikutnya sedang dipersiapkan</h3><p>Belum ada kegiatan publik dengan pendaftaran terbuka. Kunjungi katalog untuk melihat kegiatan lainnya.</p><a class="eb-text-link" href="{{ route('events.index') }}">Jelajahi katalog <x-arrow-icon direction="right" /></a></div>@endforelse
        </div>
    </section>
</div>
<section id="cara-kerja" class="eb-how eb-section" aria-labelledby="how-title">
    <div class="eb-container eb-two-column">
        <div class="eb-how-copy">
            <p class="eb-eyebrow">MULAI DARI APA YANG KAMU BISA.</p>
            <h2 id="how-title" class="eb-display">Bukan harus hebat.<br><span>Cukup mau terlibat.</span></h2>
            <p class="eb-how-intro">Pilih peranmu dan temukan kegiatan yang sesuai dengan keterampilan, waktu, serta lokasimu. Siapkan kontribusi dan pantau setiap langkahnya dalam satu tempat.</p>
            <a class="eb-how-link" href="{{ route('events.index') }}">Jelajahi kegiatan <x-arrow-icon direction="up-right" /></a>
        </div>
        <ol class="eb-steps">
            @foreach([
                ['Kenalkan kemampuanmu', 'Lengkapi profil, kota, skill, level, dan jadwal ketersediaanmu.'],
                ['Temukan kegiatanmu', 'Baca posisi, persyaratan, lokasi, dan jadwal tugas yang sesuai.'],
                ['Siapkan langkah berikutnya', 'Lengkapi dokumen dan asesmen sesuai posisi, lalu pantau perkembangan lamaran.'],
            ] as [$heading, $copy])
                <li>
                    <span class="eb-step-index" aria-hidden="true">0{{ $loop->iteration }}</span>
                    <div><h3>{{ $heading }}</h3><p>{{ $copy }}</p></div>
                </li>
            @endforeach
        </ol>
    </div>
</section>
<section class="eb-section eb-container" aria-labelledby="role-title">
    <p class="eb-eyebrow">DUA PERAN, SATU RUANG KOLABORASI</p><h2 id="role-title" class="eb-display">Mulai dari peran Anda.</h2><p class="eb-intro">Volunteer membawa keterampilan. Organizer menyiapkan kegiatan dan kebutuhan volunteer yang relevan.</p>
    <div class="eb-role-grid">
        @foreach([
            ['volunteer', 'UNTUK VOLUNTEER', 'Temukan ruang untuk kontribusi Anda.', [['Buat akun dan lengkapi profil', 'Tambahkan kota, skill, level, dan jadwal ketersediaan Anda.'], ['Jelajahi kebutuhan volunteer', 'Baca detail posisi, persyaratan, serta waktu pelaksanaan kegiatan.'], ['Ajukan lamaran dan ikuti seleksi', 'Lengkapi dokumen dan assessment sesuai posisi, lalu pantau perkembangan lamaran Anda.']]],
            ['organizer', 'UNTUK ORGANIZER', 'Susun tim volunteer dengan kebutuhan yang jelas.', [['Lengkapi profil organisasi', 'Daftarkan akun Organizer dan ikuti proses verifikasi organisasi.'], ['Rancang kegiatan dan posisi', 'Organizer aktif dapat menyiapkan event, skill, jadwal, persyaratan, dan paket.'], ['Ajukan ketika seluruh syarat siap', 'Penuhi moderasi, paket aktif, dan assessment yang sah sebelum memublikasikan kegiatan.']]],
        ] as [$role, $label, $title, $steps])
        <article id="untuk-{{ $role }}" class="eb-role-card eb-role-card--{{ $role }}">
            <p class="eb-eyebrow">{{ $label }}</p>
            <h3>{{ $title }}</h3>
            <ol>
                @foreach($steps as [$heading, $copy])
                    <li>
                        <span aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" /></svg></span>
                        <div><h4>{{ $heading }}</h4><p>{{ $copy }}</p></div>
                    </li>
                @endforeach
            </ol>
            <a class="eb-button" href="{{ auth()->check() ? route('dashboard') : route('register', ['role' => $role]) }}">{{ auth()->check() ? 'Lanjutkan ke akun saya' : 'Mulai sebagai '.ucfirst($role) }} <x-arrow-icon direction="up-right" /></a>
        </article>
        @endforeach
    </div>
</section>
<section id="paket" class="eb-packages-band" aria-labelledby="packages-title">
    <div class="eb-container eb-section">
        <div class="eb-packages-heading">
            <div>
                <p class="eb-eyebrow">PILIH KAPASITAS UNTUK KEGIATANMU</p>
                <h2 id="packages-title" class="eb-display">Ruang untuk<br>rencanamu.</h2>
            </div>
        </div>
        <div class="eb-package-grid">
            @forelse($packages as $package)
                <x-package-card :package="$package" />
            @empty
                <p class="eb-empty">Paket kegiatan belum tersedia. Silakan kunjungi kembali nanti.</p>
            @endforelse
        </div>
        @if($packages->isNotEmpty())
            @php
                $packageUser = auth()->user();
                [$packageCtaUrl, $packageCtaLabel] = match (true) {
                    ! $packageUser => [route('register', ['role' => 'organizer']), 'Daftar sebagai Organizer'],
                    $packageUser->role === 'organizer' && $packageUser->organizer_status === 'active' => [route('organizer.events.create'), 'Buat Kegiatan'],
                    $packageUser->role === 'organizer' => [route('organizer.profile.edit'), 'Lengkapi Profil Organizer'],
                    $packageUser->role === 'volunteer' => [route('events.index'), 'Jelajahi Kegiatan'],
                    default => [route('dashboard'), 'Buka Dashboard'],
                };
            @endphp
            <div class="eb-package-cta">
                <p>Paket dipilih saat membuat kegiatan. Pembayaran dilakukan setelah kegiatan disetujui.</p>
                <a class="eb-button" href="{{ $packageCtaUrl }}">{{ $packageCtaLabel }} <span aria-hidden="true">↗︎</span></a>
            </div>
        @endif
    </div>
</section>
<section class="eb-faq-band" aria-labelledby="faq-title"><div class="eb-container eb-section eb-faq">
    <div class="eb-faq-intro">
        <p class="eb-eyebrow">PERTANYAAN UMUM</p>
        <h2 id="faq-title" class="eb-display">Sebelum<br>ikut terlibat.</h2>
        <p class="eb-faq-description">Kenali cara bergabung, proses seleksi, dan kegiatan di SkillMatch sebelum memulai.</p>
    </div>
    <div class="eb-faq-questions">
    @foreach([
        ['Apa itu SkillMatch?', 'SkillMatch mempertemukan kebutuhan kegiatan Organizer dengan keterampilan, waktu, dan lokasi Volunteer. Kenali kebutuhan posisi sebelum menentukan kontribusi Anda.'],
        ['Perlu akun untuk melihat event?', 'Katalog dan detail event dapat dilihat tanpa login. Buat akun Volunteer untuk melengkapi profil, dokumen, dan melamar posisi.'],
        ['Bagaimana menjadi Organizer?', 'Daftar sebagai Organizer, lengkapi profil dan dokumen organisasi untuk verifikasi. Organizer aktif dapat menyiapkan kegiatan serta mengajukannya untuk moderasi.'],
        ['Bagaimana alur lamaran dan seleksi?', 'Pilih posisi, lengkapi persyaratan dan assessment yang diminta, lalu pantau status melalui Lamaran Saya. Keputusan seleksi ditentukan Organizer.'],
        ['Mengapa event belum muncul di katalog?', 'Event memerlukan moderasi, paket aktif, dan publikasi. Draft serta kegiatan yang belum dipublikasikan tidak ditampilkan kepada publik.'],
    ] as [$question, $answer])<details><summary>{{ $question }}<span aria-hidden="true">+</span></summary><p>{{ $answer }}</p></details>@endforeach
</div></div></section>
<section class="eb-final-cta" aria-labelledby="cta-title">
    <div class="eb-container">
        <div class="eb-cta-card">
            <div class="eb-cta-copy">
                <p class="eb-eyebrow">MULAI KONTRIBUSIMU DI SINI</p>
                <h2 id="cta-title" class="eb-display">Skillmu bisa jadi<br>awal cerita baik.</h2>
                <p class="eb-cta-description">Temukan kegiatan yang sesuai dengan keterampilan, waktu, dan lokasimu. Ambil peran dan mulai kontribusimu bersama SkillMatch.</p>
            </div>
            <div class="eb-cta-actions">
                <a class="eb-button eb-cta-secondary" href="{{ route('events.index') }}">Jelajahi kegiatan</a>
            </div>
        </div>
    </div>
</section>
@endsection
