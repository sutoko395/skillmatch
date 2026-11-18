<div class="vaa-actions sticky bottom-3 z-20 flex flex-wrap items-center gap-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
    <button type="submit" formaction="{{ route('volunteer.assessments.answers', $attempt) }}" name="_method" value="PUT" @click.prevent="save()" :disabled="busy || finished" class="volunteer-action volunteer-action--blue rounded-xl border border-indigo-600 px-4 py-2 font-semibold text-indigo-700 disabled:opacity-50"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" /></svg> Simpan jawaban</button>
    <x-button type="submit" x-bind:disabled="busy || finished">Kirim asesmen</x-button>
</div>

<div
    x-show="confirmOpen"
    x-cloak
    x-transition.opacity
    @keydown.escape.window="if (!busy) confirmOpen = false"
    class="fixed inset-0 z-[99999] flex items-center justify-center p-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="submit-assessment-title"
>
    <div
        class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm"
        @click="if (!busy) confirmOpen = false"
    ></div>

    <div
        x-show="confirmOpen"
        x-transition
        class="relative z-10 w-full max-w-md rounded-2xl bg-white p-7 shadow-2xl"
    >
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-amber-50 text-amber-500">
            <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 9v3m0 4h.01M10.3 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.7 3.86a2 2 0 0 0-3.4 0Z"/>
            </svg>
        </div>

        <h2
            id="submit-assessment-title"
            class="mt-5 text-center text-xl font-bold text-slate-900"
        >
            Kirim Asesmen?
        </h2>

        <p class="mt-3 text-center text-sm leading-6 text-slate-600">
            Apakah Anda yakin ingin mengirim asesmen ini?
            Setelah dikirim, jawaban tidak dapat diubah kembali.
        </p>

        <div class="mt-7 flex flex-col-reverse gap-3 sm:flex-row sm:justify-center">
            <button
                type="button"
                @click="confirmOpen = false"
                :disabled="busy"
                class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 disabled:opacity-50"
            >
                Kembali Periksa
            </button>

            <button
                type="button"
                @click="confirmSubmit()"
                :disabled="busy || finished"
                class="inline-flex h-11 items-center justify-center rounded-xl bg-indigo-600 px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 disabled:opacity-50"
            >
                <span x-show="!busy">Ya, Kirim Asesmen</span>
                <span x-show="busy" x-cloak>Mengirim...</span>
            </button>
        </div>
    </div>
</div>