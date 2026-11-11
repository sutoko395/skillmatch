@extends(auth()->user()->role.'.layouts.sidebar')
@section('title', 'Notifikasi')
@section('page-class', auth()->user()->role === 'volunteer' ? 'volunteer-applications' : '')
@section('content')
@if(auth()->user()->role === 'volunteer')
    @include('volunteer.notifications.index')
@else
<div class="mx-auto max-w-4xl space-y-6">
    <x-page-header title="Notifikasi" description="Pembaruan kegiatan dan proses akun Anda." />
    @include('components.flash')
    @include('notifications.list', ['notificationRows' => $notifications])
    {{ $notifications->links() }}
</div>
@endif
@endsection
