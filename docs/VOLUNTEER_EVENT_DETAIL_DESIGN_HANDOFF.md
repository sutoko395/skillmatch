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
