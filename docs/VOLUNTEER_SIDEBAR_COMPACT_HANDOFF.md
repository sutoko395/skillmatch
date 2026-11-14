# Sidebar Volunteer lebih ringkas

14 November 2026, feature/volunteer-design, dasar 8c37f57.
Pengguna menyetujui sidebar lebih ramping di seluruh panel Volunteer.
Lokal tanpa commit/push; perubahan kartu lamaran sebelumnya dipertahankan.

volunteer/layouts/sidebar.blade.php: w-72 menjadi w-64 (288→256 px), padding
identitas px-5/py-4, padding navigasi p-3, jarak menu space-y-1, menu px-3/py-2.5.
Offset konten dan navbar desktop sama-sama 256 px. Ukuran teks, ikon, warna,
tautan, aria-current, Alpine drawer dan breakpoint existing dipertahankan.
Hanya layout Volunteer; role lain/publik tidak diubah. Tidak ada route, schema,
migration atau layanan baru. PRD/kontrak/README setup tetap berlaku.

Build Vite dan view:cache lulus. Render fixture lamaran kosong/draft/pagination
lulus tanpa simpan. Chrome CDP 1440/768/390 tidak overflow; desktop diperiksa
visual, konten desktop mendapat tambahan 32 px. Artefak QA storage/app diabaikan
Git. Interaksi buka/tutup drawer mobile dan semua halaman panel belum diuji ulang
langsung di browser. Tidak menjalankan PHPUnit ulang untuk perubahan ukuran.
git diff --check lulus. Demo: refresh panel Volunteer zoom 100%; periksa sidebar,
offset navbar/konten dan menu. PIC SF memakai layout existing.
