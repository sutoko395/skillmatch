<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Informasi SkillMatch</title></head>
<body style="margin:0;background:#f8fafc;font-family:Arial,sans-serif;color:#0f172a;line-height:1.6">
    <main style="max-width:600px;margin:24px auto;padding:28px;background:#ffffff;border:1px solid #e2e8f0;border-radius:16px">
        <p style="font-weight:bold;color:#4f46e5">SkillMatch</p>
        <p>Halo, {{ $recipientName }}!</p>
        @if ($type === 'screening.failed')
            <p>Lamaranmu untuk posisi <strong>{{ $positionName }}</strong> di <strong>{{ $eventTitle }}</strong> belum bisa dilanjutkan ke tahap selanjutnya karena belum memenuhi syarat wajib.</p>
            <p>Lihat detail lamaran untuk mengetahui alasan hasil screening.</p>
        @elseif ($type === 'assessment.available')
            <p>Lamaranmu untuk posisi <strong>{{ $positionName }}</strong> di <strong>{{ $eventTitle }}</strong> telah lolos screening. Kamu bisa melanjutkan ke tahap assessment.</p>
            <ul>
                <li>Jumlah soal: <strong>{{ $assessmentInfo?->questions_count }} soal</strong></li>
                <li>Durasi maksimal: <strong>{{ $assessmentInfo?->duration_minutes }} menit</strong></li>
                <li>Batas pengerjaan: <strong>{{ $deadline?->format('d/m/Y') }}, pukul {{ $deadline?->format('H:i') }} WIB</strong></li>
            </ul>
            <p>Waktu pengerjaan mengikuti batas yang lebih dulu tercapai: durasi assessment atau batas pengerjaan. Timer baru berjalan setelah kamu menekan “Mulai sekarang” di website.</p>
        @elseif ($type === 'assessment.reminder')
            <p>Kamu belum mulai mengerjakan assessment untuk posisi <strong>{{ $positionName }}</strong> di <strong>{{ $eventTitle }}</strong>.</p>
            <p>Assessment akan ditutup pada <strong>{{ $deadline?->format('d/m/Y') }}, pukul {{ $deadline?->format('H:i') }} WIB</strong>. Selesaikan sebelum tenggat agar lamaranmu dapat dilanjutkan ke tahap peninjauan.</p>
        @elseif ($decision === 'accepted')
            <p>Selamat! Penyelenggara <strong>{{ $eventTitle }}</strong> menerima lamaranmu untuk posisi <strong>{{ $positionName }}</strong>.</p>
            <p>Buka detail lamaran untuk melihat jadwal tugas dan informasi kegiatan.</p>
        @else
            <p>Terima kasih telah mengikuti proses seleksi untuk posisi <strong>{{ $positionName }}</strong> di <strong>{{ $eventTitle }}</strong>.</p>
            <p>Setelah peninjauan oleh penyelenggara, kamu <strong>belum terpilih untuk posisi ini</strong>. Alasan keputusan dapat dilihat pada detail lamaran.</p>
        @endif
        <p style="margin:24px 0"><a href="{{ $targetUrl }}" style="display:inline-block;background:#4f46e5;color:#ffffff;text-decoration:none;padding:12px 20px;border-radius:8px;font-weight:bold">{{ in_array($type, ['assessment.available', 'assessment.reminder']) ? 'Buka Assessment' : 'Lihat Detail Lamaran' }}</a></p>
        @if ($type === 'assessment.available')
            <p>Lolos screening belum berarti diterima. Keputusan seleksi ditentukan oleh penyelenggara.</p>
        @elseif ($type === 'screening.failed')
            <p>Terima kasih atas minatmu untuk berkontribusi.</p>
        @elseif ($type === 'selection.result' && $decision === 'rejected')
            <p>Semoga kita bertemu di kesempatan kegiatan lainnya.</p>
        @endif
        <p>Salam,<br>Tim SkillMatch</p>
    </main>
</body>
</html>
