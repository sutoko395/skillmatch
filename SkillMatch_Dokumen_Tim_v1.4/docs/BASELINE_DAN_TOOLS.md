# Baseline kode dan tools

Versi 1.4.2 • sumber primer: manifest, lockfile, route, model, migration, dan Blade pada ZIP.

## 1. Teknologi yang ditemukan

| Komponen | Manifest / lockfile ZIP | Keputusan tim |
|---|---|---|
| PHP | `^8.3` pada composer.json; framework dan PHPUnit terkunci menerima PHP 8.3 | Gunakan PHP kompatibel, verifikasi `composer check-platform-reqs`. |
| Laravel | `^13.17`; composer.lock `v13.33.0` | Pertahankan lockfile dan `composer install`. |
| Autentikasi | Laravel Breeze `v2.4.2`; controller/view autentikasi sudah dihasilkan | Lanjutkan kode yang ada, jangan menjalankan scaffolding ulang hingga menimpa view. |
| Template | Blade | Semua halaman modul memakai Blade. |
| CSS | Tailwind `^3.1.0`, terkunci `3.4.19`; PostCSS + Autoprefixer + forms | Pertahankan pipeline Tailwind 3. |
| Paket tambahan CSS | `@tailwindcss/vite` terkunci `4.3.3`, tidak dipakai vite.config.js | Hapus dependensi yang tidak digunakan dalam perubahan terpisah SF; jangan beralih ke Tailwind 4. |
| Interaksi | Alpine.js terkunci `3.17.4`, dimulai di resources/js/app.js | Muat satu kali lewat Vite. Hapus CDN Alpine tambahan pada layout. |
| Bundler | Vite `8.3.1`, laravel-vite-plugin `3.2.0` | Ikuti lockfile, `npm ci` kemudian `npm run build`. |
| Node | Engine pada lockfile Vite/plugin: `^20.19.0 || >=22.12.0` | Tim menyamakan Node yang memenuhi engine tersebut; bukan versi terbaru hasil pencarian. |
| Database | ZIP awal: `.env.example` SQLite; `phpunit.xml` SQLite memory | Target revisi 1.4.2: MySQL/InnoDB untuk aplikasi dan tes integrasi; ubah kedua konfigurasi. |
| File | Local disk di storage/app/private; public disk tersedia | Disk eksplisit private_documents untuk berkas pribadi; disk publik hanya aset publik. |
| Queue/session/cache | Database dalam .env.example | Pertahankan; tidak perlu Redis. Tidak ada queue pemindai. |
| Email | `MAIL_MAILER=log` | Cocok untuk pengembangan; gunakan SMTP uji tim bila perlu pengiriman email nyata untuk verifikasi/reset. |
| Test / format | PHPUnit `12.5.36`, Pint `v1.32.1` | Gunakan PHPUnit yang sudah ada; tidak perlu migrasi ke Pest. |
| Pendukung dev | Tinker, Pail, Pao, Faker, Mockery, Collision, concurrently | Pertahankan sejauh dibutuhkan proyek; bukan fitur aplikasi. |
| Payment | Belum ada SDK Midtrans di composer.json | A2 menambah integrasi Midtrans Sandbox melalui Laravel HTTP client; jangan mengklaim gateway sudah tersedia. |
| Grafik admin | Belum ditemukan dependensi chart | A1 boleh membuat SVG sederhana dengan Blade dan tabel data alternatif; pustaka baru hanya jika disepakati. |

MySQL/InnoDB diperlukan untuk database target; versi server disamakan dan dicatat oleh tim. VS Code, Git, phpMyAdmin atau MySQL Workbench adalah alat kerja pilihan. Herd dapat menyediakan PHP di Windows tetapi MySQL tetap harus tersedia sebagai layanan terpisah. XAMPP/Docker tidak wajib; pastikan layanan yang dipilih benar-benar MySQL jika tim menetapkan MySQL, bukan mengasumsikan MariaDB identik. Redis, ClamAV, Node backend, React, Vue, Inertia, Filament dan Livewire tidak diwajibkan.

## 2. Peta baseline fungsional

