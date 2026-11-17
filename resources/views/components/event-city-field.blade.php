@props(['cities', 'selected' => null, 'helper' => null])

<x-form-field name="city_id" label="Kota/Kabupaten" required :helper="$cities->isEmpty() ? 'Belum ada kota aktif. Hubungi Admin untuk menambahkan kota.' : $helper">
    <select
        id="city_id"
        name="city_id"
        required
        class="mt-2 w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500"
    >
        <option value="">Pilih Kota/Kabupaten</option>
        <x-city-options :cities="$cities" :selected="$selected" />
    </select>
</x-form-field>