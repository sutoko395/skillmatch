<div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

    <h2 class="text-lg font-bold text-slate-900">
        Skill dan Level
    </h2>

    <div class="mt-4 space-y-3">

        @forelse($skills as $row)

            <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="break-words text-sm font-semibold text-slate-900">
                            {{ $row->skill?->name ?? 'Skill tidak tersedia' }}
                        </p>

                        @if(!$row->skill?->is_active)
                            <p class="mt-1 text-xs font-medium text-amber-700">
                                Skill sedang nonaktif
                            </p>
                        @endif
                    </div>

                    <span class="shrink-0 rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700">
                        {{ ucfirst($row->level) }}
                    </span>
                </div>
            </div>

        @empty

            <div class="rounded-xl border border-dashed border-slate-300 p-4">
                <p class="text-sm text-slate-500">
                    Belum ada skill tersimpan.
                </p>
            </div>

        @endforelse

    </div>

</div>
