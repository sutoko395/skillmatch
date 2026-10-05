# SkillMatch

SkillMatch adalah aplikasi web yang dikembangkan untuk menghubungkan Volunteer dengan kegiatan Organizer berdasarkan keterampilan, ketersediaan waktu, dan lokasi.

Aplikasi menggunakan Laravel, Blade, Tailwind CSS 3, Alpine.js, Vite, dan MySQL. Volunteer dan Organizer memakai antarmuka web utama, sementara Admin memiliki panel tersendiri.

## Status aplikasi

Saat ini tersedia beranda, login dan registrasi, verifikasi email/reset password, profil Volunteer beserta skill dan jadwal ketersediaan, profil Organizer, ringkasan profil pengguna, serta dashboard dasar Admin.

Katalog dan pengelolaan kegiatan, pembayaran, lamaran, attendance, assessment dan matching masih dalam pengembangan. Bagian yang belum tersedia ditandai pada antarmuka; aplikasi belum menyediakan seluruh alur kegiatan dari awal sampai akhir.

## Prasyarat

- PHP sesuai Composer (`^8.3`); lingkungan yang diuji PHP 8.4.24, Composer 2.8.11. Jalankan `composer check-platform-reqs`; PDO MySQL juga wajib.
- Node yang memenuhi Vite (`^20.19.0 || >=22.12.0`); diuji Node 24.14.1/npm 11.11.0.
- MySQL 8.0.16+ untuk CHECK constraint, InnoDB; diuji MySQL 8.0.30. MariaDB/SQLite belum diuji dan bukan target.
- Akun migration memerlukan CREATE/ALTER/INDEX/REFERENCES/TRIGGER pada database terkait. Runtime cukup izin aplikasi; jangan berikan izin administrasi server jika tidak dibutuhkan.
- Gunakan lockfile. Laravel 13.33.0, Tailwind 3.4.19, Alpine 3.17.4, Vite 8.3.1. Tidak memerlukan Redis atau scanner antivirus.

## Instalasi baru

Clone repository dan masuk ke folder proyek pada branch atau versi yang akan digunakan. Perintah berikut untuk PowerShell:

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
| admin@example.test | Admin terverifikasi menuju `/admin/dashboard` |
| organizer-a@example.test, organizer-b@example.test | Organisasi aktif menuju `/organizer/aktivitas` |
| organizer-pending@example.test | Pending menuju `/organizer/profile/pending` |
| organizer-inactive@example.test | Revisi/belum terverifikasi; akun tetap aktif dan dapat mengedit profil |
| organizer-suspended@example.test | Akun nonaktif, login ditolak |
| organizer-unverified@example.test | Verifikasi email dahulu; profil dasar tetap dapat dibuka |
| volunteer-a@example.test, volunteer-b@example.test, volunteer-c@example.test | Profil lengkap menuju `/volunteer/aktivitas` |
| volunteer-suspended@example.test | Akun nonaktif, login ditolak |
| volunteer-unverified@example.test | Verifikasi email dahulu |

Availability demo: 1 November 2026 pukul 09.00-12.00 WIB (02.00-05.00 UTC). Kota Malang/Surabaya, empat level skill. Akun admin dibuat secara internal melalui seeder lokal ini; registrasi publik hanya Volunteer/Organizer. Pembuatan admin produksi belum disediakan.

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

`composer test` juga tersedia dan menjalankan config:clear lalu artisan test. Untuk format, gunakan `php vendor/bin/pint --test <file/folder-yang-diubah>`. Catatan hasil pengujian dan keterbatasannya tersedia dalam [dokumentasi pengembangan](docs/DEVELOPMENT.md).

## Memperbarui instalasi

Backup database dan berkas terlebih dahulu, periksa database tujuan, lalu ikuti [panduan transisi data](docs/DEVELOPMENT.md#memperbarui-baseline-berisi-data). Migration database tes tidak memperbarui database aplikasi. Jangan mereset database kerja atau mengganti APP_KEY instalasi yang sudah digunakan.

## Troubleshooting

- **Tabel `availability_slots` tidak ditemukan:** periksa `php artisan migrate:status` pada environment aplikasi. Setelah backup dan pemeriksaan tujuan, jalankan migration yang pending dengan `php artisan migrate`, lalu `php artisan db:seed --class=MasterDataSeeder`. Jika tabel sudah ada tetapi migration pending, periksa schema dan riwayatnya sebelum melanjutkan.
- **Unknown database / Access denied:** periksa nama database dan izin akun MySQL lokal. Untuk `could not find driver`, aktifkan PDO MySQL pada PHP CLI/Herd.
- **APP_KEY kosong:** jalankan `key:generate` hanya untuk instalasi baru. Jangan mengganti key instalasi existing.
- **Vite manifest hilang:** jalankan `npm ci --ignore-scripts` lalu `npm run build`. Hapus `public/hot` hanya jika itu penanda dev server milik Anda yang sudah berhenti.
- **Tautan verifikasi gagal:** periksa APP_URL, waktu server dan akun yang sedang login. Mailer log tidak mengirim email ke inbox.
- **Trigger audit ditolak saat migration:** akun migration memerlukan izin TRIGGER. Jangan menghapus proteksi audit untuk mengatasi kesalahan izin.
- **Akun nonaktif tidak bisa login:** periksa status dan alasan penonaktifan; jangan mengaktifkan semua akun secara massal.

Email autentikasi saat ini dikirim sinkron; worker/scheduler belum diperlukan untuk alur yang tersedia. Konfigurasi pembayaran dan pemrosesan modul lanjutan belum tersedia. Jangan menjalankan `storage:link` untuk membuka dokumen pribadi kepada publik.

## Dokumentasi

- [Panduan pengembangan dan pembagian tugas](docs/DEVELOPMENT.md).
- [Spesifikasi produk, desain dan UAT](SkillMatch_Dokumen_Tim_v1.4/README.md).
