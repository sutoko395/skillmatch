@extends('admin.layouts.sidebar')

@section('title', 'Verifikasi Event')

@section('content')
<div class="w-full max-w-7xl mx-auto space-y-6">

    <div>
        <h1 class="text-2xl font-bold text-slate-800">
            Verifikasi Event
        </h1>

        <p class="mt-1 text-sm text-slate-500">
            Periksa event yang diajukan Organizer sebelum dipublikasikan kepada Volunteer.
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

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

        <div class="bg-white rounded-2xl border border-amber-100 shadow-sm p-5">
            <p class="text-xs font-semibold text-slate-400">
                Menunggu Verifikasi
            </p>

            <p class="mt-2 text-2xl font-bold text-amber-600">
                {{ $pendingEvents }}
            </p>
        </div>

        <div class="bg-white rounded-2xl border border-emerald-100 shadow-sm p-5">
            <p class="text-xs font-semibold text-slate-400">
                Disetujui
            </p>

            <p class="mt-2 text-2xl font-bold text-emerald-600">
                {{ $approvedEvents }}
            </p>
        </div>

        <div class="bg-white rounded-2xl border border-red-100 shadow-sm p-5">
            <p class="text-xs font-semibold text-slate-400">
                Ditolak
            </p>

            <p class="mt-2 text-2xl font-bold text-red-600">
                {{ $rejectedEvents }}
            </p>
        </div>

    </div>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
        <form method="GET" action="{{ route('admin.event-verification.index') }}" class="flex flex-col sm:flex-row gap-3">

            <input
                type="text"
                name="search"
                value="{{ $search }}"
                placeholder="Cari event atau organizer..."
                class="flex-1 rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500"
            >

            <button
                type="submit"
                class="px-5 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 transition"
            >
                Cari
            </button>

            <a
                href="{{ route('admin.event-verification.index') }}"
                class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-sm font-semibold hover:bg-slate-50 transition text-center"
            >
                Reset
            </a>

        </form>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">

        <div class="px-6 py-5 border-b border-slate-100">
            <h2 class="text-lg font-bold text-slate-800">
                Event Menunggu Verifikasi
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                {{ $events->total() }} event menunggu pemeriksaan.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">

                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-4 text-left font-semibold text-slate-600">No</th>
                        <th class="px-6 py-4 text-left font-semibold text-slate-600">Event</th>
                        <th class="px-6 py-4 text-left font-semibold text-slate-600">Organizer</th>
                        <th class="px-6 py-4 text-left font-semibold text-slate-600">Kategori</th>
                        <th class="px-6 py-4 text-left font-semibold text-slate-600">Tanggal</th>
                        <th class="px-6 py-4 text-right font-semibold text-slate-600">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">

                    @forelse($events as $event)
                        <tr class="hover:bg-slate-50/70">

                            <td class="px-6 py-4 text-slate-500">
                                {{ $events->firstItem() + $loop->index }}
                            </td>

                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-800">
                                    {{ $event->title }}
                                </div>

                                <div class="text-xs text-slate-500 mt-1">
                                    {{ $event->location }}
                                </div>
                            </td>

                            <td class="px-6 py-4 text-slate-700">
                                {{ $event->organizer?->name ?? '-' }}
                            </td>

                            <td class="px-6 py-4 text-slate-600">
                                {{ $event->category?->name ?? '-' }}
                            </td>

                            <td class="px-6 py-4 text-slate-600">
                                {{ optional($event->start_date)->format('d M Y') }}
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex justify-end">

                                    <a
                                        href="{{ route('admin.event-verification.show', $event) }}"
                                        class="px-3 py-2 rounded-lg bg-indigo-50 text-indigo-600 text-xs font-semibold hover:bg-indigo-100 transition"
                                    >
                                        Periksa
                                    </a>

                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="text-slate-400">
                                    Tidak ada event yang menunggu verifikasi.
                                </div>
                            </td>
                        </tr>
                    @endforelse

                </tbody>

            </table>
        </div>

        @if($events->hasPages())
            <div class="px-6 py-5 border-t border-slate-100">
                {{ $events->links() }}
            </div>
        @endif

    </div>

</div>
@endsection