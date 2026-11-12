# Tampilan Organizer — FigCraft

12 November 2026 (WIB). Branch `feature/organizer-figcraft-ui`, dasar
`main` / `4730f3fa8592f83e8c2a6affbc4d171c8d312c83`. Working tree awal bersih.
Pengguna menyetujui branch baru dan perubahan tampilan Organizer saja.
Referensi: `skillmatch-electric-blue-system.figcraft`, halaman
`Electric Blue / 02 Organizer` (33 frame O01–O33), dibaca melalui MCP.

## Perubahan dan batas

Sebelumnya sidebar ungu, menu aktif transparan, komponen bersama memakai tombol
uppercase dan aksen indigo. Kini sidebar navy #0F172A, menu aktif/tombol biru
#2563EB, header putih, kartu tanpa bayangan, kontrol membulat dan tombol normal
case. Detail event memiliki kelompok status, lokasi/jadwal, tenggat rekrutmen,
kartu posisi/assessment, manfaat paket dan panel pembatalan. Pembayaran memakai
ringkasan dua kolom (satu pada mobile), tombol sekunder dan judul ringkasan.
Tautan Kelola event/pelamar, Edit Profil dan Kembali memakai tombol sekunder.

CSS baru `resources/css/organizer-panel.css` diimpor dari app.css, dibatasi shell
`organizer-panel`. Shell/layout, view Organizer dan judul khusus Organizer pada
partial orders/detail berubah; markup pembayaran Admin tetap sama. Tidak
mengubah komponen global untuk role lain. Inter/Alpine/Vite existing digunakan.

Semua route/tujuan, method, CSRF, nama field, action dan handler Alpine existing
dipertahankan. Modal buat event/profil, form posisi/assessment, filter/pagination,
otorisasi, query, perhitungan, transisi, layanan, model dan migration tetap.
Contoh nama, angka dan status pada FigCraft tidak menjadi data runtime.
PRD/kontrak/brief bisnis dan README setup tetap berlaku; desain/UAT diperbarui.
Tidak ada migration, dependency, konfigurasi lingkungan atau route baru.

## Verifikasi

Pemeriksaan awal memakai kode/riwayat dan render fixture pada MySQL terpisah
`skillmatch_testing` (MySQL 8.0.42/InnoDB, PHP 8.4 Herd). Guard memeriksa bahwa
database ini berbeda dari .env kerja; tidak menjalankan reset/migrate:fresh.
Screenshot sebelum perubahan mencakup Organizer, profil, detail event, Volunteer
dan paket Admin. Fixture sintetis dibungkus transaksi dan di-rollback.

Hasil verifikasi:

- PHPUnit existing: **111 tes / 898 assertion lulus**. Suite:
  ActivityProfileSummaryTest, FoundationAccessTest, A2EventTest, A2PaymentTest,
  OrganizerAssessmentTest, A3ExecutionTest, SessionAttendanceTest,
  OrganizerKtpUploadTest, EventPackageFirstTest, EventCoverUploadTest,
  EventCoordinationTest. Mencakup render/simpan, role/pemilik, endpoint langsung,
  pembayaran idempoten, assessment terkunci, koordinasi dan kehadiran existing.
- Percobaan awal: 109 lulus, satu failure dan satu error pada unggah sampul akibat
  kolom cover_image_path belum ada di database tes. Migration existing
  `2026_11_05_000001_add_cover_image_path_to_events.php` diverifikasi pending lalu
  diterapkan **hanya pada skillmatch_testing**. Suite unggah ulang 4/39 lulus;
  rangkaian 111/898 di atas dijalankan ulang dan seluruhnya lulus. Tidak membuat
  atau mengubah file migration, tidak menerapkan migration pada database kerja.
- Frontend existing: **15 tes lulus**, melalui
  `node --test tests/Frontend/event-cover-upload.test.mjs tests/Frontend/event-package-form.test.mjs tests/Frontend/payment-status.test.mjs`.
- `npm.cmd run build`, `artisan view:cache` (PHP 8.4 Herd), dan
  `git diff --check` lulus. JavaScript sumber/lockfile tidak berubah.
- Perbandingan statis dengan HEAD memastikan seluruh tujuan route, CSRF,
  method, nama field dan handler Alpine dalam Blade yang berubah tetap sama.
- Render HTTP 200 pada 28 kondisi halaman Organizer. Edge headless memeriksa
  **84 kombinasi halaman/ukuran** (390/768/1440): konten terbaca, tanpa overflow
  halaman, Alpine aktif, warna sidebar sesuai, drawer mobile dapat dibuka/tutup.
- Setelah perapian akhir, delapan halaman dirender ulang dan **24 kombinasi**
  ukuran diperiksa lagi. Membuka modal Buat Event/Edit Profil serta menambah
  skill/soal berhasil pada ketiga ukuran (**12 probe interaksi**, tanpa submit).
  Screenshot desktop/mobile yang mewakili dashboard, form, profil/modal,
  detail event/posisi, dan pembayaran diperiksa langsung.
- Regresi visual Volunteer dan paket Admin: screenshot 1440 px identik byte
  terhadap CSS dari kode HEAD yang dikompilasi terpisah. Aset build lokal awal
  ternyata tertinggal dari sumber Volunteer yang sudah terintegrasi; aset lama
  itu bukan pembanding kode dasar yang tepat. Tidak mengubah sumber Volunteer
  atau Admin untuk mengatasi perbedaan aset tersebut.

Belum diuji: seluruh 33 referensi satu per satu dengan akun nyata, semua aksi
submit melalui browser, browser selain Edge, transaksi Sandbox end-to-end baru,
dan review/UAT tim. Fixture visual merupakan bukti tampilan/handler lokal;
tes otomatis existing memberi bukti backend, bukan klaim seluruh UAT lulus.

Artefak QA privat berada di `storage/app/private/organizer-design-review`, diabaikan
Git. Bukan data pengguna atau bukti pembayaran Sandbox nyata.

## Menjalankan dan meninjau

Gunakan setup README existing, jalankan `npm.cmd run build` atau `npm.cmd run dev`
dan server Laravel dengan PHP kompatibel. Login Organizer, buka
`/organizer/aktivitas`, Event Saya, detail event, posisi, assessment, paket/order,
pelamar, pelaksanaan dan profil. Periksa pula modal Buat Event/Edit Profil,
drawer mobile dan menu akun. Akun pending/revisi tetap menuju profil/verifikasi.

Lokal, belum commit/push/PR/merge. PIC A1–A4 tetap memiliki modul bisnisnya;
review tim, UAT seluruh alur dengan akun nyata serta transaksi Sandbox baru
tidak dilakukan pada perubahan tampilan ini.
