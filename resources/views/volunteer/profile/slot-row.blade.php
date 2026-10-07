<div data-row class="rounded-xl border border-slate-200 bg-slate-50/50 p-4">

    <div class="grid gap-5 sm:grid-cols-2">

        @foreach([
            'starts_at' => 'Mulai (WIB)',
            'ends_at' => 'Selesai (WIB)',
        ] as $key => $label)

            <div>

                <label
                    for="{{ $key }}-{{ $index }}"
                    class="block text-sm font-semibold text-slate-700"
                >
                    {{ $label }} *
                </label>

                <x-text-input
                    type="datetime-local"
                    id="{{ $key }}-{{ $index }}"
                    name="availability_slots[{{ $index }}][{{ $key }}]"
                    :value="$row[$key] ?? ''"
                    required
                    class="mt-2 w-full"
                />

                <x-input-error
                    :messages="$errors->get('availability_slots.'.$index.'.'.$key)"
                    class="mt-1"
                />

            </div>

        @endforeach

    </div>

    <div class="mt-4 flex justify-end">
        <x-secondary-button
            type="button"
            data-remove
        >
            Hapus interval
        </x-secondary-button>
    </div>

</div>