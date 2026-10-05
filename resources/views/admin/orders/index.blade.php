@extends('admin.layouts.sidebar')
@section('title','Transaksi Sandbox')
@section('content')
@include('components.flash')
<x-page-header title="Transaksi Sandbox" description="Status pembayaran berasal dari gateway. Tidak ada perubahan paid manual." />
<form class="mb-5 flex flex-wrap items-end gap-4" method="GET"><x-form-field name="search" label="Referensi order" :value="request('search')" /><x-form-field name="status" label="Status"><select id="status" name="status" class="mt-2 rounded-lg border-slate-300"><option value="">Semua</option>@foreach(['pending','paid','failed','expired','cancelled','review_required'] as $s)<option @selected(request('status')===$s)>{{ $s }}</option>@endforeach</select></x-form-field><x-button>Cari</x-button></form>
<x-section-card><div class="overflow-x-auto"><table class="w-full text-left text-sm"><thead><tr><th class="p-3">Referensi</th><th class="p-3">Event</th><th class="p-3">Nominal</th><th class="p-3">Status</th></tr></thead><tbody>@forelse($orders as $order)<tr class="border-t"><td class="p-3"><a class="text-indigo-700 underline" href="{{ route('admin.orders.show',$order) }}">{{ $order->order_ref }}</a></td><td class="p-3">{{ $order->event->title }}</td><td class="p-3">Rp {{ number_format($order->amount,0,',','.') }}</td><td class="p-3">{{ $order->status }} @if($order->requires_follow_up) / Perlu tindak lanjut @endif</td></tr>@empty<tr><td colspan="4" class="p-3">Tidak ada transaksi yang sesuai.</td></tr>@endforelse</tbody></table></div></x-section-card><div class="mt-6">{{ $orders->links() }}</div>
@endsection
