@extends('organizer.layouts.sidebar')

@section('title', 'Verifikasi Organizer')

@section('content')
<div class="w-full max-w-7xl mx-auto space-y-6">

    <div>
        <h1 class="text-2xl font-bold text-slate-800">
            Verifikasi Organizer
        </h1>

        <p class="mt-1 text-sm text-slate-500">
            Pengajuan organisasi Anda sedang dalam proses pemeriksaan admin.
        </p>
    </div>

    <x-section-card class="p-6 md:p-8">
        <div class="mx-auto max-w-2xl text-center">

            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-amber-50 text-amber-600">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.8"
                    stroke="currentColor"
                    class="h-8 w-8"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M12 6v6l4 2m6-2a10 10 0 1 1-20 0 10 10 0 0 1 20 0Z"
                    />
                </svg>
            </div>

            <h2 class="mt-5 text-xl font-bold text-slate-800">
                Pengajuan Organizer sedang diproses
            </h2>

            <p class="mt-3 text-sm leading-6 text-slate-600">
                Data organisasi dan dokumen pendukung Anda telah berhasil dikirim.
                Admin sedang melakukan validasi akun Anda.
                Mohon menunggu maksimal 2 hari sampai proses verifikasi selesai.
            </p>

            <div class="mt-6 rounded-xl bg-slate-50 p-5 text-sm leading-6 text-slate-600">
                Setelah akun Anda diaktifkan oleh admin, Anda dapat login dan menggunakan dashboard Organizer.
            </div>

            <div class="mt-6">
                <a
                    href="{{ route('organizer.profile.edit') }}"
                    class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                >
                    Lihat Profil
                </a>
            </div>

        </div>
    </x-section-card>

</div>
@endsection