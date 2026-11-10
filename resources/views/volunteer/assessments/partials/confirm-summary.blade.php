<h2 id="before-assessment-title">Sebelum memulai</h2>
<dl class="vac-stats">
    <div><dt>Jumlah soal</dt><dd>{{ $assessment->questions_count }} soal</dd></div>
    <div><dt>Durasi maksimal</dt><dd>{{ $assessment->duration_minutes }} menit</dd></div>
    <div><dt>Kesempatan</dt><dd>1 kali</dd></div>
</dl>
<div class="vac-deadline">
    <p>Jika dimulai sekarang, pengerjaan berakhir paling lambat</p>
    <p><strong>{{ $expires_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</strong></p>
</div>
