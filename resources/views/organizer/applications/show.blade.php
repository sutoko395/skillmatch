@extends('organizer.layouts.sidebar')
@section('title', 'Tinjau Lamaran - ' . $application->volunteer->name)
@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <x-page-header :title="'Lamaran: ' . $application->volunteer->name" :description="'Untuk posisi ' . $application->position->name . ' di ' . $application->event->title" />
        <a href="{{ route('organizer.applications.index', $application->event) }}" class="inline-flex items-center text-sm font-semibold text-slate-600 hover:text-slate-900">
            &larr; Kembali ke Daftar Pelamar
        </a>
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    {{-- Status Bar --}}
    <x-section-card>
        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 pb-4">
            <div>
                <p class="text-sm font-medium text-slate-500">Status Seleksi</p>
                <div class="mt-1 flex items-center gap-3">
                    @if($application->status === 'submitted')
                        <span class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-medium text-blue-700">
                            Menunggu evaluasi
                        </span>
                    @else
                        <x-status-badge :status="$application->status === 'accepted' ? 'success' : ($application->status === 'rejected' ? 'danger' : 'default')">
                            {{ ucfirst(str_replace('_', ' ', $application->status)) }}
                        </x-status-badge>
                    @endif
                    <span class="text-sm text-slate-600">Dikirim pada {{ $application->submitted_at?->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</span>
                </div>
            </div>
        </div>

        <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2">
            {{-- Profil Kandidat --}}
            <div class="space-y-4 rounded-xl bg-slate-50 p-5 border border-slate-200">
                <h3 class="text-base font-bold text-gray-900 border-b border-slate-200 pb-2">Informasi Kandidat</h3>
                <div>
                    <span class="text-xs font-medium text-slate-500">Nama Lengkap</span>
                    <p class="text-sm font-semibold text-slate-900">{{ $application->volunteer->name }}</p>
                </div>
                <div>
                    <span class="text-xs font-medium text-slate-500">Email</span>
                    <p class="text-sm text-slate-700">{{ $application->volunteer->email }}</p>
                </div>
                <div>
                    <span class="text-xs font-medium text-slate-500">No. Telepon</span>
                    <p class="text-sm text-slate-700">{{ $application->volunteer->volunteerProfile?->phone ?? '-' }}</p>
                </div>
                <div>
                    <span class="text-xs font-medium text-slate-500">Kota Domisili</span>
                    <p class="text-sm text-slate-700">{{ $application->volunteer->volunteerProfile?->cityRecord?->name ?? '-' }}</p>
                </div>
                <div>
                    <span class="text-xs font-medium text-slate-500">Bio Singkat</span>
                    <p class="text-sm text-slate-700">{{ $application->volunteer->volunteerProfile?->bio ?? '-' }}</p>
                </div>

                {{-- Skill Volunteer --}}
                <div class="pt-2">
                    <span class="text-xs font-medium text-slate-500">Keahlian (Skill) Volunteer</span>
                    <div class="mt-1 flex flex-wrap gap-1.5">
                        @forelse($application->volunteer->volunteerSkills as $vs)
                            <span class="inline-flex items-center rounded-md bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-700">
                                {{ $vs->skill?->name }} ({{ ucfirst($vs->level) }})
                            </span>
                        @empty
                            <span class="text-xs text-slate-500">Tidak ada skill tercatat.</span>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Posisi & Persyaratan --}}
            <div class="space-y-4 rounded-xl bg-slate-50 p-5 border border-slate-200">
                <h3 class="text-base font-bold text-gray-900 border-b border-slate-200 pb-2">Posisi & Kebutuhan</h3>
                <div>
                    <span class="text-xs font-medium text-slate-500">Posisi</span>
                    <p class="text-sm font-semibold text-slate-900">{{ $application->position->name }}</p>
                </div>
                <div>
                    <span class="text-xs font-medium text-slate-500">Deskripsi Tugas</span>
                    <p class="text-sm text-slate-700 whitespace-pre-line">{{ $application->position->description }}</p>
                </div>

                {{-- Skill yang Dibutuhkan Posisi --}}
                <div class="pt-2">
                    <span class="text-xs font-medium text-slate-500">Kebutuhan Skill Posisi</span>
                    <ul class="mt-1 list-inside list-disc text-sm text-slate-700">
                        @forelse($application->position->positionSkills as $ps)
                            <li>{{ $ps->skill?->name }} - Min. {{ ucfirst($ps->minimum_level) }} ({{ $ps->is_required ? 'Wajib' : 'Preferensi' }})</li>
                        @empty
                            <li class="text-xs text-slate-500">Tidak ada kualifikasi skill khusus.</li>
                        @endforelse
                    </ul>
                </div>

                {{-- Jadwal Posisi --}}
                <div class="pt-2">
                    <span class="text-xs font-medium text-slate-500">Jadwal Tugas</span>
                    <div class="mt-1 space-y-1 text-sm text-slate-700">
                        @forelse($application->position->schedules as $sched)
                            <p>{{ $sched->starts_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }} - {{ $sched->ends_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</p>
                        @empty
                            <p class="text-xs text-slate-500">Mengikuti rentang event.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- Dokumen Privat Kandidat --}}
        <div class="mt-8 border-t border-slate-200 pt-6">
            <h3 class="text-lg font-bold text-gray-900">Dokumen Pelamar</h3>
            <p class="text-sm text-slate-600">Unduh dokumen berkas resmi yang dilampirkan oleh kandidat.</p>

            <div class="mt-4">
                @if($application->documents->isNotEmpty())
                    <div class="overflow-hidden rounded-xl border border-slate-200">
                        <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                            <thead class="bg-slate-50 font-medium text-slate-600">
                                <tr>
                                    <th class="px-4 py-3">Nama Dokumen</th>
                                    <th class="px-4 py-3">Tipe</th>
                                    <th class="px-4 py-3">Ukuran</th>
                                    <th class="px-4 py-3">Status</th>
                                    <th class="px-4 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @foreach($application->documents as $doc)
                                    <tr>
                                        <td class="px-4 py-3 font-medium text-slate-900">
                                            {{ $doc->original_name }}
                                        </td>
                                        <td class="px-4 py-3 text-slate-600">
                                            <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium {{ $doc->document_type === 'cv' ? 'bg-indigo-50 text-indigo-700' : 'bg-slate-100 text-slate-700' }}">
                                                {{ strtoupper($doc->document_type) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-slate-600">
                                            {{ number_format($doc->size_bytes / 1024, 1) }} KB
                                        </td>
                                        <td class="px-4 py-3 text-slate-600">
                                            <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700">
                                                Dokumen tersimpan
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <a href="{{ route('documents.download', $doc) }}" class="inline-flex items-center rounded-lg bg-indigo-50 px-3 py-1.5 text-xs font-semibold text-indigo-700 hover:bg-indigo-100">
                                                Unduh Dokumen &darr;
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="rounded-xl border border-dashed border-slate-300 p-6 text-center text-sm text-slate-500">
                        Kandidat tidak melampirkan berkas dokumen tambahan.
                    </div>
                @endif
            </div>
        </div>
    </x-section-card>
</div>
@endsection
