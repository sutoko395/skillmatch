@extends('admin.layouts.sidebar')

@section('title', 'Verifikasi Kegiatan')

@section('content')
<div class="w-full max-w-7xl mx-auto space-y-6">

    <div>
        <h1 class="text-2xl font-bold text-slate-800">
            Verifikasi Kegiatan
        </h1>

        <p class="mt-1 text-sm text-slate-500">
            Periksa kegiatan yang diajukan Organizer sebelum dipublikasikan kepada Relawan.
        </p>
    </div>

    <div class="admin-summary grid grid-cols-1 md:grid-cols-3 gap-4">

        <div class="admin-stat admin-stat--amber bg-white rounded-2xl border border-amber-100 shadow-sm p-5">
            <p class="text-xs font-semibold text-slate-400">
                Menunggu Verifikasi
            </p>

            <p class="mt-2 text-2xl font-bold text-amber-600">
                {{ $pendingEvents }}
            </p>
        </div>

        <div class="admin-stat admin-stat--green bg-white rounded-2xl border border-emerald-100 shadow-sm p-5">
            <p class="text-xs font-semibold text-slate-400">
                Disetujui
            </p>

            <p class="mt-2 text-2xl font-bold text-emerald-600">
                {{ $approvedEvents }}
            </p>
        </div>

        <div class="admin-stat admin-stat--red bg-white rounded-2xl border border-red-100 shadow-sm p-5">
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

            <div>
                <label class="sr-only" for="moderation-status">Status pengajuan</label>
                <select id="moderation-status" name="status" class="w-full rounded-xl border-slate-200 text-sm">
                    @foreach(['pending' => 'Menunggu verifikasi', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', 'all' => 'Semua pengajuan'] as $value => $label)
                        <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button
                type="submit"
                class="px-5 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 transition"
            >
                Cari
            </button>

            @if(request()->filled('search') || (request()->filled('status') && request('status') !== 'pending'))
            <a href="{{ route('admin.event-verification.index') }}" class="admin-filter-reset">Reset</a>
            @endif

        </form>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">

        <div class="px-6 py-5 border-b border-slate-100">
            <h2 class="text-lg font-bold text-slate-800">
                Daftar pengajuan kegiatan
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
                        <th class="px-6 py-4 text-left font-semibold text-slate-600">Kegiatan</th>
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
                                    <span class="ml-2 rounded bg-slate-100 px-2 py-0.5 text-xs">{{ match($event->status) { 'approved' => 'Disetujui', 'rejected' => 'Ditolak', default => 'Menunggu verifikasi' } }}</span>
                                </div>
                            </td>

                            <td class="px-6 py-4 text-slate-700">
                                {{ $event->organizer?->name ?? '-' }}
                            </td>

                            <td class="px-6 py-4 text-slate-600">
                                {{ $event->category?->name ?? '-' }}
                            </td>

                            <td class="px-6 py-4 text-slate-600">
                                {{ $event->starts_at?->setTimezone('Asia/Jakarta')->format('d M Y') ?? '-' }}
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex justify-end">

                                    <a
                                        href="{{ route('admin.event-verification.show', $event) }}"
                                        class="px-3 py-2 rounded-lg bg-indigo-50 text-indigo-600 text-xs font-semibold hover:bg-indigo-100 transition"
                                    >
                                        {{ $event->status === 'pending' ? 'Periksa' : 'Lihat detail' }}
                                    </a>

                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="text-slate-400">
                                    Tidak ada pengajuan yang sesuai filter.
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
