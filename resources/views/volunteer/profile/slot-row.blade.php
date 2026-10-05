<div data-row class="rounded-xl border border-slate-200 p-4">
<div class="grid gap-4 sm:grid-cols-2">
@foreach(['starts_at' => 'Mulai (WIB)', 'ends_at' => 'Selesai (WIB)'] as $key => $label)
    <div><label for="{{ $key }}-{{ $index }}" class="block text-sm font-medium">{{ $label }} *</label>
        <x-text-input type="datetime-local" id="{{ $key }}-{{ $index }}" name="availability_slots[{{ $index }}][{{ $key }}]" :value="$row[$key] ?? ''" required class="mt-2 w-full" />
        <x-input-error :messages="$errors->get('availability_slots.'.$index.'.'.$key)" />
    </div>
@endforeach
</div><x-secondary-button data-remove class="mt-3">Hapus interval</x-secondary-button>
</div>
