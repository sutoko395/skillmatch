@extends('admin.layouts.sidebar')

@section('title', 'Detail Transaksi')

@section('content')



    <div class="w-full max-w-7xl mx-auto space-y-6">

        <div><a href="{{ route('admin.orders.index') }}" class="admin-action admin-action-back">Kembali</a></div>
        <x-page-header
            title="Detail Transaksi"
            description="Sandbox - simulasi pembayaran"
        />

        @include('admin.orders.detail', [
            'admin' => true,
        ])

    </div>

@endsection