@props(['title', 'intro' => null, 'register' => false, 'scrollable' => false])
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} · SkillMatch</title>
    @include('layouts.fonts')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="electric-auth font-sans antialiased">
    <a href="#auth-main" class="sr-only focus:not-sr-only">Lewati ke formulir</a>
    <div class="eb-auth-shell">
        <aside class="eb-auth-brand">
            <a href="{{ route('home') }}" aria-label="SkillMatch beranda"><x-public-brand /></a>
            <div class="eb-auth-story"><p class="eb-eyebrow">SKILL KECIL. DAMPAK BERARTI.</p><h1 class="eb-display">{{ $register ? 'Ada tempat untuk niat baikmu.' : 'Senang kamu kembali terlibat.' }}</h1><p>Temukan ruang untuk skill, waktu,<br>dan niat baikmu.</p><img src="{{ asset('images/electric-blue/seed.svg') }}" alt="" width="250" height="320"></div>
            <p class="eb-auth-footnote">SkillMatch / Ruang untuk berkontribusi.</p>
        </aside>
        <main id="auth-main" class="eb-auth-main">
            <header class="eb-auth-header"><a href="{{ route('home') }}" class="eb-auth-mobile-brand" aria-label="SkillMatch beranda"><x-public-brand /></a><a href="{{ route('home') }}">Kembali ke beranda <span aria-hidden="true">↗︎</span></a></header>
            <div @class(['eb-auth-content', 'eb-auth-register' => $register])>
                <div class="eb-auth-heading"><p class="eb-eyebrow">{{ $register ? 'MULAI BERSAMA SKILLMATCH' : 'SELAMAT DATANG KEMBALI' }}</p><h2 class="eb-display">{{ $title }}.</h2>@if($intro)<p>{{ $intro }}</p>@endif</div>
                {{ $slot }}
            </div>
        </main>
    </div>
</body>
</html>
