@extends('layouts.user')
@section('title', 'Profil Organisasi')
@section('content')
<div class="mx-auto max-w-3xl">
<x-page-header title="Profil Organisasi" description="Perbarui profil dan kontak organisasi Anda." />
<p class="mb-6">Status organisasi: <x-status-badge :status="$user->organizer_status">{{ ['pending' => 'Menunggu verifikasi', 'active' => 'Terverifikasi', 'inactive' => 'Belum terverifikasi / perlu revisi'][$user->organizer_status] }}</x-status-badge></p>
<form method="POST" action="{{ route('organizer.profile.update') }}" class="space-y-6" data-saving-form>
@csrf
<x-section-card class="space-y-5">
    <x-form-field name="organization_name" label="Nama organisasi" :value="$profile?->organization_name" required :readonly="$user->organizer_status === 'active' && $profile" helper="Perubahan nama organisasi terverifikasi menunggu layanan verifikasi ulang." />
    @foreach(['contact_person' => 'Penanggung jawab', 'phone' => 'Telepon', 'email' => 'Email kontak', 'address' => 'Alamat', 'city' => 'Kota'] as $name => $label)
        <x-form-field :name="$name" :label="$label" :value="$profile?->$name" :type="$name === 'email' ? 'email' : 'text'" required />
    @endforeach
    <x-form-field name="website" label="Website" type="url" :value="$profile?->website" />
    <x-form-field name="description" label="Deskripsi" required>
        <textarea name="description" id="description" rows="4" required maxlength="1000" class="mt-2 w-full rounded-xl border-slate-300">{{ old('description', $profile?->description) }}</textarea>
    </x-form-field>
</x-section-card>
<x-empty-state title="Dokumen verifikasi belum tersedia" description="Penyimpanan dan peninjauan dokumen privat sedang disiapkan. Perubahan kontak dapat disimpan tanpa unggah ulang dokumen." />
<x-button type="submit">Simpan profil</x-button><span data-saving-status role="status" class="ml-3 text-sm"></span>
</form>
</div>
@endsection
