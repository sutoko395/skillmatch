@extends('admin.layouts.sidebar')

@section('title', 'Dashboard Utama Admin')

@section('content')
<div class="mx-auto w-full max-w-7xl space-y-6">

    <div class="relative overflow-hidden rounded-2xl border border-gray-100 bg-white p-6 shadow-sm md:p-8">
        <div class="absolute -right-4 -top-4 h-32 w-32 rounded-full bg-indigo-500/10 blur-xl"></div>
        <div class="relative space-y-2">
            <h1 class="text-xl font-semibold tracking-tight text-gray-900 md:text-2xl">
                Selamat Datang Kembali,
                <span class="font-bold text-indigo-600">{{ Auth::user()->name }}</span>!
            </h1>
            <p class="text-xs leading-relaxed text-gray-500 md:text-sm">
                Pantau statistik pengguna, transaksi, kegiatan, dan master data SkillMatch dalam satu dashboard.
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">

        <button type="button" data-chart="volunteers"
            class="stat-card rounded-2xl border border-gray-100 bg-white p-5 text-left shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2m12-13a4 4 0 11-8 0 4 4 0 018 0zm2 5a4 4 0 014 4v2m-3-15a4 4 0 010 7"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-sm text-gray-500">Total Volunteer</p>
                    <h3 class="mt-1 text-3xl font-bold text-gray-800">{{ number_format($totalVolunteers) }}</h3>
                    <p class="mt-1 text-xs text-indigo-600">Lihat tren pendaftaran →</p>
                </div>
            </div>
        </button>

        <button type="button" data-chart="organizers"
            class="stat-card rounded-2xl border border-gray-100 bg-white p-5 text-left shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M5 21V7l8-4v18m6 0V11l-6-4M9 9v.01M9 12v.01M9 15v.01M9 18v.01"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-sm text-gray-500">Total Organizer</p>
                    <h3 class="mt-1 text-3xl font-bold text-gray-800">{{ number_format($totalOrganizers) }}</h3>
                    <p class="mt-1 text-xs text-emerald-600">Lihat tren pendaftaran →</p>
                </div>
            </div>
        </button>

        <button type="button" data-chart="transactions"
            class="stat-card rounded-2xl border border-gray-100 bg-white p-5 text-left shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-sky-50 text-sky-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M5 7V5a2 2 0 012-2h10a2 2 0 012 2v2M5 7v12a2 2 0 002 2h10a2 2 0 002-2V7M9 12h6m-6 4h6"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-sm text-gray-500">Total Transaksi</p>
                    <h3 class="mt-1 text-3xl font-bold text-gray-800">{{ number_format($totalTransactions) }}</h3>
                    <p class="mt-1 text-xs text-sky-600">Lihat tren transaksi →</p>
                </div>
            </div>
        </button>

        <a href="{{ route('admin.event-verification.index') }}"
            class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M10.3 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.7 3.86a2 2 0 00-3.4 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Kegiatan Menunggu Verifikasi</p>
                    <h3 class="mt-1 text-3xl font-bold text-amber-600">{{ number_format($pendingEvents) }}</h3>
                    <p class="mt-1 text-xs text-gray-400">Buka antrean verifikasi →</p>
                </div>
            </div>
        </a>

        <a href="{{ route('admin.master-data.skills.index') }}"
            class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 7l3 3m-8 8l-5 2 2-5L16 5a2.12 2.12 0 013 3L11 18z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Master Skills</p>
                    <h3 class="mt-1 text-3xl font-bold text-blue-600">{{ number_format($totalSkills) }}</h3>
                    <p class="mt-1 text-xs text-gray-400">Kelola keterampilan →</p>
                </div>
            </div>
        </a>

        <a href="{{ route('admin.master-data.event-categories.index') }}"
            class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-purple-50 text-purple-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M3 3h7l11 11-7 7L3 10V3z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Kategori Event</p>
                    <h3 class="mt-1 text-3xl font-bold text-purple-600">{{ number_format($totalCategories) }}</h3>
                    <p class="mt-1 text-xs text-gray-400">Kelola kategori →</p>
                </div>
            </div>
        </a>
    </div>

    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h2 id="chartTitle" class="text-lg font-bold text-gray-900">
                Tren Pendaftaran Volunteer
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                @if ($range === 'all')
                    Tren sejak data pertama dibuat hingga sekarang.
                @elseif ($range === 'year')
                    Tren bulanan tahun {{ $year }}.
                @else
                    Tren harian {{ \Carbon\Carbon::create($year, $month, 1)->translatedFormat('F Y') }}.
                @endif
            </p>
        </div>

        <form action="{{ route('admin.dashboard') }}" method="GET"
            class="flex flex-wrap items-end gap-3">
            <div>
                <label for="range" class="mb-1 block text-xs font-medium text-gray-600">
                    Periode
                </label>
                <select id="range" name="range"
                    class="rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="all" @selected($range === 'all')>All</option>
                    <option value="year" @selected($range === 'year')>Tahun</option>
                    <option value="month" @selected($range === 'month')>Bulan</option>
                </select>
            </div>

            <div>
                <label for="year" class="mb-1 block text-xs font-medium text-gray-600">
                    Tahun
                </label>
                <select id="year" name="year"
                    class="rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @foreach ($years as $availableYear)
                        <option value="{{ $availableYear }}"
                            @selected((int) $year === (int) $availableYear)>
                            {{ $availableYear }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="month" class="mb-1 block text-xs font-medium text-gray-600">
                    Bulan
                </label>

                <select id="month" name="month"
                    class="rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">

                    <option value="" @selected($range !== 'month')>
                        Pilih Bulan
                    </option>

                    @foreach (range(1, 12) as $monthNumber)
                        <option value="{{ $monthNumber }}"
                            @selected($range === 'month' && (int) $month === $monthNumber)>
                            {{ \Carbon\Carbon::create()->month($monthNumber)->translatedFormat('F') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit"
                class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">
                Terapkan
            </button>
        </form>
    </div>
    
    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm md:p-6">
        <div class="relative h-72 md:h-96">
            <canvas id="adminTrendChart"></canvas>
        </div>
    </div>


    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm md:p-6">
        <a href="{{ route('admin.orders.index') }}"
            class="flex items-center justify-between gap-4">
            <div>
                <p class="text-sm text-gray-500">Manajemen Transaksi</p>
                <h3 class="mt-1 font-semibold text-gray-800">
                    Lihat seluruh pesanan dan detail transaksi
                </h3>
                <p class="mt-2 text-sm text-gray-500">
                    Total {{ number_format($totalTransactions) }} transaksi tercatat.
                </p>
            </div>
            <span class="shrink-0 text-sm font-semibold text-indigo-600">
                Buka transaksi →
            </span>
        </a>
    </div>

    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <h2 class="text-base font-bold text-gray-900">Aksi Cepat Admin</h2>
        <div class="mt-4 flex flex-wrap gap-3">
            <a href="{{ route('admin.volunteers.index') }}"
                class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700">
                Kelola Volunteer
            </a>
            <a href="{{ route('admin.organizers.index') }}"
                class="rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700">
                Kelola Organizer
            </a>
            <a href="{{ route('admin.master-data.skills.index') }}"
                class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700">
                Kelola Master Skills
            </a>
            <a href="{{ route('admin.orders.index') }}"
                class="rounded-xl bg-sky-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-sky-700">
                Kelola Transaksi
            </a>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const labels = @js($labels);
    const chartData = @js($chartData);
    const canvas = document.getElementById('adminTrendChart');
    const title = document.getElementById('chartTitle');

    if (!canvas || !title || typeof Chart === 'undefined') {
        console.error('Elemen grafik atau library Chart.js tidak ditemukan.');
        return;
    }

    const configs = {
        volunteers: {
            title: 'Tren Pendaftaran Volunteer',
            label: 'Volunteer baru',
            color: '#4f46e5'
        },
        organizers: {
            title: 'Tren Pendaftaran Organizer',
            label: 'Organizer baru',
            color: '#059669'
        },
        transactions: {
            title: 'Tren Transaksi Bulanan',
            label: 'Transaksi dibuat',
            color: '#0284c7'
        }
    };

    let chart;

    function renderChart(type) {
        const config = configs[type] || configs.volunteers;
        title.textContent = config.title;

        if (chart) chart.destroy();

        chart = new Chart(canvas, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: config.label,
                    data: chartData[type],
                    borderColor: config.color,
                    backgroundColor: config.color + '20',
                    fill: true,
                    tension: 0.3,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0 }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom'
                    }
                }
            }
        });

        document.querySelectorAll('.stat-card').forEach(function (card) {
            const active = card.dataset.chart === type;
            card.classList.toggle('ring-2', active);
            card.classList.toggle('ring-indigo-500', active);
        });
    }

    document.querySelectorAll('.stat-card').forEach(function (card) {
        card.addEventListener('click', function () {
            renderChart(card.dataset.chart);
        });
    });

    renderChart('volunteers');
});
</script>
@endsection
