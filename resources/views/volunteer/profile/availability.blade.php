<x-section-card>

    <div class="border-b border-slate-100 pb-4">
        <h2 class="text-lg font-bold text-slate-800">
            Ketersediaan
        </h2>

        <p class="mt-1 text-sm text-slate-500">
            Atur tanggal dan jam ketika Anda tersedia untuk kegiatan volunteer.
        </p>
    </div>

    <div class="rounded-xl border border-orange-100 bg-orange-50 px-4 py-3 text-sm text-orange-800">
        Masukkan tanggal dan jam mulai serta akhir.
        Interval yang tumpang tindih tetap disimpan dan durasinya akan digabungkan saat perhitungan.
    </div>

    @if($volunteerProfile?->availability)
        <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            Preferensi lama:
            <span class="font-semibold">
                {{ $volunteerProfile->availability }}
            </span>.
            Lengkapi tanggal dan jam ketersediaan karena label lama tidak digunakan sebagai interval.
        </div>
    @endif

    <div
        data-repeater="availability"
        class="space-y-4"
    >

        <div data-rows class="space-y-4">

            @foreach(
                old(
                    'availability_slots',
                    $slots->map(
                        fn ($slot) => [
                            'starts_at' => $slot->starts_at
                                ->setTimezone('Asia/Jakarta')
                                ->format('Y-m-d\TH:i'),

                            'ends_at' => $slot->ends_at
                                ->setTimezone('Asia/Jakarta')
                                ->format('Y-m-d\TH:i'),
                        ]
                    )->all()
                    ?: [
                        [
                            'starts_at' => '',
                            'ends_at' => '',
                        ],
                    ]
                ) as $index => $row
            )

                @include('volunteer.profile.slot-row')

            @endforeach

        </div>

        <template data-template>
            @include('volunteer.profile.slot-row', [
                'index' => '__INDEX__',
                'row' => [
                    'starts_at' => '',
                    'ends_at' => '',
                ],
            ])
        </template>

        <div>
            <x-secondary-button data-add>
                Tambah interval
            </x-secondary-button>
        </div>

    </div>

</x-section-card>
