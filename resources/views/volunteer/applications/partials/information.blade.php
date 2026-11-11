@if ($status === 'accepted' && !$eventCancelled)
    @include('volunteer.applications.partials.attendance')
@elseif ($terminal)
    <x-section-card class="order-1 {{ $status === 'rejected' ? '!border-rose-200 !bg-rose-50/70' : '!bg-slate-50' }}">
        <h2 class="text-lg font-bold text-slate-900">{{ $status === 'rejected' ? 'Hasil seleksi' : 'Status lamaran' }}</h2>
        <div class="mt-3"><x-application-status :application="$application" /></div>
        @if ($application->decision_at)
            <p class="mt-4 text-sm text-slate-500">{{ $status === 'rejected' ? 'Diputuskan pada' : 'Diperbarui pada' }}</p>
            <p class="mt-1 text-sm font-semibold text-slate-900">{{ $application->decision_at->copy()->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</p>
        @endif
        @if ($application->decision_reason)
            <div class="mt-4 border-t border-slate-100 pt-4">
                <h3 class="text-sm font-medium text-slate-500">{{ $status === 'rejected' ? 'Catatan dari penyelenggara' : 'Alasan' }}</h3>
                <p class="mt-2 break-words whitespace-pre-line text-sm leading-relaxed text-slate-700">{{ $application->decision_reason }}</p>
            </div>
        @elseif ($status === 'screening_failed')
            <p class="mt-3 text-sm text-slate-600">Alasan tersedia pada rincian hasil screening.</p>
        @endif
    </x-section-card>
@else
    <x-section-card class="order-1 !border-indigo-200 !bg-indigo-50/70">
        <div class="flex items-center gap-3">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white text-indigo-700" aria-hidden="true">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8 3v4m8-4v4M4 10h16M5 5h14a1 1 0 0 1 1 1v14H4V6a1 1 0 0 1 1-1Z" /></svg>
            </span>
            <h2 class="text-lg font-bold text-slate-900">Pengumuman hasil</h2>
        </div>
        @if ($recruitmentDates['decision_deadline'])
            <p class="mt-4 text-sm text-slate-500">Hasil seleksi paling lambat:</p>
            <p class="mt-1 text-lg font-bold text-indigo-800">{{ $recruitmentDates['decision_deadline']->format('d/m/Y H:i') }} WIB</p>
            <p class="mt-2 text-sm text-slate-600">Hasil dapat diumumkan lebih awal.</p>
        @else
            <p class="mt-4 text-sm text-slate-600">Jadwal hasil seleksi belum tersedia.</p>
        @endif
    </x-section-card>
@endif

<x-section-card class="order-3">
    @php
        $positionSchedules = $application->position->schedules;
        $showEventTime = $positionSchedules->count() !== 1
            || !$positionSchedules->first()->starts_at->equalTo($application->event->starts_at)
            || !$positionSchedules->first()->ends_at->equalTo($application->event->ends_at);
    @endphp
    <h2 class="text-lg font-bold text-slate-900">{{ in_array($status, ['rejected', 'screening_failed', 'withdrawn', 'cancelled'], true) || $eventCancelled ? 'Jadwal posisi' : 'Jadwal tugas' }}</h2>
    <div class="mt-4 space-y-3 text-sm">
        @forelse ($positionSchedules as $slot)
            @php
                $taskStartsAtWib = $slot->starts_at->copy()->setTimezone('Asia/Jakarta');
                $taskEndsAtWib = $slot->ends_at->copy()->setTimezone('Asia/Jakarta');
            @endphp
            <div class="flex items-start gap-3">
                <svg aria-hidden="true" class="mt-0.5 h-9 w-9 shrink-0 rounded-lg bg-indigo-50 p-2 text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9" /><path stroke-linecap="round" d="M12 7v5l3 2" /></svg>
                <div class="min-w-0">
                    <p class="font-semibold text-slate-700">{{ $showEventTime ? 'Waktu tugas' : 'Waktu event' }}</p>
                    <p class="mt-1 break-words text-slate-900">{{ $taskStartsAtWib->format('d/m/Y H:i') }} &ndash; {{ $taskEndsAtWib->format($taskStartsAtWib->isSameDay($taskEndsAtWib) ? 'H:i' : 'd/m/Y H:i') }} WIB</p>
                </div>
            </div>
        @empty
            <p class="text-slate-500">Jadwal posisi belum tersedia.</p>
        @endforelse
    </div>
    <div class="mt-4 border-t border-slate-100 pt-4">
        <div class="flex items-start gap-3">
            <svg aria-hidden="true" class="mt-0.5 h-9 w-9 shrink-0 rounded-lg bg-indigo-50 p-2 text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z" /><circle cx="12" cy="10" r="2.5" /></svg>
            <div class="min-w-0">
                <h3 class="text-sm font-semibold text-slate-700">Lokasi event</h3>
                <p class="mt-1 break-words text-sm text-slate-900">{{ $application->event->location }} &middot; {{ $application->event->cityRecord?->name }}</p>
            </div>
        </div>
        @if ($showEventTime)
            @php
                $eventStartsAtWib = $application->event->starts_at->copy()->setTimezone('Asia/Jakarta');
                $eventEndsAtWib = $application->event->ends_at->copy()->setTimezone('Asia/Jakarta');
            @endphp
            <details class="vd-event-time mt-3 text-sm" @if(in_array($status, ['draft', 'submitted', 'under_review', 'screening_failed', 'rejected', 'accepted'], true)) open @endif>
                <summary class="w-fit cursor-pointer rounded font-medium text-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">Waktu event</summary>
                <p class="mt-2 text-slate-600">{{ $eventStartsAtWib->format('d/m/Y H:i') }} &ndash; {{ $eventEndsAtWib->format($eventStartsAtWib->isSameDay($eventEndsAtWib) ? 'H:i' : 'd/m/Y H:i') }} WIB</p>
            </details>
        @endif
    </div>
</x-section-card>

<x-section-card class="order-4">
    <h2 class="text-lg font-bold text-slate-900">Informasi posisi</h2>
    <dl class="mt-4 space-y-4 text-sm">
        <div>
            <dt class="font-semibold text-slate-700">Penyelenggara</dt>
            <dd class="mt-1 break-words text-slate-900">{{ $application->event->organizer->organizerProfile?->organization_name ?? $application->event->organizer->name }}</dd>
        </div>
        <div>
            <dt class="font-semibold text-slate-700">Deskripsi posisi</dt>
            <dd class="mt-1 break-words whitespace-pre-line leading-relaxed text-slate-900">{{ $application->position->description ?: 'Deskripsi posisi belum tersedia.' }}</dd>
        </div>
    </dl>
    @if ($application->position->positionSkills->isNotEmpty())
        <div class="mt-4 border-t border-slate-100 pt-4">
            <h3 class="text-sm font-semibold text-slate-700">Skill yang dibutuhkan</h3>
            <ul class="mt-3 flex flex-wrap gap-2">
                @foreach ($application->position->positionSkills as $skill)
                    <li class="max-w-full break-words rounded-lg border border-indigo-100 bg-indigo-50 px-3 py-1.5 text-xs text-indigo-800">{{ $skill->skill->name }} &middot; {{ ucfirst($skill->minimum_level) }} ({{ $skill->is_required ? 'Wajib' : 'Preferensi' }})</li>
                @endforeach
            </ul>
        </div>
    @endif
</x-section-card>
