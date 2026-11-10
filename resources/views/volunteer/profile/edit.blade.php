@extends('volunteer.layouts.sidebar')

@section('title', 'Profil Volunteer')
@section('page-class', 'volunteer-profile')

@section('content')
<div class="vp-profile w-full max-w-7xl mx-auto space-y-6">
    <x-page-header
        title="Profil Volunteer"
        description="Lengkapi identitas, kota, skill dan tanggal ketersediaan Anda."
    />
    <form method="POST" action="{{ route('volunteer.profile.update') }}" class="space-y-6" data-saving-form>
        @csrf
        @include('volunteer.profile.identity')
        @include('volunteer.profile.skills')
        @include('volunteer.profile.availability')
        @include('volunteer.profile.actions')
    </form>
</div>
@endsection
