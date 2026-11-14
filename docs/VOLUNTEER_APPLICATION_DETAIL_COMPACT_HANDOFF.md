# Detail lamaran lebih ringkas

14 November 2026, feature/volunteer-design, dasar 8c37f57. Pengguna menyetujui
perapian detail setelah screenshot accepted: ringkas tanpa mengganti informasi.
Lokal tanpa commit/push; semua perubahan sebelumnya dipertahankan.

CSS baru volunteer-application-compact.css diimpor setelah gaya status existing.
Scoped .volunteer-application-detail .vd-detail: heading 28 px, kartu 20 px,
banner 16/20 px, gap 20 px, timeline 24 px antar tahap, summary rincian 44 px,
panel rincian 16 px. Dokumen desktop menjadi baris flex dengan aksi di kanan,
mobile tetap bertumpuk. Gaya/warna status tetap. Tidak menutup rincian, mengubah
label/data atau memodifikasi Blade, route, controller, layanan, schema/Policy.
Target tombol 44 px dipertahankan. Sidebar/list/dashboard tidak terkena selector.
PRD/kontrak/README setup tetap berlaku; design/UAT diperbarui.

Build Vite lulus. VolunteerAssessmentInfoTest + A3ApplicationDocumentsTest +
EventCoordinationTest lulus 20 tes/250 assertion pada MySQL khusus tes dengan
guard existing, tanpa reset database kerja. Preview accepted fixture tidak
disimpan memeriksa readonly, kosong, pembatalan dan koordinasi. Chrome CDP
1440/768/390 tidak overflow; desktop diperiksa visual. Fixture utama dokumen
kosong; layout dokumen terisi dan seluruh status belum diperiksa visual browser.
Login/klik/unduh akun kerja belum diuji. Artefak QA di storage/app diabaikan Git.
git diff --check lulus. Demo: Lamaran Saya → Lihat Detail, periksa timeline,
banner, kartu kanan serta dokumen sesuai akses. PIC A3/A4 memakai komponen lama.
Tidak ada dependensi atau migration baru.

## Penyesuaian jarak tahap kedua — 14 November 2026

Pengguna menyetujui uji perapian jarak dengan opsi pemulihan. Salinan CSS sebelum
tahap ini: storage/app/application-detail-before-spacing-20261114/volunteer-application-compact.css.
Pemulihan hanya menyalin CSS itu kembali; tidak membatalkan panel penarikan atau
perapian tahap sebelumnya. Jarak bagian 20 ke 16 px, judul/header lebih rapat,
antartahap timeline 24 ke 16 px, koordinasi/PIC dipadatkan. Teks utama dan tombol
44 px tetap. Build, fixture accepted/readonly/cancelled dan diff --check lulus.
CDP 1440/768/390 tidak overflow; desktop diperiksa visual. Tanpa PHPUnit ulang
karena hanya CSS jarak. Klik browser akun nyata belum diuji. Tanpa commit/push.
