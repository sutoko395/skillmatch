# SkillMatch

## Seleksi dan pelaksanaan A3 — 28 Oktober 2026

Organizer yang organisasinya disetujui Admin memakai alur auth Organizer existing;
seleksi/pelaksanaan tidak menambah syarat email verified. Akun tetap harus aktif dan
event milik sendiri. Perbaikan ini mengatasi tombol Terima yang sebelumnya berhenti
di middleware verifikasi lalu kembali ke dashboard tanpa mengubah lamaran.

Perbaikan tombol Terima Pelamar: setelah klik, hasil selalu diarahkan ke detail kandidat.
Berhasil menampilkan Diterima dan pesan sukses; gagal validasi menampilkan alasan.
Tidak memerlukan migration tambahan untuk perbaikan tombol ini. Aturan penerimaan
tetap sama; data kandidat tidak diubah hanya untuk mencoba tampilan.

Implementasi A3 kini mencakup draft/submit dan dokumen privat, kandidat Siap Ditinjau,
accept/reject, pemeriksaan kuota/bentrok jadwal, penarikan, attendance manual,
completion, riwayat, pembatalan bersama A2/A4, serta retensi/recovery. Skor A4 dibaca
tanpa dihitung ulang oleh controller A3. Status/bukti terbaru ada di
[A3_HANDOFF](docs/A3_HANDOFF.md); catatan tahap lama di bawah bukan status terkini.
Notifikasi internal menunggu NotificationService/outbox A4. Utilitas dokumen organisasi
tersedia untuk A1, tetapi penggantian alur/schema organisasi tetap pekerjaan A1.

Sebelum memakai halaman baru, backup dan periksa database tujuan, lalu jalankan
`php artisan migrate` dengan PHP yang memenuhi vendor (di lingkungan ini PHP 8.4 Herd).
Migration tambahan hanya membuat attendances, document_purges, dan revision attendance;
tidak backfill penerimaan/kehadiran lama. Migration pekerjaan ini telah diuji pada
MySQL khusus tes; belum diterapkan pada database kerja.

Organizer: Event Saya → detail event → Pelamar → Siap Ditinjau → tinjau hasil dan
Terima/Tolak dengan alasan. Pelaksanaan menampilkan rekap serta pencatatan
Hadir/Tidak hadir sejak awal event. Belum dicatat bukan Tidak hadir. Selesaikan
kegiatan setelah akhir jadwal, seleksi final, dan seluruh attendance final; nol peserta
memerlukan konfirmasi. Koreksi setelah completion wajib alasan. Volunteer melihat
Riwayat Partisipasi dan dapat menarik lamaran sebelum accepted; pengunduran accepted
dicatat Organizer dengan alasan sebelum awal event. Counter paket tidak dikembalikan.

Dokumen baru memakai disk `private_documents` dengan root `storage/app/private`;
alias `private` dipertahankan untuk dokumen lama. Jangan menambah symlink publik.
Sediakan izin tulis direktori ini dan batas upload PHP/web server minimal 5 MB per file
(post_max_size perlu lebih besar dari upload_max_filesize). Download attachment memakai
Policy, throttle, MIME terdeteksi dan nosniff. Ready bukan jaminan bebas malware.

Konfigurasi baseline **demo**: `A3_DRAFT_RETENTION_DAYS=7`,
`A3_DOCUMENT_RETENTION_DAYS=90`. Di luar local/testing, cleanup hanya aktif setelah
kebijakan publik ditetapkan dan `A3_RETENTION_ENABLED=true`. Scheduler harian
menjalankan cleanup; tidak ada worker khusus A3. Gunakan `php artisan schedule:work`
pada development. Tanpa scheduler, cleanup/timeout otomatis tertunda.

```powershell
php artisan a3:documents cleanup
php artisan a3:documents cleanup --execute
php artisan a3:documents recover --execute
php artisan a3:retry-notifications --after=0 --limit=100
```

Tanpa --execute perintah dokumen hanya menampilkan kebijakan. Jangan menjalankan cleanup
pada data kerja untuk demo. Purge dibukukan sebelum file dihapus; penghapusan gagal dapat
diulang. Recovery memverifikasi checksum, menandai missing, dan dapat mengembalikan ready
hanya jika berkas kembali dengan checksum tepat dan tidak berada dalam purge ledger.
Jurnal upload menangani orphan akibat rollback/crash setelah masa aman 24 jam.
Replay notifikasi mengembalikan kegagalan yang jelas selama A4 belum tersedia.

