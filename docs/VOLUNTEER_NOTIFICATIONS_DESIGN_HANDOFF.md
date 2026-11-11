# Notifikasi Volunteer

11 November 2026. Branch feature/volunteer-design, dasar 7d1bb12.
Pengguna meminta halaman Semua notifikasi dari dashboard konsisten dengan panel.
Sebelumnya shell oranye, konten sempit dan keadaan kosong berupa garis putus.
Kini shell navy/biru existing, konten sejajar halaman lain, tautan kembali
Ringkasan Aktivitas, kartu putih, ikon lonceng, keadaan kosong, badge read/unread,
tanggal WIB dan tombol detail/baca yang jelas. Tidak ada data notifikasi palsu.

notifications/index memberi page-class hanya untuk Volunteer dan memilih partial
volunteer/notifications/index beserta partial icon/item. Role lain tetap memakai
isi/index/list existing. Daftar ringkasan dashboard juga tetap memakai list lama.
Tidak ada route, controller, query, service, model, Policy, schema atau migration
baru. Pagination 20, urutan terbaru, tujuan melalui ActivityReadService,
PATCH read dengan CSRF dan pembatasan pemilik/channel existing dipertahankan.
Flash Volunteer tetap dari layout, tanpa duplikasi flash dalam konten baru.
PRD/kontrak dan README setup tetap berlaku. Design/UAT diperbarui.

Validasi: IntegratedFlowsTest + FoundationAccessTest lulus 31 tes/237 assertion
pada MySQL skillmatch_testing dengan guard existing, tanpa reset database kerja.
Build Vite 8.3.1, view:cache, git diff --check lulus. Fixture tanpa simpan untuk
kosong/read/unread; Chrome CDP 1440/768/390 tidak overflow pada kedua kondisi.
Screenshot desktop/mobile diperiksa. Fixture memakai route profil untuk shell,
sehingga menu aktif fixture bukan bukti menu route notifikasi sebenarnya.
Artefak QA di storage/app diabaikan Git. Klik/login browser akun nyata belum diuji.

Demo: login Volunteer, Ringkasan Aktivitas, Semua notifikasi. Jika ada data,
buka detail atau tandai dibaca; jika kosong tampil kartu informasi. PIC A4/SF
tetap memakai route/service existing; tidak ada dependensi baru. Lokal tanpa
staging/commit/push, perubahan lokal di luar lingkup dipertahankan.
