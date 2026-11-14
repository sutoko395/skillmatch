# Perapian panel penarikan

14 November 2026. feature/volunteer-design, dasar 8c37f57. Pengguna menyetujui
perapian panel saja dengan opsi pemulihan. Salinan withdrawal.blade.php dan CSS
compact sebelum perubahan di storage/app/withdrawal-before-refine-20261114.
Jika diminta, pulihkan dua file tersebut saja dan selaraskan dokumentasi; jangan
reset perubahan detail/dashboard/sidebar lain. Lokal tanpa commit/push.

Partial withdrawal kini merender form Volunteer langsung tanpa section-card
berlapis. Judul Tarik lamaran, peringatan final rose, alasan opsional dua baris,
tombol merah. CSS scoped mengatur summary/content/textarea. Komponen bersama
application-selection untuk Organizer tidak diubah. POST route, CSRF, reason
max1000 dan aturan server final tetap. Old input/error ditampilkan. Kondisi
visibilitas/open existing tetap. Tidak ada route/controller/migration baru.
PRD/kontrak/README setup tetap berlaku.

Build Vite, view:cache dan git diff --check lulus. A3ExecutionTest lulus
17 tes/112 assertion pada MySQL khusus tes, guard existing. Fixture under_review
tanpa simpan, Chrome CDP 1440/768/390 tanpa overflow; desktop diperiksa visual.
Browser akun nyata/pengiriman penarikan belum diuji. Artefak QA/backup di
storage/app diabaikan Git. PIC A3/SF memakai endpoint lama.
