# Koordinasi Peserta - 3 November 2026

Keputusan pengguna: satu tautan grup opsional per event, nama PIC dan kontak PIC.
Tidak menambahkan field arahan, titik kumpul atau waktu kedatangan; informasi
tersebut disampaikan melalui grup. Jadwal posisi dan kehadiran per sesi tetap berlaku.

Branch `fix/integrated-application-flows`, basis `4067a6c`; melanjutkan perubahan
integrasi dan kehadiran yang belum commit. Halaman existing dari commit `6798ed4`
dipertahankan; hanya menambahkan section pada detail event dan detail lamaran.

## Kontrak implementasi

- Migration `2026_11_03_000003_add_event_coordination.php` menambah tabel
  `event_coordinations`: unique event_id, group_url nullable text (maksimum input
  2048), pic_name nullable varchar(100), pic_contact nullable varchar(255),
  revision unsigned integer, updated_by FK users restrict, timestamps UTC.
  FK event cascade hanya membersihkan koordinasi jika draft awal yang memenuhi
  aturan penghapusan existing dihapus. Tidak mengubah snapshot/riwayat lamaran.
- `EventCoordinationService.update(Event, User, array)` mengunci user pemilik dan
  event, memeriksa ownership/status akun/organisasi dari database, lalu menyimpan
  audit dan notifikasi dalam transaksi yang sama. Revision berbeda ditolak jika
  isian berubah; replay identik tidak menambah audit/notifikasi. Seluruh field
  boleh kosong. HTTPS wajib untuk tautan, tanpa user/password URL; server tidak
  membuka tautan eksternal. Teks kontak di-escape, tidak menjadi href bebas.
- `PUT /organizer/events/{event}/coordination`, nama
  `organizer.events.coordination.update`, memakai middleware organizer existing
  dan Policy update Event. Bisa diisi setelah publikasi tanpa membuka konfigurasi
  rekrutmen. Completed/cancelled tidak dapat diedit. Owner tetap melihat riwayat.
- `forParticipant(Application, User): ?array` memeriksa status dan ownership aktual
  sebelum mengambil data privat. Hanya Volunteer aktif pemilik lamaran accepted
  pada event non-cancelled. Lamaran lain dan Admin tidak mendapat data. Data tidak
  berada pada atribut/relasi Event publik atau snapshot. Model menyembunyikan
  nilai privat dari serialisasi otomatis. Accepted di event completed tetap dapat
  membaca riwayat; withdrawn/cancelled kehilangan akses aplikasi.
- Detail lamaran accepted menampilkan tombol Gabung grup peserta dan kontak PIC,
  atau placeholder bila belum dibagikan. Tombol eksternal membuka tab baru dengan
  noopener/noreferrer. Form organizer terdiri dari tiga field opsional, revision
  dan tombol Simpan koordinasi. Tidak mengganti layout/navigation/komponen lama.
- `event.coordination_updated` dikirim via NotificationService database existing
  untuk peserta accepted, dedupe event/revision/volunteer. Payload hanya type,
  event_id, application_id. Tujuan notifikasi memakai detail lamaran existing;
  penerimaan setelah koordinasi disimpan tetap membaca versi terbaru melalui
  detail lamaran dari notifikasi accepted existing. Audit hanya nama field dan
  revision. URL/kontak tidak dimasukkan audit, notifikasi atau email.
- Pengosongan tautan menarik akses tautan di aplikasi. Pengelolaan anggota dan
  pencabutan undangan yang sudah diketahui dilakukan organizer di platform grup.
  Tidak menambahkan chat, API grup, otomasi bergabung, atau pengiriman pesan luar.
- Field opsional ini tidak menjadi syarat publish, payment, seleksi, attendance,
  completion. Keadaan semua kosong tidak membuat data baru.

## Demonstrasi dan verifikasi

Jalankan `php artisan migrate` untuk migration tambahan. Tidak perlu seeder,
konfigurasi rahasia atau dependency baru. Organizer aktif pemilik event membuka
detail event, mengisi koordinasi, lalu menyimpan. Volunteer accepted membuka
Lamaran Saya -> Detail Lamaran -> Koordinasi Peserta atau notifikasi pembaruan.

## Hasil verifikasi aktual

- Preflight memastikan MySQL 8.0.30/InnoDB: database kerja `db_skillmatch`, database
  tes terpisah `skillmatch_testing`, environment lokal, config tidak dicache.
  Migration tambahan berhasil di testing lalu lokal. Tidak reset/seed/backfill.
- `php vendor/bin/phpunit tests/Feature/EventCoordinationTest.php`: 5/5 tes,
  78 assertion. Seluruh suite `php vendor/bin/phpunit`: 321/321 tes, 2.208 assertion.
  Meliputi akses lintas role/pemilik/status, accepted baru, placeholder/pengosongan,
  URL tidak aman, revision/replay, payload privat, snapshot/event tetap sama,
  optional tanpa koordinasi, event terminal dan rollback saat notifikasi gagal.
- `php tools/coordination-concurrency-check.php --env=testing`: tiga skenario
  dengan dua proses independen lulus: replay sama, perubahan berbeda dari revision
  sama (satu ditolak), pembaruan bersamaan pengunduran accepted (akses dicabut).
  Fixture sintetis hanya tertinggal di database tes; mailer array.
- `npm run build` lulus. Sandbox awal menolak spawn EPERM; pengulangan build di
  luar sandbox berhasil. Pint file PHP terkait dan `git diff --check` lulus.
- `php tools/coordination-ui-review.php --env=testing`: tiga render HTTP 200,
  fixture dalam transaksi rollback. `node tools/flow-browser-review.mjs
  --coordination`: sembilan kombinasi halaman/lebar 360/768/1280 lulus: owner,
  accepted dengan grup/PIC, accepted belum ada tautan. Tidak overflow horizontal,
  Alpine tersedia, tepat tiga input koordinasi dan tombol eksternal aman.
  Screenshot mobile peserta diperiksa langsung. Artefak privat di
  `storage/app/private/coordination-review`. Ini render data uji melalui kernel
  dan review browser, bukan end-to-end pengguna nyata atau pengujian grup luar.
- Tidak commit/push/PR/merge. Perubahan sebelumnya tetap ada pada branch yang sama.
  Integrasi platform grup, keanggotaan eksternal, pengiriman email dan pembayaran
  Sandbox tidak diuji ulang.
