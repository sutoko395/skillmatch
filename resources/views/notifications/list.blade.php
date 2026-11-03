<div class="space-y-3">
@forelse($notificationRows as $notification)
    <article class="rounded-xl border border-slate-200 bg-white p-4 {{ $notification->read_at ? '' : 'border-l-4 border-l-indigo-500' }}">
        <p class="font-semibold text-slate-800">{{ $notification->label() }}</p>
        <p class="mt-1 text-xs text-slate-500">{{ $notification->created_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</p>
        @php($destination = app(\App\Services\ActivityReadService::class)->notificationDestination($notification, auth()->user()))
        <div class="mt-3 flex flex-wrap gap-4 text-sm">
            <a href="{{ $destination }}" class="font-semibold text-indigo-600">Lihat detail</a>
            @if(!$notification->read_at)
                <form method="POST" action="{{ route('notifications.read', $notification) }}">
                    @csrf @method('PATCH')
                    <button type="submit" class="font-semibold text-slate-600">Tandai sudah dibaca</button>
                </form>
            @endif
        </div>
    </article>
@empty
    <p class="rounded-xl border border-dashed border-slate-200 p-5 text-sm text-slate-500">Belum ada notifikasi.</p>
@endforelse
</div>
