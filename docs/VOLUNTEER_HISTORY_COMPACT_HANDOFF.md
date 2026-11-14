# Riwayat Partisipasi lebih ringkas

Koreksi pengguna: tautan Lihat Detail dan garis footer dihapus karena judul biru
sudah membuka tujuan yang sama. Judul tetap link dengan underline saat hover.
Catatan penambahan tautan di bawah merupakan tahap sebelumnya yang dibatalkan.

14 November 2026. Branch feature/volunteer-design, dasar 8c37f57.
Pengguna menyetujui perapian kartu riwayat sesuai penilaian screenshot.
Lokal tanpa commit/push. Perubahan sidebar, dashboard, Lamaran Saya dan pekerjaan
lokal lain dipertahankan.

CSS volunteer-history mengurangi heading, padding kartu/kotak, jarak dan badge.
Index menyederhanakan deskripsi. Partial participation-card menambah Lihat Detail
ke endpoint detail pemilik existing dan penjelasan khusus ketika Belum dicatat.
Status cancelled tetap netral; hadir/tidak hadir/tidak lengkap, catatan lama,
rincian sesi, query, pagination, Policy dan data tidak diubah. Tanpa route,
layanan, schema atau migration baru. PRD/kontrak/README setup tetap berlaku.

Build Vite, view:cache dan git diff --check lulus. SessionAttendanceTest:
9 tes/77 assertion lulus pada MySQL khusus tes dengan guard existing.
Fixture tanpa simpan: catatan lama/cancelled/absent/unrecorded/rejected/sesi
campuran/penuh/kosong lulus. Chrome CDP 1440/768/390 tidak overflow; screenshot
desktop diperiksa. Artefak QA di storage/app diabaikan Git. Klik/detail browser
akun nyata belum diuji. Demo: Riwayat Partisipasi, periksa kartu ringkas dan
tautan detail; Belum dicatat tetap berbeda dengan Tidak hadir. PIC A3/SF tetap
memakai komponen attendance existing. Tidak ada dependensi baru.
