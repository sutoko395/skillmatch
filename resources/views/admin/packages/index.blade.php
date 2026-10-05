@extends('admin.layouts.sidebar')
@section('title','Paket')
@section('content')
@include('components.flash')
<x-page-header title="Paket" description="Kelola harga dan manfaat. Pembelian lama tetap memakai snapshot semula." />
<a class="mb-5 inline-block rounded-lg bg-indigo-600 px-4 py-2 text-white" href="{{ route('admin.packages.create') }}">Tambah paket</a>
<div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">@forelse($packages as $package)<x-package-card :package="$package" :show-status="true"><a class="inline-flex min-h-[44px] w-full items-center justify-center rounded-xl border border-indigo-200 px-4 py-3 text-sm font-semibold text-indigo-700 hover:bg-indigo-50" href="{{ route('admin.packages.edit',$package) }}">Edit paket</a></x-package-card>@empty<x-empty-state title="Belum ada paket" description="Tambahkan harga dan manfaat yang telah disepakati." />@endforelse</div><div class="mt-6">{{ $packages->links() }}</div>
@endsection