| Area kode | Temuan | Tindakan / PIC |
|---|---|---|
| routes/web.php dan RoleMiddleware | Route terpisah per role; admin login khusus belum tersedia | SF mempertahankan login bersama/guard web dan menegakkan role admin di server; tidak menambah login khusus. |
| User dan LoginRequest | `is_active` belum ditegakkan; User belum implements MustVerifyEmail | SF menegakkan verifikasi/status akun pada server. |
| VolunteerProfileController | Profil dan skill tersimpan; availability berupa label | SF menambah availability_slots, menjaga field lama sementara. |
| OrganizerProfileController | Dokumen disimpan public; tiap update wajib upload dan menjadi pending | A1 membenahi profil dan memakai utilitas dokumen A3. |
| Admin controllers | CRUD master, pengguna, verifikasi, hitungan ringkas | A1 menyelesaikan moderasi, analitik, audit dan nonaktifkan data referensi. |
| EventVerificationController / EventPosition | Controller/view memakai `positions.skills`; model punya `positionSkills` | A1 memperbaiki ke `positions.positionSkills.skill` dan menyesuaikan Blade bersama A2. |
| Event / EventPosition / PositionSkill / PositionRequirement | Tabel dan relasi fondasi sudah ada | A2 memperluas melalui migration tambahan. |
| Organizer dashboard/sidebar | Angka tetap 0 dan tautan `#` | A2/A3/A4 menghubungkan modul; SF menyediakan layout web utama. |
| Volunteer dashboard | Ringkasan profil tersedia; Lihat Event ke landing page | A4 mengganti route aktivitas, A2 menyediakan katalog. |
| tests/Feature/ProfileTest.php | Masih memakai /profile yang tidak terdaftar | SF memperbarui tes, jangan mempertahankan tes lama sebagai bukti lulus. |
| layouts | Inter dimuat tetapi CSS html menyebut Poppins; Alpine juga dimuat CDN | SF menyatukan font dan runtime JavaScript. |
| account deletion / cascade FK | Penghapusan akun dapat menghapus data terkait | A1 menonaktifkan akun; hard delete tidak diekspos sebelum desain retensi diselesaikan. |

## 3. MySQL/InnoDB dan integritas

Keputusan pengguna: database target memakai MySQL/InnoDB. SQLite dalam ZIP adalah baseline lama, bukan database target. Setiap anggota menyediakan database lokal sendiri dengan schema dari migration; jangan berbagi satu database pengembangan yang dapat saling di-reset. Catat versi MySQL yang sama untuk tim dan lingkungan demo.

SF mengganti DB_CONNECTION ke mysql pada .env.example/config default bila perlu dan mengubah phpunit.xml/.env.testing agar tes integrasi memakai database khusus skillmatch_testing. Hapus override SQLite :memory: pada phpunit.xml. Jangan menjalankan RefreshDatabase/migrate:fresh terhadap database kerja/demo. Tes unit perhitungan murni tidak memerlukan database.

Untuk acceptance, gunakan transaksi dan locking reads pada baris yang menjadi titik serialisasi: kunci baris Volunteer terlebih dahulu, kemudian event/entitlement, lalu posisi, dengan urutan ID konsisten jika lebih dari satu. Semua jalur yang mengubah acceptance, penarikan accepted atau kuota wajib memakai urutan yang sama. Setelah lock, baca ulang status, jumlah accepted dan jadwal bentrok dengan pembacaan terkini sebelum menulis. Baris Volunteer yang dikunci menyelaraskan penerimaan satu orang di dua event; lock posisi saja tidak cukup. Uji konkurensi menentukan apakah implementasi benar, bukan keberadaan lockForUpdate semata.

Constraint unik tetap wajib pada lamaran, attempt, order reference, payment event dan entitlement. Callback payment mengunci order/event yang relevan, memeriksa status terbaru, kemudian mengaktifkan hak sekali dalam transaksi singkat. Jangan melakukan HTTP gateway atau operasi file besar saat memegang lock. Deadlock/lock timeout ditangani dengan retry terbatas untuk operasi idempoten dan pesan yang jelas bila gagal.

Uji dua koneksi/proses memakai database MySQL uji yang sama: dua penerimaan berebut kuota satu dan dua penerimaan orang yang sama pada jadwal bentrok. Uji menggunakan transaksi yang benar-benar commit agar proses lain dapat melihat fixture. SQLite tidak dipakai sebagai pengganti pembuktian perilaku InnoDB.

Jika database SQLite lama sudah berisi data penting, perubahan DB_CONNECTION tidak memindahkannya. Backup dan buat migration schema MySQL, lalu lakukan proses ekspor/impor terkontrol dengan validasi jumlah record, FK, ID, timestamp dan path berkas. Untuk data demo yang dapat dibuat ulang, gunakan seeder pada database MySQL baru.

## 4. Setup yang diserahkan pada README aplikasi

Urutan untuk instalasi baru: periksa PHP/Composer/Node → `composer install` → `npm ci` → salin `.env.example` ke `.env` → sediakan database MySQL kosong dan akun database, lalu isi koneksi mysql → generate APP_KEY sekali → `php artisan migrate` → seeder demo yang ditentukan → `npm run build` → jalankan aplikasi.

- Gunakan `php artisan serve` dan `npm run dev` di terminal terpisah saat pengembangan jika tidak memakai web server lokal. Script `composer dev` di ZIP memanggil `php artisan dev`; ketersediaannya perlu diverifikasi setelah dependensi terpasang.
- Jalankan queue worker hanya untuk pekerjaan asinkron yang benar-benar digunakan; notifikasi/outbox yang diantrikan memerlukannya. Scheduler diperlukan untuk deadline assessment, outbox retry, dan retensi.
- `storage:link` tidak dipakai untuk mengekspos dokumen privat. Symlink publik, bila digunakan untuk poster, hanya menunjuk folder aset publik.
- Tambahkan konfigurasi MIDTRANS_SERVER_KEY, MIDTRANS_CLIENT_KEY, MIDTRANS_IS_PRODUCTION=false dan URL webhook pada .env.example tanpa nilai rahasia. Server key hanya di server.
- Webhook sandbox memerlukan endpoint yang dapat dijangkau gateway. Jika lokal belum memiliki URL tersebut, dokumentasikan endpoint uji publik atau sinkronisasi status server; redirect browser tetap bukan bukti bayar.
- Jangan jalankan `migrate:fresh` pada data kerja anggota. Perubahan schema memakai migration tambahan.
- `.env`, dump database berisi data pengguna, berkas pribadi, dan kredensial tidak masuk repository.

