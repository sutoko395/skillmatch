@props(['cities', 'selected' => null, 'helper' => null])

<x-form-field name="city_id" label="Kota" required :helper="$cities->isEmpty() ? 'Belum ada kota aktif. Hubungi Admin untuk menambahkan kota.' : $helper">
    <select
        id="city_id"
        name="city_id"
        required
        class="mt-2 w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500"
    >
        <option value="">Pilih Kota</option>
        @foreach($cities as $city)
            <option value="{{ $city->id }}" @selected(old('city_id', $selected) == $city->id)>
                {{ $city->name }}
            </option>
        @endforeach
    </select>
</x-form-field>
