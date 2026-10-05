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
<body class="bg-bg-light font-sans text-txt-light-primary antialiased">
    <a href="#main" class="sr-only focus:not-sr-only">Lewati navigasi</a>
    @include('layouts.navigation')
    <main id="main" class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <x-flash />
        @yield('content')
    </main>
</body>
</html>
