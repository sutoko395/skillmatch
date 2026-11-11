<div class="mx-auto max-w-7xl space-y-6">
    <a href="{{ route('volunteer.activity.index') }}" class="inline-flex rounded-xl border border-blue-200 bg-white px-4 py-3 text-sm font-semibold text-blue-600 hover:bg-blue-50">&larr; Ringkasan Aktivitas</a>
    <x-page-header title="Notifikasi" description="Pantau pembaruan kegiatan, lamaran, dan proses akun Anda." />
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white" aria-label="Daftar notifikasi">
        <div class="flex items-center gap-3 border-b border-slate-100 p-5 sm:px-6">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600" aria-hidden="true">
                @include('volunteer.notifications.partials.icon')
            </span>
            <div><h2 class="font-bold text-slate-900">Semua notifikasi</h2><p class="mt-1 text-xs text-slate-500">Pembaruan terbaru ditampilkan lebih dahulu.</p></div>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse($notifications as $notification)
                @include('volunteer.notifications.partials.item')
            @empty
                <div class="flex flex-col items-center px-6 py-16 text-center">
                    <span class="mb-5 flex h-16 w-16 items-center justify-center rounded-2xl bg-blue-50 text-blue-600" aria-hidden="true">@include('volunteer.notifications.partials.icon')</span>
                    <h3 class="text-lg font-bold text-slate-900">Belum ada notifikasi</h3>
                    <p class="mt-2 max-w-md text-sm leading-relaxed text-slate-500">Pembaruan lamaran dan kegiatan Anda akan muncul di sini ketika tersedia.</p>
                </div>
            @endforelse
        </div>
    </section>
    {{ $notifications->links() }}
</div>
