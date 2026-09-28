<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SkillMatch Volunteer</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-800 antialiased min-h-screen flex flex-col justify-between">

    <!-- Header Navigation -->
    <header class="w-full py-6 px-8 flex justify-between items-center max-w-7xl mx-auto">
        <div class="flex items-center gap-2">
            <span class="text-2xl font-extrabold text-indigo-600 tracking-tight">SkillMatch</span>
            <span class="text-xs bg-indigo-100 text-indigo-800 font-semibold px-2 py-0.5 rounded-full">Volunteer</span>
        </div>
        <div>
            @if (Route::has('login'))
                @auth
                    <a href="{{ url('/dashboard') }}" class="font-semibold text-gray-600 hover:text-indigo-600 transition">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="font-semibold text-gray-600 hover:text-indigo-600 mr-4 transition">Masuk</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="bg-indigo-600 text-white font-semibold px-4 py-2 rounded-lg hover:bg-indigo-700 transition shadow-sm">Daftar Akun</a>
                    @endif
                @endauth
            @endif
        </div>
    </header>

    <!-- Main Hero Section -->
    <main class="max-w-4xl mx-auto px-6 text-center my-auto py-16">
        <span class="text-xs font-bold uppercase tracking-widest text-indigo-600 bg-indigo-50 px-3 py-1 rounded-full border border-indigo-100">Platform Rekrutmen Relawan Berbasis Skill</span>
        <h1 class="text-4xl md:text-6xl font-black text-gray-900 mt-6 leading-tight">
            Hubungkan Talent Relawan dengan Event Terbaik
        </h1>
        <p class="text-lg text-gray-600 mt-4 max-w-2xl mx-auto">
            Sistem pencocokan presisi berbasis skill, ketersediaan waktu, dan lokasi untuk membantu Organizer menemukan Volunteer yang paling tepat.
        </p>

        <div class="mt-8 flex justify-center gap-4">
            @auth
                <a href="{{ url('/dashboard') }}" class="bg-indigo-600 text-white font-bold px-8 py-3 rounded-lg hover:bg-indigo-700 transition shadow-lg">Buka Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="bg-indigo-600 text-white font-bold px-8 py-3 rounded-lg hover:bg-indigo-700 transition shadow-lg">Masuk ke Sistem</a>
                <a href="{{ route('register') }}" class="bg-white border border-gray-300 text-gray-700 font-bold px-8 py-3 rounded-lg hover:bg-gray-50 transition">Daftar Sekarang</a>
            @endauth
        </div>
    </main>

    <!-- Footer -->
    <footer class="py-6 text-center text-sm text-gray-500 border-t border-gray-200">
        &copy; 2026 SkillMatch Volunteer - Kelompok 5 Politeknik Negeri Malang
    </footer>

</body>
</html>