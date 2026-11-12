# Tampilan Admin — FigCraft

12 November 2026 (WIB). Branch `feature/admin-figcraft-ui`, dasar
`164825042cae7681c33a23ee2bbfa673ed777244` dari branch Organizer yang sudah
di-commit/push. Working tree awal bersih; hasil Organizer tetap ada pada parent.
Instruksi pengguna melanjutkan penyesuaian role Admin dengan lingkup tampilan
yang sama. Referensi MCP: `skillmatch-electric-blue-system.figcraft`, halaman
`Electric Blue / 01 Admin`, 17 frame A01–A17 termasuk variasi modal.

## Hasil dan batas perubahan

Sidebar ungu/aksen campuran menjadi navy #0F172A, aksen biru #2563EB, header
putih, kartu tanpa bayangan dan kontrol membulat sesuai referensi. Menu aktif
Admin tetap abu-abu transparan, berbeda dari menu aktif Organizer. Tabel/filter,
modal pengguna/master/paket dan halaman moderasi/transaksi tetap tersedia.
Badge aktif/inaktif/pending tetap memiliki label; pesan validasi tetap terlihat.
Filter akun ditumpuk pada mobile, modal panjang dapat digeser dalam viewport.

Grafik existing mempertahankan query, periode, seri, data serta handler klik.
Hanya presentasi Chart.js di Blade yang berubah: warna biru, garis lurus/titik,
grid horizontal dan legend tersembunyi karena judul menunjukkan seri aktif.
Tidak menyalin statistik atau grafik contoh FigCraft ke runtime. CDN Chart.js
existing tetap dipakai, tanpa library/instalasi baru.

Pemeriksaan visual menemukan heading posisi kosong di verifikasi: Blade membaca
`position_name`, tetapi model/migration existing memakai `name`, tanpa alias.
Binding Blade dikoreksi ke `$position->name` agar nama aktual muncul seperti
referensi. Tidak mengubah model, data tersimpan, query atau schema.

`resources/css/admin-panel.css` diimpor dari app.css dan dibatasi `.admin-panel`.
Kelas konteks ditambahkan pada layout, dashboard, daftar akun/master dan moderasi.
Tidak mengganti komponen bersama, route, controller, Policy, middleware, service,
model, migration, JavaScript eksternal aplikasi, manifest atau lockfile.
Form/action, nama field, method, CSRF, validasi, transisi, audit/notifikasi,
otorisasi dokumen dan pembayaran existing dipertahankan. PRD/kontrak/brief A1
dan README setup tetap berlaku; design/UAT mencatat perubahan visual ini.

## Verifikasi

- MySQL tes diverifikasi baca-saja: `skillmatch_testing`, MySQL 8.0.42/InnoDB,
  PHP 8.4 Herd. Fixture sintetis dibungkus transaksi dan rollback. Tidak ada
  migration/reset/perubahan pada database kerja untuk tugas Admin ini.
- **80 tes / 566 assertion lulus**: FoundationAccessTest,
  FoundationIntegrityTest, IntegratedFlowsTest, A2EventTest, A2PaymentTest,
  DefaultPackageTest, OrganizerKtpUploadTest. Mencakup akses lintas role, master
  referensi, moderasi, revisi akun/dokumen, paket dan pembayaran existing.
- Setelah koreksi binding nama posisi, **A2EventTest 17 tes / 118 assertion
  lulus** dijalankan ulang. Ini subset suite di atas, bukan 17 tes berbeda.
- `npm.cmd run build`, `artisan view:cache` dan `git diff --check` lulus.
  Perbandingan statis delapan Blade memastikan tujuan route, CSRF, method,
  nama field dan handler Alpine/onclick/onchange tetap sama. Perubahan inline
  script grafik terbatas pada konfigurasi presentasi.
- 13 halaman fixture merespons HTTP 200: dashboard, Organizer, Volunteer,
  verifikasi daftar/detail, skill, kategori, paket daftar/buat/edit, transaksi
  daftar/detail, dan notifikasi.
- Edge headless: **39 kombinasi halaman/ukuran** pada 390/768/1440 px lulus
  keterbacaan, Alpine, tanpa overflow dokumen, sidebar navy dan drawer mobile.
  **42 probe modal** membuka View/Edit/Delete Organizer, Edit/Delete Volunteer,
  tambah/edit/hapus master, tambah/edit paket dan penolakan event tanpa submit.
  Proses pertama timeout setelah 10 halaman; tiga halaman terakhir dijalankan
  ulang terpisah dan lulus. Tidak mengklaim satu proses penuh lulus tanpa retry.
- Setelah perapian grafik/nama posisi, dashboard/verifikasi diperiksa ulang pada
  ketiga ukuran (6 kombinasi dan 3 modal penolakan). Chart.js nyata tersedia;
  klik kartu Organizer mengubah judul/seri dengan 30 titik data fixture pada
  ketiga ukuran. Ini bukan pengujian analitik dengan semua periode/data produksi.
- Screenshot desktop/mobile yang mewakili dashboard, daftar akun/filter,
  verifikasi serta modal diperiksa langsung. Organizer dan Volunteer memiliki
  screenshot identik byte dengan CSS commit dasar yang dikompilasi terpisah.
  Pembanding awal Organizer menangkap animasi sidebar belum selesai; capture
  ulang menunggu animasi, tidak mengubah sumber Organizer untuk mengatasinya.

Artefak fixture/screenshot/script QA privat di
`storage/app/private/admin-design-review`, diabaikan Git. Angka fixture hanya
bukti tes. Belum diuji: submit semua aksi melalui browser dengan akun nyata,
semua kombinasi data/status/periode, browser lain, dokumen identitas nyata,
transaksi Sandbox end-to-end baru dan review/UAT tim menyeluruh.

## Meninjau dan melanjutkan

Setup README existing tetap. Jalankan frontend dengan `npm.cmd run dev` atau
`npm.cmd run build`, buka server Laravel dengan PHP kompatibel, login Admin
melalui `/login`, lalu `/admin/dashboard`. Tinjau Manajemen User, Verifikasi
Event, Master Data, Kelola Paket dan Transaksi, termasuk modal serta mobile.
Tidak ada endpoint/service baru yang perlu diintegrasikan PIC lain.

Lokal, belum commit/push/PR/merge. PIC A1 tetap pemilik bisnis Admin, A2 tetap
pemilik paket/pembayaran. Tidak mengklaim review tim atau seluruh P0/UAT selesai.
