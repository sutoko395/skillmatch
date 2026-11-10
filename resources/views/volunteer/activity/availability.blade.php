<div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm md:p-8">

    <div class="flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
        <div>
            <h2 class="text-lg font-bold text-slate-900">
                Ketersediaan
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Waktu ketersediaan Anda dalam zona waktu WIB.
            </p>
        </div>

        <a
            href="{{ route('volunteer.profile.edit') }}"
            class="text-sm font-semibold text-indigo-600 hover:text-indigo-700"
        >
            Kelola profil
        </a>
    </div>

    <div class="mt-5 overflow-hidden rounded-xl border border-slate-200">

        @forelse($slots->take(5) as $slot)

            <div class="flex flex-col gap-2 border-b border-slate-100 p-4 last:border-0 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-semibold text-slate-900">
                        {{ $slot->starts_at->setTimezone('Asia/Jakarta')->format('d/m/Y') }}
                    </p>

                    <p class="mt-1 text-sm text-slate-500">
                        {{ $slot->starts_at->setTimezone('Asia/Jakarta')->format('H:i') }}
                        -
                        {{ $slot->ends_at->setTimezone('Asia/Jakarta')->format('H:i') }}
                        WIB
                    </p>
                </div>

                <span class="inline-flex w-fit rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                    Tersedia
                </span>
            </div>

        @empty

            <div class="p-6 text-center">
                <p class="text-sm text-slate-500">
                    Belum ada tanggal dan jam ketersediaan.
                </p>
            </div>

        @endforelse

    </div>

    @if($slots->count() > 5)
        <p class="mt-3 text-sm text-slate-500">
            Menampilkan 5 dari {{ $slots->count() }} interval ketersediaan.
        </p>
    @endif

    @if($volunteerProfile?->availability)
        <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-4">
            <p class="text-sm text-amber-800">
                Preferensi lama:
                <span class="font-semibold">{{ $volunteerProfile->availability }}</span>
            </p>
        </div>
    @endif

</div>
