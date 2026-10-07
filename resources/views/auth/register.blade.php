<x-auth-layout
    title="Buat akun Anda"
    intro="Pilih peran Anda, lalu siapkan profil untuk langkah berikutnya."
    :register="true"
>
    <form
        method="POST"
        action="{{ route('register') }}"
        x-data="{
            submitting: false,
            role: '{{ old('role', request('role') === 'organizer' ? 'organizer' : 'volunteer') }}'
        }"
        @submit="submitting = true"
        class="space-y-3.5"
    >
        @csrf

        <fieldset aria-describedby="role-error">
            <legend class="mb-2 text-sm font-medium text-slate-700">
                Saya ingin bergabung sebagai
            </legend>

            <div class="grid gap-3 sm:grid-cols-2">
                <label class="relative cursor-pointer">
                    <input
                        type="radio"
                        name="role"
                        value="volunteer"
                        x-model="role"
                        class="peer absolute left-4 top-4 h-4 w-4 border-slate-300 text-indigo-600 focus:ring-indigo-500"
                        required
                    >

                    <span class="block h-full rounded-xl border border-slate-200 p-3.5 pl-10 transition peer-checked:border-indigo-600 peer-checked:bg-indigo-50 peer-focus-visible:ring-2 peer-focus-visible:ring-indigo-500">
                        <span class="block text-sm font-semibold text-slate-900">
                            Volunteer
                        </span>

                        <span class="mt-0.5 block text-xs leading-relaxed text-slate-600">
                            Kenalkan skill dan jelajahi kegiatan.
                        </span>
                    </span>
                </label>

                <label class="relative cursor-pointer">
                    <input
                        type="radio"
                        name="role"
                        value="organizer"
                        x-model="role"
                        class="peer absolute left-4 top-4 h-4 w-4 border-slate-300 text-indigo-600 focus:ring-indigo-500"
                        required
                    >

                    <span class="block h-full rounded-xl border border-slate-200 p-3.5 pl-10 transition peer-checked:border-indigo-600 peer-checked:bg-indigo-50 peer-focus-visible:ring-2 peer-focus-visible:ring-indigo-500">
                        <span class="block text-sm font-semibold text-slate-900">
                            Organizer
                        </span>

                        <span class="mt-0.5 block text-xs leading-relaxed text-slate-600">
                            Siapkan kegiatan dan kebutuhan relawan.
                        </span>
                    </span>
                </label>
            </div>

            <x-input-error
                id="role-error"
                :messages="$errors->get('role')"
                class="mt-1.5"
            />
        </fieldset>

        <div class="grid gap-3.5 sm:grid-cols-2">
            <div>
                <x-input-label
                    for="name"
                    x-text="role === 'organizer' ? 'Nama organizer' : 'Nama lengkap'"
                />

                <x-text-input
                    id="name"
                    class="mt-1.5 block w-full rounded-xl py-3"
                    type="text"
                    name="name"
                    :value="old('name')"
                    required
                    autofocus
                    autocomplete="name"
                    maxlength="255"
                    placeholder="Nama lengkap Anda"
                    x-bind:placeholder="role === 'organizer' ? 'Nama Organizer Anda' : 'Nama lengkap Anda'"
                    :aria-invalid="$errors->has('name') ? 'true' : 'false'"
                    aria-describedby="name-error"
                />

                <x-input-error
                    id="name-error"
                    :messages="$errors->get('name')"
                    class="mt-1.5"
                />
            </div>

            <div>
                <x-input-label for="email" value="Alamat email" />

                <x-text-input
                    id="email"
                    class="mt-1.5 block w-full rounded-xl py-3"
                    type="email"
                    name="email"
                    :value="old('email')"
                    required
                    autocomplete="username"
                    maxlength="255"
                    placeholder="nama@email.com"
                    :aria-invalid="$errors->has('email') ? 'true' : 'false'"
                    aria-describedby="email-error"
                />

                <x-input-error
                    id="email-error"
                    :messages="$errors->get('email')"
                    class="mt-1.5"
                />
            </div>
        </div>

        <div class="grid gap-3.5 sm:grid-cols-2">
            <x-auth-password-field autocomplete="new-password" />

            <x-auth-password-field
                name="password_confirmation"
                label="Konfirmasi kata sandi"
                autocomplete="new-password"
            />
        </div>

        <p class="text-xs leading-5 text-slate-500">
            <span x-show="role === 'organizer'">
                Setelah membuat akun, Anda akan diarahkan untuk melengkapi data organisasi dan mengunggah dokumen verifikasi.
            </span>

            <span x-show="role === 'volunteer'">
                Setelah membuat akun, Anda akan diarahkan untuk melengkapi profil volunteer.
            </span>
        </p>

        <button
            type="submit"
            :disabled="submitting"
            class="flex min-h-[48px] w-full items-center justify-center gap-3 rounded-xl bg-indigo-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 disabled:cursor-wait disabled:opacity-60"
        >
            <span x-text="submitting ? 'Membuat akun...' : 'Buat akun SkillMatch'">
                Buat akun SkillMatch
            </span>

            <span aria-hidden="true">→</span>
        </button>

        <p
            x-cloak
            x-show="submitting"
            role="status"
            class="text-center text-sm text-slate-500"
        >
            Menyimpan akun Anda...
        </p>
    </form>

    <p class="mt-5 border-t border-slate-100 pt-5 text-center text-sm text-slate-600">
        Sudah punya akun?
        <a
            class="font-semibold text-indigo-700 hover:underline"
            href="{{ route('login') }}"
        >
            Masuk di sini
        </a>
    </p>
</x-auth-layout>