<x-auth-layout title="Verifikasi email Anda" intro="Konfirmasikan alamat email yang digunakan pada akun Anda." :scrollable="true">
    <div class="space-y-5">
    <p class="text-sm leading-6 text-slate-600">
        Buka tautan verifikasi untuk <strong class="break-all text-slate-900">{{ auth()->user()->email }}</strong>.
        Jika belum menerima tautan, gunakan tombol Kirim ulang dan periksa kotak masuk serta folder spam.
        @if(auth()->user()->role === 'volunteer')
            Akun Anda sudah terdaftar dan aktif. Verifikasi email diperlukan sebelum membuat atau mengirim lamaran.
        @endif
    </p>

    @if (session('status') == 'verification-link-sent')
        <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            Tautan verifikasi telah dikirim. Tautan berlaku selama {{ config('auth.verification.expire', 60) }} menit.
        </div>
    @endif

    @if (session('status') == 'verification-send-failed')
        <div role="alert" class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            Tautan belum berhasil dikirim. Akun Anda tetap tersimpan. Coba kirim ulang; jika masih gagal, hubungi pengelola.
        </div>
    @endif

    @if(auth()->user()->role === 'volunteer')
        <p class="text-sm"><a class="text-indigo-700 underline focus-visible:outline focus-visible:outline-2 focus-visible:outline-indigo-600" href="{{ route('volunteer.profile.edit') }}">Lengkapi profil sambil menunggu verifikasi</a></p>
    @endif

    <div class="flex flex-wrap items-center justify-between gap-4">
        <form method="POST" action="{{ route('verification.send') }}" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf

            <x-primary-button x-bind:disabled="submitting">
                <span x-show="!submitting">Kirim ulang tautan verifikasi</span>
                <span x-show="submitting" x-cloak role="status">Mengirim tautan...</span>
            </x-primary-button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Keluar
            </button>
        </form>
    </div>
    </div>
</x-auth-layout>
