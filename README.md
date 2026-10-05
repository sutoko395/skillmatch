# SkillMatch ? Shared Foundation

Implementasi SF-01?SF-07 dari baseline teman pada commit `8fba603`. Laravel/Blade tetap dipakai; fitur admin lanjutan, event/payment, dokumen privat/lamaran, dan engine aktivitas belum selesai. Lihat [serah-terima dan bukti verifikasi](docs/SF_HANDOFF.md).

## Prasyarat

- PHP sesuai Composer (`^8.3`); lingkungan yang diuji PHP 8.4.24, Composer 2.8.11. Jalankan `composer check-platform-reqs`; PDO MySQL juga wajib.
- Node yang memenuhi Vite (`^20.19.0 || >=22.12.0`); diuji Node 24.14.1/npm 11.11.0.
- MySQL 8.0.16+ untuk CHECK constraint, InnoDB; diuji MySQL 8.0.30. MariaDB/SQLite belum diuji dan bukan target.
- Akun migration memerlukan CREATE/ALTER/INDEX/REFERENCES/TRIGGER pada database terkait. Runtime cukup izin aplikasi; jangan berikan izin administrasi server jika tidak dibutuhkan.
- Gunakan lockfile. Laravel 13.33.0, Tailwind 3.4.19, Alpine 3.17.4, Vite 8.3.1. Tidak memerlukan Redis atau scanner antivirus.

## Instalasi baru

Clone repository dan checkout commit SF yang disepakati, lalu masuk folder proyek. Perintah berikut untuk PowerShell:

```powershell
composer install --no-interaction --prefer-dist
composer check-platform-reqs
npm ci --ignore-scripts
Copy-Item .env.example .env
```

Salin `.env` hanya jika belum ada; jangan menimpa konfigurasi lokal. Buat database kosong melalui administrator MySQL:

