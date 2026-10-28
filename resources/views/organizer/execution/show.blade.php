@extends('organizer.layouts.sidebar')
@section('title', 'Pelaksanaan - '.$event->title)
@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    <x-page-header :title="'Pelaksanaan: '.$event->title" description="Kehadiran dicatat Organizer. Waktu pencatatan bukan jam kedatangan." />
    <a class="font-semibold text-indigo-600" href="{{ route('organizer.applications.index', $event) }}">Kembali ke pelamar</a>
    <x-section-card>
        <dl class="mb-5 grid grid-cols-2 gap-4 md:grid-cols-4">@foreach(['accepted' => 'Peserta diterima', 'present' => 'Hadir', 'absent' => 'Tidak hadir', 'unrecorded' => 'Belum dicatat'] as $key => $label)<div class="rounded-xl bg-slate-50 p-4"><dt class="text-sm text-slate-600">{{ $label }}</dt><dd class="text-2xl font-semibold">{{ $summary->$key }}</dd></div>@endforeach</dl>
        <p class="mb-4">Status kegiatan: <strong>{{ $event->lifecycle_status }}</strong>. Jadwal: {{ $event->starts_at?->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }}–{{ $event->ends_at?->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB.</p>
        <form method="GET" class="mb-5 flex flex-wrap gap-4">
            <label>Cari nama<input class="block rounded-lg border-slate-300" name="search" value="{{ request('search') }}" maxlength="100"></label>
            <label>Posisi<select class="block rounded-lg border-slate-300" name="position_id"><option value="">Semua posisi</option>@foreach($event->positions as $position)<option value="{{ $position->id }}" @selected(request('position_id') == $position->id)>{{ $position->name }}</option>@endforeach</select></label>
            <label>Kehadiran<select class="block rounded-lg border-slate-300" name="status"><option value="">Semua status</option>@foreach(['unrecorded' => 'Belum dicatat', 'present' => 'Hadir', 'absent' => 'Tidak hadir'] as $key => $label)<option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>@endforeach</select></label>
            <x-primary-button>Terapkan</x-primary-button>
        </form>
        <p class="mb-2 text-sm text-slate-600">Pada layar kecil, geser tabel untuk melihat catatan dan aksi.</p>
        <div class="overflow-x-auto focus:outline-indigo-500" tabindex="0" role="region" aria-label="Tabel kehadiran peserta">
            <table class="w-full text-left text-sm"><thead><tr><th class="p-3">Nama / Posisi</th><th class="p-3">Status / Waktu pencatatan</th><th class="p-3">Catatan dan aksi</th></tr></thead><tbody>
            @forelse($applications as $application)
                @php($attendance = $application->attendance)
                <tr class="border-t border-slate-200">
                    <td class="p-3">{{ $application->volunteer->name }}<br>{{ $application->position->name }}</td>
                    <td class="p-3">{{ ['unrecorded' => 'Belum dicatat', 'present' => 'Hadir', 'absent' => 'Tidak hadir'][$attendance?->status ?? 'unrecorded'] }}<br>{{ $attendance?->recorded_at ? $attendance->recorded_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i').' WIB' : 'Belum ada waktu pencatatan' }}</td>
                    <td class="min-w-64 p-3">
                        @if($attendance && $event->lifecycle_status !== 'cancelled')
                        <form method="POST" action="{{ route('organizer.attendances.update', $attendance) }}" class="space-y-3" x-data="{busy:false}" @submit="busy=true">
                            @csrf @method('PATCH')
                            <input type="hidden" name="revision" value="{{ $attendance->revision }}">
                            <label class="block">Kehadiran<select name="status" class="ml-2 rounded-lg border-slate-300" required><option value="">Pilih</option><option value="present" @selected($attendance->status === 'present')>Hadir</option><option value="absent" @selected($attendance->status === 'absent')>Tidak hadir</option></select></label>
                            <label class="block">Catatan<textarea name="note" maxlength="1000" class="block w-full rounded-lg border-slate-300">{{ $attendance->note }}</textarea></label>
                            @if($event->lifecycle_status === 'completed')<label class="block">Alasan koreksi<textarea name="reason" required minlength="5" maxlength="1000" class="block w-full rounded-lg border-slate-300"></textarea></label>@endif
                            <x-primary-button x-bind:disabled="busy">Simpan kehadiran</x-primary-button>
                        </form>
                        @else <p>Kehadiran tidak dapat diubah.</p> @endif
                    </td>
                </tr>
            @empty <tr><td colspan="3" class="p-6 text-slate-600">Tidak ada peserta diterima yang sesuai filter.</td></tr> @endforelse
            </tbody></table>
        </div>
        <div class="mt-4">{{ $applications->links() }}</div>
    </x-section-card>
    @if(!in_array($event->lifecycle_status, ['cancelled', 'completed']))
    <x-section-card>
        <h2 class="mb-3 text-lg font-semibold">Tutup seleksi tertunda</h2>
        <p class="mb-3 text-sm text-slate-600">Lamaran yang masih menunggu evaluasi, assessment, atau tinjauan akan ditolak dengan alasan ini. Keputusan final tetap disimpan.</p>
        <form method="POST" action="{{ route('organizer.events.close-selection', $event) }}" class="space-y-3">
            @csrf
            <label class="block">Alasan penutupan<textarea name="reason" required minlength="5" maxlength="1000" class="block w-full rounded-lg border-slate-300">{{ old('reason') }}</textarea></label>
            <label class="block"><input type="checkbox" name="confirmed" value="1" required> Saya mengonfirmasi penutupan seluruh lamaran tertunda.</label>
            <x-primary-button>Tutup seleksi tertunda</x-primary-button>
        </form>
    </x-section-card>
    <x-section-card>
        <h2 class="mb-3 text-lg font-semibold">Selesaikan kegiatan</h2>
        <p class="mb-3 text-sm text-slate-600">Hanya setelah akhir jadwal, seluruh seleksi final, dan semua peserta sudah dicatat Hadir/Tidak hadir.</p>
        <form method="POST" action="{{ route('organizer.events.complete', $event) }}" class="space-y-3">
            @csrf
            <label class="block"><input type="checkbox" name="confirmed" value="1" required> Saya mengonfirmasi rekap kegiatan.</label>
            <label class="block"><input type="checkbox" name="confirm_zero" value="1"> Jika tidak ada peserta diterima, saya mengonfirmasi jumlah nol.</label>
            <x-primary-button>Selesaikan kegiatan</x-primary-button>
        </form>
    </x-section-card>
    @endif
</div>
@endsection
