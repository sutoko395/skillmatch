@php($destination = app(\App\Services\ActivityReadService::class)->notificationDestination($notification, auth()->user()))
<article class="flex items-start gap-4 p-5 sm:p-6 {{ $notification->read_at ? 'bg-white' : 'bg-blue-50/40' }}">
    <span class="mt-1 flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $notification->read_at ? 'bg-slate-100 text-slate-500' : 'bg-blue-100 text-blue-600' }}" aria-hidden="true">@include('volunteer.notifications.partials.icon')</span>
    <div class="min-w-0 flex-1">
        <div class="flex flex-wrap items-start justify-between gap-2">
            <h3 class="min-w-0 break-words text-sm font-semibold leading-relaxed text-slate-900">{{ $notification->label() }}</h3>
            <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-medium {{ $notification->read_at ? 'bg-slate-100 text-slate-500' : 'bg-blue-100 text-blue-700' }}">{{ $notification->read_at ? 'Sudah dibaca' : 'Belum dibaca' }}</span>
        </div>
        <time class="mt-2 block text-xs text-slate-500" datetime="{{ $notification->created_at->toIso8601String() }}">{{ $notification->created_at->copy()->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</time>
        <div class="mt-4 flex flex-wrap items-center gap-3 text-sm">
            <a href="{{ $destination }}" class="inline-flex min-h-11 items-center rounded-lg bg-blue-600 px-4 py-2 font-semibold text-white hover:bg-blue-700">Lihat detail</a>
            @if(!$notification->read_at)
                <form method="POST" action="{{ route('notifications.read', $notification) }}">
                    @csrf @method('PATCH')
                    <button type="submit" class="min-h-11 rounded-lg border border-slate-200 bg-white px-4 py-2 font-medium text-slate-600 hover:bg-slate-50">Tandai sudah dibaca</button>
                </form>
            @endif
        </div>
    </div>
</article>
