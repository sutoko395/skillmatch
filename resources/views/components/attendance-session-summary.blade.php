@props(['attendance'])
@if($attendance?->uses_sessions)
    <details class="v-session-summary mt-4 rounded-lg border border-slate-200">
        <summary class="cursor-pointer rounded-lg px-3 py-2 text-sm text-slate-600"><span>{{ $attendance->sessions->where('status', 'present')->count() }}/{{ $attendance->sessions->count() }} sesi hadir; {{ $attendance->sessions->where('status', 'absent')->count() }} tidak hadir; {{ $attendance->sessions->where('status', 'unrecorded')->count() }} belum dicatat.</span><span class="v-session-toggle ml-2 text-xs font-medium text-blue-600">Rincian sesi</span></summary>
        <div class="space-y-3 border-t border-slate-100 p-3">
        @if($attendance->legacy_record)
            <details class="rounded-lg bg-amber-50 p-3 text-sm text-amber-900"><summary>Arsip catatan lama per kegiatan</summary>
                <p class="mt-2">Status lama: {{ ['present' => 'Hadir', 'absent' => 'Tidak hadir', 'unrecorded' => 'Belum dicatat'][$attendance->legacy_record['status']] }}. Catatan ini tidak digunakan sebagai bukti hadir setiap sesi.</p>
                @if($attendance->legacy_record['note'] ?? null)<p>{{ $attendance->legacy_record['note'] }}</p>@endif
            </details>
        @endif
        @foreach($attendance->sessions as $session)
            <div class="rounded-lg border border-slate-200 p-3 text-sm">
                <p class="font-semibold">{{ $session->starts_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }} - {{ $session->ends_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</p>
                <p class="mt-1">{{ ['unrecorded' => 'Belum dicatat', 'present' => 'Hadir', 'absent' => 'Tidak hadir'][$session->status] }}</p>
                @if($session->note)<p class="mt-2 text-xs font-medium text-slate-500">Catatan panitia</p><p class="mt-1 break-words whitespace-pre-line text-slate-600">{{ $session->note }}</p>@endif
                @if($session->recorded_at)<p class="mt-1 text-xs text-slate-500">Dicatat {{ $session->recorded_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</p>@endif
            </div>
        @endforeach
        </div>
    </details>
@elseif($attendance)
    <p class="mt-3 text-sm text-amber-800">Catatan lama per kegiatan. Rincian kehadiran per sesi/hari belum tersedia.</p>
@endif
