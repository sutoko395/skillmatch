@extends('admin.layouts.sidebar')

@section('title', 'Manajemen User')

@section('content')
<div class="w-full max-w-7xl mx-auto space-y-6">

    <div>
        <h1 class="text-2xl font-bold text-slate-800">
            Manajemen User
        </h1>
        <p class="mt-1 text-sm text-slate-500">
            Kelola akun Volunteer, Organizer, dan Admin dalam sistem SkillMatch.
        </p>
    </div>

    @if(session('success'))
        <div class="rounded-xl bg-emerald-50 border border-emerald-100 px-4 py-3 text-sm text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-xl bg-red-50 border border-red-100 px-4 py-3 text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <p class="text-xs font-semibold text-slate-400">Total User</p>
            <p class="mt-2 text-2xl font-bold text-slate-800">{{ $totalUsers }}</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <p class="text-xs font-semibold text-slate-400">Volunteer</p>
            <p class="mt-2 text-2xl font-bold text-indigo-600">{{ $totalVolunteers }}</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <p class="text-xs font-semibold text-slate-400">Organizer</p>
            <p class="mt-2 text-2xl font-bold text-emerald-600">{{ $totalOrganizers }}</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <p class="text-xs font-semibold text-slate-400">Nonaktif</p>
            <p class="mt-2 text-2xl font-bold text-red-600">{{ $inactiveUsers }}</p>
        </div>

    </div>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
        <form method="GET" action="{{ route('admin.users.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">

            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-slate-700">
                    Cari User
                </label>

                <input
                    type="text"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Nama atau email..."
                    class="mt-2 w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700">
                    Role
                </label>

                <select
                    name="role"
                    class="mt-2 w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
                    <option value="">Semua Role</option>
                    <option value="volunteer" @selected($role === 'volunteer')>Volunteer</option>
                    <option value="organizer" @selected($role === 'organizer')>Organizer</option>
                    <option value="admin" @selected($role === 'admin')>Admin</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700">
                    Status
                </label>

                <select
                    name="status"
                    class="mt-2 w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
                    <option value="">Semua Status</option>
                    <option value="active" @selected($status === 'active')>Aktif</option>
                    <option value="inactive" @selected($status === 'inactive')>Nonaktif</option>
                </select>
            </div>

            <div class="md:col-span-4 flex justify-end gap-3">
                <a
                    href="{{ route('admin.users.index') }}"
                    class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-sm font-semibold hover:bg-slate-50 transition"
                >
                    Reset
                </a>

                <button
                    type="submit"
                    class="px-5 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 transition"
                >
                    Cari
                </button>
            </div>

        </form>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">

        <div class="px-6 py-5 border-b border-slate-100">
            <h2 class="text-lg font-bold text-slate-800">
                Daftar User
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                {{ $users->total() }} user terdaftar.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-4 text-left font-semibold text-slate-600">No</th>
                        <th class="px-6 py-4 text-left font-semibold text-slate-600">User</th>
                        <th class="px-6 py-4 text-left font-semibold text-slate-600">Role</th>
                        <th class="px-6 py-4 text-left font-semibold text-slate-600">Status</th>
                        <th class="px-6 py-4 text-left font-semibold text-slate-600">Terdaftar</th>
                        <th class="px-6 py-4 text-right font-semibold text-slate-600">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse($users as $user)
                        <tr class="hover:bg-slate-50/70">

                            <td class="px-6 py-4 text-slate-500">
                                {{ $users->firstItem() + $loop->index }}
                            </td>

                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-800">
                                    {{ $user->name }}
                                </div>

                                <div class="text-xs text-slate-500 mt-1">
                                    {{ $user->email }}
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                @if($user->role === 'volunteer')
                                    <span class="inline-flex px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-600 text-xs font-bold">
                                        Volunteer
                                    </span>
                                @elseif($user->role === 'organizer')
                                    <span class="inline-flex px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-600 text-xs font-bold">
                                        Organizer
                                    </span>
                                @else
                                    <span class="inline-flex px-2.5 py-1 rounded-lg bg-purple-50 text-purple-600 text-xs font-bold">
                                        Admin
                                    </span>
                                @endif
                            </td>

                            <td class="px-6 py-4">
                                @if($user->is_active)
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
                                {{ $user->created_at->format('d M Y') }}
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex justify-end">

                                    @if($user->role !== 'admin')
                                        <form
                                            action="{{ route('admin.users.toggle-status', $user) }}"
                                            method="POST"
                                        >
                                            @csrf
                                            @method('PATCH')

                                            <button
                                                type="submit"
                                                class="px-3 py-2 rounded-lg {{ $user->is_active ? 'bg-red-50 text-red-600 hover:bg-red-100' : 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100' }} text-xs font-semibold transition"
                                            >
                                                {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                    @else
                                        <span class="px-3 py-2 text-xs font-semibold text-slate-400">
                                            Admin
                                        </span>
                                    @endif

                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                Tidak ada user yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="px-6 py-5 border-t border-slate-100">
                {{ $users->links() }}
            </div>
        @endif

    </div>

</div>
@endsection