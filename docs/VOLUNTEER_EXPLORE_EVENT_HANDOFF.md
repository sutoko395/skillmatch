# Menu Jelajahi Event Volunteer

11 November 2026. Branch feature/volunteer-design, dasar 1eb7220.
Disetujui pengguna: tambahkan akses langsung katalog pada sidebar Volunteer.
Perubahan lokal, tanpa staging/commit/push. Perubahan lain dipertahankan.

resources/views/volunteer/layouts/sidebar.blade.php menambah tautan Jelajahi Event
dengan ikon pencarian setelah pemisah, sebelum Lihat Website Utama. Tampilan
mengikuti menu existing. Tujuan route events.index (GET /events) memakai katalog
publik yang sudah ada, dibuka di tab yang sama. Menu beranda tetap tersedia.
Tidak ada migration, controller, layanan, route baru, atau perubahan otorisasi.
PRD/kontrak/README setup tetap berlaku.

Build Vite, artisan view:cache, route:list events.index dan git diff --check lulus.
Belum diuji klik/navigasi serta pemeriksaan visual browser pada perubahan ini.
Tidak menambah tes khusus untuk tautan sederhana; tidak ada logika bisnis baru.
Demo: login Volunteer, pilih Jelajahi Event dari sidebar menuju katalog /events;
pada mobile buka menu terlebih dahulu. PIC SF/A2 dapat memakai route existing.
Tidak ada dependensi baru, commit atau push.

## Koreksi lingkup pengguna: katalog di dalam panel

Pengguna meminta katalog tetap memakai sidebar Volunteer, bukan layout website
utama. Menu kini menuju volunteer.events.index (GET /volunteer/events), dilindungi
auth/account.active/role:volunteer pada routes/volunteer.php. CatalogController
memakai query/filter/pagination publiclyVisible yang sama dan memilih view
volunteer.events.index hanya untuk route baru. /events dan view publik tetap.
Kartu terpisah di volunteer/events/partials/card; pencarian/reset tetap di panel.
Tombol detail tetap menuju events.show existing; detail tidak didesain ulang.
Tidak ada migration atau perubahan aturan visibilitas/lamaran. Ini menggantikan
tujuan /events yang dicatat pada tahap awal di atas.

Build dan view:cache lulus. VolunteerCatalogTest: 1 tes/9 assertion lulus
(guest ke login, Volunteer mendapat view panel, katalog publik view lama,
Organizer ditolak). Database MySQL tes terpisah dengan guard existing.
Pemeriksaan visual/klik browser belum dilakukan. Tanpa commit atau push.

## Penyesuaian desain katalog sesuai screenshot

Pengguna meminta desain katalog publik dipakai di panel. View katalog Volunteer
kini memakai heading serif "Ada kegiatan. Ada tempat untukmu.", form filter,
grid dan komponen x-event-card beserta CSS katalog existing. Wrapper
electric-public hanya pada isi katalog, sidebar/layout Volunteer tetap.
Form/reset tetap volunteer.events.index; file view publik tidak diubah.
Partial kartu panel tahap awal masih tersedia, tetapi katalog kini memakai
komponen kartu publik supaya mengikuti desain yang diminta.
Build, view:cache dan VolunteerCatalogTest 1 tes/9 assertion lulus.
Tanpa commit atau push. Detail event tetap events.show seperti lingkup awal.

## Detail event di panel (persetujuan lanjutan pengguna)

GET /volunteer/events/{event}, volunteer.events.show ditambahkan dalam middleware
Volunteer existing. CatalogController.show tetap memeriksa publiclyVisible dan
memuat data yang sama. View events/show memilih sidebar hanya pada route panel,
dengan tautan kembali ke katalog panel; route publik tetap layout publik.
Komponen event-card menerima detailRoute opsional (default events.show). Katalog
panel mengirim volunteer.events.show, sehingga gambar/judul/CTA tetap di panel.
Informasi posisi/skill/jadwal/persyaratan, POST lamaran dan CSRF tetap dipakai.
Tidak ada layanan/schema/migration baru. Tanpa commit/push.

Build/view:cache lulus. VolunteerCatalogTest 2 tes/20 assertion lulus: akses role,
panel/publik, event belum published ditolak, tautan kartu panel dan detail beserta
sidebar. Fixture publikasi lewat layanan existing, MySQL khusus tes, rollback.
Klik/visual browser detail belum diuji. Satu percobaan tes awal gagal karena
mass-assignment fixture entitlement; fixture diperbaiki memakai layanan publikasi,
tanpa mengubah model runtime.
