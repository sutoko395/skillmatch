@extends('admin.layouts.sidebar')
@section('title','Paket')
@section('content')
@include('components.flash')
<x-page-header title="Paket" description="Kelola harga dan manfaat. Pembelian lama tetap memakai snapshot semula." />
<a class="mb-5 inline-block rounded-lg bg-indigo-600 px-4 py-2 text-white" href="{{ route('admin.packages.create') }}">Tambah paket</a>
<div class="grid gap-4 md:grid-cols-2">@forelse($packages as $package)<x-section-card><h2 class="text-xl font-semibold">{{ $package->name }}</h2><p>Rp {{ number_format($package->price,0,',','.') }} &middot; {{ $package->is_active ? 'Aktif' : 'Nonaktif' }}</p><p>{{ $package->max_positions }} posisi, {{ $package->max_applications }} lamaran, {{ $package->max_registration_days }} hari pendaftaran.</p><a class="mt-3 inline-block text-indigo-700 underline" href="{{ route('admin.packages.edit',$package) }}">Edit/nonaktifkan</a></x-section-card>@empty<x-empty-state title="Belum ada paket" description="Tambahkan harga dan manfaat yang telah disepakati." />@endforelse</div><div class="mt-6">{{ $packages->links() }}</div>
@endsection
