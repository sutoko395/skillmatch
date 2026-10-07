@extends('organizer.layouts.sidebar')

@section('title', 'Pilih Paket')

@section('content')
<div class="w-full max-w-7xl mx-auto space-y-6">

    <div>
        <h1 class="text-2xl font-bold text-slate-800">
            Pilih Paket
        </h1>

        <p class="mt-1 text-sm text-slate-500">
            {{ $event->title }}
        </p>
    </div>

    <div class="rounded-xl border border-indigo-100 bg-indigo-50 px-5 py-4 text-sm text-indigo-700">
        Sandbox — simulasi pembayaran. Harga dan manfaat berikut berasal dari konfigurasi paket.
    </div>

    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
        @forelse($packages as $package)
            <x-package-card :package="$package">
                <form
                    method="POST"
                    action="{{ route('organizer.packages.update', $event) }}"
                >
                    @csrf

                    <input
                        type="hidden"
                        name="package_id"
                        value="{{ $package->id }}"
                    >

                    <x-button class="w-full justify-center">
                        Pilih Paket
                    </x-button>
                </form>
            </x-package-card>
        @empty
            <div class="md:col-span-2 xl:col-span-3">
                <x-empty-state
                    title="Paket belum tersedia"
                    description="Admin perlu mengonfigurasi paket terlebih dahulu."
                />
            </div>
        @endforelse
    </div>

    @if($packages->hasPages())
        <div>
            {{ $packages->links() }}
        </div>
    @endif

</div>
@endsection