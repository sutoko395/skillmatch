# Handoff desain Kerjakan Assessment

9 November 2026 WIB. Branch feature/a3-applications-attendance.
Commit dasar a86f3543503434f2f74870606d4967762c5693e1.
Lokal tanpa commit/push/PR/merge/deployment. Semua perubahan lokal/staged
sebelumnya termasuk desain Volunteer dan perbaikan sidebar dipertahankan.

## Hasil

SVG C:/Users/waely/Downloads/Kerjakan assessment.svg, 1440 x 1000.
Sebelumnya halaman attempt memakai shell oranye/tombol uppercase. Kini shell
navy, aksen biru, header/timer, panel soal/radio, navigasi nomor dan footer sesuai
SVG. Nomor aktif dan terjawab tetap ditandai; tombol disabled/fokus keyboard
terlihat. Mobile memiliki daftar soal toggle dan footer tombol satu kolom.

show.blade.php menyusun partial baru attempt-status, question-navigation,
question-panel dan attempt-actions dalam resources/views/volunteer/assessments/
partials. CSS resources/css/volunteer-assessment-work.css diimpor app.css;
selector shell menambah volunteer-assessment-work. Perbaikan binding toggle
daftar soal diperiksa setelah pemisahan partial. Halaman konfirmasi tetap.

Tidak mengubah assessment-attempt.js, controller/service, route, schema/migration,
Policy, deadline, scoring, status, autosave 15 detik, queue/revision atau API.
x-data/x-ref, handler, radio/nama input, payload, CSRF, fallback noscript, timer,
status gagal, penyimpanan/submit/recovery existing tetap dipakai. Data SVG tidak
menjadi soal/jawaban/status palsu. Kunci/nilai tetap dibatasi oleh backend.
Design/UAT diperbarui; PRD/kontrak/README setup tetap berlaku.

## Bukti

- npm.cmd run build lulus, Vite 8.3.1, final app-BI-LPonM.css.
- PHP 8.4 Herd artisan view:cache lulus.
- ParticipantAssessmentTest + VolunteerAssessmentInfoTest:
  19 tes, 205 assertion lulus pada MySQL skillmatch_testing, guard pemisahan
  existing, tanpa reset/migrate:fresh/RefreshDatabase.
- Cypress assessment-attempt-unit.cy.js: 20 tes lulus. Runner awal exit karena
  ELECTRON_RUN_AS_NODE=1 dari lingkungan; mengosongkannya hanya pada proses tes
  membuat headless Electron berjalan. Tidak mengubah konfigurasi/dependensi.
- Chrome CDP 1440/768/390: tanpa overflow; timer, pilih jawaban/hitungan, next,
  daftar mobile, kembali/pilihan terjaga, simpan dan submit lulus pada DOM Blade.
  Transport hanya mock lokal, tidak mengirim jawaban ke endpoint nyata. Screenshot
  assessment-work-*.png dibandingkan SVG/mobile. Fixture tanpa simpan data dan
  seluruh artefak hanya storage/app yang diabaikan Git.
- git diff --check lulus; JavaScript dan controller assessment tidak berubah.

## Demo dan batas

Login Volunteer dengan assessment_pending yang memenuhi gate dan tenggat.
Buka assessment, konfirmasi Mulai sekarang; halaman attempt menampilkan soal.
Pilih jawaban, pindah soal/nomor, simpan lalu kirim dengan konfirmasi. Server
menentukan expiry/finalisasi; jangan mengubah data kerja untuk melewati syarat.

Belum pengerjaan sampai submit end-to-end di browser akun kerja. Browser QA
menggunakan mock, Cypress unit terisolasi dan backend memakai database tes;
bukan klaim integrasi browser penuh, semua UAT atau review tim. Tidak ada
integrasi baru. PIC A4 meninjau varian soal panjang/banyak dan data nyata.
Tidak ada commit baru.
