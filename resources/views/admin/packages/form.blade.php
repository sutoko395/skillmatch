@extends('admin.layouts.sidebar')
@section('title', $package->exists ? 'Edit Paket' : 'Tambah Paket')
@section('content')
<div class="mx-auto max-w-3xl space-y-6">
    <x-page-header :title="$package->exists ? 'Edit Paket' : 'Tambah Paket'" description="Harga dan manfaat pembelian lama tetap mengikuti snapshot." />
    @include('components.flash')
    <x-section-card>
        <form method="POST" action="{{ $package->exists ? route('admin.packages.update', $package) : route('admin.packages.store') }}" class="space-y-5">
            @csrf
            @if($package->exists) @method('PUT') @endif
            <x-form-field name="name" label="Nama Paket" :value="old('name', $package->name)" required />
            @foreach(['price' => 'Harga (Rupiah)', 'max_positions' => 'Maksimal Posisi', 'max_applications' => 'Maksimal Lamaran Terkirim', 'max_registration_days' => 'Maksimal Hari Pendaftaran'] as $field => $label)
                <x-form-field :name="$field" :label="$label" type="number" :value="old($field, $package->$field)" :min="$field === 'price' ? 0 : 1" required />
            @endforeach
            <x-form-field name="is_active" label="Status">
                <select id="is_active" name="is_active" class="w-full rounded-xl border-slate-300">
                    <option value="1" @selected((int) old('is_active', $package->is_active ?? true) === 1)>Aktif</option>
                    <option value="0" @selected((int) old('is_active', $package->is_active ?? true) === 0)>Nonaktif</option>
                </select>
            </x-form-field>
            <div class="flex gap-4"><x-button type="submit">Simpan Paket</x-button><a href="{{ route('admin.packages.index') }}" class="py-3 font-semibold text-slate-600">Kembali</a></div>
        </form>
    </x-section-card>
</div>
@endsection
