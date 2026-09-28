<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard Admin SkillMatch') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Cards Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                    <p class="text-sm font-medium text-gray-500">Total Volunteer</p>
                    <p class="text-3xl font-bold text-indigo-600 mt-2">{{ $totalVolunteers }}</p>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                    <p class="text-sm font-medium text-gray-500">Total Organizer</p>
                    <p class="text-3xl font-bold text-green-600 mt-2">{{ $totalOrganizers }}</p>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                    <p class="text-sm font-medium text-gray-500">Master Skill</p>
                    <p class="text-3xl font-bold text-blue-600 mt-2">{{ $totalSkills }}</p>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                    <p class="text-sm font-medium text-gray-500">Kategori Event</p>
                    <p class="text-3xl font-bold text-purple-600 mt-2">{{ $totalCategories }}</p>
                </div>
            </div>

            <!-- Quick Actions Panel -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Aksi Cepat Admin</h3>
                <div class="flex gap-4">
                    <a href="{{ route('admin.master-data') }}" class="inline-flex items-center bg-indigo-600 text-white px-5 py-2.5 rounded-lg font-semibold hover:bg-indigo-700 transition">
                        Kelola Master Data (Skill & Kategori)
                    </a>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>