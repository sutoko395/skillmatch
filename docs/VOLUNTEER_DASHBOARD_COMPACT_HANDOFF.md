# Dashboard ringkas

14 November 2026, feature/volunteer-design, dasar 8c37f57. Pengguna menyetujui
perapian kepadatan dashboard dengan opsi mengembalikan desain sebelumnya.
Salinan empat file sebelum perubahan di storage/app/dashboard-before-compact-20261114
(files.txt mencatat sumber). Pemulihan hanya empat file ini; jangan reset/stash
perubahan sidebar, kartu Lamaran Saya atau pekerjaan lain. Dokumentasi bagian
dashboard perlu diselaraskan jika pengguna meminta pemulihan. Tanpa commit/push.

Perubahan: CSS scoped .va-dashboard untuk sambutan, statistik, padding kartu,
jarak, judul, daftar lamaran dan tindakan yang lebih padat. Welcome menambah
hook va-welcome. Status kegiatan menggunakan x-application-status existing,
label Indonesia dan warna gagal sesuai halaman lamaran. Statistik Kegiatan
Diikuti menjadi Lamaran Terkirim: ActivityReadService menghitung submitted_at
not null milik pengguna; tidak mengubah query/angka/rumus. Tidak ada route,
schema, migration atau alur baru. Shared shell CSS tidak diubah.
PRD/kontrak/README setup tetap berlaku; design/UAT diperbarui.

Build Vite, view:cache dan git diff --check lulus. IntegratedFlowsTest dan
VolunteerAssessmentInfoTest: 16 tes/183 assertion lulus pada MySQL khusus tes
dengan guard existing. Fixture tanpa simpan dan Chrome CDP 1440/768/390 tidak
overflow; screenshot desktop diperiksa. Preview memakai profil belum lengkap,
skill/ketersediaan kosong dan satu lamaran. Akun kerja lengkap seperti screenshot
pengguna belum diuji lewat browser. Tidak mengirim lamaran/assessment nyata.
Artefak QA/backup di storage/app diabaikan Git. Sidebar dan Lamaran Saya ringkas
yang sudah disetujui tetap tersedia. PIC A4/SF dapat meninjau data aktual.
