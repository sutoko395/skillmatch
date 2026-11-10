<x-section-card class="vd-documents">
    <div class="-mx-6 -mt-6 rounded-t-2xl border-b border-orange-100 bg-orange-50 px-6 py-4">
        <h2 class="text-lg font-bold text-slate-900">Dokumen lamaran</h2>
    </div>
    @if ($application->status === 'draft')
        <p class="mt-1 text-sm text-slate-600">Unggah CV (wajib format PDF) dan dokumen pendukung (PDF, JPG, PNG). Maksimal 5 MB per file, maksimal 5 file.</p>
    @endif
    <ul class="mt-4 space-y-3" aria-label="Daftar dokumen lamaran">
        @forelse ($application->documents as $doc)
            <li class="flex flex-col gap-3 rounded-xl border border-slate-200 bg-slate-50/70 p-4 sm:flex-row sm:items-center">
                <div class="flex min-w-0 flex-1 items-start gap-3">
                    <span aria-hidden="true" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-700">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M14 3H6a1 1 0 0 0-1 1v16h14V8l-5-5Zm0 0v5h5M8 12h8m-8 4h5" /></svg>
                    </span>
                    <div class="min-w-0">
                        <p class="break-words text-sm font-semibold text-slate-900">{{ $doc->original_name }}</p>
                        <p class="mt-1 text-xs text-slate-500">{{ strtoupper($doc->document_type) }} &middot; {{ number_format($doc->size_bytes / 1024, 1) }} KB</p>
                        <p class="mt-1 text-xs {{ $doc->status === 'ready' ? 'text-emerald-700' : 'text-slate-500' }}">{{ ['ready' => 'Dokumen tersimpan', 'missing' => 'Berkas tidak tersedia', 'purged' => 'Dokumen telah dihapus'][$doc->status] ?? 'Tidak tersedia' }}</p>
                    </div>
                </div>
                <div class="flex shrink-0 flex-wrap items-center gap-4 text-sm">
                    @if ($doc->status === 'ready')
                        <a href="{{ route('documents.download', $doc) }}" class="rounded font-semibold text-indigo-700 hover:text-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500">Unduh<span class="sr-only"> {{ $doc->original_name }}</span></a>
                        @if ($application->status === 'draft')
                            <form method="POST" action="{{ route('volunteer.documents.destroy', $doc) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus dokumen ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded font-semibold text-rose-600 hover:text-rose-800 focus:outline-none focus:ring-2 focus:ring-rose-500">Hapus<span class="sr-only"> {{ $doc->original_name }}</span></button>
                            </form>
                        @endif
                    @endif
                </div>
            </li>
        @empty
            <li class="rounded-xl border border-dashed border-slate-300 p-6 text-center text-sm text-slate-500">Belum ada dokumen yang diunggah untuk lamaran ini.</li>
        @endforelse
    </ul>
    {{-- Form Upload Dokumen (Jika masih Draft) --}}
    @if($application->status === 'draft')
        <div class="mt-6 rounded-xl bg-slate-50 p-4 border border-slate-200">
            <h4 class="font-semibold text-gray-900">Unggah Dokumen Baru</h4>
            <form method="POST" action="{{ route('volunteer.documents.store', $application) }}" enctype="multipart/form-data" class="mt-3 grid grid-cols-1 gap-4 sm:grid-cols-3">
                @csrf
                <div>
                    <label for="application-document-type" class="block text-xs font-medium text-slate-700">Tipe Dokumen</label>
                    <select id="application-document-type" name="document_type" class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        <option value="cv">Curriculum Vitae (CV) - PDF</option>
                        <option value="supporting">Dokumen Pendukung - PDF/JPG/PNG</option>
                    </select>
                </div>
                <div>
                    <label for="application-document-file" class="block text-xs font-medium text-slate-700">Pilih File (Maks. 5 MB)</label>
                    <input id="application-document-file" type="file" name="file" class="mt-1 block w-full text-sm text-slate-500 file:mr-4 file:rounded-md file:border-0 file:bg-indigo-50 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100" required />
                </div>
                <div class="flex items-end">
                    <x-button type="submit" class="w-full justify-center">Unggah Dokumen</x-button>
                </div>
            </form>
        </div>
    @endif
</x-section-card>
