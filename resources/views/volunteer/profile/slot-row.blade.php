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
            class="volunteer-action volunteer-action--danger"
         class="volunteer-action volunteer-action--danger">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M9 6V4h6v2M5 6l1 14h12l1-14M10 10v6m4-6v6" /></svg> Hapus interval
        </x-secondary-button>
    </div>

</div>