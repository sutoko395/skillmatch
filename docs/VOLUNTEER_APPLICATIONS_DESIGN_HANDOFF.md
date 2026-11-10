# Handoff desain Lamaran Saya

Tanggal: 9 November 2026 (WIB).
Branch aktual: `feature/a3-applications-attendance`.
Commit dasar: `a86f3543503434f2f74870606d4967762c5693e1`.
Lokal tanpa commit/push/PR/merge/deployment sesuai instruksi pengguna. Seluruh
perubahan lokal sebelumnya, termasuk Aktivitas dan Profil, dipertahankan.

## Lingkup dan hasil

Referensi: `C:/Users/waely/Downloads/layout terukur.svg`, FigCraft 1440 x 1000.
Halaman `/volunteer/applications`: sidebar navy, menu aktif biru, header putih,
latar slate dan kartu putih sesuai SVG. Sebelumnya status sejajar judul dan
seluruh metadata menumpuk dalam satu blok; kini status kanan atas, posisi di
bawah judul, lokasi berlabel, dan footer terpisah berisi waktu dibuat/dikirim WIB
serta Lihat Detail. Responsif tanpa membekukan tinggi halaman pada 1000 px.

File utama: resources/views/volunteer/applications/index.blade.php.
Partial kartu: resources/views/volunteer/applications/partials/list-card.blade.php.
Style: resources/css/volunteer-applications.css, diimpor app.css.
Shell pada volunteer-activity.css diperluas secara scoped ke halaman daftar
lamaran; sidebar existing memberi aria-current untuk menu Lamaran Saya.
Komponen application-status existing tetap memetakan status nyata. Judul/nama
akun, lokasi, waktu dan status dari SVG tidak dijadikan data aplikasi.

Tidak ada migration/route/controller/model/service atau aturan bisnis baru.
Nama route index/show, query pemilik, pagination, empty state, akses Policy,
status, detail lamaran dan alur lain tetap. Halaman detail tidak didesain ulang.
PRD/kontrak tetap berlaku; design.md dan UAT.md mencatat keputusan visual baru.
Setup/dependensi tidak berubah sehingga README setup tetap berlaku.

## Validasi aktual

- npm.cmd run build lulus, Vite 8.3.1.
- PHP 8.4 Herd artisan view:cache lulus.
- PHPUnit A3ApplicationTest + FoundationAccessTest: **37 tes, 238 assertion lulus**,
  MySQL skillmatch_testing dengan guard pemisahan existing. Tidak memakai reset,
  migrate:fresh atau RefreshDatabase.
- Render Blade fixture tidak disimpan: daftar kosong, draft tanpa tanggal kirim,
  dan pagination page=2 lulus.
- Chrome headless CDP, viewport aktual 1440/768/390: tidak overflow. Screenshot
  desktop dibandingkan SVG; mobile diperiksa secara visual, status/footer/tombol
  menyusun ulang dengan benar. Artefak di storage/app/applications-*-final.png.
- git diff --check lulus. Preview/fixture hanya di storage/app yang diabaikan Git.

## Demonstrasi dan batas

Login Volunteer melalui server aplikasi existing dan buka Lamaran Saya
(`/volunteer/applications`). Periksa kartu dan tekan Lihat Detail untuk lamaran
milik akun; gunakan data akun sendiri untuk mengecek draft/submitted/hasil seleksi.
Daftar lebih dari 10 lamaran memakai pagination existing. Tidak ada integrasi baru.

Belum menjalankan login/navigasi detail end-to-end melalui browser dengan akun
kerja, belum review tim, dan bukan klaim seluruh UAT lulus. Berbagai label status
memakai komponen existing; tidak seluruh status/teks ekstrem diperiksa visual.
Lanjutan PIC A3: review desain dan varian data nyata. Tidak ada commit baru.
