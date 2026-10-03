@extends('admin.layouts.sidebar')

@section('title', 'Master Data Skill')

@section('content')
<div
    x-data="{
        addModal: false,
        editModal: false,
        deleteModal: false,
        selectedSkill: null,
        editUrl: '',
        deleteUrl: '',
        editName: '',
        editCategory: ''
    }"
    class="w-full max-w-7xl mx-auto space-y-6"
>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">
                Master Data Skill
            </h1>
            <p class="mt-1 text-sm text-slate-500">
                Kelola daftar skill yang tersedia untuk volunteer.
            </p>
        </div>

        <button
            type="button"
            @click="addModal = true"
            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 transition"
        >
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Tambah Skill
        </button>
    </div>

    @if(session('success'))
        <div class="rounded-xl bg-emerald-50 border border-emerald-100 px-4 py-3 text-sm text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-xl bg-red-50 border border-red-100 px-4 py-3 text-sm text-red-700">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">

        <div class="px-6 py-5 border-b border-slate-100">
            <h2 class="text-lg font-bold text-slate-800">
                Daftar Skill
            </h2>
            <p class="text-sm text-slate-500 mt-1">
                {{ $skills->count() }} skill tersedia.
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
                            Nama Skill
                        </th>
                        <th class="px-6 py-4 text-left font-semibold text-slate-600">
                            Kategori
                        </th>
                        <th class="px-6 py-4 text-right font-semibold text-slate-600">
                            Aksi
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">

                    @forelse($skills as $skill)

                        <tr class="hover:bg-slate-50/70">

                            <td class="px-6 py-4 text-slate-500">
                                {{ $loop->iteration }}
                            </td>

                            <td class="px-6 py-4 font-semibold text-slate-800">
                                {{ $skill->name }}
                            </td>

                            <td class="px-6 py-4 text-slate-600">
                                {{ $skill->category ?: '-' }}
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex justify-end gap-2">

                                    <button
                                        type="button"
                                        @click="
                                            editModal = true;
                                            editUrl = '{{ route('admin.master-data.skills.update', $skill) }}';
                                            editName = @js($skill->name);
                                            editCategory = @js($skill->category);
                                        "
                                        class="px-3 py-2 rounded-lg bg-indigo-50 text-indigo-600 text-xs font-semibold hover:bg-indigo-100 transition"
                                    >
                                        Edit
                                    </button>

                                    <button
                                        type="button"
                                        @click="
                                            deleteModal = true;
                                            deleteUrl = '{{ route('admin.master-data.skills.destroy', $skill) }}';
                                            selectedSkill = @js($skill->name);
                                        "
                                        class="px-3 py-2 rounded-lg bg-red-50 text-red-600 text-xs font-semibold hover:bg-red-100 transition"
                                    >
                                        Hapus
                                    </button>

                                </div>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">

                                    <div class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center mb-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-slate-400">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75v10.5m-5.25-5.25h10.5M6.75 3.75h10.5A2.25 2.25 0 0119.5 6v12a2.25 2.25 0 01-2.25 2.25H6.75A2.25 2.25 0 014.5 18V6a2.25 2.25 0 012.25-2.25z" />
                                        </svg>
                                    </div>

                                    <p class="text-sm font-semibold text-slate-700">
                                        Belum ada skill
                                    </p>

                                    <p class="text-xs text-slate-500 mt-1">
                                        Tambahkan skill pertama Anda.
                                    </p>

                                </div>
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>
        </div>

    </div>

    <template x-teleport="body">
        <div
            x-show="addModal"
            x-cloak
            x-transition.opacity
            class="fixed inset-0 z-[9999] flex h-screen w-screen items-center justify-center p-4"
        >
            <div
                class="fixed inset-0 h-screen w-screen bg-slate-900/50 backdrop-blur-sm"
                @click="addModal = false"
            ></div>

            <div
                x-show="addModal"
                x-transition
                class="relative z-10 w-full max-w-lg bg-white rounded-2xl shadow-2xl"
            >
                <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">
                            Tambah Skill
                        </h3>

                        <p class="text-sm text-slate-500 mt-1">
                            Tambahkan skill baru ke sistem.
                        </p>
                    </div>

                    <button
                        type="button"
                        @click="addModal = false"
                        class="p-2 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form action="{{ route('admin.master-data.skills.store') }}" method="POST">
                    @csrf

                    <div class="p-6 space-y-5">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700">
                                Nama Skill
                            </label>

                            <input
                                type="text"
                                name="name"
                                required
                                class="mt-2 w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="Contoh: Graphic Design"
                            >
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700">
                                Kategori
                            </label>

                            <input
                                type="text"
                                name="category"
                                class="mt-2 w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="Contoh: Kreatif"
                            >
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 px-6 py-4 bg-slate-50 border-t border-slate-100 rounded-b-2xl">
                        <button
                            type="button"
                            @click="addModal = false"
                            class="px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-600 text-sm font-semibold hover:bg-slate-50 transition"
                        >
                            Batal
                        </button>

                        <button
                            type="submit"
                            class="px-4 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 transition"
                        >
                            Simpan Skill
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <template x-teleport="body">
        <div
            x-show="editModal"
            x-cloak
            x-transition.opacity
            class="fixed inset-0 z-[9999] flex h-screen w-screen items-center justify-center p-4"
        >

            <div
                class="fixed inset-0 h-screen w-screen bg-slate-900/50 backdrop-blur-sm"
                @click="editModal = false"
            ></div>

            <div
                x-show="editModal"
                x-transition
                class="relative z-10 w-full max-w-lg bg-white rounded-2xl shadow-2xl"
            >

                <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">
                            Edit Skill
                        </h3>

                        <p class="text-sm text-slate-500 mt-1">
                            Perbarui informasi skill.
                        </p>
                    </div>

                    <button
                        type="button"
                        @click="editModal = false"
                        class="p-2 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form :action="editUrl" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="p-6 space-y-5">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700">
                                Nama Skill
                            </label>

                            <input
                                type="text"
                                name="name"
                                x-model="editName"
                                required
                                class="mt-2 w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700">
                                Kategori
                            </label>

                            <input
                                type="text"
                                name="category"
                                x-model="editCategory"
                                class="mt-2 w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 px-6 py-4 bg-slate-50 border-t border-slate-100 rounded-b-2xl">
                        <button
                            type="button"
                            @click="editModal = false"
                            class="px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-600 text-sm font-semibold hover:bg-slate-50 transition"
                        >
                            Batal
                        </button>

                        <button
                            type="submit"
                            class="px-4 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 transition"
                        >
                            Simpan Perubahan
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </template>

    <template x-teleport="body">
        <div
            x-show="deleteModal"
            x-cloak
            x-transition.opacity
            class="fixed inset-0 z-[9999] flex h-screen w-screen items-center justify-center p-4"
        >

            <div
                class="fixed inset-0 h-screen w-screen bg-slate-900/50 backdrop-blur-sm"
                @click="deleteModal = false"
            ></div>

            <div
                x-show="deleteModal"
                x-transition
                class="relative z-10 w-full max-w-md bg-white rounded-2xl shadow-2xl"
            >

                <div class="p-6 text-center">

                    <div class="mx-auto w-14 h-14 rounded-full bg-red-50 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-7 h-7 text-red-500">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.007v.008H12V16.5zM9.75 3.75h4.5l6 6v4.5l-6 6h-4.5l-6-6v-4.5l6-6z" />
                        </svg>
                    </div>

                    <h3 class="mt-4 text-lg font-bold text-slate-800">
                        Hapus Skill?
                    </h3>

                    <p class="mt-2 text-sm text-slate-500">
                        Skill
                        <span class="font-semibold text-slate-700" x-text="selectedSkill"></span>
                        akan dihapus dari sistem.
                    </p>

                </div>

                <form :action="deleteUrl" method="POST">
                    @csrf
                    @method('DELETE')

                    <div class="flex justify-end gap-3 px-6 py-4 bg-slate-50 border-t border-slate-100 rounded-b-2xl">

                        <button
                            type="button"
                            @click="deleteModal = false"
                            class="px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-600 text-sm font-semibold hover:bg-slate-50 transition"
                        >
                            Batal
                        </button>

                        <button
                            type="submit"
                            class="px-4 py-2.5 rounded-xl bg-red-600 text-white text-sm font-semibold hover:bg-red-700 transition"
                        >
                            Ya, Hapus
                        </button>

                    </div>
                </form>

            </div>
        </div>
    </template>
@endsection