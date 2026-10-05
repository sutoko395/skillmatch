@extends('layouts.user')
@section('title', 'Status Organisasi')
@section('content')
<x-page-header title="Status Organisasi" />
<x-empty-state :title="$user->organizer_status === 'inactive' ? 'Organisasi belum terverifikasi' : 'Menunggu verifikasi organisasi'" description="Anda masih dapat melengkapi profil dan kontak. Pengelolaan rekrutmen tersedia setelah organisasi terverifikasi.">
    <a class="text-indigo-700 underline" href="{{ route('organizer.profile.edit') }}">Lengkapi profil organisasi</a>
    @unless($user->hasVerifiedEmail())<p class="mt-3"><a class="text-indigo-700 underline" href="{{ route('verification.notice') }}">Verifikasi email Anda</a></p>@endunless
</x-empty-state>
@endsection