Backup/restore: hentikan penulisan dan scheduler, jalankan recover untuk menguras purge
yang sudah commit ke ledger privat, lalu ambil dump MySQL konsisten serta salinan
berkas privat pada titik yang sama; simpan salinan terbaru
`storage/app/private/a3-purge-ledger` secara terpisah dari snapshot lama. Restore ke
database uji terpisah, pertahankan ledger terbaru, lalu jalankan recover sebelum membuka
akses atau menyalakan scheduler. Ledger menyimpan lokasi internal, sehingga juga privat.
Tanpa ledger terbaru, penghapusan setelah tanggal backup tidak dapat diketahui dari
backup lama; jangan membuka hasil restore tersebut sebagai data aktif. Prosedur penuh
backup/restore operasional oleh anggota lain belum diuji.

Tes terarah sesudah MySQL skillmatch_testing diverifikasi dan dimigrasi:

```powershell
php vendor/bin/phpunit tests/Feature/A3ExecutionTest.php tests/Feature/A3RetentionTest.php tests/Feature/A3ApplicationTest.php tests/Feature/A3ApplicationDocumentsTest.php tests/Unit/ApplicationDocumentPolicyTest.php
php tools/a3-concurrency-check.php --env=testing
npm run build
```

Tool konkurensi mempertahankan fixture sintetis hanya di database tes karena audit
append-only. Tes A4 lama yang memakai count/sole seluruh tabel perlu database fixture
terisolasi jika dijalankan sesudah tool ini; jangan mereset database kerja.

## Assessment Organizer (10 Oktober 2026)

Setelah mengambil kode, jalankan `php artisan migrate` memakai PHP kompatibel dengan vendor (lingkungan lokal ini memakai PHP 8.4 Herd), lalu `npm.cmd run build`. Migration `2026_10_10_000002_create_organizer_assessments.php` membuat assessments/questions/options; tidak perlu reset atau seeder assessment.

Login sebagai Organizer aktif/terverifikasi, buka Event Saya -> detail event draft -> Assessment (di bawah Posisi dan jadwal) -> Kelola Assessment pada posisi. Isi durasi 1–120 menit, tambah 1–50 soal dengan empat opsi A–D dan satu kunci tiap soal. Simpan Draft boleh parsial. Publikasikan Assessment menampilkan konfirmasi; centang persetujuan lalu Konfirmasi Publikasi untuk mengunci set. Simpan draft dan kembali tersedia bila belum siap. Readiness nyata terhubung ke pengajuan/publikasi event dan snapshot lamaran, tetapi semua syarat A2 lainnya tetap berlaku. Publikasi assessment tidak menerbitkan event. Published tidak dibuka kembali; draft stale dari tab lain ditolak. Posisi dengan draft assessment dapat dihapus, sedangkan posisi dengan assessment published dipertahankan.

Tes validasi: `php vendor/bin/phpunit tests/Unit/OrganizerAssessmentValidationTest.php`. Tes integrasi hanya sesudah konfigurasi MySQL `skillmatch_testing` terpisah dan migration tes: `php vendor/bin/phpunit tests/Feature/OrganizerAssessmentTest.php`. Timer/attempt peserta dan screening/matching sudah diimplementasikan pada tahap berikutnya; aktivitas/notifikasi lengkap masih tertunda. Bukti historis tahap Organizer ada di [handoff assessment](docs/A4_ASSESSMENT_HANDOFF.md), bukti A3 terbaru pada bagian atas.

## Update jadwal posisi (10 Oktober 2026)

Form event memilih kota aktif dari master agar `city_id` dapat dipakai pada moderasi, katalog dan snapshot. Request buat/edit cukup mengirim `city_id`; teks `city` diisi server dari nama master, bukan input wajib tambahan. Untuk draft lama yang hanya menyimpan kota teks, buka Edit Event, pilih kota lalu simpan kembali sebelum mengajukan moderasi. Teks lama ditampilkan sebagai petunjuk; tidak ada pemetaan otomatis, reset data atau migration tambahan untuk perbaikan relasi ini.

