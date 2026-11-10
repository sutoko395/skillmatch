<div class="va-applications rounded-2xl border border-slate-200 bg-white p-6 shadow-sm md:p-8">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-lg font-bold text-slate-900">
                Kegiatan Saya
            </h2>
            <p class="mt-1 text-sm text-slate-500">
                Daftar kegiatan yang sudah Anda lamar.
            </p>
        </div>

        <span class="inline-flex w-fit rounded-full bg-indigo-50 px-3 py-1.5 text-xs font-semibold text-indigo-700">
            {{ $applicationCount }} kegiatan
        </span>
    </div>

    <div class="mt-5 space-y-3">
        @forelse($applications as $application)
            <div class="flex flex-col gap-3 rounded-xl border border-slate-200 p-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <h3 class="font-semibold text-slate-900">
                        {{ $application->event?->title ?? 'Kegiatan tidak tersedia' }}
                    </h3>

                    <p class="mt-1 text-sm text-slate-500">
                        Posisi:
                        {{ $application->position?->title ?? $application->position?->name ?? 'Tidak tersedia' }}
                    </p>

                    <p class="mt-1 text-xs text-slate-400">
                        Dilamar:
                        {{ $application->submitted_at?->setTimezone('Asia/Jakarta')->format('d M Y') ?? 'Tanggal tidak tersedia' }}
                    </p>
                </div>

                <span class="va-status-pill inline-flex w-fit shrink-0 rounded-full px-3 py-1.5 text-xs font-semibold
                    {{ $application->status === 'accepted'
                        ? 'bg-emerald-50 text-emerald-700'
                        : ($application->status === 'rejected'
                            ? 'bg-red-50 text-red-700'
                            : 'bg-amber-50 text-amber-700') }}">
                    {{ ucfirst(str_replace('_', ' ', $application->status)) }}
                </span>
            </div>
        @empty
            <div class="rounded-xl border border-dashed border-slate-300 p-8 text-center">
                <p class="font-semibold text-slate-700">
                    Belum ada kegiatan yang dilamar
                </p>
                <p class="mt-2 text-sm text-slate-500">
                    Kegiatan yang sudah Anda kirim lamarannya akan muncul di sini.
                </p>
            </div>
        @endforelse
    </div>
</div>
