@php($visibleSessions = $attendance->sessions->filter(fn ($session) => (!request('session_date') || $session->session_date->toDateString() === request('session_date')) && (!request('status') || $session->status === request('status'))))
<div class="organizer-attendance-sessions">
    @if($attendance->legacy_record)
        <details class="rounded-lg bg-amber-50 p-3 text-sm text-amber-900"><summary>Arsip absensi lama per kegiatan</summary>
            <p class="mt-2">Status lama: {{ ['present' => 'Hadir', 'absent' => 'Tidak hadir', 'unrecorded' => 'Belum dicatat'][$attendance->legacy_record['status']] }}.</p>
            <p>{{ $attendance->legacy_record['note'] ?? '' }}</p>
            <p>Alasan pengalihan: {{ $attendance->legacy_record['conversion_reason'] ?? '' }}</p>
        </details>
    @endif
    @forelse($visibleSessions as $session)
        @php($canEditSession = !in_array($event->lifecycle_status, ['completed', 'cancelled']) && now()->gte($session->starts_at) && now()->gte($event->starts_at))
        <section class="organizer-attendance-session organizer-checkin-session">
            <h4 class="organizer-attendance-session-date"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M7 3v4m10-4v4M3 11h18"/></svg>{{ $session->session_date->locale('id')->translatedFormat('d M Y') }}</h4>
            <p class="organizer-checkin-time"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg><span>{{ $session->starts_at->setTimezone('Asia/Jakarta')->format('H:i') }} &ndash; {{ $session->ends_at->setTimezone('Asia/Jakarta')->format('H:i') }}@if($session->ends_at->setTimezone('Asia/Jakarta')->toDateString() !== $session->session_date->toDateString()) ({{ $session->ends_at->setTimezone('Asia/Jakarta')->format('d/m/Y') }})@endif WIB</span></p>
            <div class="organizer-checkin-status">
                <span class="organizer-attendance-state organizer-attendance-state--{{ $session->status }}">@if($session->status === 'present')<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6"/></svg>@endif{{ ['unrecorded' => 'Belum dicatat', 'present' => 'Sudah hadir', 'absent' => 'Tidak hadir'][$session->status] }}</span>
            </div>
            <div class="organizer-checkin-actions">
                @if($session->recorded_at)<p class="organizer-checkin-recorded">Dicatat {{ $session->recorded_at->setTimezone('Asia/Jakarta')->format('H:i') }} WIB</p>@endif
                @if($canEditSession && $session->status !== 'present')
                    <form method="POST" action="{{ route('organizer.attendance-sessions.update', $session) }}" x-data="{busy:false}" @submit="if (busy) { $event.preventDefault(); } else { $nextTick(() => busy = true); }" @pageshow.window="busy=false">
                        @csrf @method('PATCH')
                        <input type="hidden" name="revision" value="{{ $session->revision }}">
                        <input type="hidden" name="status" value="present">
                        <input type="hidden" name="note" value="{{ $session->note }}">
                        <button type="submit" class="organizer-action-button" x-bind:disabled="busy"><span x-text="busy ? 'Menyimpan...' : 'Tandai hadir'">Tandai hadir</span></button>
                    </form>
                @elseif(!$canEditSession && !in_array($event->lifecycle_status, ['completed', 'cancelled']))
                    <p class="text-xs text-slate-500">Pencatatan dibuka saat sesi dimulai.</p>
                @elseif($event->lifecycle_status === 'cancelled')
                    <p class="text-xs text-slate-500">Absensi terkunci: kegiatan dibatalkan.</p>
                @endif
            </div>
            <div class="organizer-checkin-tools">
                @if($canEditSession || $session->note)
                    <div class="organizer-checkin-note" x-data="{ open: {{ (int) old('_note_session') === $session->id ? 'true' : 'false' }} }">
                        <button type="button" class="organizer-checkin-note-toggle" @click="open = !open" :aria-expanded="open.toString()" aria-controls="checkin-note-editor-{{ $session->id }}" aria-label="{{ $canEditSession ? ($session->note ? 'Lihat/Edit catatan' : 'Tambah catatan') : 'Lihat catatan' }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9Zm0 0v6h6M8 13h8M8 17h5"/></svg>Catatan</button>
                        @if($canEditSession)
                            <form method="POST" action="{{ route('organizer.attendance-sessions.note', $session) }}" id="checkin-note-editor-{{ $session->id }}" x-show="open" x-cloak class="organizer-checkin-editor space-y-2" x-data="{busy:false}" @submit="if (busy) { $event.preventDefault(); } else { $nextTick(() => busy = true); }" @pageshow.window="busy=false">
                                @csrf @method('PATCH')
                                <input type="hidden" name="revision" value="{{ $session->revision }}">
                                <input type="hidden" name="_note_session" value="{{ $session->id }}">
                                <label class="block" for="session-note-{{ $session->id }}">Catatan (opsional)</label>
                                <textarea x-ref="noteInput" id="session-note-{{ $session->id }}" name="note" maxlength="1000" rows="2" class="block w-full rounded-lg border-slate-300">{{ (int) old('_note_session') === $session->id ? old('note', $session->note) : $session->note }}</textarea>
                                <p class="text-xs text-slate-500">Catatan ini dapat dilihat volunteer.</p>
                                <div class="flex flex-wrap items-center gap-3">
                                    <button type="submit" class="organizer-secondary-button organizer-info-edit" x-bind:disabled="busy">Simpan catatan</button>
                                    <button type="button" class="organizer-secondary-button organizer-position-delete" x-bind:disabled="busy" @click="$refs.noteInput.value = @js($session->note ?? ''); open = false">Batal</button>
                                </div>
                            </form>
                        @else
                            <p id="checkin-note-editor-{{ $session->id }}" x-show="open" x-cloak class="organizer-checkin-editor whitespace-pre-line text-sm text-slate-600">Catatan: {{ $session->note }}</p>
                        @endif
                    </div>
                @endif
                @if($canEditSession)
                    <div class="organizer-checkin-correction" x-data="{ open: false }">
                        <button type="button" class="organizer-checkin-edit" @click="open = !open" :aria-expanded="open.toString()" aria-controls="checkin-correction-{{ $session->id }}" aria-label="Edit absensi"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m16 3 5 5M4 20l4-1L21 6a2.8 2.8 0 0 0-4-4L4 15l-1 6Z"/></svg>Edit absensi</button>
                        <form method="POST" action="{{ route('organizer.attendance-sessions.update', $session) }}" id="checkin-correction-{{ $session->id }}" x-show="open" x-cloak class="organizer-checkin-editor organizer-checkin-correction-editor space-y-2" x-data="{busy:false}" @submit="if (busy) { $event.preventDefault(); } else { $nextTick(() => busy = true); }" @pageshow.window="busy=false">
                            @csrf @method('PATCH')
                            <input type="hidden" name="revision" value="{{ $session->revision }}">
                            <input type="hidden" name="note" value="{{ $session->note }}">
                            <label class="block" for="session-status-{{ $session->id }}">Kehadiran sesi</label>
                            <select x-ref="statusInput" id="session-status-{{ $session->id }}" name="status" required class="block w-full rounded-lg border-slate-300"><option value="">Pilih</option><option value="present" @selected($session->status === 'present')>Hadir</option><option value="absent" @selected($session->status === 'absent')>Tidak hadir</option></select>
                            <div class="flex flex-wrap items-center gap-3">
                                <button type="submit" class="organizer-secondary-button organizer-info-edit" x-bind:disabled="busy">Simpan kehadiran sesi</button>
                                <button type="button" class="organizer-secondary-button organizer-position-delete" x-bind:disabled="busy" @click="$refs.statusInput.value = @js($session->status === 'unrecorded' ? '' : $session->status); open = false">Batal</button>
                            </div>
                        </form>
                    </div>
                @endif
            </div>
        </section>
    @empty
        <p class="text-sm text-slate-500">Tidak ada sesi yang sesuai filter.</p>
    @endforelse
</div>
