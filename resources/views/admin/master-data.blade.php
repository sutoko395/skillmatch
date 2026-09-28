@extends('admin.layouts.sidebar')

@section('title', 'Kelola Master Data')

@section('content')
<div class="w-full max-w-7xl mx-auto space-y-6 px-4 sm:px-6 md:px-8 py-4">

    {{-- HEADER TITLE --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl md:text-2xl font-bold text-gray-900 tracking-tight">Kelola Master Data</h1>
            <p class="text-xs md:text-sm text-gray-500 mt-1">Atur daftar skill relawan dan kategori event yang tersedia dalam sistem SkillMatch.</p>
        </div>
    </div>

    {{-- ALERT NOTIFIKASI --}}
    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl flex items-center gap-3 shadow-sm text-xs md:text-sm">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-emerald-600 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif

    {{-- GRID SECTION MASTER SKILL & MASTER KATEGORI --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- SECTION MASTER SKILL --}}
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 space-y-6">
            <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 01-3.586-3.586l5.654-4.654m0 0l3.03-2.496c.384-.317.626-.74.766-1.208" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-900">Master Data Skill</h2>
                        <p class="text-xs text-gray-400">Total: {{ count($skills) }} Skill Terdaftar</p>
                    </div>
                </div>
            </div>

            {{-- Form Tambah Skill --}}
            <form action="{{ route('admin.skills.store') }}" method="POST" class="space-y-3 bg-gray-50/70 p-4 rounded-xl border border-gray-100">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <input type="text" name="name" placeholder="Nama Skill (misal: Graphic Design)" class="text-xs md:text-sm border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl w-full" required>
                    <input type="text" name="category" placeholder="Kategori Skill (misal: Creative)" class="text-xs md:text-sm border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl w-full">
                </div>
                <button type="submit" class="w-full bg-indigo-600 text-white font-semibold text-xs md:text-sm px-4 py-2.5 rounded-xl hover:bg-indigo-700 transition shadow-sm flex items-center justify-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Tambah Skill Baru
                </button>
            </form>

            {{-- Tabel Skill --}}
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs md:text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 text-gray-400 font-semibold uppercase text-[10px] tracking-wider">
                            <th class="py-3 px-2">Nama Skill</th>
                            <th class="py-3 px-2">Kategori</th>
                            <th class="py-3 px-2 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($skills as $skill)
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="py-3 px-2 font-semibold text-gray-800">{{ $skill->name }}</td>
                                <td class="py-3 px-2">
                                    @if($skill->category)
                                        <span class="inline-block px-2.5 py-0.5 bg-indigo-50 text-indigo-700 font-semibold text-[10px] rounded-full border border-indigo-100">
                                            {{ $skill->category }}
                                        </span>
                                    @else
                                        <span class="text-gray-400 text-xs">-</span>
                                    @endif
                                </td>
                                <td class="py-3 px-2 text-right">
                                    <form action="{{ route('admin.skills.destroy', $skill->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus skill ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition" title="Hapus Skill">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                            </svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-6 text-center text-gray-400 text-xs">Belum ada data skill terdaftar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- SECTION MASTER KATEGORI EVENT --}}
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 space-y-6">
            <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.674.509a13.91 13.91 0 003.354-2.1c.78-.658.95-1.745.424-2.583L11.16 3.66a2.25 2.25 0 00-1.592-.661z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-900">Master Kategori Event</h2>
                        <p class="text-xs text-gray-400">Total: {{ count($categories) }} Kategori Terdaftar</p>
                    </div>
                </div>
            </div>

            {{-- Form Tambah Kategori --}}
            <form action="{{ route('admin.categories.store') }}" method="POST" class="space-y-3 bg-gray-50/70 p-4 rounded-xl border border-gray-100">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <input type="text" name="name" placeholder="Nama Kategori (misal: Konser & Festival)" class="text-xs md:text-sm border-gray-200 focus:border-purple-500 focus:ring-purple-500 rounded-xl w-full" required>
                    <input type="text" name="description" placeholder="Deskripsi Singkat" class="text-xs md:text-sm border-gray-200 focus:border-purple-500 focus:ring-purple-500 rounded-xl w-full">
                </div>
                <button type="submit" class="w-full bg-purple-600 text-white font-semibold text-xs md:text-sm px-4 py-2.5 rounded-xl hover:bg-purple-700 transition shadow-sm flex items-center justify-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Tambah Kategori Baru
                </button>
            </form>

            {{-- Tabel Kategori --}}
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs md:text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 text-gray-400 font-semibold uppercase text-[10px] tracking-wider">
                            <th class="py-3 px-2">Nama Kategori</th>
                            <th class="py-3 px-2">Deskripsi</th>
                            <th class="py-3 px-2 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($categories as $category)
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="py-3 px-2 font-semibold text-gray-800">{{ $category->name }}</td>
                                <td class="py-3 px-2 text-gray-500 max-w-[180px] truncate">{{ $category->description ?? '-' }}</td>
                                <td class="py-3 px-2 text-right">
                                    <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus kategori ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition" title="Hapus Kategori">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                            </svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-6 text-center text-gray-400 text-xs">Belum ada data kategori terdaftar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>
@endsection