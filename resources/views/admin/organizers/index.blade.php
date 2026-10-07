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
    class="w-full max-w-7xl mx-auto space-y-6"
>

    <div>
        <h1 class="text-2xl font-bold text-slate-800">
            Manajemen Organizer
        </h1>

        <p class="mt-1 text-sm text-slate-500">
            Kelola dan verifikasi akun Organizer dalam sistem SkillMatch.
        </p>
    </div>

    @if(session('success'))
        <div class="rounded-xl bg-emerald-50 border border-emerald-100 px-4 py-3 text-sm text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-xl bg-red-50 border border-red-100 px-4 py-3 text-sm text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <p class="text-xs font-semibold text-slate-400">
                Total Organizer
            </p>

            <p class="mt-2 text-2xl font-bold text-slate-800">
                {{ $totalOrganizers }}
            </p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <p class="text-xs font-semibold text-slate-400">
                Pending
            </p>

            <p class="mt-2 text-2xl font-bold text-amber-600">
                {{ $pendingOrganizers }}
            </p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <p class="text-xs font-semibold text-slate-400">
                Aktif
            </p>

            <p class="mt-2 text-2xl font-bold text-emerald-600">
                {{ $activeOrganizers }}
            </p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
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
            class="flex items-end gap-3"
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

            <div class="w-52 flex-shrink-0">

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
                        Pending
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

            <a
                href="{{ route('admin.organizers.index') }}"
                title="Reset Filter"
                class="h-11 w-11 flex-shrink-0 flex items-center justify-center rounded-xl border border-slate-200 text-slate-500 hover:bg-slate-50 hover:text-slate-700 transition"
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
                        d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.992 0 3.181 3.183a8.25 8.25 0 0013.803-3.7M21.015 4.356v4.992m0 0h-4.992m4.992 0-3.181-3.183a8.25 8.25 0 00-13.803 3.7"
                    />
                </svg>
            </a>

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
                                        Pending
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
                                        class="px-3 py-2 rounded-lg bg-slate-50 text-slate-600 hover:bg-slate-100 text-xs font-semibold transition"
                                    >
                                        View
                                    </button>

                                    <button
                                        type="button"
                                        @click="editOrganizer = {{ $organizer->id }}"
                                        class="px-3 py-2 rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 text-xs font-semibold transition"
                                    >
                                        Edit
                                    </button>

                                    <button
                                        type="button"
                                        @click="deleteOrganizer = {{ $organizer->id }}"
                                        class="px-3 py-2 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 text-xs font-semibold transition"
                                    >
                                        Delete
                                    </button>

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

        <div class="px-6 py-5 border-t border-slate-100 flex flex-col md:flex-row md:items-center md:justify-between gap-4">

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

            @if($organizers->hasPages())

                <div>
                    {{ $organizers->links() }}
                </div>

            @endif

        </div>

    </div>

    <template x-teleport="body">

        <div
            x-show="viewOrganizer"
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

                        <button
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

                                @forelse($organizer->organizerDocuments as $document)
                                    <div class="flex items-center justify-between gap-4 rounded-xl border border-slate-100 bg-slate-50 p-4">
                                        <div class="min-w-0">
                                            <p class="truncate font-semibold text-slate-800">
                                                {{ $document->document_name }}
                                            </p>

                                            <p class="mt-1 text-xs uppercase text-slate-500">
                                                {{ $document->document_type }}
                                            </p>
                                        </div>

                                        <button
                                            type="button"
                                            @click="
                                                documentPreview = '{{ route('admin.organizers.documents.view', $document) }}';
                                                documentName = @js($document->document_name);
                                                documentType = '{{ strtolower(pathinfo($document->file_path, PATHINFO_EXTENSION)) }}';
                                            "
                                            class="shrink-0 inline-flex items-center rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700"
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
            x-show="editOrganizer"
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

                        <button
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
                                Pending
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
                                type="button"
                                @click="editOrganizer = null"
                                class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-sm font-semibold hover:bg-slate-50 transition"
                            >
                                Batal
                            </button>

                            <button
                                type="submit"
                                class="px-5 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 transition"
                            >
                                Simpan
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
            class="fixed inset-0 z-[9999] flex h-screen w-screen items-center justify-center p-4"
        >

            <div
                class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm"
                @click="deleteOrganizer = null"
            ></div>

            @foreach($organizers as $organizer)

                <div
                    x-show="deleteOrganizer === {{ $organizer->id }}"
                    x-cloak
                    class="relative z-10 w-full max-w-md rounded-2xl bg-white shadow-2xl"
                >

                    <div class="p-6">

                        <div class="w-12 h-12 rounded-xl bg-red-50 text-red-600 flex items-center justify-center">

                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="2"
                                stroke="currentColor"
                                class="w-6 h-6"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M12 9v3.75m0 3.75h.007v.008H12V16.5zM10.5 3.75h3L19.5 9v6l-6 5.25h-3L4.5 15V9l6-5.25z"
                                />
                            </svg>

                        </div>

                        <h2 class="mt-5 text-lg font-bold text-slate-800">
                            Hapus Organizer?
                        </h2>

                        <p class="mt-2 text-sm leading-6 text-slate-500">
                            Anda akan menghapus akun
                            <span class="font-semibold text-slate-700">
                                {{ $organizer->name }}
                            </span>
                            secara permanen.
                        </p>

                        <form
                            action="{{ route('admin.organizers.destroy', $organizer) }}"
                            method="POST"
                            class="mt-6 flex justify-end gap-3"
                        >

                            @csrf
                            @method('DELETE')

                            <button
                                type="button"
                                @click="deleteOrganizer = null"
                                class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-sm font-semibold hover:bg-slate-50 transition"
                            >
                                Batal
                            </button>

                            <button
                                type="submit"
                                class="px-5 py-2.5 rounded-xl bg-red-600 text-white text-sm font-semibold hover:bg-red-700 transition"
                            >
                                Hapus
                            </button>

                        </form>

                    </div>

                </div>

            @endforeach

        </div>

    </template>

    <template x-teleport="body">

        <div
            x-show="documentPreview"
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

                    <button
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
@endsection