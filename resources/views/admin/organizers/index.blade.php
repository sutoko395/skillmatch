@extends('admin.layouts.sidebar')

@section('title', 'Manajemen Organizer')

@section('content')
<div
    x-data="{
        viewOrganizer: null,
        editOrganizer: null,
        deleteOrganizer: null,
        documentPreview: null,
        documentName: null,
        documentType: null
    }"
    class="admin-users w-full max-w-7xl mx-auto space-y-6"
>

    <div>
        <h1 class="text-2xl font-bold text-slate-800">
            Manajemen Organizer
        </h1>

        <p class="mt-1 text-sm text-slate-500">
            Kelola dan verifikasi akun Organizer dalam sistem SkillMatch.
        </p>
    </div>

    <div class="admin-summary grid grid-cols-2 lg:grid-cols-4 gap-4">

        <div class="admin-stat admin-stat--blue bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <p class="text-xs font-semibold text-slate-400">
                Total Organizer
            </p>

            <p class="mt-2 text-2xl font-bold text-slate-800">
                {{ $totalOrganizers }}
            </p>
        </div>

        <div class="admin-stat admin-stat--amber bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <p class="text-xs font-semibold text-slate-400">
                Menunggu verifikasi
            </p>

            <p class="mt-2 text-2xl font-bold text-amber-600">
                {{ $pendingOrganizers }}
            </p>
        </div>

        <div class="admin-stat admin-stat--green bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <p class="text-xs font-semibold text-slate-400">
                Aktif
            </p>

            <p class="mt-2 text-2xl font-bold text-emerald-600">
                {{ $activeOrganizers }}
            </p>
        </div>

        <div class="admin-stat admin-stat--red bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <p class="text-xs font-semibold text-slate-400">
                Nonaktif
            </p>

            <p class="mt-2 text-2xl font-bold text-red-600">
                {{ $inactiveOrganizers }}
            </p>
        </div>

    </div>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">

        <form
            method="GET"
            action="{{ route('admin.organizers.index') }}"
            class="admin-user-filters flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-end lg:flex-nowrap"
        >

            <div class="flex-1 min-w-0">

                <label class="block text-sm font-semibold text-slate-700 mb-2">
                    Cari Organizer
                </label>

                <input
                    type="text"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Nama atau email..."
                    class="w-full h-11 rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                >

            </div>

            <div class="w-full sm:w-52 flex-shrink-0">

                <label class="block text-sm font-semibold text-slate-700 mb-2">
                    Status
                </label>

                <select
                    name="status"
                    class="w-full h-11 rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                >

                    <option value="">
                        Semua Status
                    </option>

                    <option
                        value="pending"
                        @selected($status === 'pending')
                    >
                        Menunggu verifikasi
                    </option>

                    <option
                        value="active"
                        @selected($status === 'active')
                    >
                        Aktif
                    </option>

                    <option
                        value="inactive"
                        @selected($status === 'inactive')
                    >
                        Nonaktif
                    </option>

                </select>

            </div>

            <button
                type="submit"
                class="h-11 px-5 flex-shrink-0 rounded-xl bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 transition"
            >
                Cari
            </button>

            @if(request()->filled('search') || request()->filled('status') || (int) request('per_page', 10) !== 10)
            <a href="{{ route('admin.organizers.index') }}" class="admin-filter-reset">Reset</a>
            @endif

        </form>

    </div>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">

        <div class="px-6 py-5 border-b border-slate-100">

            <h2 class="text-lg font-bold text-slate-800">
                Daftar Organizer
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Menampilkan {{ $organizers->firstItem() ?? 0 }}-{{ $organizers->lastItem() ?? 0 }}
                dari {{ $organizers->total() }} Organizer.
            </p>

        </div>

        <div class="flex justify-end border-b border-slate-100 px-6 py-4">
