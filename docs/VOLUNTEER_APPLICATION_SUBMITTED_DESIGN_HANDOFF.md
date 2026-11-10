# Handoff desain Detail Lamaran Submitted

Tanggal: 9 November 2026 (WIB).
Branch: feature/a3-applications-attendance.
Commit dasar: a86f3543503434f2f74870606d4967762c5693e1.
Lokal tanpa commit/push/PR/merge/deployment sesuai instruksi pengguna. Perubahan
lokal sebelumnya dipertahankan, termasuk seluruh desain Volunteer sebelumnya.

## Hasil

Sumber: C:/Users/waely/Downloads/Lamaran · Submitted.svg (1440 x 1946).
Sebelumnya detail submitted memakai shell oranye dan rincian/panel penarikan
tertutup. Kini shell navy/biru, banner status nyata, layout dua kolom, rincian
screening/assessment serta Waktu event terbuka default, dokumen hanya-baca,
dan panel penarikan terbuka dengan tombol merah sesuai SVG. Native details tetap
dapat dilipat. Mobile menyusun satu kolom tanpa menyembunyikan informasi.

File: show.blade.php memilih page-class menurut status. Partial progress dan
information memberi open hanya untuk status yang disetujui. Panel withdrawal
menjadi partial terpisah. CSS dasar volunteer-application-draft.css dibagikan
ke Draft/Submitted; volunteer-application-submitted.css mengatur rincian/panel
khusus submitted, diimpor app.css. Shell scoped diperluas ke submitted.

Tidak ada route, migration, schema, model, controller, service, Policy atau
transisi baru. Komponen hasil screening, assessment, application-status dan
application-selection dipakai ulang. Profil/snapshot, tenggat, durasi, jumlah
soal, dokumen privat, pemeriksaan server dan sifat final penarikan tidak berubah.
Label lolos hanya dari hasil passed; tidak menahan proses matching pada status
submitted untuk menyerupai screenshot. Dokumen submitted tetap tanpa upload/hapus.
Design/UAT diperbarui; PRD/kontrak dan README setup masih berlaku.

## Validasi

- npm.cmd run build lulus (Vite 8.3.1), build final app-DVJNaYdY.css.
- PHP 8.4 Herd artisan view:cache lulus.
- ScreeningTest + VolunteerAssessmentInfoTest + A3ExecutionTest:
  36 tes, 266 assertion lulus; MySQL skillmatch_testing dengan guard pemisahan
  database existing, tanpa reset/migrate:fresh/RefreshDatabase.
- Render fixture tanpa menyimpan data: submitted dengan screening passed,
  screening belum tersedia, metadata assessment kosong, dokumen kosong/terisi,
  tanpa aksi draft/upload/hapus lulus. Bukan bukti integrasi matching baru.
- Chrome headless CDP viewport 1440/768/390: tidak overflow. Desktop dibandingkan
  SVG dan mobile diperiksa visual. Screenshot submitted-*-final.png/preview QA
  berada di storage/app yang diabaikan Git.
- git diff --check lulus.

## Demonstrasi dan batas

Login Volunteer, Lamaran Saya, Lihat Detail pada lamaran submitted milik akun.
Rincian langsung terbuka dan dapat dilipat. Isi menyesuaikan data sebenarnya;
status dapat segera berubah setelah screening/matching sukses, sesuai alur lama.
Penarikan mengubah status secara final melalui endpoint existing.

Belum mengirim penarikan atau login/navigasi end-to-end melalui browser pada
akun kerja. Feature tests memverifikasi perilaku backend terkait; preview memakai
fixture yang tidak disimpan. Bukan klaim seluruh UAT/review tim lulus. Tidak ada
dependensi integrasi baru. PIC A3/A4 meninjau varian data nyata; status lain tetap
menunggu desain/permintaan pengguna masing-masing. Tidak ada commit baru.
