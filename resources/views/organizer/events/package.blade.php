@extends('layouts.user')
@section('title', 'Pilih Paket')
@section('content')
<x-page-header title="Pilih Paket" :description="$event->title" />
<p class="mb-4">Sandbox - simulasi pembayaran. Harga dan manfaat berikut berasal dari konfigurasi paket.</p>
<div class="grid gap-5 md:grid-cols-3">@forelse($packages as $package)<x-package-card :package="$package"><form method="POST" action="{{ route('organizer.packages.update',$event) }}">@csrf<input type="hidden" name="package_id" value="{{ $package->id }}"><x-button class="w-full justify-center">Pilih paket</x-button></form></x-package-card>@empty<x-empty-state title="Paket belum tersedia" description="Admin perlu mengonfigurasi paket terlebih dahulu." />@endforelse</div><div class="mt-6">{{ $packages->links() }}</div>
@endsection
