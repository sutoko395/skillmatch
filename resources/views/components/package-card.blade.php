@props(['package', 'showStatus' => false, 'showFutureFeatures' => false])
<article class="flex h-full flex-col rounded-2xl border border-slate-200 bg-white p-7 shadow-sm sm:p-8">
    <div class="flex flex-wrap items-center justify-between gap-3"><h3 class="text-2xl font-bold text-slate-900">{{ $package->name }}</h3>@if($showStatus)<span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700">{{ $package->is_active ? 'Aktif' : 'Nonaktif' }}</span>@endif</div>
    <p class="mt-7 text-3xl font-bold tracking-tight text-slate-900">{{ $package->price === 0 ? 'Gratis' : 'Rp'.number_format($package->price, 0, ',', '.') }}</p>
    <p class="mt-2 text-sm text-slate-500">per event</p>
    <ul class="mb-8 mt-7 space-y-3 text-sm leading-relaxed text-slate-700">
        <li>{{ $package->max_positions }} posisi</li>
        <li>{{ $package->max_applications }} lamaran terkirim</li>
        <li>{{ $package->max_registration_days }} hari pendaftaran</li>
        @if($showFutureFeatures)<li>Screening, assessment, dan seleksi <span class="block text-xs text-slate-500">Dalam pengembangan</span></li><li>Dokumen privat dan attendance <span class="block text-xs text-slate-500">Dalam pengembangan</span></li>@endif
    </ul>
    <div class="mt-auto">{{ $slot }}</div>
</article>
