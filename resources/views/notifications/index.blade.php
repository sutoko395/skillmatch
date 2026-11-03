@extends(auth()->user()->role.'.layouts.sidebar')
@section('title', 'Notifikasi')
@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    <x-page-header title="Notifikasi" description="Pembaruan kegiatan dan proses akun Anda." />
    @include('components.flash')
    @include('notifications.list', ['notificationRows' => $notifications])
    {{ $notifications->links() }}
</div>
@endsection