Jalankan migration tambahan dengan PHP yang memenuhi lockfile/dependensi terpasang, setelah backup dan pemeriksaan database: `php artisan migrate`. Migration `2026_10_10_000001_add_event_schedule_mode_to_positions.php` menambah `follows_event_schedule`; tidak mengubah interval posisi lama. Posisi baru default mengikuti jadwal event. Pilih Atur jadwal tugas khusus bila perlu; jadwal event selalu terlihat. Event satu hari cukup mengubah jam, beberapa hari memakai tanggal/jam. Perubahan event editable memperbarui posisi otomatis, dan ditolak jika jadwal khusus keluar rentang/deadline. Snapshot dan freeze published tetap berlaku.

Verifikasi pada MySQL khusus tes sesuai panduan di bawah: `php artisan migrate --env=testing`, lalu `php vendor/bin/phpunit tests/Feature/PositionScheduleModeTest.php` dan `npm run build` (PowerShell dapat memakai `npm.cmd run build`). Pengujian database perubahan ini belum dijalankan: `.env.testing` belum ada dan akun database kerja tidak memiliki izin menyiapkan `skillmatch_testing`. Sediakan database/kredensial tes terpisah sebelum menjalankan pengujian. PHP terminal 8.3.31 ditolak dependensi vendor yang meminta >=8.4.1; gunakan PHP 8.4 Herd yang tersedia. Jangan reset database kerja.

SkillMatch adalah aplikasi web yang dikembangkan untuk menghubungkan Volunteer dengan kegiatan Organizer berdasarkan keterampilan, ketersediaan waktu, dan lokasi.

Aplikasi menggunakan Laravel, Blade, Tailwind CSS 3, Alpine.js, Vite, dan MySQL. Volunteer dan Organizer memakai antarmuka web utama, sementara Admin memiliki panel tersendiri.

Font Inter disertakan di `public/fonts/inter` (empat bobot 400/500/600/700, lisensi SIL OFL 1.1). Sertakan direktori ini saat membagikan/deploy aplikasi. Layout bersama memuat font lokal dengan fallback system sans sehingga menu tidak menunggu stylesheet font dari internet; tidak perlu CDN font atau instalasi dependensi baru.

## Status aplikasi

Beranda publik menjelaskan tujuan platform, peran Volunteer/Organizer, cara memulai, FAQ, pencarian dan pratinjau event aktual dengan pendaftaran terbuka. Ketika belum ada event tersedia, beranda tetap menampilkan informasi dan langkah berikutnya.

Saat ini tersedia login dan registrasi, verifikasi email/reset password, profil Volunteer beserta skill dan jadwal ketersediaan, profil Organizer, ringkasan profil pengguna, serta dashboard dasar Admin.

Pengelolaan event/posisi/jadwal, katalog, paket/order dan pembayaran Sandbox tersedia, beserta lamaran/dokumen, screening/matching, assessment, seleksi, attendance dan riwayat A3. Validator assessment nyata dipakai pengajuan/publikasi; pembatalan terhubung ke lamaran/attempt. Notifikasi internal, aktivitas lengkap dan review/UAT aplikasi menyeluruh masih tertunda. Lihat status A3 terbaru di atas untuk migration dan batas pengujian.

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

- **Klik pertama loading tetapi tetap di halaman asal, klik kedua baru berpindah:** gunakan konfigurasi Tailwind/Vite terbaru, hentikan lalu jalankan kembali `npm run dev`, kemudian Ctrl+F5 sekali. Cache Blade di `storage/framework/views` sebelumnya ikut dipindai Tailwind dan memicu reload Vite yang membatalkan navigasi. Cache/runtime kini dikecualikan; refresh file sumber tetap aktif. Bukti reproduksi dan pengujian cache kosong tersedia di [DEVELOPMENT](docs/DEVELOPMENT.md). Instalasi tanpa dev server memakai `npm run build`.
- **Dropdown belum merespons ketika halaman baru dimuat:** pastikan kode terbaru dan `public/fonts/inter` tersedia. Font lokal menghindari stylesheet eksternal yang menunda Alpine; ini berbeda dari request navigasi yang dibatalkan Vite. Tombol mobile membuka drawer, lalu satu klik pada item menuju halaman.

