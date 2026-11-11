# Desain Riwayat Partisipasi Volunteer

11 November 2026. Branch `feature/volunteer-design`, dasar `9197d13`.
Permintaan pengguna: mengikuti `Riwayat Partisipasi.svg` (1440 × 1000).
Perubahan lokal tanpa staging, commit atau push. Sembilan perubahan lokal di luar
desain dipertahankan.

Sebelumnya halaman memakai shell oranye dan isi riwayat berupa baris teks.
Kini sidebar navy, menu aktif biru, heading dan kartu putih sesuai SVG.
Kartu menempatkan tautan judul/posisi di kiri, badge status di kanan, dua kotak
waktu WIB dan hasil partisipasi, serta catatan lama dengan latar amber.
Mobile menumpuk isi tanpa overflow. Judul tetap membuka detail lamaran.

Komponen terpisah: `resources/views/volunteer/history/partials/participation-card.blade.php`.
Halaman index menggunakan partial; CSS `resources/css/volunteer-history.css`
diimpor app.css. Selector shell existing volunteer-activity.css diperluas khusus
kelas volunteer-history; halaman lain tetap mendapat aturan sebelumnya.
Komponen application-status dan attendance-session-summary dipakai ulang.

Tidak ada migration, route, controller, layanan, model, Policy atau rumus baru.
Route tetap GET /volunteer/history, volunteer.history.index. Query tetap hanya
lamaran terkirim pemilik pada event completed/cancelled, pagination 15.
Hasil dari participationLabel existing: absent bukan sukses, unrecorded bukan
absent, cancelled selalu Dibatalkan. Catatan lama dan rincian per sesi/hari tetap
tersedia sesuai data. Nama/status/tanggal contoh SVG tidak dipasang ke aplikasi.
PRD/kontrak dan README setup tetap berlaku; desain/UAT diperbarui.

Validasi:
- A3ExecutionTest, SessionAttendanceTest, FoundationAccessTest: 48 tes/348 assertion
  lulus. MySQL skillmatch_testing dengan guard existing; tanpa reset database kerja.
- npm run build (Vite 8.3.1), artisan view:cache dan git diff --check lulus.
- Render fixture model tanpa penyimpanan: catatan lama hadir, cancelled, absent,
  unrecorded, rejected, sesi campuran/semua hadir dan kosong lulus.
- Chrome CDP 1440/768/390: tidak overflow, screenshot desktop/mobile diperiksa
  terhadap SVG. Preview memakai data sintetis; bukan login end-to-end akun nyata.
  Script/screenshot QA di storage/app yang diabaikan Git.

Demo: login Volunteer, pilih Riwayat Partisipasi dengan data completed/cancelled
milik akun. Periksa hasil, catatan/sesi, klik judul ke detail. Jika belum ada
riwayat, keadaan kosong existing tetap tampil. Tidak ada data demo ditambahkan.
Login/navigasi end-to-end browser akun nyata dan pagination beberapa halaman
belum diuji lewat browser. Tidak ada dependensi baru; PIC A3 dapat meninjau
varian data aktual. Ini bukan klaim seluruh UAT/review tim telah lulus.
