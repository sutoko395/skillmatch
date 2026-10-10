@extends('volunteer.layouts.sidebar')
@section('title', 'Detail Lamaran - ' . $application->event->title)
@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    <x-page-header :title="$application->event->title" :description="'Posisi: ' . $application->position->name" />

    @if (session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">
            <p class="font-semibold">Terjadi kesalahan:</p>
            <ul class="mt-1 list-inside list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <x-section-card>
        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 pb-4">
            <div>
                <p class="text-sm font-medium text-slate-500">Status Lamaran</p>
                <div class="mt-1 flex flex-wrap items-center gap-3">
                    <x-status-badge :status="$application->status === 'submitted' || $application->status === 'accepted' ? 'success' : ($application->status === 'draft' ? 'pending' : 'default')">
                        {{ ucfirst(str_replace('_', ' ', $application->status)) }}
                    </x-status-badge>

                    @if($application->status === 'submitted')
                        <span class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-medium text-blue-700">
                            Menunggu evaluasi
                        </span>
                        <span class="text-sm text-slate-600">Dikirim pada {{ $application->submitted_at?->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</span>
                    @endif
                </div>
            </div>

            @if($application->status === 'draft')
                <div>
                    <form method="POST" action="{{ route('volunteer.applications.submit', $application) }}">
                        @csrf
                        <x-button type="submit" class="bg-indigo-600 hover:bg-indigo-700">Kirimkan Lamaran</x-button>
                    </form>
                </div>
            @endif
        </div>

        @if($application->status === 'draft' && !($eligibility['complete'] ?? true))
            <div class="mt-4 rounded-xl bg-amber-50 p-4 text-sm text-amber-800">
                <p class="font-semibold">Profil Anda belum lengkap!</p>
                <p class="mt-1">Silakan lengkapi data profil dan skill Anda sebelum mengirimkan lamaran.</p>
                <a href="{{ route('volunteer.profile.edit') }}" class="mt-2 inline-block font-semibold underline">Lengkapi Profil &rarr;</a>
            </div>
        @endif

        {{-- Kelola Dokumen Privat Lamaran --}}
        <div class="mt-6 border-b border-slate-200 pb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Dokumen Lamaran</h3>
                    <p class="text-sm text-slate-600">Unggah CV (wajib format PDF) dan dokumen pendukung (PDF, JPG, PNG). Maksimal 5 MB per file, maksimal 5 file.</p>
                </div>
            </div>

            {{-- Daftar Dokumen Tersimpan --}}
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
                                        <td class="px-4 py-3 text-right space-x-2">
                                            <a href="{{ route('documents.download', $doc) }}" class="inline-flex items-center text-xs font-semibold text-indigo-600 hover:text-indigo-800">
                                                Unduh
                                            </a>
                                            @if($application->status === 'draft')
                                                <form method="POST" action="{{ route('volunteer.documents.destroy', $doc) }}" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus dokumen ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-xs font-semibold text-rose-600 hover:text-rose-800">
                                                        Hapus
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="rounded-xl border border-dashed border-slate-300 p-6 text-center text-sm text-slate-500">
                        Belum ada dokumen yang diunggah untuk lamaran ini.
                    </div>
                @endif
            </div>

            {{-- Form Upload Dokumen (Jika masih Draft) --}}
            @if($application->status === 'draft')
                <div class="mt-6 rounded-xl bg-slate-50 p-4 border border-slate-200">
                    <h4 class="font-semibold text-gray-900">Unggah Dokumen Baru</h4>
                    <form method="POST" action="{{ route('volunteer.documents.store', $application) }}" enctype="multipart/form-data" class="mt-3 grid grid-cols-1 gap-4 sm:grid-cols-3">
                        @csrf
                        <div>
                            <label class="block text-xs font-medium text-slate-700">Tipe Dokumen</label>
                            <select name="document_type" class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                <option value="cv">Curriculum Vitae (CV) - PDF</option>
                                <option value="supporting">Dokumen Pendukung - PDF/JPG/PNG</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700">Pilih File (Maks. 5 MB)</label>
                            <input type="file" name="file" class="mt-1 block w-full text-sm text-slate-500 file:mr-4 file:rounded-md file:border-0 file:bg-indigo-50 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100" required />
                        </div>
                        <div class="flex items-end">
                            <x-button type="submit" class="w-full justify-center">Unggah Dokumen</x-button>
                        </div>
                    </form>
                </div>
            @endif
        </div>

        <div class="mt-6 space-y-4">
            <div>
                <h3 class="text-lg font-bold text-gray-900">Penyelenggara</h3>
                <p class="text-slate-700">{{ $application->event->organizer->organizerProfile?->organization_name ?? $application->event->organizer->name }}</p>
            </div>

            <div>
                <h3 class="text-lg font-bold text-gray-900">Lokasi & Waktu Event</h3>
                <p class="text-slate-700">{{ $application->event->location }} &middot; {{ $application->event->cityRecord?->name }}</p>
                <p class="text-slate-700">{{ $application->event->starts_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }} - {{ $application->event->ends_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</p>
            </div>

            <div>
                <h3 class="text-lg font-bold text-gray-900">Deskripsi Posisi ({{ $application->position->name }})</h3>
                <p class="whitespace-pre-line text-slate-700">{{ $application->position->description }}</p>
            </div>

            @if($application->position->positionSkills->isNotEmpty())
                <div>
                    <h4 class="font-semibold text-gray-900">Skill yang Dibutuhkan</h4>
                    <ul class="mt-1 list-inside list-disc text-slate-700">
                        @foreach($application->position->positionSkills as $s)
                            <li>{{ $s->skill->name }} - {{ ucfirst($s->minimum_level) }} ({{ $s->is_required ? 'Wajib' : 'Preferensi' }})</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if($application->position->schedules->isNotEmpty())
                <div>
                    <h4 class="font-semibold text-gray-900">Jadwal Tugas (WIB)</h4>
                    @foreach($application->position->schedules as $slot)
                        <p class="text-slate-700">{{ $slot->starts_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }} - {{ $slot->ends_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }}</p>
                    @endforeach
                </div>
            @endif
        </div>
    </x-section-card>
</div>
@endsection
