<x-auth-layout
    title="Buat kata sandi baru"
    intro="Satu langkah lagi untuk kembali ke SkillMatch. Buat kata sandi baru agar Anda bisa melanjutkan perjalanan kontribusi."
>
    <form
        method="POST"
        action="{{ route('password.store') }}"
        x-data="{ submitting: false }"
        @submit="submitting = true"
        class="space-y-4"
    >
        @csrf

        <input
            type="hidden"
            name="token"
            value="{{ $request->route('token') }}"
        >

        <input
            type="hidden"
            name="email"
            value="{{ old('email', $request->email) }}"
        >

        <div>
            <x-input-label for="email" value="Alamat email" />

            <x-text-input
                id="email"
                class="mt-1.5 block w-full rounded-xl bg-slate-50 py-3"
                type="email"
                :value="old('email', $request->email)"
                disabled
                autocomplete="email"
            />
        </div>

        <x-auth-password-field
            name="password"
            label="Kata sandi baru"
            autocomplete="new-password"
        />

        <x-auth-password-field
            name="password_confirmation"
            label="Konfirmasi kata sandi baru"
            autocomplete="new-password"
        />

        @if ($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <button
            type="submit"
            :disabled="submitting"
            class="flex min-h-[48px] w-full items-center justify-center gap-3 rounded-xl bg-indigo-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 disabled:cursor-wait disabled:opacity-60"
        >
            <span x-text="submitting ? 'Memperbarui kata sandi...' : 'Simpan kata sandi baru'">
                Simpan kata sandi baru
            </span>

            <span aria-hidden="true">→</span>
        </button>

        <p
            x-cloak
            x-show="submitting"
            role="status"
            class="text-center text-sm text-slate-500"
        >
            Memproses perubahan kata sandi Anda...
        </p>

        <p class="border-t border-slate-100 pt-5 text-center text-sm text-slate-600">
            Ingin kembali ke halaman login?
            <a
                href="{{ route('login') }}"
                class="font-semibold text-indigo-700 hover:underline"
            >
                Masuk di sini
            </a>
        </p>
    </form>
</x-auth-layout>
