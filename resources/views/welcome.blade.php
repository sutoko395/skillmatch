@extends('layouts.public')
@section('title', 'SkillMatch Volunteer')
@section('content')
<section class="mx-auto max-w-4xl px-2 py-12 text-center sm:py-20">
    <span class="inline-block rounded-full border border-indigo-100 bg-indigo-50 px-3 py-1 text-xs font-bold uppercase tracking-widest text-indigo-700">Platform Rekrutmen Relawan Berbasis Skill</span>
    <h1 class="mt-6 text-4xl font-black leading-tight text-gray-900 md:text-6xl">
        Hubungkan Talent Relawan dengan Event Terbaik
    </h1>
    <p class="mx-auto mt-4 max-w-2xl text-lg text-gray-600">
        Kenalkan skill, ketersediaan waktu, dan lokasi Anda untuk mempersiapkan kontribusi bersama Organizer dan Volunteer.
    </p>
    <div class="mt-8 flex flex-wrap justify-center gap-4">
        @auth
            <a href="{{ route('dashboard') }}" class="rounded-lg bg-indigo-600 px-8 py-3 font-bold text-white shadow-sm transition hover:bg-indigo-700">
                {{ auth()->user()->role === 'admin' ? 'Buka Dashboard Admin' : 'Buka Aktivitas' }}
            </a>
        @else
            <a href="{{ route('login') }}" class="rounded-lg bg-indigo-600 px-8 py-3 font-bold text-white shadow-sm transition hover:bg-indigo-700">Masuk ke Sistem</a>
            <a href="{{ route('register') }}" class="rounded-lg border border-gray-300 bg-white px-8 py-3 font-bold text-gray-700 transition hover:bg-gray-50">Daftar Sekarang</a>
        @endauth
    </div>
    <p class="mx-auto mt-8 max-w-2xl text-sm text-slate-600">
        Pendaftaran dan pengisian profil sudah tersedia. Katalog event dan pencocokan relawan sedang disiapkan.
    </p>
</section>
<footer class="border-t border-gray-200 py-6 text-center text-sm text-gray-600">
    &copy; {{ date('Y') }} SkillMatch Volunteer - Kelompok 5 Politeknik Negeri Malang
</footer>
@endsection
