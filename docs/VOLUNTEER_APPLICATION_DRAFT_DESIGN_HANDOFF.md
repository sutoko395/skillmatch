# Handoff desain Lengkapi Lamaran

Tanggal: 9 November 2026 (WIB).
Branch: `feature/a3-applications-attendance`.
Commit dasar: `a86f3543503434f2f74870606d4967762c5693e1`.
Instruksi: lokal, tanpa commit/push/PR/merge/deployment. Perubahan lokal/staged
sebelumnya termasuk desain Aktivitas/Profil/Lamaran Saya dipertahankan.

## Hasil dan lingkup

SVG: `C:/Users/waely/Downloads/Lengkapi lamaran.svg`, FigCraft 1440 x 1808.
Diterapkan khusus halaman detail lamaran status draft. Sebelumnya shell oranye,
header panel berwarna, daftar dokumen horizontal dan form unggah tiga kolom.
Kini shell navy/biru, banner draft dan kartu dua kolom sesuai SVG, header panel
putih berborder, aksi dokumen di bawah identitas, upload satu kolom, ikon jadwal
outline serta Waktu event terbuka saat draft. Mobile memakai satu kolom dan
mempertahankan urutan operasional existing. Native file picker mengikuti browser.

Partial baru detail-header dan status-banner di
resources/views/volunteer/applications/partials/. show.blade.php menyusunnya;
logika status/assessment tetap di view existing. Partial progress/documents
memiliki hook class; information membuka details Waktu event hanya pada draft.
Stylesheet resources/css/volunteer-application-draft.css diimpor app.css.
Selector shell existing ditambah untuk kelas volunteer-application-draft.

Tidak mengubah controller, service, route, schema, migration, Policy, dokumen
privat, batas format/ukuran/jumlah file, gate verifikasi atau transisi. Status
non-draft tidak mendapat stylesheet baru. Flash sukses tetap kondisional, bukan
teks statis. Koordinasi, attendance, assessment, rincian screening, penarikan,
warning profil, error dan empty state tetap tersedia. PRD/kontrak tetap berlaku;
design/UAT diperbarui. Setup/dependensi tidak berubah; README tetap berlaku.

## Bukti validasi

- npm.cmd run build lulus, Vite 8.3.1, build final app-DDFuaC-a.css.
- PHP 8.4 Herd artisan view:cache lulus.
- PHPUnit A3ApplicationDocumentsTest + VolunteerAssessmentInfoTest +
  A3ExecutionTest: **32 tes, 284 assertion lulus** pada MySQL skillmatch_testing
  dengan guard database existing. Tanpa reset/migrate:fresh/RefreshDatabase.
- Fixture model tidak disimpan: draft dengan dua dokumen, dokumen kosong dan
  warning profil belum lengkap berhasil dirender.
- Chrome headless CDP: viewport aktual 1440/768/390, tidak overflow; desktop
  dibandingkan SVG, mobile diperiksa visual. Screenshot draft-*-final.png dan
  fixture/QA hanya berada di storage/app yang diabaikan Git.
- git diff --check lulus.

## Demonstrasi, batas dan lanjutan

Login Volunteer, Lamaran Saya, Lihat Detail pada draft milik akun sendiri.
Periksa profil/dokumen, unggah CV PDF atau pendukung, unduh/hapus dokumen saat
masih draft, lalu Kirimkan Lamaran. Validasi, batas file dan izin tetap server.
Data contoh/sukses di SVG tidak dipasang sebagai data aplikasi.

Belum melakukan unggah/unduh/hapus/kirim end-to-end lewat browser dengan akun
kerja. Pengujian file/izin/status berasal dari feature tests terpisah dan preview
visual memakai fixture; bukan bukti semua UAT, review tim, atau upload produksi.
Tidak ada dependensi integrasi baru. PIC A3 meninjau varian data nyata dan desain
berikutnya untuk status non-draft bila diminta pengguna. Tidak ada commit baru.
