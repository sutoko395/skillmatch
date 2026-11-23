@php($notificationItems = app(\App\Services\NotificationReadService::class)->recent(auth()->user()))
<section class="mt-6 space-y-2.5" aria-labelledby="recent-notifications-heading">
    <div class="flex flex-wrap items-center justify-between gap-x-3 gap-y-1">
        <h2 id="recent-notifications-heading" class="text-lg font-bold text-slate-900">Notifikasi terbaru</h2>
        <a href="{{ route('notifications.index') }}" class="rounded text-xs font-semibold text-blue-700 hover:underline focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">Semua notifikasi</a>
    </div>
    @include('notifications.list')
</section>
