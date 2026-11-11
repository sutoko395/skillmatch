# Desain Lamaran Ditarik

11 November 2026. Branch feature/volunteer-design, dasar 63d74d8.
Pengguna meminta desain Lamaran ditarik.svg (1440 × 1568).
Perubahan lokal tanpa staging, commit/push; sembilan perubahan lain dipertahankan.

Sebelumnya withdrawn memakai shell navy tetapi isi kartu memakai gaya bawaan dan
rincian tertutup. Kini kelas volunteer-application-withdrawn memakai ulang layout
detail putih/dua kolom: banner abu-abu, timeline, dokumen hanya-baca, kartu status
kanan dengan waktu/alasan nyata, jadwal posisi dan informasi posisi. Rincian
screening, assessment dan Waktu event terbuka default, tetap dapat dilipat.
Mobile mengikuti susunan responsif existing. Alasan tetap di-escape oleh Blade;
teks contoh SVG tidak dipasang ke aplikasi.

File: applications/show, partial progress/information; selector CSS dasar detail
draft/submitted diperluas ke withdrawn. CSS volunteer-application-withdrawn.css
diimpor app.css untuk banner netral. Komponen existing tetap dipakai, tanpa route,
migration, controller, layanan, Policy, schema atau transisi baru. PRD/kontrak dan
README setup tetap berlaku. Tidak ada aksi upload/hapus/submit/assessment ulang/
tarik ulang. Riwayat assessment asli tetap dapat dibaca sesuai data.

Verifikasi render fixture tanpa penyimpanan: alasan markup di-escape; alasan/
tanggal/screening kosong, assessment cancelled/submitted, dokumen ready dan event
cancelled; tidak ada aksi edit/assessment. Browser menggunakan fixture sintetis,
bukan login atau penarikan akun nyata. Artefak QA di storage/app diabaikan Git.

Validasi lulus: A3ExecutionTest + VolunteerAssessmentInfoTest +
A3ApplicationDocumentsTest, 32 tes/284 assertion pada MySQL skillmatch_testing
dengan guard existing, tanpa reset database kerja. Build Vite 8.3.1,
artisan view:cache dan git diff --check lulus. Chrome CDP 1440/768/390 tidak
overflow; screenshot desktop/mobile dibandingkan dengan SVG. Konten/tanggal
fixture berbeda dari SVG karena bersifat dinamis.

Demo: login Volunteer, Lamaran Saya, buka detail lamaran withdrawn milik akun.
Periksa banner, waktu/alasan (jika ada), rincian dan dokumen sesuai hak akses.
Tidak perlu menarik lamaran nyata untuk meninjau preview. Login/navigasi end-to-end
browser akun nyata belum diuji. Tidak ada dependensi baru; PIC A3/A4 dapat meninjau
varian data aktual. Bukan klaim seluruh UAT/review tim lulus.
