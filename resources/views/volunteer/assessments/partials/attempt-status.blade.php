<x-section-card class="vaa-status sticky top-20 z-20">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <p>Sisa waktu: <strong x-text="timeLeft">Menghitung…</strong></p>
        <p class="text-sm text-slate-600">Berakhir {{ $attempt->expires_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</p>
    </div>
    <p class="mt-2 text-sm text-slate-600">Terjawab <strong x-text="answeredCount">{{ count(array_filter($answers, fn ($answer) => $answer !== null)) }}</strong> dari {{ $questions->count() }} soal.</p>
    <p class="mt-2 text-sm" role="status" aria-live="polite" x-text="message">Jawaban terakhir dimuat dari server.</p>
    <x-input-error :messages="$errors->all()" class="mt-3" />
    <noscript><p class="mt-3 text-rose-700">Aktifkan JavaScript untuk timer dan penyimpanan otomatis. Kamu juga bisa menyimpan atau mengirim melalui tombol di bawah.</p></noscript>
</x-section-card>
