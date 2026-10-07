@extends('layouts.user')

@section('title', 'Verifikasi Organizer')

@section('content')
<div class="mx-auto max-w-2xl">
    <x-page-header title="Akun Sedang Diverifikasi" />

    <x-empty-state
        title="Pengajuan Organizer sedang diproses"
        description="Data organisasi dan dokumen pendukung Anda telah berhasil dikirim. Admin sedang melakukan validasi akun Anda. Mohon menunggu maksimal 2 hari sampai proses verifikasi selesai."
    >
        <p class="text-sm text-slate-600">
            Setelah akun Anda diaktifkan oleh admin, Anda dapat login dan menggunakan dashboard Organizer.
        </p>
    </x-empty-state>
</div>
@endsection