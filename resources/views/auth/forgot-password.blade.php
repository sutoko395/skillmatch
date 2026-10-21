<x-auth-layout
    title="Lupa kata sandi?"
    intro="Masukkan alamat email yang terdaftar. Kami akan mengirimkan tautan untuk membuat kata sandi baru."
>
    <div class="space-y-5">
        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm leading-6 text-emerald-800">
                Jika alamat email terdaftar, tautan reset password telah dikirim. Silakan periksa kotak masuk atau folder spam Anda.
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ $errors->first('email') ?? $errors->first() }}
            </div>
        @endif

        <form
            method="POST"
            action="{{ route('password.email') }}"
            x-data="{ submitting: false }"
            @submit="submitting = true"
            class="space-y-4"
        >
            @csrf

            <div>
                <x-input-label for="email" value="Alamat email" />

                <x-text-input
                    id="email"
                    class="mt-1.5 block w-full rounded-xl py-3"
                    type="email"
                    name="email"
                    :value="old('email')"
                    required
                    autofocus
                    autocomplete="email"
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

            <button
                type="submit"
                :disabled="submitting"
                class="flex min-h-[48px] w-full items-center justify-center gap-3 rounded-xl bg-indigo-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 disabled:cursor-wait disabled:opacity-60"
            >
                <span x-text="submitting ? 'Mengirim tautan...' : 'Kirim tautan reset'">
                    Kirim tautan reset
                </span>

                <span aria-hidden="true">→</span>
            </button>

            <p
                x-cloak
                x-show="submitting"
                role="status"
                class="text-center text-sm text-slate-500"
            >
                Memproses permintaan Anda...
            </p>
        </form>

        <p class="border-t border-slate-100 pt-5 text-center text-sm text-slate-600">
            Ingat kata sandi Anda?
            <a
                href="{{ route('login') }}"
                class="font-semibold text-indigo-700 hover:underline"
            >
                Masuk di sini
            </a>
        </p>
    </div>
</x-auth-layout>