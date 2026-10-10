@props(['packages', 'selected' => null])

<section class="rounded-xl border border-indigo-100 bg-indigo-50 p-5" aria-label="Paket event">
    <h2 class="mb-3 text-lg font-semibold text-indigo-950">1. Pilih paket event</h2>
    <x-form-field name="package_id" label="Paket" required>
        <select id="package_id" name="package_id" required x-model="packageId"
            class="mt-2 w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">Pilih paket terlebih dahulu</option>
            @foreach($packages as $package)
                <option value="{{ $package['id'] }}" @selected(old('package_id', $selected) == $package['id'])>
                    {{ $package['name'] }} - {{ $package['price'] == 0 ? 'Gratis' : 'Rp'.number_format($package['price'], 0, ',', '.') }} / event - maksimal {{ $package['max_registration_days'] }} hari pendaftaran
                </option>
            @endforeach
        </select>
    </x-form-field>
    <p class="mt-3 text-sm text-indigo-900" x-show="selectedPackage" x-cloak>
        <span x-text="selectedPackage?.max_positions"></span> posisi;
        <span x-text="selectedPackage?.max_applications"></span> lamaran;
        maksimal <span x-text="selectedPackage?.max_registration_days"></span> hari pendaftaran.
    </p>
    <p class="mt-2 text-sm text-slate-600">Paket berlaku untuk satu event. Pembayaran paket berbayar dilakukan setelah persetujuan Admin.</p>
    @if($packages->isEmpty())
        <p role="status" class="mt-3 text-sm text-rose-800">Paket belum tersedia. Hubungi Admin untuk menambahkan paket aktif.</p>
    @endif
</section>
