# Notifikasi Volunteer

## Penerapan pada tiga peran

Pada branch `feature/skillmatch-ui-and-features`, panel terbaru dan daftar
berpaginasi memakai komponen `notifications.list` bersama. Layanan
`NotificationReadService` menyiapkan konteks dan tujuan berdasarkan penerima.
Organizer aktif mendapat tautan ke objek miliknya, Organizer pending mendapat
tautan profil, dan Admin dapat membuka antrean verifikasi. Volunteer tetap
mendapat tindakan sesuai status mutakhir asesmen atau lamaran. Route,
aturan bisnis, dan schema yang sudah berlaku tetap dipakai.

Build Vite, kompilasi Blade, dan 9 tes terkait notifikasi lulus (88 asersi).
Tes mencakup panel terbaru dan Semua notifikasi, tautan berdasarkan role,
status baca, serta pembatasan penerima dan kepemilikan. Pemeriksaan visual di
browser untuk penerapan lintas role belum dilakukan.

## Panel Notifikasi terbaru

Branch `design/notification-context`, basis `2a02e69`. Rancangan yang disetujui
memakai satu panel membulat pada Ringkasan Aktivitas, dengan ikon yang sejajar
dengan judul. Lima notifikasi terbaru menampilkan konteks yang dapat diakses
Volunteer, tindakan yang sesuai, dan status baca. Tujuan asesmen mengikuti status
terkini dan versi pada snapshot lamaran. Notifikasi lama dapat membuka detail
lamaran untuk melihat perkembangan. Penandaan baca memakai endpoint existing dan
kembali ke Semua notifikasi.

Buka Ringkasan Aktivitas sebagai Alya Demo untuk contoh lamaran diterima. Render
dengan data localhost menampilkan kegiatan, posisi, tautan, dan status baca.
Vite, kompilasi Blade dan FoundationAccessTest lulus. IntegratedFlowsTest lulus
9 dari 10, termasuk regresi notifikasi. Pemeriksaan hapus akun berhistori pada
suite tersebut menerima 302 ketika mengharapkan 403. Pemeriksaan visual desktop
dan seluler pada browser untuk implementasi belum dijalankan.

Perubahan berupa presentasi dan konteks pembacaan daftar. Aturan bisnis dalam
PRD dan kontrak, schema, setup README, serta mekanisme pengiriman tetap berlaku.

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