- **Tabel `availability_slots` tidak ditemukan:** periksa `php artisan migrate:status` pada environment aplikasi. Setelah backup dan pemeriksaan tujuan, jalankan migration yang pending dengan `php artisan migrate`, lalu `php artisan db:seed --class=MasterDataSeeder`. Jika tabel sudah ada tetapi migration pending, periksa schema dan riwayatnya sebelum melanjutkan.
- **Unknown database / Access denied:** periksa nama database dan izin akun MySQL lokal. Untuk `could not find driver`, aktifkan PDO MySQL pada PHP CLI/Herd.
- **APP_KEY kosong:** jalankan `key:generate` hanya untuk instalasi baru. Jangan mengganti key instalasi existing.
- **Vite manifest hilang:** jalankan `npm ci --ignore-scripts` lalu `npm run build`. Hapus `public/hot` hanya jika itu penanda dev server milik Anda yang sudah berhenti.
- **Tautan verifikasi gagal:** periksa APP_URL, waktu server dan akun yang sedang login. Mailer log tidak mengirim email ke inbox.
- **Trigger audit ditolak saat migration:** akun migration memerlukan izin TRIGGER. Jangan menghapus proteksi audit untuk mengatasi kesalahan izin.
- **Akun nonaktif tidak bisa login:** periksa status dan alasan penonaktifan; jangan mengaktifkan semua akun secara massal.

Email autentikasi dan sinkronisasi pembayaran berjalan sinkron. Worker/scheduler belum diperlukan untuk keduanya; pengiriman notifikasi internal menunggu layanan outbox dan worker modul terkait. Jangan menjalankan `storage:link` untuk membuka dokumen pribadi kepada publik.

## Event dan pembayaran Sandbox

Setelah memperbarui kode, backup database, periksa tujuan dengan `php tools/sf-db-check.php`, lalu jalankan `php artisan migrate`. Dua migration event/payment menambahkan schema tanpa memublikasikan event lama. Detail konversi data tersedia dalam [panduan pengembangan](docs/DEVELOPMENT.md).

Organizer terverifikasi mengelola event melalui **Event Saya**. Urutan konfigurasi: paket, informasi event, posisi/jadwal, assessment, lalu pengajuan moderasi. Admin mengelola harga dan manfaat melalui `/admin/packages` serta transaksi melalui `/admin/orders`. Waktu form dan tampilan menggunakan WIB; penyimpanan menggunakan UTC.

Tambahkan konfigurasi berikut ke `.env` lokal, lalu jalankan `php artisan config:clear`:

```dotenv
MIDTRANS_SERVER_KEY=
MIDTRANS_CLIENT_KEY=
MIDTRANS_IS_PRODUCTION=false
```

Isi server key dari akun Midtrans **Sandbox**, jangan memakai key produksi atau memasukkannya ke Git/chat. Checkout menggunakan halaman Snap redirect yang dihosting Midtrans; client key tidak diperlukan untuk mode ini. Mode produksi ditolak oleh adapter saat ini.

Atur Payment Notification URL pada dashboard Sandbox ke URL HTTPS aplikasi yang dapat diakses gateway dengan path `/payments/midtrans/notification`. URL localhost tidak dapat dijangkau gateway; endpoint publik/tunnel belum disediakan oleh repository. Halaman status Organizer memeriksa order pending yang sudah memiliki checkout sekali saat kembali dari checkout baru (penanda `check_payment=1`), melalui POST ber-CSRF ke server. Membuka detail transaksi biasa tidak memanggil gateway otomatis. Status final terverifikasi memuat ulang halaman selama pengguna belum berpindah; klik menu membatalkan pemeriksaan di browser dan mencegah reload terlambat. Pemeriksaan status gateway dibatasi lima detik; checkout tetap memiliki batas tersendiri. Bila masih pending atau pemeriksaan gagal, tunggu sebentar lalu gunakan **Sinkronkan status**; jangan membuat order baru. Tombol ini tetap mengirim form POST jika JavaScript dimatikan. Parameter redirect checkout tidak menjadi bukti paid.

Paket Free tidak membuat order paid. Paket berbayar hanya dapat dibuatkan order setelah approval. Checkout, callback dan sinkronisasi menyimpan status serta hak paket secara idempoten. Pembayaran valid pada event cancelled tetap dicatat untuk tindak lanjut tanpa publikasi; refund otomatis tidak tersedia.

Pada Buat Event, pilih paket terlebih dahulu, lalu isi informasi dan jadwal. Jika durasi pembukaan pendaftaran melebihi batas paket, warning muncul dan simpan diblokir; pilih paket lebih besar atau pendekkan jadwal. Batas dihitung dari waktu pembukaan sampai deadline, bukan lama kegiatan. Paket dan draft disimpan bersama; pembayaran berbayar tetap setelah approval. Draft lama tanpa paket harus memilih saat disimpan kembali. Edit dengan paket yang sama mempertahankan harga/limit snapshot; penggantian paket eksplisit memakai konfigurasi aktif terbaru. Tidak memerlukan migration/seeder baru untuk alur ini.

