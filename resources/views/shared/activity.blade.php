@extends('layouts.user')
@section('title', 'Ringkasan Aktivitas')
@section('content')
<x-page-header title="Ringkasan Aktivitas" :description="'Selamat datang, '.auth()->user()->name" />
<x-empty-state title="Ringkasan aktivitas belum tersedia" description="Anda sudah dapat melengkapi profil. Informasi kegiatan dan tugas akan tersedia setelah modul aktivitas diintegrasikan.">
    <a class="font-medium text-indigo-700 underline" href="{{ route(auth()->user()->role.'.profile.edit') }}">Lengkapi profil</a>
</x-empty-state>
@endsection
