@extends('admin.layouts.sidebar')

@section('title', 'Manajemen Volunteer')

@section('content')

<div
    x-data="{
        editVolunteer: null,
        deleteVolunteer: null
    }"
    class="w-full max-w-7xl mx-auto space-y-6"
>

    <div>
        <h1 class="text-2xl font-bold text-slate-800">
            Manajemen Volunteer
        </h1>

        <p class="mt-1 text-sm text-slate-500">
            Kelola akun Volunteer dalam sistem SkillMatch.
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

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <p class="text-xs font-semibold text-slate-400">
                Total Volunteer
            </p>

            <p class="mt-2 text-2xl font-bold text-slate-800">
                {{ $totalVolunteers }}
            </p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <p class="text-xs font-semibold text-slate-400">
                Aktif
            </p>

            <p class="mt-2 text-2xl font-bold text-emerald-600">
                {{ $activeVolunteers }}
            </p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <p class="text-xs font-semibold text-slate-400">
                Nonaktif
            </p>

            <p class="mt-2 text-2xl font-bold text-red-600">
                {{ $inactiveVolunteers }}
            </p>
        </div>

    </div>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">

        <form
            method="GET"
            action="{{ route('admin.volunteers.index') }}"
            class="flex items-end gap-3"
        >

            <div class="flex-1 min-w-0">

                <label class="block text-sm font-semibold text-slate-700 mb-2">
                    Cari Volunteer
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
                href="{{ route('admin.volunteers.index') }}"
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
                Daftar Volunteer
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Menampilkan {{ $volunteers->firstItem() ?? 0 }}-{{ $volunteers->lastItem() ?? 0 }}
                dari {{ $volunteers->total() }} Volunteer.
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

                    @forelse($volunteers as $volunteer)

                        <tr class="hover:bg-slate-50/70">

                            <td class="px-6 py-4 text-slate-500">
                                {{ $volunteers->firstItem() + $loop->index }}
                            </td>

                            <td class="px-6 py-4">

                                <div class="font-semibold text-slate-800">
                                    {{ $volunteer->name }}
                                </div>

                                <div class="text-xs text-slate-500 mt-1">
                                    {{ $volunteer->email }}
                                </div>

                            </td>

                            <td class="px-6 py-4">

                                <span class="inline-flex px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-600 text-xs font-bold">
                                    Volunteer
                                </span>

                            </td>

                            <td class="px-6 py-4">

                                @if($volunteer->is_active)

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
                                {{ $volunteer->created_at->format('d M Y') }}
                            </td>

                            <td class="px-6 py-4">

                                <div class="flex justify-end items-center gap-2">

                                    <button
                                        type="button"
                                        @click="editVolunteer = {{ $volunteer->id }}"
                                        class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 text-xs font-semibold transition"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke-width="2"
                                            stroke="currentColor"
                                            class="w-4 h-4"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M16.862 4.487a2.25 2.25 0 113.182 3.182L8.25 19.463 4.5 20.25l.788-3.75L16.862 4.487z"
                                            />
                                        </svg>

                                        Edit
                                    </button>

                                    <button
                                        type="button"
                                        @click="deleteVolunteer = {{ $volunteer->id }}"
                                        class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 text-xs font-semibold transition"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke-width="2"
                                            stroke="currentColor"
                                            class="w-4 h-4"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M6 7.5h12M9.75 7.5V5.25A1.25 1.25 0 0111 4h2a1.25 1.25 0 011.25 1.25V7.5m-7.5 0 .75 12A1.5 1.5 0 009 21h6a1.5 1.5 0 001.5-1.5l.75-12M10.5 11.25v6M13.5 11.25v6"
                                            />
                                        </svg>

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
                                Tidak ada Volunteer yang ditemukan.
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
                    action="{{ route('admin.volunteers.index') }}"
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

            @if($volunteers->hasPages())

                <div>
                    {{ $volunteers->links() }}
                </div>

            @endif

        </div>

    </div>

    <template x-teleport="body">

        <div
            x-show="editVolunteer"
            x-cloak
            class="fixed inset-0 z-[9999] flex h-screen w-screen items-center justify-center p-4"
        >

            <div
                class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm"
                @click="editVolunteer = null"
            ></div>

            @foreach($volunteers as $volunteer)

                <div
                    x-show="editVolunteer === {{ $volunteer->id }}"
                    x-cloak
                    class="relative z-10 w-full max-w-md rounded-2xl bg-white shadow-2xl"
                >

                    <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100">

                        <div>

                            <h2 class="text-lg font-bold text-slate-800">
                                Ubah Status Volunteer
                            </h2>

                            <p class="mt-1 text-sm text-slate-500">
                                {{ $volunteer->name }}
                            </p>

                        </div>

                        <button
                            type="button"
                            @click="editVolunteer = null"
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

                    <div class="p-6">

                        <div class="rounded-xl bg-slate-50 border border-slate-100 p-4">

                            <p class="text-xs font-semibold text-slate-400">
                                Email
                            </p>

                            <p class="mt-1 text-sm font-semibold text-slate-700">
                                {{ $volunteer->email }}
                            </p>

                        </div>

                        <div class="mt-5">

                            <p class="text-sm font-semibold text-slate-700">
                                Status saat ini
                            </p>

                            <div class="mt-2">

                                @if($volunteer->is_active)

                                    <span class="inline-flex px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-600 text-xs font-bold">
                                        Aktif
                                    </span>

                                @else

                                    <span class="inline-flex px-3 py-1.5 rounded-lg bg-red-50 text-red-600 text-xs font-bold">
                                        Nonaktif
                                    </span>

                                @endif

                            </div>

                        </div>

                        <form
                            action="{{ route('admin.volunteers.toggle-status', $volunteer) }}"
                            method="POST"
                            class="mt-6 flex justify-end gap-3"
                        >

                            @csrf
                            @method('PATCH')

                            <button
                                type="button"
                                @click="editVolunteer = null"
                                class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-sm font-semibold hover:bg-slate-50 transition"
                            >
                                Batal
                            </button>

                            <button
                                type="submit"
                                class="px-5 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 transition"
                            >
                                @if($volunteer->is_active)
                                    Nonaktifkan
                                @else
                                    Aktifkan
                                @endif
                            </button>

                        </form>

                    </div>

                </div>

            @endforeach

        </div>

    </template>

    <template x-teleport="body">

        <div
            x-show="deleteVolunteer"
            x-cloak
            class="fixed inset-0 z-[10000] flex h-screen w-screen items-center justify-center p-4"
        >

            <div
                class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm"
                @click="deleteVolunteer = null"
            ></div>

            @foreach($volunteers as $volunteer)

                <div
                    x-show="deleteVolunteer === {{ $volunteer->id }}"
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
                            Hapus Volunteer?
                        </h2>

                        <p class="mt-2 text-sm leading-6 text-slate-500">
                            Anda akan menghapus akun
                            <span class="font-semibold text-slate-700">
                                {{ $volunteer->name }}
                            </span>
                            secara permanen.
                        </p>

                        <p class="mt-3 text-xs text-red-500">
                            Tindakan ini tidak dapat dibatalkan.
                        </p>

                        <form
                            action="{{ route('admin.volunteers.destroy', $volunteer) }}"
                            method="POST"
                            class="mt-6 flex justify-end gap-3"
                        >

                            @csrf
                            @method('DELETE')

                            <button
                                type="button"
                                @click="deleteVolunteer = null"
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

</div>

@endsection