Tidak ada instruksi pemasangan ClamAV atau layanan scanner. Ekstensi PHP diperiksa dengan Composer; tidak mengarang daftar dependensi berdasarkan Laravel versi lain.

## 5. Isi minimum README aplikasi yang wajib diserahkan

Panduan berikut adalah struktur wajib untuk pengembang/AI agent. Perintah harus diverifikasi pada aplikasi hasil implementasi; paket dokumen ini belum menjalankan aplikasinya.

1. **Prasyarat:** versi PHP/Composer/Node sesuai lockfile, layanan MySQL/InnoDB, ekstensi PDO MySQL, dan pengecekan composer check-platform-reqs.
2. **Persiapan proyek:** clone/ekstrak, masuk folder, composer install, npm ci, salin .env.example menjadi .env (sertakan contoh PowerShell Copy-Item untuk Windows).
3. **Database kosong:** buat skillmatch dan skillmatch_testing secara terpisah, charset utf8mb4; sediakan akun dengan izin sesuai database. Tabel dibuat migration; pengguna tidak perlu mengetik seluruh CREATE TABLE secara manual.
4. **Konfigurasi:** jelaskan DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD dan APP_URL. Contoh tanpa password asli:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=skillmatch
DB_USERNAME=skillmatch_app
DB_PASSWORD=isi_password_lokal_anda
```

5. **Inisialisasi:** php artisan key:generate hanya instalasi baru; php artisan config:clear sesudah perubahan konfigurasi; php artisan migrate; perintah seeder yang benar-benar tersedia pada implementasi. Jangan merekomendasikan migrate:fresh untuk update rutin.
6. **Menjalankan lokal:** php artisan serve dan npm run dev di terminal terpisah. Jelaskan URL yang benar berdasarkan APP_URL/server; untuk build gunakan npm run build.
7. **Worker/scheduler:** php artisan queue:work dan php artisan schedule:work pada development apabila outbox/timeout/retensi memakai keduanya; jelaskan fungsi dan gejala saat tidak berjalan. Tidak ada worker scanner.
8. **Email:** konfigurasi pengembangan log/SMTP uji dan cara verifikasi/reset akun untuk demo. MAIL_MAILER=log tidak berarti email dikirim ke inbox.
9. **Payment:** konfigurasi Sandbox, endpoint webhook, cara uji pembayaran dan sync status; kredensial hanya .env.
10. **File:** disk privat, permission folder, batas ukuran PHP/web server selaras aplikasi, dokumen tanpa symlink publik dan tanpa ClamAV.
11. **Akun demo dan akses:** akun lokal dari seeder, /login, /admin/dashboard, /volunteer/aktivitas dan /organizer/aktivitas; tidak memakai password demo untuk deployment publik.
12. **Pengujian:** database skillmatch_testing terpisah, PHPUnit, build frontend dan langkah UAT; tunjukkan cara memastikan koneksi test sebelum perintah destruktif.
13. **Troubleshooting:** Access denied/Unknown database, driver PDO MySQL, port MySQL/server bentrok, APP_KEY, Vite manifest, email log, queue berhenti, webhook tidak terjangkau. Solusi mengikuti error yang benar-benar ditemukan.
14. **Backup/restore:** dump konsisten MySQL dan file privat, restore ke database uji, validasi dan purge ledger; larang menaruh dump berisi data pribadi di Git.

SF menulis dan menguji README awal fondasi; A1 bertanggung jawab menggabungkan README akhir; A2 menulis payment, A3 storage/retensi, A4 assessment/queue/scheduler. Setiap PR memperbarui instruksi jika setup berubah. UAT-35 membuktikan anggota lain dapat menjalankan aplikasi dari panduan tanpa langkah yang hilang.

## Koreksi pemakaian baseline - 5 Oktober 2026

`welcome.blade.php` dari baseline sudah berisi hero, CTA autentikasi dan footer; SF memulihkan konten tersebut dalam layout publik bersama, dengan keterangan katalog/matching belum tersedia. A2 melanjutkan beranda dan katalog. Dashboard dasar admin membaca hitungan nyata dan dipertahankan untuk dilanjutkan A1. Jangan menafsirkan shell sebagai penggantian seluruh halaman yang sudah berfungsi. Instalasi, database, auth, dependency lockfile dan batas pengujian tetap mengikuti README aplikasi serta docs/SF_HANDOFF.md di root aplikasi.
