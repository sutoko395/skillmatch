<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="@yield('description', 'Jelajahi kegiatan relawan dan kenali kebutuhan posisi melalui SkillMatch.')">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SkillMatch') · SkillMatch</title>
    @include('layouts.fonts')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="electric-public font-sans antialiased">
    <a href="#main" class="sr-only focus:not-sr-only">Lewati navigasi</a>
    <x-public-navigation />
    <main id="main" @class(['eb-public-main', 'eb-container eb-interior' => !request()->routeIs('home')])>
        <div class="eb-container"><x-flash /></div>
        @yield('content')
    </main>
    <x-public-footer />
</body>
</html>
