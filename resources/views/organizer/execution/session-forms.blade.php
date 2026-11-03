@php($visibleSessions = $attendance->sessions->filter(fn ($session) => (!request('session_date') || $session->session_date->toDateString() === request('session_date')) && (!request('status') || $session->status === request('status'))))
<div class="space-y-4">
    @if($attendance->legacy_record)
        <details class="rounded-lg bg-amber-50 p-3 text-sm text-amber-900"><summary>Arsip absensi lama per kegiatan</summary>
            <p class="mt-2">Status lama: {{ ['present' => 'Hadir', 'absent' => 'Tidak hadir', 'unrecorded' => 'Belum dicatat'][$attendance->legacy_record['status']] }}.</p>
            <p>{{ $attendance->legacy_record['note'] ?? '' }}</p>
            <p>Alasan pengalihan: {{ $attendance->legacy_record['conversion_reason'] ?? '' }}</p>
        </details>
    @endif
    @forelse($visibleSessions as $session)
        <section class="rounded-xl border border-slate-200 p-4">
            <h3 class="font-semibold">{{ $session->session_date->format('d/m/Y') }} WIB</h3>
            <p class="mt-1 text-sm">{{ $session->starts_at->setTimezone('Asia/Jakarta')->format('H:i') }} - {{ $session->ends_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</p>
            <p class="mt-2 font-semibold">{{ ['unrecorded' => 'Belum dicatat', 'present' => 'Hadir', 'absent' => 'Tidak hadir'][$session->status] }}</p>
            <p class="mt-1 text-xs text-slate-500">{{ $session->recorded_at ? 'Dicatat '.$session->recorded_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i').' WIB' : 'Belum ada waktu pencatatan' }}</p>
            @if($event->lifecycle_status === 'cancelled')
                <p class="mt-2 text-sm text-slate-500">Kegiatan dibatalkan; kehadiran tidak dapat diubah.</p>
            @elseif(now()->lt($session->starts_at) || now()->lt($event->starts_at))
                <p class="mt-2 text-sm text-slate-500">Pencatatan dibuka saat sesi dimulai.</p>
            @else
                <form method="POST" action="{{ route('organizer.attendance-sessions.update', $session) }}" class="mt-3 space-y-3" x-data="{busy:false}" @submit="if (busy) { $event.preventDefault(); } else { $nextTick(() => busy = true); }" @pageshow.window="busy=false">
                    @csrf @method('PATCH')
                    <input type="hidden" name="revision" value="{{ $session->revision }}">
                    <label class="block">Kehadiran sesi<select name="status" class="ml-2 rounded-lg border-slate-300" required><option value="">Pilih</option><option value="present" @selected($session->status === 'present')>Hadir</option><option value="absent" @selected($session->status === 'absent')>Tidak hadir</option></select></label>
                    <label class="block">Catatan sesi<textarea name="note" maxlength="1000" class="block w-full rounded-lg border-slate-300">{{ $session->note }}</textarea></label>
                    @if($event->lifecycle_status === 'completed')<label class="block">Alasan koreksi<textarea name="reason" required minlength="5" maxlength="1000" class="block w-full rounded-lg border-slate-300"></textarea></label>@endif
                    <x-primary-button x-bind:disabled="busy">Simpan kehadiran sesi</x-primary-button>
                </form>
            @endif
        </section>
    @empty
        <p class="text-sm text-slate-500">Tidak ada sesi yang sesuai filter.</p>
    @endforelse
</div>
