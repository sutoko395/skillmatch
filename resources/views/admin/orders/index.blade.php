@extends('admin.layouts.sidebar')

@section('title', 'Transaksi')

@section('content')

    @include('components.flash')

    <div class="w-full max-w-7xl mx-auto space-y-6">

        <x-page-header
            title="Transaksi"
            description="Status pembayaran berasal dari gateway. Tidak ada perubahan paid manual."
        />

        <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">

            <form
                method="GET"
                class="flex flex-col gap-4 md:flex-row md:items-end"
            >

                <div class="flex-1">
                    <x-form-field
                        name="search"
                        label="Referensi Order"
                        :value="request('search')"
                    />
                </div>

                <div class="w-full md:w-64">
                    <x-form-field
                        name="status"
                        label="Status"
                    >
                        <select
                            id="status"
                            name="status"
                            class="mt-2 w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                            <option value="">
                                Semua Status
                            </option>

                            @foreach([
                                'pending',
                                'paid',
                                'failed',
                                'expired',
                                'cancelled',
                                'review_required',
                            ] as $statusOption)

                                <option
                                    value="{{ $statusOption }}"
                                    @selected(request('status') === $statusOption)
                                >
                                    {{ ucfirst(str_replace('_', ' ', $statusOption)) }}
                                </option>

                            @endforeach
                        </select>
                    </x-form-field>
                </div>

                <div>
                    <x-button type="submit">
                        Cari
                    </x-button>
                </div>

            </form>

        </div>

        <x-section-card>

            <div class="overflow-x-auto">

                <table class="w-full text-left text-sm">

                    <thead class="bg-slate-50">
                        <tr>

                            <th class="px-4 py-4 font-semibold text-slate-600">
                                Referensi
                            </th>

                            <th class="px-4 py-4 font-semibold text-slate-600">
                                Event
                            </th>

                            <th class="px-4 py-4 font-semibold text-slate-600">
                                Nominal
                            </th>

                            <th class="px-4 py-4 font-semibold text-slate-600">
                                Status
                            </th>

                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">

                        @forelse($orders as $order)

                            <tr class="transition hover:bg-slate-50">

                                <td class="px-4 py-4">
                                    <a
                                        href="{{ route('admin.orders.show', $order) }}"
                                        class="font-semibold text-indigo-600 hover:text-indigo-700 hover:underline"
                                    >
                                        {{ $order->order_ref }}
                                    </a>
                                </td>

                                <td class="px-4 py-4 text-slate-700">
                                    {{ $order->event?->title ?? '-' }}
                                </td>

                                <td class="px-4 py-4 font-semibold text-slate-800">
                                    Rp {{ number_format($order->amount, 0, ',', '.') }}
                                </td>

                                <td class="px-4 py-4">

                                    <div class="flex flex-wrap items-center gap-2">

                                        @php
                                            $statusClasses = match ($order->status) {
                                                'paid' => 'bg-emerald-50 text-emerald-700',
                                                'pending' => 'bg-amber-50 text-amber-700',
                                                'failed' => 'bg-red-50 text-red-700',
                                                'expired' => 'bg-slate-100 text-slate-700',
                                                'cancelled' => 'bg-slate-100 text-slate-700',
                                                'review_required' => 'bg-purple-50 text-purple-700',
                                                default => 'bg-slate-100 text-slate-700',
                                            };
                                        @endphp

                                        <span
                                            class="inline-flex rounded-lg px-2.5 py-1 text-xs font-bold {{ $statusClasses }}"
                                        >
                                            {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                        </span>

                                        @if($order->requires_follow_up)

                                            <span class="inline-flex rounded-lg bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-600">
                                                Perlu tindak lanjut
                                            </span>

                                        @endif

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td
                                    colspan="4"
                                    class="px-4 py-12 text-center text-sm text-slate-500"
                                >
                                    Tidak ada transaksi yang sesuai.
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </x-section-card>

        @if($orders->hasPages())

            <div>
                {{ $orders->links() }}
            </div>

        @endif

    </div>

@endsection