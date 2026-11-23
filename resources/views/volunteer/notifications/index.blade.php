<div class="vn-notifications mx-auto max-w-7xl space-y-6">
    <a href="{{ route('volunteer.activity.index') }}" class="volunteer-action volunteer-action--back inline-flex rounded-xl border border-blue-200 bg-white px-4 py-3 text-sm font-semibold text-blue-600 hover:bg-blue-50"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5m7 7-7-7 7-7" /></svg> Kembali</a>
    <x-page-header title="Notifikasi" description="Pantau pembaruan kegiatan, lamaran, dan proses akun Anda." />
    <section class="space-y-3" aria-label="Daftar notifikasi">
        <div class="flex items-center gap-3">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600" aria-hidden="true">
                @include('volunteer.notifications.partials.icon')
            </span>
            <div><h2 class="font-bold text-slate-900">Semua notifikasi</h2><p class="mt-1 text-xs text-slate-500">Pembaruan terbaru ditampilkan lebih dahulu.</p></div>
        </div>
        @include('notifications.list')
    </section>
    {{ $notifications->links() }}
</div>
