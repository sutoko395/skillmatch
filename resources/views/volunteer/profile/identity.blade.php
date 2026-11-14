<x-section-card class="space-y-5">

    <div class="border-b border-slate-100 pb-4">
        <h2 class="text-lg font-bold text-slate-800">
            Identitas dan Kontak
        </h2>

        <p class="mt-1 text-sm text-slate-500">
            Lengkapi informasi dasar dan kontak yang dapat digunakan.
        </p>
    </div>

    <div class="grid gap-5 md:grid-cols-2">

        <x-form-field
            name="name"
            label="Nama"
            :value="$user->name"
            required
        />

        <x-phone-field :value="$volunteerProfile?->phone" required />

    </div>

    <x-form-field
        name="city_id"
        label="Kota domisili"
        required
    >
        <select
            id="city_id"
            name="city_id"
            required
            class="mt-2 w-full rounded-xl border-slate-200 text-sm transition focus:border-orange-500 focus:ring-orange-500"
        >
            <option value="">
                Pilih kota aktif
            </option>

            @foreach($cities as $city)
                <option
                    value="{{ $city->id }}"
                    @selected(old('city_id', $volunteerProfile?->city_id) == $city->id)
                >
                    {{ $city->name }}
                </option>
            @endforeach
        </select>

        @if($volunteerProfile?->city && !$volunteerProfile?->city_id)
            <p class="mt-2 text-sm text-amber-800">
                Kota lama: {{ $volunteerProfile->city }}.
                Pilih kota dari daftar agar profil dapat digunakan.
            </p>
        @endif
    </x-form-field>

    <div class="grid gap-5 md:grid-cols-3"
        x-data="{
            birth: @js(old('birth_date', $volunteerProfile?->birth_date?->format('Y-m-d'))),
            today: @js(now()->setTimezone('Asia/Jakarta')->format('Y-m-d')),
            get age() {
                if (!this.birth || !/^\d{4}-\d{2}-\d{2}$/.test(this.birth) || this.birth > this.today) return '';
                const [y,m,d] = this.birth.split('-').map(Number);
                const date = new Date(0); date.setUTCFullYear(y,m-1,d);
                if (date.getUTCFullYear() !== y || date.getUTCMonth() !== m-1 || date.getUTCDate() !== d) return '';
                const [ty,tm,td] = this.today.split('-').map(Number);
                return (ty-y-((tm &lt; m || (tm === m &amp;&amp; td &lt; d)) ? 1 : 0)) + ' tahun';
            }
        }"
        @input="if ($event.target.name === 'birth_date') birth = $event.target.value">

        <x-form-field
            name="birth_date"
            label="Tanggal lahir"
            type="date"
            :max="now()->format('Y-m-d')"
            :value="$volunteerProfile?->birth_date?->format('Y-m-d')"
            required
        />

        <div>
            <label for="profile-age" class="block text-sm font-medium text-slate-600">Usia</label>
            <input id="profile-age" type="text" readonly :value="age" placeholder="Pilih tanggal lahir" aria-describedby="profile-age-help" class="mt-2 block w-full rounded-lg border-slate-200 bg-slate-50 text-slate-600" />
            <p id="profile-age-help" class="mt-2 text-xs text-slate-500">Dihitung otomatis dari tanggal lahir.</p>
            <noscript><p class="mt-1 text-xs text-slate-500">Aktifkan JavaScript untuk melihat usia otomatis.</p></noscript>
        </div>

        <x-form-field
            name="gender"
            label="Jenis kelamin"
            required
        >
            <select
                id="gender"
                name="gender"
                required
                class="mt-2 w-full rounded-xl border-slate-200 text-sm transition focus:border-orange-500 focus:ring-orange-500"
            >
                <option value="">
                    Pilih
                </option>

                <option
                    value="male"
                    @selected(old('gender', $volunteerProfile?->gender) === 'male')
                >
                    Laki-laki
                </option>

                <option
                    value="female"
                    @selected(old('gender', $volunteerProfile?->gender) === 'female')
                >
                    Perempuan
                </option>
            </select>
        </x-form-field>

    </div>

    <x-form-field
        name="address"
        label="Alamat"
        :value="$volunteerProfile?->address"
        required
    />

    <x-form-field
        name="bio"
        label="Bio singkat"
        helper="Maksimal 100 kata / 700 karakter."
        required
    >
        <textarea
            id="bio"
            name="bio"
            required
            maxlength="700"
            rows="4"
            class="mt-2 w-full rounded-xl border-slate-200 text-sm transition focus:border-orange-500 focus:ring-orange-500"
        >{{ old('bio', $volunteerProfile?->bio) }}</textarea>
    </x-form-field>

</x-section-card>
