@php
    $statusLabel = match($order->status) {
        'paid' => 'Lunas', 'pending' => 'Menunggu pembayaran', 'failed' => 'Gagal',
        'expired' => 'Kedaluwarsa', 'cancelled' => 'Dibatalkan', 'review_required' => 'Perlu pemeriksaan',
        default => 'Status belum diketahui',
    };
    $statusTone = match($order->status) {
        'paid' => 'bg-emerald-50 text-emerald-700', 'pending', 'review_required' => 'bg-amber-50 text-amber-700',
        'failed' => 'bg-red-50 text-red-700', default => 'bg-slate-100 text-slate-600',
    };
    $organizer = $order->event?->organizer;
@endphp
<div class="admin-order-detail space-y-5">
    <section class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6" aria-label="Ringkasan transaksi">
        <div class="flex flex-wrap items-start justify-between gap-5">
            <div class="min-w-0">
                <p class="text-xs text-slate-500">Kegiatan</p>
                <h2 class="mt-1 break-words">{{ $order->event?->title ?? 'Kegiatan tidak tersedia' }}</h2>
                <div class="mt-3 flex flex-wrap items-center gap-2">
                    <span class="rounded-lg px-3 py-1 text-xs font-semibold {{ $statusTone }}">{{ $statusLabel }}</span>
                    <span class="rounded-lg bg-slate-50 px-2 py-1 text-xs text-slate-500">Sandbox</span>
                </div>
            </div>
            <div>
                <p class="text-xs text-slate-500">Total pembayaran</p>
                <p class="mt-1 text-2xl font-semibold tracking-tight text-slate-900">Rp {{ number_format($order->amount, 0, ',', '.') }}</p>
                <p class="mt-1 text-xs text-slate-500">{{ $order->currency }}</p>
            </div>
        </div>
    </section>

    <div class="grid gap-5 lg:grid-cols-2">
        <section class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6" aria-label="Informasi transaksi">
            <h2 class="flex items-center gap-2"><svg class="h-5 w-5 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 10h18M7 15h4"/></svg>Informasi transaksi</h2>
            <dl class="mt-4 space-y-4">
                <div><dt class="text-xs text-slate-500">Nomor transaksi</dt><dd class="mt-1 break-all text-sm font-medium text-slate-900">{{ $order->order_ref }}</dd></div>
                <div><dt class="text-xs text-slate-500">Paket</dt><dd class="mt-1 text-sm text-slate-900">{{ $order->package_snapshot['name'] ?? 'Tidak tersedia' }}</dd></div>
                <div><dt class="text-xs text-slate-500">Dibuat pada</dt><dd class="mt-1 text-sm text-slate-700">{{ $order->created_at?->setTimezone('Asia/Jakarta')->format('d/m/Y H:i').' WIB' }}</dd></div>
            </dl>
        </section>
        <section class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6" aria-label="Organisasi pemilik transaksi">
            <h2 class="flex items-center gap-2"><svg class="h-5 w-5 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M4 21V3h12v18M16 9h4v12M8 7h4M8 11h4M8 15h4M2 21h20"/></svg>EO / Organisasi</h2>
            <dl class="mt-4 space-y-4">
                <div><dt class="text-xs text-slate-500">Nama organisasi</dt><dd class="mt-1 break-words text-sm font-medium text-slate-900">{{ $organizer?->organizerProfile?->organization_name ?? $organizer?->name ?? 'Tidak tersedia' }}</dd></div>
                <div><dt class="text-xs text-slate-500">Nama akun</dt><dd class="mt-1 text-sm text-slate-700">{{ $organizer?->name ?? 'Tidak tersedia' }}</dd></div>
                <div><dt class="text-xs text-slate-500">Email</dt><dd class="mt-1 break-all text-sm text-slate-700">{{ $organizer?->email ?? 'Tidak tersedia' }}</dd></div>
            </dl>
        </section>
    </div>

    <section class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6" aria-label="Pembayaran dan aktivasi">
        <h2>Pembayaran &amp; aktivasi</h2>
        <dl class="mt-4 grid gap-4 sm:grid-cols-2">
            @foreach(['paid_at' => 'Pembayaran terverifikasi', 'activated_at' => 'Aktivasi hak paket'] as $field => $label)
                <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                    <dt class="text-xs text-slate-500">{{ $label }}</dt>
                    <dd class="mt-2 text-sm font-medium text-slate-900">{{ $order->$field ? $order->$field->setTimezone('Asia/Jakarta')->format('d/m/Y H:i').' WIB' : ($field === 'paid_at' ? 'Belum terverifikasi' : 'Belum diaktifkan') }}</dd>
                </div>
            @endforeach
        </dl>
        @if($order->requires_follow_up)
            <p role="status" class="mt-4 rounded-lg bg-amber-50 p-3 text-xs leading-relaxed text-amber-900">Transaksi memerlukan pemeriksaan admin. Refund otomatis tidak tersedia.</p>
        @endif
        @if($order->status === 'paid' && $order->event?->publication_status !== 'published')
            <p class="mt-4 text-xs text-slate-600">Pembayaran tercatat. Kegiatan belum dipublikasikan.</p>
        @endif
        <div class="mt-5 flex flex-wrap items-center gap-3 border-t border-slate-100 pt-4">
            <form method="POST" action="{{ route('admin.orders.sync', $order) }}" x-data="{busy:false}" @submit="if (busy) { $event.preventDefault(); } else { $nextTick(() => busy = true); }" @pageshow.window="busy = false">
                @csrf
                <button type="submit" class="admin-action admin-action-blue" :disabled="busy" aria-label="Sinkronkan status">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M20 7v5h-5M4 17v-5h5M5 8a8 8 0 0 1 13-3l2 2M4 17l2 2a8 8 0 0 0 13-3"/></svg>
                    <span x-text="busy ? 'Memeriksa...' : 'Sinkronkan status'">Sinkronkan status</span>
                </button>
            </form>
            <p class="text-xs text-slate-500">Status mengikuti hasil pemeriksaan gateway.</p>
        </div>
    </section>
</div>
