<section class="mt-6 space-y-4" aria-label="Tindakan Organizer">
    <h2 class="text-lg font-bold text-slate-900">Tindak lanjut kegiatan</h2>
    <p class="text-sm text-slate-600">{{ $profile?->organization_name ?? 'Organisasi belum diisi' }} · {{ $profile?->contact_person ?? 'Kontak belum diisi' }} · {{ $user->organizer_status === 'active' ? 'Terverifikasi' : 'Menunggu verifikasi organisasi' }}</p>
    @forelse($actionEvents as $actionEvent)
        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <a href="{{ route('organizer.events.show', $actionEvent) }}" class="font-semibold text-indigo-600">{{ $actionEvent->title }}</a>
            <p class="mt-1 text-sm text-slate-600">
                @if(!$actionEvent->assessment_deadline && in_array($actionEvent->status, ['pending', 'approved'])) Lengkapi tenggat event lama.
                @elseif($actionEvent->status === 'rejected') Perbaiki event sesuai catatan Admin.
                @elseif($actionEvent->status === 'pending') Menunggu moderasi Admin.
                @elseif($actionEvent->status === 'approved' && $actionEvent->publication_status !== 'published') Periksa pembayaran, hak paket dan persyaratan publikasi.
                @elseif($actionEvent->status === 'draft') Lengkapi konfigurasi dan ajukan event.
                @elseif($actionEvent->decision_deadline && $actionEvent->decision_deadline->isPast()) Batas keputusan telah lewat. Tutup seleksi tertunda dengan alasan melalui halaman pelaksanaan.
                @else Periksa kandidat yang siap ditinjau dan batas keputusan. @endif
            </p>
        </div>
    @empty
        <p class="text-sm text-slate-500">Tidak ada tindak lanjut konfigurasi atau seleksi.</p>
    @endforelse
    @foreach($attendanceEvents as $attendanceEvent)
        <a href="{{ route('organizer.execution.show', $attendanceEvent) }}" class="block rounded-xl border border-indigo-200 bg-white p-4 font-semibold text-indigo-600">Catat kehadiran / selesaikan: {{ $attendanceEvent->title }}</a>
    @endforeach
</section>
