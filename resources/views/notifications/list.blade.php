<ul class="divide-y divide-slate-200 rounded-2xl border border-slate-200 bg-white px-3.5">
    @forelse ($notificationItems as $notice)
        @php
            $row = $notice['row'];
            $iconTone = match ($notice['icon']) {
                'check' => 'bg-emerald-50 text-emerald-700',
                'closed' => 'bg-rose-50 text-rose-700',
                default => 'bg-blue-50 text-blue-700',
            };
        @endphp
        <li class="flex items-start gap-2.5 py-3.5">
            <span class="grid h-7 w-7 shrink-0 place-items-center self-start rounded-full {{ $iconTone }}" aria-hidden="true">
                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    @switch($notice['icon'])
                        @case('check')
                            <path d="m5 12 4 4L19 6"/>
                            @break
                        @case('assessment')
                            <rect x="5" y="3" width="14" height="18" rx="2"/>
                            <path d="M9 9h6m-6 4h6"/>
                            @break
                        @case('closed')
                            <path d="m7 7 10 10m0-10L7 17"/>
                            @break
                        @case('organization')
                            <path d="M3 21h18M5 21V7l8-4v18m6 0V11l-6-4M9 9h.01M9 13h.01M9 17h.01"/>
                            @break
                        @case('calendar')
                            <rect x="4" y="6" width="16" height="15" rx="2"/>
                            <path d="M8 3v5m8-5v5M4 11h16"/>
                            @break
                        @case('payment')
                            <rect x="3" y="5" width="18" height="14" rx="2"/>
                            <path d="M3 10h18m-14 5h4"/>
                            @break
                        @default
                            <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/>
                    @endswitch
                </svg>
            </span>
            <div class="min-w-0 grow sm:grid sm:grid-cols-[minmax(0,1fr)_auto] sm:gap-x-4">
                <div>
                    <h3 class="break-words text-sm font-bold leading-7 text-slate-900">{{ $notice['title'] }}</h3>
                    @if ($notice['event'] || $notice['position'])
                        <p class="break-words text-xs font-semibold leading-5 text-slate-700">
                            {{ implode(' / ', array_filter([$notice['event'], $notice['position']])) }}
                        </p>
                    @endif
                    @if ($notice['message'])
                        <p class="break-words text-xs leading-5 text-slate-600">{{ $notice['message'] }}</p>
                    @endif
                </div>
                <div class="mt-2 flex flex-wrap items-center gap-x-2 gap-y-1 self-start sm:mt-1 sm:flex-col sm:items-end">
                    <time class="text-xs tabular-nums leading-5 text-slate-500" datetime="{{ $row->created_at->toIso8601String() }}">{{ $row->created_at->setTimezone('Asia/Jakarta')->format('d/m/Y · H:i') }} WIB</time>
                    @unless ($row->read_at)
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700">
                            <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-blue-600" aria-hidden="true"></span>Belum dibaca
                        </span>
                    @endunless
                </div>
                <div class="mt-1.5 flex flex-wrap items-center gap-x-2 gap-y-1">
                    <a href="{{ $notice['destination'] }}" class="inline-flex min-h-8 items-center rounded-full bg-blue-50 px-3 text-xs font-semibold text-blue-800 hover:bg-blue-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">{{ $notice['action'] }}</a>
                    @unless ($row->read_at)
                        <form method="POST" action="{{ route('notifications.read', $row) }}">
                            @csrf @method('PATCH')
                            <button type="submit" class="min-h-8 rounded-full px-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700" aria-label="Tandai dibaca: {{ $notice['title'] }}">Tandai dibaca</button>
                        </form>
                    @endunless
                </div>
            </div>
        </li>
    @empty
        <li class="py-4 text-sm text-slate-600">Belum ada notifikasi.</li>
    @endforelse
</ul>
