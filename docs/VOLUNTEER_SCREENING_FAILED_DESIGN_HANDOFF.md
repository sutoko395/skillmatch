# Handoff desain Screening Gagal

Tanggal: 9 November 2026 WIB.
Branch aktual: feature/a3-applications-attendance.
Commit dasar: a86f3543503434f2f74870606d4967762c5693e1.
Lokal tanpa commit/push/PR/merge/deployment. Perubahan lokal/staged sebelumnya
serta desain Volunteer yang sudah disetujui dipertahankan.

## Hasil dan integrasi

SVG: C:/Users/waely/Downloads/Lamaran · Screening gagal.svg, 1440 x 1759.
Khusus screening_failed: shell navy, banner rose, timeline gagal dan rincian
alasan terbuka, assessment tidak tersedia namun informasinya terbuka, panel
status, jadwal posisi, informasi posisi serta dokumen mengikuti SVG.
Sebelumnya status ini masih memakai shell oranye dan rincian assessment/Waktu
event tertutup. Mobile satu kolom; native details tetap dapat dilipat.

show.blade.php memilih kelas volunteer-application-screening-failed. Partial
progress/information membuka assessment/Waktu event pada status ini. Komponen
status, screening, assessment, dokumen dan informasi existing dipakai ulang.
CSS dasar detail/summary dibagikan ke status yang sudah disetujui; CSS khusus
rose di resources/css/volunteer-application-screening-failed.css, diimpor app.css.
Aturan jarak timeline dibatasi ke anak ol langsung agar beberapa alasan tidak
mendapat jarak antar-tahap. Tidak mendesain ulang status lainnya.

Tidak ada migration, route, schema, controller, model, service, Policy, transisi,
formula atau snapshot baru. Alasan dari hasil screening nyata; metadata yang
belum tersedia tetap diberi label belum tersedia. Status gagal tetap terminal:
tanpa upload/hapus/kirim ulang/penarikan atau akses mulai assessment. Dokumen
privat dan download existing dipertahankan. PRD/kontrak tetap berlaku;
design/UAT diperbarui. Setup/dependensi tidak berubah; README masih berlaku.

## Bukti

- npm.cmd run build lulus, Vite 8.3.1.
- PHP 8.4 Herd artisan view:cache lulus.
- ScreeningTest + VolunteerAssessmentInfoTest + A3ApplicationDocumentsTest:
  27 tes, 221 assertion lulus pada MySQL skillmatch_testing dengan guard database
  existing. Tanpa reset/migrate:fresh/RefreshDatabase.
- Chrome headless CDP viewport aktual 1440/768/390: tidak overflow. Desktop
  dibandingkan SVG, mobile diperiksa visual; screening-failed-*-final.png.
- Fixture model tidak disimpan: alasan gagal, dokumen kosong, hasil screening
  hilang, ketiadaan aksi draft/penarikan/assessment, serta render regresi
  Draft/Submitted lulus. Artefak QA hanya di storage/app yang diabaikan Git.
- git diff --check lulus.

## Demonstrasi dan batas

Login Volunteer, Lamaran Saya, buka detail lamaran screening_failed milik akun.
Periksa alasan dan informasi; tidak membuat lamaran gagal buatan pada data kerja
untuk demo. Belum login/unduh end-to-end melalui browser dengan akun kerja;
feature tests menggunakan database tes, preview visual memakai fixture.
Tidak seluruh kombinasi alasan panjang/banyak/dokumen terisi diperiksa visual;
bukan klaim seluruh UAT atau review tim lulus. Tidak ada dependensi integrasi
baru. PIC A3/A4 melanjutkan review varian data nyata. Tidak ada commit baru.