<div class="flex items-center gap-2 text-sm text-slate-500">

                <span>
                    Lihat
                </span>

                <form
                    method="GET"
                    action="{{ route('admin.organizers.index') }}"
                >

                    <input
                        type="hidden"
                        name="search"
                        value="{{ $search }}"
                    >

                    <input
                        type="hidden"
                        name="status"
                        value="{{ $status }}"
                    >

                    <select
                        aria-label="Jumlah data per halaman"
                        name="per_page"
                        onchange="this.form.submit()"
                        class="rounded-lg border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >

                        <option
                            value="10"
                            @selected($perPage === 10)
                        >
                            10
                        </option>

                        <option
                            value="50"
                            @selected($perPage === 50)
                        >
                            50
                        </option>

                        <option
                            value="100"
                            @selected($perPage === 100)
                        >
                            100
                        </option>

                    </select>

                </form>

                <span>
                    data per halaman
                </span>

            </div>
        </div>

        <div class="overflow-x-auto">

            <table class="w-full text-sm">

                <thead class="bg-slate-50">

                    <tr>

                        <th class="px-6 py-4 text-left font-semibold text-slate-600">
                            No
                        </th>

                        <th class="px-6 py-4 text-left font-semibold text-slate-600">
                            User
                        </th>

                        <th class="px-6 py-4 text-left font-semibold text-slate-600">
                            Role
                        </th>

                        <th class="px-6 py-4 text-left font-semibold text-slate-600">
                            Status
                        </th>

                        <th class="px-6 py-4 text-left font-semibold text-slate-600">
                            Terdaftar
                        </th>

                        <th class="px-6 py-4 text-right font-semibold text-slate-600">
                            Aksi
                        </th>

                    </tr>

                </thead>

                <tbody class="divide-y divide-slate-100">

                    @forelse($organizers as $organizer)

                        <tr class="hover:bg-slate-50/70">

                            <td class="px-6 py-4 text-slate-500">
                                {{ $organizers->firstItem() + $loop->index }}
                            </td>

                            <td class="px-6 py-4">

                                <div class="font-semibold text-slate-800">
                                    {{ $organizer->name }}
                                </div>

                                <div class="text-xs text-slate-500 mt-1">
                                    {{ $organizer->email }}
                                </div>

                            </td>

                            <td class="px-6 py-4">

                                <span class="inline-flex px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-600 text-xs font-bold">
                                    Organizer
                                </span>

                            </td>

                            <td class="px-6 py-4">

                                @if($organizer->organizer_status === 'pending')

                                    <span class="inline-flex px-2.5 py-1 rounded-lg bg-amber-50 text-amber-600 text-xs font-bold">
                                        Menunggu verifikasi
                                    </span>

                                @elseif($organizer->organizer_status === 'active')

                                    <span class="inline-flex px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-600 text-xs font-bold">
                                        Aktif
                                    </span>

                                @else

                                    <span class="inline-flex px-2.5 py-1 rounded-lg bg-red-50 text-red-600 text-xs font-bold">
                                        Nonaktif
                                    </span>

                                @endif

                            </td>

                            <td class="px-6 py-4 text-slate-500">
                                {{ $organizer->created_at->format('d M Y') }}
                            </td>

                            <td class="px-6 py-4">

                                <div class="flex justify-end items-center gap-2">

                                    <button
                                        type="button"
                                        @click="viewOrganizer = {{ $organizer->id }}"
                                        class="admin-action admin-action-blue px-3 py-2 rounded-lg bg-slate-50 text-slate-600 hover:bg-slate-100 text-xs font-semibold transition"
                                    >
                                        Detail
                                    </button>

                                    <button
                                        type="button"
                                        @click="editOrganizer = {{ $organizer->id }}"
                                        class="admin-action admin-action-blue px-3 py-2 rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 text-xs font-semibold transition"
                                    >
                                        <svg class="admin-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m16 3 5 5M4 20l4-1 13-13a2.8 2.8 0 0 0-4-4L4 15l-1 6Z"/></svg>Edit
                                    </button>

                                    <button
                                        type="button"
                                        @click="deleteOrganizer = {{ $organizer->id }}"
                                        class="admin-action admin-action-danger px-3 py-2 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 text-xs font-semibold transition"
                                    ><svg class="admin-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M3 6h18M9 6V3h6v3M5 6l1 15h12l1-15M10 10v7m4-7v7"/></svg>Hapus</button>

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="6"
                                class="px-6 py-12 text-center text-slate-500"
                            >
                                Tidak ada Organizer yang ditemukan.
                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        @if($organizers->hasPages())
        <div class="border-t border-slate-100 px-6 py-4">{{ $organizers->links() }}</div>
        @endif

    </div>

    <template x-teleport="body">

        <div
            x-show="viewOrganizer" @keydown.escape.window="viewOrganizer = null"
            x-cloak
            class="fixed inset-0 z-[9999] flex h-screen w-screen items-center justify-center p-4"
        >

            <div
                class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm"
                @click="viewOrganizer = null"
            ></div>

            @foreach($organizers as $organizer)

                <div
                    x-show="viewOrganizer === {{ $organizer->id }}"
                    x-cloak
                    class="relative z-10 w-full max-w-4xl max-h-[90vh] overflow-y-auto rounded-2xl bg-white shadow-2xl"
                >

                    <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100">

                        <div>

                            <h2 class="text-xl font-bold text-slate-800">
                                Detail Organizer
                            </h2>

                            <p class="mt-1 text-sm text-slate-500">
                                Informasi akun, profil, dan dokumen Organizer.
                            </p>

                        </div>

                        <button aria-label="Tutup dialog"
                            type="button"
                            @click="viewOrganizer = null"
                            class="w-9 h-9 flex items-center justify-center rounded-xl text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="2"
                                stroke="currentColor"
                                class="w-5 h-5"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M6 18 18 6M6 6l12 12"
                                />
                            </svg>
                        </button>

                    </div>

                    <div class="p-6 space-y-6">
                        @if($organizer->organizer_verification_note)
                            <section class="rounded-xl border border-amber-200 bg-amber-50 p-4" aria-label="Catatan pemeriksaan terakhir">
                                <h3>Catatan pemeriksaan terakhir</h3>
                                <p class="mt-2 whitespace-pre-line text-sm text-amber-900">{{ $organizer->organizer_verification_note }}</p>
                                <p class="mt-2 text-xs text-amber-800">{{ $organizer->last_reviewed_at ? $organizer->last_reviewed_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i').' WIB' : 'Waktu keputusan belum tercatat' }}</p>
                            </section>
                        @endif


                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                            <div>
                                <p class="text-xs text-slate-400">
                                    Nama Akun
                                </p>

                                <p class="mt-1 font-semibold text-slate-800">
                                    {{ $organizer->name }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-slate-400">
                                    Email
                                </p>

                                <p class="mt-1 font-semibold text-slate-800">
                                    {{ $organizer->email }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-slate-400">
                                    Nama Organisasi
                                </p>

                                <p class="mt-1 font-semibold text-slate-800">
                                    {{ $organizer->organizerProfile?->organization_name ?: '-' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-slate-400">
                                    Contact Person
                                </p>

                                <p class="mt-1 font-semibold text-slate-800">
                                    {{ $organizer->organizerProfile?->contact_person ?: '-' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-slate-400">
                                    Telepon
                                </p>

                                <p class="mt-1 font-semibold text-slate-800">
                                    {{ $organizer->organizerProfile?->phone ?: '-' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-slate-400">
                                    Website
                                </p>

                                <p class="mt-1 font-semibold text-slate-800">
                                    {{ $organizer->organizerProfile?->website ?: '-' }}
                                </p>
                            </div>

                            <div class="md:col-span-2">

                                <p class="text-xs text-slate-400">
                                    Alamat
                                </p>

                                <p class="mt-1 font-semibold text-slate-800">
                                    {{ $organizer->organizerProfile?->address ?: '-' }}
                                </p>

                            </div>

                            <div class="md:col-span-2">

                                <p class="text-xs text-slate-400">
                                    Deskripsi
                                </p>

                                <p class="mt-1 text-sm leading-6 text-slate-600">
                                    {{ $organizer->organizerProfile?->description ?: '-' }}
                                </p>

                            </div>

                        </div>

                        <div class="border-t border-slate-100 pt-6">

                            <h3 class="text-lg font-bold text-slate-800">
                                Dokumen Organizer
                            </h3>

                            <div class="mt-4 space-y-3">

                                @forelse($organizer->organizerDocuments->where('document_type', 'ktp')->sortByDesc('created_at')->take(1) as $document)
                                    <div class="flex items-center justify-between gap-4 rounded-xl border border-slate-100 bg-slate-50 p-4">
                                        <div class="min-w-0">
                                            <p class="truncate font-semibold text-slate-800">
                                                {{ $document->document_name }}
                                            </p>

                                            <p class="mt-1 text-xs uppercase text-slate-500">
                                                {{ $document->document_type === 'ktp' ? 'Dokumen verifikasi' : strtoupper($document->document_type) }}
                                            </p>
                                        </div>

                                        <button
                                            type="button"
                                            @click="
                                                documentPreview = '{{ route('admin.organizers.documents.view', $document) }}';
                                                documentName = @js($document->document_name);
                                                documentType = '{{ strtolower(pathinfo($document->file_path, PATHINFO_EXTENSION)) }}';
                                            "
                                            class="admin-action admin-action-blue shrink-0 inline-flex items-center rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700"
                                        >
                                            Lihat Dokumen
                                        </button>
                                    </div>
                                @empty

                                    <div class="rounded-xl bg-amber-50 border border-amber-100 px-4 py-4 text-sm text-amber-700">
                                        Belum ada dokumen yang diupload oleh Organizer.
                                    </div>

                                @endforelse

                            </div>

                        </div>

                    </div>

                </div>

            @endforeach

        </div>

    </template>

    <template x-teleport="body">

        <div
            x-show="editOrganizer" @keydown.escape.window="editOrganizer = null"
            x-cloak
            class="fixed inset-0 z-[9999] flex h-screen w-screen items-center justify-center p-4"
        >

            <div
                class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm"
                @click="editOrganizer = null"
            ></div>

            @foreach($organizers as $organizer)

                <div
                    x-show="editOrganizer === {{ $organizer->id }}"
                    x-cloak
                    class="relative z-10 w-full max-w-md rounded-2xl bg-white shadow-2xl"
                >

                    <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100">

                        <div>

                            <h2 class="text-lg font-bold text-slate-800">
                                Ubah Status Organizer
                            </h2>

                            <p class="mt-1 text-sm text-slate-500">
                                {{ $organizer->name }}
                            </p>

                        </div>

                        <button aria-label="Tutup dialog"
                            type="button"
                            @click="editOrganizer = null"
                            class="w-9 h-9 flex items-center justify-center rounded-xl text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="2"
                                stroke="currentColor"
                                class="w-5 h-5"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M6 18 18 6M6 6l12 12"
                                />
                            </svg>
                        </button>

                    </div>

                    <form
                        action="{{ route('admin.organizers.update-status', $organizer) }}"
                        method="POST"
                        class="p-6"
                    >

                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="revision" value="{{ $organizer->organizer_revision }}">
                        <label for="account_active_{{ $organizer->id }}" class="block text-sm font-semibold text-slate-700">Akses akun (terpisah dari verifikasi organisasi)</label>
                        <select id="account_active_{{ $organizer->id }}" name="is_active" class="mb-4 mt-2 w-full rounded-xl border-slate-200">
                            <option value="1" @selected($organizer->is_active)>Aktif</option>
                            <option value="0" @selected(!$organizer->is_active)>Nonaktif — sesi dan akses terproteksi ditolak</option>
                        </select>
                        <x-form-field name="verification_note" label="Catatan pemeriksaan (wajib jika perlu revisi)" :value="old('verification_note', $organizer->organizer_verification_note)" />

                        <label class="block text-sm font-semibold text-slate-700">
                            Status Organizer
                        </label>

                        <select
                            name="organizer_status"
                            class="mt-2 w-full h-11 rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >

                            <option
                                value="pending"
                                @selected($organizer->organizer_status === 'pending')
                            >
                                Menunggu verifikasi
                            </option>

                            <option
                                value="active"
                                @selected($organizer->organizer_status === 'active')
                            >
                                Aktif
                            </option>

                            <option
                                value="inactive"
                                @selected($organizer->organizer_status === 'inactive')
                            >
                                Nonaktif
                            </option>

                        </select>

                        <div class="mt-6 flex justify-end gap-3">

                            <button
                                type="submit"
                                class="admin-action admin-action-blue px-5 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 transition"
                            >
                                Simpan
                            </button>

                            <button
                                type="button"
                                @click="editOrganizer = null"
                                class="admin-action admin-action-danger px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-sm font-semibold hover:bg-slate-50 transition"
                            >
                                Batal
                            </button>

                        </div>

                    </form>

                </div>

            @endforeach

        </div>

    </template>

    <template x-teleport="body">
        <div
            x-show="deleteOrganizer"
            x-cloak
            @keydown.escape.window="deleteOrganizer = null"
            class="fixed inset-0 z-[99999] flex min-h-screen items-center justify-center p-4"
        >
            <div
                class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm"
                @click="deleteOrganizer = null"
            ></div>

            @foreach($organizers as $organizer)
                <div
                    x-show="deleteOrganizer === {{ $organizer->id }}"
                    x-cloak
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="relative z-10 mx-auto w-full max-w-lg rounded-2xl bg-white shadow-2xl"
                >
                    <div class="p-7">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-red-50 text-red-600">
                            <svg
                                class="h-6 w-6"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                                stroke-width="2"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M12 9v3.75m0 3.75h.007v.008H12V16.5zM10.5 3.75h3L19.5 9v6l-6 5.25h-3L4.5 15V9l6-5.25z"
                                />
                            </svg>
                        </div>

                        <h2 class="mt-5 text-xl font-bold text-slate-800">
                            Hapus Organizer?
                        </h2>

                        <p class="mt-2 text-sm leading-6 text-slate-500">
                            Anda akan menghapus akun
                            <span class="font-semibold text-slate-700">
                                {{ $organizer->name }}
                            </span>
                            secara permanen jika belum memiliki riwayat. Akun yang memiliki data terkait harus dinonaktifkan agar dokumen dan riwayat tetap tersedia.
                        </p>

                        <form
                            action="{{ route('admin.organizers.destroy', $organizer) }}"
                            method="POST"
                            class="mt-8 flex w-full items-center justify-center gap-3"
                        >
                            @csrf
                            @method('DELETE')

                            <button
                                type="submit"
                                style="background-color: #dc2626 !important; color: #ffffff !important;"
                                class="!inline-flex h-12 min-w-36 items-center justify-center gap-2 rounded-xl !bg-red-600 px-5 text-sm font-semibold !text-white shadow-sm transition hover:!bg-red-700 active:scale-[0.98]"
                            >
                                <svg
                                    class="h-5 w-5"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <path d="M3 6h18M9 6V3h6v3M5 6l1 15h12l1-15M10 10v7m4-7v7"/>
                                </svg>
                                Hapus
                            </button>

                            <button
                                type="button"
                                @click="deleteOrganizer = null"
                                class="inline-flex h-12 min-w-36 items-center justify-center rounded-xl border border-slate-200 bg-white px-5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50 active:scale-[0.98]"
                            >
                                Batal
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </template>

    <template x-teleport="body">

        <div
            x-show="documentPreview" @keydown.escape.window="documentPreview = null"
            x-cloak
            class="fixed inset-0 z-[10000] flex h-screen w-screen items-center justify-center p-4"
        >

            <div
                class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm"
                @click="documentPreview = null"
            ></div>

            <div
                class="relative z-10 w-full max-w-5xl h-[90vh] rounded-2xl bg-white shadow-2xl overflow-hidden flex flex-col"
            >

                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                    <div>
                        <h2
                            class="text-lg font-bold text-slate-800"
                            x-text="documentName"
                        ></h2>

                        <p class="text-xs text-slate-500 mt-1">
                            Preview Dokumen Organizer
                        </p>
                    </div>

                    <button aria-label="Tutup dialog"
                        type="button"
                        @click="documentPreview = null"
                        class="w-9 h-9 flex items-center justify-center rounded-xl text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="2"
                            stroke="currentColor"
                            class="w-5 h-5"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M6 18 18 6M6 6l12 12"
                            />
                        </svg>
                    </button>
                </div>

                <div class="flex-1 bg-slate-100 overflow-auto flex items-center justify-center p-4">

                    <template x-if="['jpg', 'jpeg', 'png', 'webp'].includes(documentType)">
                        <img
                            :src="documentPreview"
                            :alt="documentName"
                            class="max-w-full max-h-full object-contain rounded-xl shadow-sm"
                        >
                    </template>

                    <template x-if="documentType === 'pdf'">
                        <iframe
                            :src="documentPreview"
                            class="w-full h-full rounded-xl bg-white"
                        ></iframe>
                    </template>

                </div>

            </div>

        </div>

    </template>
</div>

@if(session('delete_error'))
    <div
        x-data="{ show: true }"
        x-show="show"
        x-cloak
        @keydown.escape.window="show = false"
        class="fixed inset-0 z-[100000] flex min-h-screen items-center justify-center p-4"
    >
        <div
            class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm"
            @click="show = false"
        ></div>

        <div class="relative z-10 w-full max-w-md rounded-2xl bg-white p-7 shadow-2xl">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v3.75m0 3.75h.007v.008H12V16.5zM10.5 3.75h3L19.5 9v6l-6 5.25h-3L4.5 15V9l6-5.25z"/>
                </svg>
            </div>

            <h2 class="mt-5 text-xl font-bold text-slate-800">
                Organizer Tidak Dapat Dihapus
            </h2>

            <p class="mt-3 text-sm leading-6 text-slate-500">
                {{ session('delete_error') }}
            </p>

            <div class="mt-8 flex justify-center">
                <button
                    type="button"
                    @click="show = false"
                    class="inline-flex h-12 min-w-36 items-center justify-center rounded-xl bg-indigo-600 px-6 text-sm font-semibold text-white transition hover:bg-indigo-700"
                >
                    Mengerti
                </button>
            </div>
        </div>
    </div>
@endif
@endsection