Paket awal untuk local/testing disertakan oleh seeder dasar, dengan harga dan limit yang disepakati:

| Paket | Harga per event | Maksimal posisi | Maksimal lamaran terkirim | Hari pendaftaran |
|---|---:|---:|---:|---:|
| Free | Gratis | 2 | 30 | 7 |
| Standard | Rp30.000 | 5 | 100 | 30 |
| Premium | Rp50.000 | 10 | 300 | 60 |

Admin dapat mengedit nama, harga, manfaat dan status melalui **Kelola Paket**. Beranda dan pilihan paket Organizer membaca data aktif terbaru; snapshot event/order lama tetap memakai nilai saat dipilih/dibeli. Untuk instalasi existing yang sudah memiliki akun admin aktif/verified, tambahkan paket awal saja:

```powershell
php artisan db:seed --class=DefaultPackageSeeder
```

Seeder tidak menimpa edit admin, termasuk rename/nonaktif, saat diulang. Tidak perlu menjalankan ulang seeder akun atau migration untuk paket ini. Copy status fitur pada kartu paket milik A2 masih perlu diselaraskan dengan implementasi A3/A4 terbaru; bukan sumber status penyelesaian modul.

Fixture demo tambahan bersifat opsional, hanya untuk local/testing setelah seeder dasar:

```powershell
php artisan db:seed --class=A2DemoSeeder
```

Seeder menambahkan paket berlabel **Demo ... (fixture)** dan event **Demo A2 ...**, bukan harga produksi. Seluruh event demo tetap unpublished. Satu fixture berbayar berstatus approved untuk mengembangkan pembayaran secara terpisah; tidak menyatakan assessment atau moderasi end-to-end sudah tersedia. Seeder tidak menimpa event/paket dengan nama yang sama. Jangan menggunakannya pada deployment publik.

Pengajuan dan publikasi menampilkan alasan penolakan ketika assessment tidak valid. Pembatalan memakai layanan lamaran A3 dan adapter attempt A4 dalam transaksi event yang sama. Status pembayaran yang sudah terverifikasi tetap tersimpan walaupun event belum memenuhi syarat publikasi.

Notifikasi internal belum terkirim selama layanan notifikasi belum terpasang. Setelah tersedia, produsen memakai layanan yang sama dan catatan bisnis dapat diproses ulang:

```powershell
php artisan a2:retry-notifications
```

Perintah tersebut meneruskan catatan ke outbox, bukan menggantikan worker pengiriman. Petunjuk worker mengikuti implementasi layanan notifikasi setelah diintegrasikan.

Tes tambahan, hanya setelah konfigurasi MySQL tes benar:

```powershell
php vendor/bin/phpunit --filter A2
php tools/a2-concurrency-check.php --env=testing
```

Skrip konkurensi menolak target selain `skillmatch_testing` dan menyimpan fixture sintetis beserta audit commit untuk bukti pengujian. Jangan mengganti targetnya menjadi database kerja. Tes gateway memakai fake HTTP; itu bukan bukti transaksi Sandbox nyata. Bukti dan bagian yang belum diuji tersedia di [catatan implementasi](docs/A2_HANDOFF.md).

Untuk mencoba gateway nyata secara terpisah, seed `A2DemoSeeder` pada database tes, pastikan `.env.testing` menunjuk MySQL `skillmatch_testing`, lalu jalankan:

```powershell
php tools/a2-sandbox-check.php --env=testing --checkout
php tools/a2-sandbox-check.php --env=testing --sync=ID_ORDER_LOKAL
```

Skrip membaca key Sandbox dari `.env` lokal tanpa mencetaknya dan menolak database kerja. Checkout disimpan dalam `storage/app/private/a2-sandbox-checkout.html` yang diabaikan Git; buka file lokal tersebut untuk memilih metode dan menyelesaikan simulasi. Ganti `ID_ORDER_LOKAL` dengan ID yang dicetak skrip. Sinkronkan lagi setelah simulasi untuk memeriksa status dan entitlement; ulangi untuk memeriksa idempotensi. Sebelum metode dipilih, status gateway mungkin belum tersedia. Setelah timeout checkout, tunggu lima menit sebelum mencoba reference yang sama. Jangan membagikan file checkout atau tokennya. Ini belum menguji callback publik maupun assessment/publikasi lintas modul.

