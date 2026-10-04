@extends('layouts.public')
@section('title', 'Selamat datang')
@section('content')
<x-page-header title="Temukan ruang untuk berkontribusi" description="Lengkapi profil dan skill Anda bersama SkillMatch." />
<x-empty-state title="Katalog kegiatan belum tersedia" description="Katalog akan tersedia setelah modul kegiatan diintegrasikan.">
@guest <a class="text-indigo-700 underline" href="{{ route('register') }}">Daftar sebagai Volunteer atau Organizer</a> @endguest
</x-empty-state>
@endsection
