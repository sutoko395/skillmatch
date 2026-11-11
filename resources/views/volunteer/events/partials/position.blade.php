<x-section-card class="space-y-5">
    <header class="flex flex-wrap items-start justify-between gap-3">
        <h3 class="min-w-0 break-words text-xl font-bold text-slate-900">{{ $position->name }}</h3>
        <span class="shrink-0 rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">Kuota: {{ $position->quota }}</span>
    </header>
    @if($position->description)<p class="whitespace-pre-line break-words text-sm leading-relaxed text-slate-600">{{ $position->description }}</p>@endif
    <div class="border-t border-slate-100 pt-5">
        <h4 class="text-sm font-semibold text-slate-900">Skill yang dibutuhkan</h4>
        <ul class="mt-3 flex flex-wrap gap-2">
            @forelse($position->positionSkills as $s)
                <li class="rounded-lg border border-blue-100 bg-blue-50 px-3 py-2 text-xs leading-relaxed text-blue-700">{{ $s->skill->name }} · {{ ucfirst($s->minimum_level) }} ({{ $s->is_required ? 'wajib' : 'preferensi' }})</li>
            @empty<li class="text-sm text-slate-500">Tidak ada skill khusus yang dicantumkan.</li>@endforelse
        </ul>
    </div>
    <div class="rounded-xl bg-slate-50 p-4">
        <h4 class="text-sm font-semibold text-slate-900">Jadwal tugas (WIB)</h4>
        <ul class="mt-3 space-y-2 text-sm leading-relaxed text-slate-600">
            @forelse($position->schedules as $slot)
                <li>{{ $slot->starts_at->copy()->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }} &ndash; {{ $slot->ends_at->copy()->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }}</li>
            @empty<li>Jadwal belum tersedia.</li>@endforelse
        </ul>
    </div>
    <div>
        <h4 class="text-sm font-semibold text-slate-900">Persyaratan dan dokumen</h4>
        <ul class="mt-3 space-y-3 text-sm text-slate-600">
            @if($position->required_full_availability)<li>Wajib tersedia sepanjang jadwal.</li>@endif
            @if($position->required_same_city)<li>Wajib berdomisili di kota event.</li>@endif
            @forelse($position->requirements as $r)
                <li class="rounded-xl border border-slate-200 p-3">
                    <p class="font-semibold text-slate-900">{{ $r->name }} <span class="ml-1 text-xs font-medium {{ $r->is_required ? 'text-blue-600' : 'text-slate-500' }}">{{ $r->is_required ? 'Wajib' : 'Opsional' }}</span></p>
                    @if($r->description)<p class="mt-1 whitespace-pre-line break-words">{{ $r->description }}</p>@endif
                    @if($r->kind === 'document')<p class="mt-2 text-xs text-slate-500">Dokumen {{ strtoupper($r->document_type) }}</p>@endif
                </li>
            @empty<li>Tidak ada persyaratan tambahan yang dicantumkan.</li>@endforelse
        </ul>
    </div>
    <div class="ve-apply border-t border-slate-100 pt-5">
        @include('volunteer.events.partials.apply')
    </div>
</x-section-card>
