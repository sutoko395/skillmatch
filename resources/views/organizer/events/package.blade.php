@extends('layouts.user')
@section('title', 'Pilih Paket')
@section('content')
<x-page-header title="Pilih Paket" :description="$event->title" />
<p class="mb-4">Sandbox - simulasi pembayaran. Harga dan manfaat berikut berasal dari konfigurasi paket.</p>
<div class="grid gap-4 md:grid-cols-3">@forelse($packages as $package)<x-section-card><h2 class="text-xl font-semibold">{{ $package->name }}</h2><p class="mt-3 text-2xl font-bold">Rp {{ number_format($package->price,0,',','.') }}</p><ul class="mt-4 space-y-2"><li>{{ $package->max_positions }} posisi</li><li>{{ $package->max_applications }} lamaran terkirim</li><li>{{ $package->max_registration_days }} hari pendaftaran</li></ul><form class="mt-4" method="POST" action="{{ route('organizer.packages.update',$event) }}">@csrf<input type="hidden" name="package_id" value="{{ $package->id }}"><x-button>Pilih paket</x-button></form></x-section-card>@empty<x-empty-state title="Paket belum tersedia" description="Admin perlu mengonfigurasi paket terlebih dahulu." />@endforelse</div><div class="mt-6">{{ $packages->links() }}</div>
@endsection
