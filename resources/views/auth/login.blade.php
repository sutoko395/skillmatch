<x-auth-layout
    title="Masuk ke SkillMatch"
    intro="Lanjutkan perjalanan Anda dan kelola kontribusi dari akun yang sama."
>
    <x-auth-session-status
        class="mb-4 rounded-xl bg-emerald-50 p-3"
        :status="session('status')"
    />

    <form method="POST" action="{{ route('login') }}" x-data="{ submitting: false }" @submit="submitting = true" class="space-y-4">
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
                autocomplete="username"
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

        <div>
            <x-auth-password-field
                name="password"
                label="Kata sandi"
                autocomplete="current-password"
            />
        </div>

        <div class="flex items-center justify-between gap-3 text-sm">
            <label for="remember_me" class="inline-flex items-center gap-2 text-slate-600">
                <input
                    id="remember_me"
                    type="checkbox"
                    class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                    name="remember"
                    @checked(old('remember'))
                >
                Ingat saya
            </label>

            <a
                href="{{ route('password.request') }}"
                class="font-semibold text-indigo-700 hover:underline"
            >
                Lupa kata sandi?
            </a>
        </div>

        <button
            type="submit"
            :disabled="submitting"
            class="flex min-h-[48px] w-full items-center justify-center gap-3 rounded-xl bg-indigo-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 disabled:cursor-wait disabled:opacity-60"
        >
            <span x-text="submitting ? 'Sedang masuk...' : 'Masuk ke akun'">
                Masuk ke akun
            </span>

            <span aria-hidden="true">→</span>
        </button>

        <p
            x-cloak
            x-show="submitting"
            role="status"
            class="text-center text-sm text-slate-500"
        >
            Memeriksa akun Anda...
        </p>
    </form>

    <p class="mt-5 border-t border-slate-100 pt-5 text-center text-sm text-slate-600">
        Belum punya akun?
        <a
            class="font-semibold text-indigo-700 hover:underline"
            href="{{ route('register') }}"
        >
            Daftar sekarang
        </a>
    </p>
</x-auth-layout>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const countdownPattern = /Please try again in (\d+) seconds?\.?/i;

    function updateCountdown() {
        document.querySelectorAll('body *').forEach((element) => {
            if (element.children.length > 0) return;

            const text = element.textContent.trim();
            const match = text.match(countdownPattern);

            if (!match || element.dataset.countdownActive === 'true') return;

            let remaining = Number(match[1]);

            if (remaining <= 0) {
                element.textContent = 'Waktu pembatasan hampir berakhir. Silakan coba masuk kembali.';
                return;
            }

            element.dataset.countdownActive = 'true';

            const render = () => {
                element.textContent = remaining > 0
                    ? `Too many login attempts. Please try again in ${remaining} seconds.`
                    : 'Waktu pembatasan berakhir. Silakan coba masuk kembali.';
            };

            render();

            const timer = window.setInterval(() => {
                remaining--;
                render();

                if (remaining <= 0) {
                    window.clearInterval(timer);
                    delete element.dataset.countdownActive;
                }
            }, 1000);
        });
    }

    updateCountdown();

    const observer = new MutationObserver(updateCountdown);
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
});
</script>