## Screening A4

Setelah pull, jalankan `php artisan migrate` untuk tabel `screening_results` (migration
`2026_10_11_000001_create_screening_results_table.php`). Gunakan PHP yang memenuhi
composer.lock. Tidak ada dependency, konfigurasi `.env`, worker atau seeder tambahan.

Screening otomatis sesudah submit memeriksa snapshot profil, skill wajib/minimum level,
dokumen wajib ready saat submit, availability penuh dan kota jika diwajibkan. Syarat
manual tetap untuk Organizer. Detail lamaran Volunteer menampilkan hasil ringkas/alasan;
detail pelamar Organizer menampilkan pemeriksaan per syarat. Daftar lamaran memakai badge
hasil yang sama. Hasil gagal menjadi `screening_failed`. Hasil lolos memicu matching
setelah commit; status menjadi `assessment_pending` sesudah skor tersimpan. Bila matching
tertunda, status masih `submitted` dengan label **Lolos screening — menunggu pencocokan**.
Pengerjaan assessment peserta sudah tersedia; notifikasi internal masih menunggu A4.

Untuk memproses lamaran submitted yang belum memiliki hasil setelah kegagalan sementara:

```powershell
php artisan a4:retry-screening --limit=100
```

Limit 1–1000; aman diulang dan tidak menimpa hasil tersimpan/lamaran terminal. Lamaran
lama tanpa bukti `profile_eligibility` tetap tertunda, bukan otomatis gagal/lolos;
jangan mengisi bukti historis dari profil terbaru. Retry menyebut ID operasional tanpa
mencetak data profil/dokumen. Jika ada legacy yang tertunda, periksa handoff sebelum demo.

Pengujian dengan MySQL tes terpisah yang sudah dimigrasi:

```powershell
php vendor/bin/phpunit tests/Unit/ScreeningRulesTest.php
php artisan test --filter=ScreeningTest
php artisan test --filter=A3Application
npm.cmd run build
```

Hasil dan batas pengujian tersedia di [handoff screening](docs/A4_SCREENING_HANDOFF.md).

## Matching A4

Setelah pull, jalankan `php artisan migrate` untuk migration
`2026_10_27_000001_create_match_results_table.php`. Tidak ada dependency, konfigurasi .env,
worker, route publik atau seeder baru. Bobot matching tetap skill 50%, availability 30%,
lokasi 20% dari snapshot saat submit. Hasil komponen/total tidak dibulatkan sebelum disimpan;
tampilan dua desimal. Skor dan rincian hanya di daftar/detail pelamar EO. Volunteer hanya
melihat **Menunggu assessment**, tanpa skor. Matching tidak menerima/menolak otomatis;
nilai assessment terpisah dan pengerjaan assessment peserta kini sudah tersedia.

Untuk lamaran lama yang sudah lolos screening atau kegagalan sementara matching:

```powershell
php artisan a4:retry-matching --limit=100
```

Aman diulang, hanya submitted dengan screening passed tanpa skor. Limit 1–1000;
`--after=ID_TERAKHIR` melanjutkan batch sesudah ID yang dicetak command. Snapshot malformed
tetap tertunda; jangan memperbaiki bukti historis dengan membaca profil terbaru.

Setelah MySQL tes terpisah siap dan dimigrasi:

```powershell
php vendor/bin/phpunit tests/Unit/MatchingServiceTest.php
php artisan test --filter=MatchingTest
php artisan test --filter=ScreeningTest
```

Pemeriksaan dua proses bersamaan, hanya pada database tes terpisah yang sudah dimigrasi:

```powershell
php tools/a4-matching-concurrency-check.php --env=testing
```

Tool memverifikasi target MySQL skillmatch_testing, menjalankan dua proses independen,
dan mempertahankan fixture sintetis/audit di database tes. Tidak diarahkan ke database kerja.

Bukti dan batas pengujian ada di [handoff matching](docs/A4_MATCHING_HANDOFF.md).

## Dokumentasi

- [Panduan pengembangan dan pembagian tugas](docs/DEVELOPMENT.md).
- [Spesifikasi produk, desain dan UAT](SkillMatch_Dokumen_Tim_v1.4/README.md).
