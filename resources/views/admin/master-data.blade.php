<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kelola Master Data (Skill & Kategori)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Section Master Skill -->
            <div class="p-6 bg-white shadow rounded-lg">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Master Data Skill</h3>
                
                <form action="{{ route('admin.skills.store') }}" method="POST" class="flex gap-4 mb-6">
                    @csrf
                    <input type="text" name="name" placeholder="Nama Skill (misal: Graphic Design)" class="border-gray-300 rounded-md flex-1" required>
                    <input type="text" name="category" placeholder="Kategori Skill (misal: Creative)" class="border-gray-300 rounded-md flex-1">
                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">Tambah Skill</button>
                </form>

                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b bg-gray-50">
                            <th class="p-3">Nama Skill</th>
                            <th class="p-3">Kategori</th>
                            <th class="p-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($skills as $skill)
                            <tr class="border-b">
                                <td class="p-3 font-semibold">{{ $skill->name }}</td>
                                <td class="p-3 text-gray-600">{{ $skill->category ?? '-' }}</td>
                                <td class="p-3 text-right">
                                    <form action="{{ route('admin.skills.destroy', $skill->id) }}" method="POST" onsubmit="return confirm('Yakin hapus skill ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline text-sm">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Section Master Kategori Event -->
            <div class="p-6 bg-white shadow rounded-lg">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Master Kategori Event</h3>
                
                <form action="{{ route('admin.categories.store') }}" method="POST" class="flex gap-4 mb-6">
                    @csrf
                    <input type="text" name="name" placeholder="Nama Kategori (misal: Konser & Festival)" class="border-gray-300 rounded-md flex-1" required>
                    <input type="text" name="description" placeholder="Deskripsi Singkat" class="border-gray-300 rounded-md flex-1">
                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">Tambah Kategori</button>
                </form>

                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b bg-gray-50">
                            <th class="p-3">Nama Kategori</th>
                            <th class="p-3">Deskripsi</th>
                            <th class="p-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($categories as $category)
                            <tr class="border-b">
                                <td class="p-3 font-semibold">{{ $category->name }}</td>
                                <td class="p-3 text-gray-600">{{ $category->description ?? '-' }}</td>
                                <td class="p-3 text-right">
                                    <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" onsubmit="return confirm('Yakin hapus kategori ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline text-sm">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</x-app-layout>