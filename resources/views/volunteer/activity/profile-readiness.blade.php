<div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2 md:p-8">

    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-900">
                Mulai Perjalanan Volunteer
            </h2>

            <p class="mt-2 text-sm leading-6 text-slate-600">
                Pastikan seluruh data profil dan skill sudah lengkap sebelum mengikuti kegiatan.
            </p>
        </div>

        <a
            href="{{ route('volunteer.profile.edit') }}"
            class="inline-flex shrink-0 items-center justify-center rounded-xl bg-indigo-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-indigo-700"
        >
            {{ ($eligibility['complete'] ?? false) ? 'Perbarui Profil' : 'Lengkapi Profil' }}
        </a>
    </div>

    @unless($eligibility['complete'] ?? false)

        @php
            $labels = [
                'name' => 'Nama',
                'email' => 'Email',
                'phone' => 'Nomor telepon',
                'birth_date' => 'Tanggal lahir',
                'gender' => 'Jenis kelamin',
                'address' => 'Alamat',
                'bio' => 'Bio',
                'city_id' => 'Kota aktif',
                'skills' => 'Skill aktif',
                'availability_slots' => 'Tanggal dan jam ketersediaan',
            ];
        @endphp

        <div class="mt-6 rounded-xl border border-amber-200 bg-amber-50 p-4">
            <p class="text-sm font-semibold text-amber-900">
                Data yang perlu dilengkapi
            </p>

            <ul class="mt-3 space-y-2 text-sm text-amber-800">
                @forelse($eligibility['missing_fields'] ?? [] as $field)
                    <li class="flex items-start gap-2">
                        <span class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-amber-500"></span>
                        <span>{{ $labels[$field] ?? 'Profil Volunteer' }}</span>
                    </li>
                @empty
                    <li>Profil masih perlu diperiksa.</li>
                @endforelse
            </ul>
        </div>

    @else

        <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4">
            <p class="text-sm font-semibold text-emerald-800">
                Profil Anda sudah lengkap dan siap digunakan.
            </p>

            <p class="mt-1 text-sm text-emerald-700">
                Silakan perbarui profil apabila ada perubahan data.
            </p>
        </div>

    @endunless

</div>
