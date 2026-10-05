@extends('admin.layouts.sidebar')
@section('title','Konfigurasi Paket')
@section('content')
@include('components.flash')
<x-page-header title="Konfigurasi Paket" description="Nominal rupiah bulat. Harga 0 berarti paket gratis, tanpa transaksi paid." />
<x-section-card><form class="space-y-4" method="POST" action="{{ $package->exists ? route('admin.packages.update',$package) : route('admin.packages.store') }}">@csrf @if($package->exists) @method('PUT') @endif
<x-form-field name="name" label="Nama paket" :value="$package->name" required />
@foreach(['price'=>'Harga (rupiah)','max_positions'=>'Maksimal posisi','max_applications'=>'Maksimal lamaran terkirim','max_registration_days'=>'Maksimal hari pendaftaran'] as $field=>$label)<x-form-field :name="$field" :label="$label" type="number" :value="$package->$field" :min="$field==='price' ? 0 : 1" required />@endforeach
<x-form-field name="is_active" label="Status"><select id="is_active" name="is_active" class="mt-2 w-full rounded-lg border-slate-300"><option value="1" @selected(old('is_active',$package->is_active ?? true))>Aktif</option><option value="0" @selected(!old('is_active',$package->is_active ?? true))>Nonaktif</option></select></x-form-field>
<x-button>Simpan paket</x-button></form></x-section-card>
@endsection
