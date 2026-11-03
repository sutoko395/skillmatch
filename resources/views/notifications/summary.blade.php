@php($notificationRows = \App\Models\NotificationOutbox::where('recipient_id', auth()->id())->where('channel', 'database')->latest('id')->limit(5)->get())
<section class="mt-6 space-y-4" aria-label="Notifikasi terbaru">
    <div class="flex items-center justify-between gap-4">
        <h2 class="text-lg font-bold text-slate-900">Notifikasi terbaru</h2>
        <a href="{{ route('notifications.index') }}" class="text-sm font-semibold text-indigo-600">Semua notifikasi</a>
    </div>
    @include('notifications.list')
</section>
