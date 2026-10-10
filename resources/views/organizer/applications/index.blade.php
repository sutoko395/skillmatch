@extends('organizer.layouts.sidebar')
@section('title', 'Daftar Pelamar - ' . $event->title)
@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <x-page-header :title="'Daftar Pelamar: ' . $event->title" description="Kelola dan tinjau lamaran yang masuk untuk setiap posisi kegiatan ini." />
        <a href="{{ route('organizer.events.show', $event) }}" class="inline-flex items-center text-sm font-semibold text-slate-600 hover:text-slate-900">
            &larr; Kembali ke Detail Event
        </a>
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    {{-- Filter Posisi --}}
    <x-section-card>
        <form method="GET" action="{{ route('organizer.applications.index', $event) }}" class="flex flex-wrap items-center gap-4">
            <div class="w-full sm:w-64">
                <label for="position_id" class="block text-xs font-medium text-slate-700">Filter Berdasarkan Posisi</label>
                <select name="position_id" id="position_id" class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" onchange="this.form.submit()">
                    <option value="">Semua Posisi</option>
                    @foreach($positions as $pos)
                        <option value="{{ $pos->id }}" {{ request('position_id') == $pos->id ? 'selected' : '' }}>
                            {{ $pos->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @if(request('position_id'))
                <div class="flex items-end">
                    <a href="{{ route('organizer.applications.index', $event) }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">
                        Reset Filter
                    </a>
                </div>
            @endif
        </form>
    </x-section-card>

    <x-section-card>
        @if($applications->isEmpty())
            <div class="py-12 text-center">
                <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <h3 class="mt-2 text-sm font-semibold text-gray-900">Belum Ada Pelamar</h3>
                <p class="mt-1 text-sm text-slate-500">Belum ada kandidat yang mengirimkan lamaran untuk kegiatan ini.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 font-medium text-slate-600">
                        <tr>
                            <th class="px-4 py-3">Nama Kandidat</th>
                            <th class="px-4 py-3">Posisi Dilamar</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Tanggal Mengirim</th>
                            <th class="px-4 py-3">Dokumen</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach($applications as $app)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-slate-900">{{ $app->volunteer->name }}</div>
                                    <div class="text-xs text-slate-500">{{ $app->volunteer->volunteerProfile?->cityRecord?->name ?? 'Kota belum diisi' }}</div>
                                </td>
                                <td class="px-4 py-3 text-slate-700">
                                    {{ $app->position->name }}
                                </td>
                                <td class="px-4 py-3">
                                    @if($app->status === 'submitted')
                                        <span class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-medium text-blue-700">
                                            Menunggu evaluasi
                                        </span>
                                    @else
                                        <x-status-badge :status="$app->status === 'accepted' ? 'success' : ($app->status === 'rejected' ? 'danger' : 'default')">
                                            {{ ucfirst(str_replace('_', ' ', $app->status)) }}
                                        </x-status-badge>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-slate-600">
                                    {{ $app->submitted_at?->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') ?? '-' }} WIB
                                </td>
                                <td class="px-4 py-3 text-slate-600">
                                    <span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-700">
                                        {{ $app->documents->count() }} file
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('organizer.applications.show', $app) }}" class="inline-flex items-center rounded-lg bg-indigo-50 px-3 py-1.5 text-xs font-semibold text-indigo-700 hover:bg-indigo-100">
                                        Tinjau Lamaran &rarr;
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $applications->links() }}
            </div>
        @endif
    </x-section-card>
</div>
@endsection