```sql
CREATE DATABASE skillmatch CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE skillmatch_testing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Sediakan akun database lokal, idealnya akun aplikasi dan tes terpisah dengan izin hanya pada database masing-masing. Kredensial diisi sendiri di `.env`, tidak di Git:

```dotenv
APP_NAME=SkillMatch
APP_ENV=local
APP_URL=http://localhost:8000
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=skillmatch
DB_USERNAME=skillmatch_app
DB_PASSWORD=isi_password_lokal_anda
```

`DB_URL` harus kosong/tidak disetel agar tidak menimpa koneksi. Waktu aplikasi/database UTC; input/tampilan availability WIB. Sesudah konfigurasi:

```powershell
php artisan key:generate
php artisan config:clear
php tools/sf-db-check.php
php artisan migrate
php artisan db:seed
npm run build
php artisan serve --host=127.0.0.1 --port=8000
```

`key:generate` hanya pada instalasi baru; jangan mengganti key instalasi yang sudah dipakai. Untuk pengembangan, jalankan `npm run dev` di terminal kedua. Herd juga dapat melayani proyek; sesuaikan APP_URL dengan domain Herd. Jangan menggunakan `migrate:fresh` pada database kerja.

`db:seed` menjalankan MasterDataSeeder dan UserSeeder, dibatasi lingkungan local/testing. Seeder memakai key nama/email stabil dan tidak mengubah akun/password/profil yang sudah ada. Kategori dan akun adalah fixture lokal; bukan data atau kebijakan produksi. Seeder tidak membuat event/payment/lamaran palsu.

## Akun demo lokal

Semua akun memakai password **`SkillMatch-local-2026!`**, khusus lokal dan dilarang untuk deployment publik. Semua masuk lewat `/login`; tidak ada pemilih role ataupun login admin terpisah.

| Email | Keadaan / tujuan |
|---|---|
| admin@example.test | Admin terverifikasi ? `/admin/dashboard` |
| organizer-a@example.test, organizer-b@example.test | Organisasi aktif ? `/organizer/aktivitas` |
| organizer-pending@example.test | Pending ? `/organizer/profile/pending` |
| organizer-inactive@example.test | Revisi/belum terverifikasi; akun tetap aktif dan dapat mengedit profil |
| organizer-suspended@example.test | Akun nonaktif, login ditolak |
| organizer-unverified@example.test | Verifikasi email dahulu; profil dasar tetap dapat dibuka |
| volunteer-a@example.test, volunteer-b@example.test, volunteer-c@example.test | Profil lengkap ? `/volunteer/aktivitas` |
| volunteer-suspended@example.test | Akun nonaktif, login ditolak |
| volunteer-unverified@example.test | Verifikasi email dahulu |

Availability demo: 1 November 2026 pukul 09.00?12.00 WIB (02.00?05.00 UTC). Kota Malang/Surabaya, empat level skill. Akun admin dibuat secara internal melalui seeder lokal ini; registrasi publik hanya Volunteer/Organizer. Pembuatan admin produksi belum disediakan.

## Email autentikasi

`MAIL_MAILER=log` pada `.env.example` menulis email verifikasi/reset ke `storage/logs/laravel.log`, **tidak mengirim ke inbox**. Buka tautan verifikasi/reset lokal dari log tanpa membagikan token. Untuk pengiriman nyata gunakan SMTP uji dan konfigurasi MAIL_HOST/PORT/USERNAME/PASSWORD/FROM_ADDRESS di `.env`. APP_URL harus sesuai alamat yang dibuka karena verifikasi memakai signed URL.

Registrasi publik mengirim notifikasi verifikasi, login/registrasi meregenerasi sesi, logout POST ber-CSRF menginvalidasi sesi dan token. Akun demo terverifikasi dapat langsung login; akun unverified dipakai untuk menguji alur email. Password reset memakai token framework, masa berlaku 60 menit, throttle broker 60 detik, serta throttle route. Login gagal dibatasi lima percobaan per email/IP. Pengiriman SMTP nyata belum diuji.

## Tes MySQL terpisah

Jangan memakai database kerja/demo. `.env.testing` tidak dilacak Git. Buat dari contoh dan isi kredensial database tes:

```powershell
Copy-Item .env.testing.example .env.testing
php artisan key:generate --env=testing
php artisan config:clear
php tools/sf-db-check.php --env=testing
```

Pastikan output menunjukkan `mysql`, `skillmatch_testing`, server MySQL, dan InnoDB. `.env` kerja tidak boleh menunjuk `skillmatch_testing`. Jika belum ada database tes, buat dengan SQL di atas. Alternatif lokal bagi akun yang memiliki izin CREATE DATABASE: `php tools/sf-prepare-testing.php` membuat database tersebut dan menyalin konfigurasi lokal hanya jika `.env.testing` belum ada; tidak menghapus tabel atau mengubah file testing yang sudah ada.

Setelah pemeriksaan koneksi:

```powershell
php artisan migrate --env=testing
php artisan db:seed --env=testing
npm run build
php vendor/bin/phpunit
php tools/sf-audit-commit-check.php --env=testing
```

Tes memakai `DatabaseTransactions`, bukan RefreshDatabase. TestCase memeriksa lingkungan, nama database, driver, config cache, DB_URL, dan perbedaan database kerja sebelum transaksi. Migration dilakukan terpisah; tes tidak mereset schema. Skrip audit memakai dua koneksi independen untuk membuktikan commit/rollback nyata dan mempertahankan catatan bukti pada database tes karena audit append-only. Ulangi seeder tanpa reset jika membutuhkan akun demo; jangan berharap seeder mengembalikan perubahan akun yang sudah diedit.

`composer test` juga tersedia dan menjalankan config:clear lalu artisan test. Untuk format, gunakan `php vendor/bin/pint --test <file/folder-yang-diubah>`; baseline di luar SF belum diformat ulang. Hasil SF saat implementasi: 52 tes/227 assertion lulus; build berhasil. Uji browser manual 360/768/1280, SMTP nyata, dan setup oleh anggota lain masih diperlukan (lihat handoff).

## Memperbarui baseline berisi data

1. Backup konsisten database dan berkas; uji pada salinan terlebih dahulu. Migration SF wajib diterapkan juga pada database kerja sebelum membuka halaman Volunteer; migration pada database tes tidak memperbarui database kerja. Perbaikan lokal 5 Oktober 2026 sudah menerapkan schema SF pada database kerja (lihat `docs/SF_HANDOFF.md`); anggota lain tetap perlu memeriksa database masing-masing.
2. Jalankan `php artisan migrate` setelah mengecek database tujuan. Migration lama tidak diubah. SF menambah cities, city_id nullable, availability_slots, flag master dan audit_logs; FK skill menjadi restrict.
3. `city` dan `availability` lama dipertahankan. Tidak ada pemetaan kota otomatis dan tidak ada interval buatan dari Weekend/Weekday/Flexibel. Pengguna memilih kota aktif dan mengisi interval nyata.
4. `is_active=false` lama tidak otomatis diubah: belum dapat dibedakan antara suspensi asli dan efek submit organisasi pada baseline. Reviewer/admin mengklasifikasikan data lama sebelum pemulihan terkontrol A1.
5. Konversi konfigurasi SQLite ke MySQL tidak memindahkan data. Ekspor/impor terkontrol perlu mempertahankan ID/FK/jumlah record/timestamp; belum diuji pada salinan data kerja nyata.
6. Dokumen organisasi lama masih mungkin ada di disk public. SF menghentikan upload/preview publik, tetapi tidak memindahkan/menghapus berkas. A1/A3 wajib backup, copy/checksum, migrasi metadata, lalu menutup akses publik sebelum penggunaan nyata.

Untuk backup MySQL, gunakan `mysqldump --single-transaction` dengan kredensial lokal melalui mekanisme aman, koordinasikan snapshot berkas, dan hindari DDL selama dump. Restore ke database terpisah, jangan menimpa kerja. Jangan commit dump, token, atau dokumen pribadi. Prosedur restore lengkap dan purge ledger menjadi integrasi A1/A3 yang belum tersedia.

## Modul lanjutan dan troubleshooting

- `/admin/dashboard` menampilkan dashboard dasar baseline dengan hitungan nyata Volunteer, Organizer, skill, kategori dan event pending serta aksi cepat. Filter periode, tren dan analitik lengkap belum tersedia; A1 melanjutkan controller/view yang sama. Moderasi, audit UI, master UI kota/nonaktifkan, serta suspend dengan alasan/audit lengkap tetap A1. Hard delete akun ditolak server.
- Aktivitas pengguna mempertahankan gaya kartu baseline dan ringkasan profil nyata milik akun sendiri. Volunteer melihat kelengkapan dari ProfileEligibilityService, skill, kota dan availability WIB; Organizer melihat profil/kontak serta status akun/organisasi. Fitur kegiatan belum tersedia diberi keterangan tanpa statistik palsu. A4 melanjutkan ringkasan ini dengan ActivityReadService, notifikasi, screening, assessment dan matching.
- Event/katalog/paket/Midtrans Sandbox belum diimplementasikan oleh SF; konfigurasi payment belum tersedia (A2).
- Beranda `/` mempertahankan hero, tombol Masuk/Daftar dan footer tim dari baseline, memakai navbar SF. Pengguna login diarahkan melalui tujuan role/status yang sah. Katalog dan matching diberi keterangan sedang disiapkan, bukan mengganti seluruh beranda. A2 melanjutkan halaman ini beserta katalog/detail event.
- Dokumen privat, download terotorisasi, lamaran/attendance/retensi belum tersedia (A3 dengan integrasi A1). Tidak menjalankan `storage:link` untuk dokumen pribadi.
- SF mengirim email auth sinkron; tidak memerlukan worker/scheduler. Instruksi `queue:work`/`schedule:work` untuk outbox/attempt/retensi baru ditambahkan pemilik modul saat implementasi.
- Unknown database/Access denied: periksa database dan grant lokal. `could not find driver`: aktifkan PDO MySQL pada PHP CLI/Herd yang dipakai. `sf-db-check` tidak mencetak password.
- `Table ... availability_slots doesn't exist` saat membuka Volunteer: jalankan `php artisan migrate:status` pada environment aplikasi. Jika migration SF pending, ikuti prosedur backup/upgrade di atas lalu `php artisan migrate` dan `php artisan db:seed --class=MasterDataSeeder`. Jangan memakai `migrate:fresh` atau menjalankan tes pada database kerja. Jika tabel sudah ada tetapi migration masih pending, periksa schema dan riwayat migration terlebih dahulu; jangan menghapus tabel atau menandai migration selesai tanpa verifikasi.
- APP_KEY kosong: generate hanya untuk instalasi baru atau file testing baru. Vite manifest hilang: `npm ci --ignore-scripts` lalu `npm run build`; hapus `public/hot` hanya jika itu penanda dev server milik Anda yang sudah berhenti.
- Port 8000/MySQL bentrok: pilih port yang sesuai dan ubah APP_URL/DB_PORT. Signed verification gagal: periksa URL, waktu, hash dan akun yang sedang login.
- Trigger audit ditolak saat migration: akun migration memerlukan TRIGGER; ikuti kebijakan administrator MySQL untuk binary logging. Jangan menghapus proteksi audit sebagai solusi.
- Akun nonaktif lama tidak bisa login: periksa keputusan suspensi bersama A1; jangan menjalankan aktivasi massal.
