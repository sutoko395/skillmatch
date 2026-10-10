<x-section-card>
<p class="mb-4 font-semibold">{{ $order->event->title }}</p>
<dl class="space-y-3"><div><dt class="text-sm text-slate-600">Referensi</dt><dd class="break-all font-semibold">{{ $order->order_ref }}</dd></div><div><dt class="text-sm text-slate-600">Paket</dt><dd>{{ $order->package_snapshot['name'] }}</dd></div><div><dt class="text-sm text-slate-600">Nominal</dt><dd>Rp {{ number_format($order->amount,0,',','.') }} ({{ $order->currency }})</dd></div><div><dt class="text-sm text-slate-600">Status server</dt><dd>{{ $order->status }}</dd></div><div><dt class="text-sm text-slate-600">Pembayaran terverifikasi</dt><dd>{{ $order->paid_at?->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') ?? 'Belum terverifikasi' }} @if($order->paid_at) WIB @endif</dd></div><div><dt class="text-sm text-slate-600">Aktivasi hak paket</dt><dd>{{ $order->activated_at?->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') ?? 'Belum diaktifkan' }} @if($order->activated_at) WIB @endif</dd></div></dl>
@if($order->requires_follow_up)<p role="status" class="mt-4 rounded-lg bg-amber-50 p-3 text-amber-900">Transaksi memerlukan pemeriksaan admin. Refund otomatis tidak tersedia.</p>@endif
@if($order->status==='paid' && $order->event->publication_status!=='published')<p class="mt-4 text-slate-600">Pembayaran tercatat, event belum dipublikasikan. Buka event untuk memeriksa status organisasi, assessment, jadwal dan kelengkapan publikasi.</p>@endif
<div @if(!$admin) x-data="paymentStatus(@js($order->status), @js(($checkPayment ?? false) && $order->status === 'pending' && (bool) $order->checkout_url))" @click.document="navigateAway($event)" @pagehide.window="cancel()" @pageshow.window="leaving = false" @endif>
@if($order->status === 'pending')
<p class="mt-4 rounded-lg bg-indigo-50 p-3 text-sm text-indigo-900">Sudah membayar? @if(!$admin && ($checkPayment ?? false) && $order->checkout_url) Status akan diperiksa ke Midtrans setelah kembali dari checkout. @endif Jika masih menunggu, gunakan Sinkronkan status. Jangan membuat transaksi baru.</p>
@endif
@if(!$admin)
<p x-cloak x-show="message" x-text="message" role="status" aria-live="polite" class="mt-4 rounded-lg p-3 text-sm" :class="failed ? 'bg-amber-50 text-amber-900' : 'bg-slate-50 text-slate-700'"></p>
@endif
<div class="mt-6 flex flex-wrap items-center gap-3">
@if(!$admin && $order->status==='pending' && $order->event->status==='approved' && $order->event->lifecycle_status==='upcoming' && $order->event->publication_status!=='suspended' && $order->event->ends_at > now())
@if($order->checkout_url)<a class="rounded-lg bg-indigo-600 px-4 py-2 text-white" href="{{ $order->checkout_url }}" rel="noreferrer">Buka pembayaran Sandbox</a>@else<form method="POST" action="{{ route('organizer.orders.checkout',$order) }}" x-data="{busy:false}" @submit="busy=true">@csrf<x-button x-bind:disabled="busy">Siapkan checkout Sandbox</x-button></form>@endif
@endif
<form method="POST" action="{{ route($admin ? 'admin.orders.sync' : 'organizer.orders.sync',$order) }}" @if($admin) x-data="{busy:false}" @submit="busy=true" @else x-ref="syncForm" @submit.prevent="sync()" @endif>@csrf<x-button type="submit" variant="secondary" x-bind:disabled="busy" aria-label="Sinkronkan status" class="min-h-10 rounded-lg">Sinkronkan status</x-button></form>
@if(!$admin)
<a class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2" href="{{ route('organizer.events.show',$order->event_id) }}">
<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5m7-7-7 7 7 7" /></svg>
Kembali ke event
</a>
@endif
</div>
</div>
</x-section-card>
