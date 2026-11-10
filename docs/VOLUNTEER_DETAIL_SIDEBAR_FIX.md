# Koreksi sidebar detail lamaran

9 November 2026 WIB. Branch feature/a3-applications-attendance.
Commit dasar a86f3543503434f2f74870606d4967762c5693e1.
Lokal, tanpa commit/push. Seluruh perubahan lokal/staged sebelumnya dipertahankan.

Penyebab: page-class di show.blade.php hanya memberi kelas pada draft, submitted,
screening_failed, rejected. Assessment_pending dan status lain memakai default
kosong, sehingga selector navy tidak cocok dan sidebar fallback oranye.

Perbaikan terarah: tambah volunteer-application-detail sebagai kelas dasar semua
status di show.blade.php. CSS shell volunteer-activity.css memakai kelas dasar
tersebut, menggantikan daftar kelas status. Desain konten per status, route,
controller, service, schema, Policy dan transisi tidak diubah. Tidak ada migration
atau setup baru. PRD/kontrak/README tetap berlaku; design/UAT diperbarui.

Validasi: npm.cmd run build dan artisan view:cache lulus. PHP 8.4 Herd PHPUnit
VolunteerAssessmentInfoTest + FoundationAccessTest: 29 tes/264 assertion lulus,
MySQL skillmatch_testing dengan guard pemisahan existing, tanpa reset/fresh.
Chrome CDP memeriksa warna computed sidebar navy, menu aktif dan avatar biru pada
9 status x 2 viewport (1440/390): 18/18 lulus. Fixture tidak disimpan; screenshot
assessment_pending diperiksa visual. Artefak hanya storage/app yang diabaikan Git.
git diff --check lulus. Belum login browser akun kerja; bukan seluruh UAT/review tim.

Demo: login Volunteer, Lamaran Saya, Lihat Detail pada status apa pun. Refresh
halaman untuk memuat CSS/Blade terbaru. Tidak ada blocker/dependensi integrasi
baru; PIC A3/A4 dapat melanjutkan desain konten status lain sesuai permintaan.
