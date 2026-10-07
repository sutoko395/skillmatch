@extends('admin.layouts.sidebar')

@section('title', 'Detail Transaksi')

@section('content')

    @include('components.flash')

    <div class="w-full max-w-7xl mx-auto space-y-6">

        <x-page-header
            title="Detail Transaksi"
            description="Sandbox - simulasi pembayaran"
        />

        @include('orders.detail', [
            'admin' => true,
        ])

    </div>

@endsection