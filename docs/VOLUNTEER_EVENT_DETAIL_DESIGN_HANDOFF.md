# Perapian detail event panel Volunteer

11 November 2026. Branch feature/volunteer-design, dasar 2289576.
Pengguna menyetujui perapian berdasarkan screenshot detail: dua kolom, label
informasi, kartu posisi/persyaratan dan tombol biru. Tanpa commit/push.
Sembilan perubahan lokal di luar lingkup dipertahankan.

CatalogController.show kini memilih volunteer.events.show hanya untuk route
panel. events.show publik tidak diedit. View baru menyusun header, deskripsi,
posisi kiri dan ringkasan lokasi/pelaksanaan/pendaftaran kanan. Kartu posisi
memisahkan badge kuota, deskripsi, badge skill, jadwal dan persyaratan/dokumen.
Mobile satu kolom, tombol penuh. Tidak mengartikan kuota sebagai sisa tempat.
CSS volunteer-event-detail.css scoped .ve-detail diimpor app.css.
Partial terpisah: summary, position, apply. Apply memakai kondisi existing untuk
pendaftaran belum buka/tutup, limit lamaran, role/verifikasi dan POST CSRF.
Label tombol menjadi Pilih Posisi & Mulai Lamaran, warna biru dan normal case.
Nama/tanggal/skill/persyaratan berasal dari data asli, bukan fixture screenshot.

Tidak ada migration, route baru, schema, layanan, Policy atau perubahan aturan
bisnis. Route volunteer.events.show dan publiclyVisible tetap. PRD/kontrak dan
README setup tetap berlaku; desain/UAT diperbarui. Pemilik integrasi SF/A2/A3
tetap memakai endpoint katalog/lamaran existing.

Validasi: VolunteerCatalogTest + A2EventTest + A3ApplicationTest lulus
34 tes/217 assertion pada MySQL skillmatch_testing dengan guard existing;
tanpa reset database kerja. Build Vite 8.3.1, view:cache, git diff --check lulus.
Preview model fixture tanpa simpan, desktop/mobile diperiksa visual; Chrome CDP
1440/768/390 tidak overflow. Screenshot di storage/app diabaikan Git. Fixture
preview memakai route lamaran lama untuk shell, sehingga menu aktif screenshot
fixture bukan bukti menu aktif route nyata; tes request memakai route panel asli.
Login/klik/melamar browser akun nyata belum diuji. Tidak ada dependensi baru.

Demo: login Volunteer, Jelajahi Event, Lihat posisi dan persyaratan pada event
published. Lihat ringkasan, posisi dan tombol. Coba variasi pendaftaran terbuka,
belum buka/tutup serta email belum terverifikasi pada data tes.

## Detail event lebih ringkas - 14 November 2026

Disetujui pengguna berdasarkan screenshot /volunteer/events/11. Dasar branch
feature/volunteer-design, HEAD 8c37f57; perubahan lokal sebelumnya dipertahankan.
Hanya volunteer-event-detail.css: selector .volunteer-applications .ve-detail
membatasi perubahan pada panel. Judul 28px (mobile 26), padding kartu 20px
(mobile 16), gap utama 16/20px, bagian posisi 14px. Tanggal pelaksanaan
mengalir dalam satu blok flex-wrap; tombol kembali minimal 44px.
Layout dua kolom, konten, kondisi CTA, route dan desain publik tetap berlaku.
Tidak ada migration/layanan/dependensi baru; PRD/kontrak/setup tidak berubah.
Build Vite dan git diff --check lulus. Preview fixture tanpa simpan diperiksa
visual desktop; audit Chrome 1440/768/390 tanpa overflow horizontal. Fixture
memakai route shell lama sehingga menu aktif preview bukan bukti route nyata.
Browser akun nyata/POST belum diuji ulang; perubahan hanya CSS.
Backup CSS sebelum perubahan: storage/app/event-detail-before-compact-20261114/
volunteer-event-detail.css (diabaikan Git). Tidak commit/push.
Demo: login Volunteer > Jelajahi Event > Lihat posisi dan persyaratan; periksa
judul, ringkasan tanggal, persyaratan panjang dan tombol pada desktop/mobile.
