# Panduan pengembangan tim SkillMatch

Dokumen ini memuat pembagian pekerjaan, dependensi, dan panduan transisi database. Untuk pengenalan aplikasi, instalasi, akun demo dan tes, lihat [README utama](../README.md).

## Regresi validasi kota saat menyimpan draft event - 11 Oktober 2026

Pada main basis 676a848, EventCityTest mereproduksi pesan "The city field is required": 6 tes, 5 lulus, 1 gagal / 69 assertion. EventService kembali mewajibkan city pada request setelah commit 776c21d, sementara popup/halaman buat/edit mengirim city_id sesuai kontrak. Fixture A2 juga ikut mengirim city, sehingga pembuatan event lewat fixture menyamarkan ketidaksesuaian request form.

Perbaikan pada branch fix/event-draft-city-validation menghapus validasi input city tambahan dan fallback mapping teks yang tidak diperlukan. city_id tetap wajib, harus menunjuk master aktif; nama events.city tetap diturunkan di server. Fixture A2 memakai city_id tanpa input city. Tes round-trip sekarang memeriksa edit tanpa city serta teks palsu melebihi batas lama, tetap menyimpan nama master. Tidak menambah migration, route, schema atau mengubah alur pembayaran/lamaran. Aturan profil organisasi yang memakai city merupakan konteks berbeda dan tetap berlaku.

Bukti awal setelah perbaikan: EventCityTest + EventPackageFirstTest **12 tes / 158 assertion lulus** pada MySQL skillmatch_testing 8.0.30/InnoDB, DatabaseTransactions; mencakup create, edit, kota hilang/tidak ada/nonaktif, moderasi legacy, snapshot paket, akses pemilik dan rollback. Pint dan build Vite berhasil. Database kerja dan .env tidak diubah. Pemeriksaan browser pengguna, seluruh UAT dan review tim belum dilakukan. Perubahan belum commit/push/PR/merge; branch berbasis 676a848 yang sudah memuat integrasi A3, bukan branch perbaikan lama.

Pemeriksaan tambahan OrganizerAssessmentTest, PositionScheduleModeTest, A3ApplicationTest dan A3ApplicationDocumentsTest awalnya terhalang tabel application_documents yang belum ada pada database tes. Migration existing 2026_10_06_000002 diterapkan hanya ke skillmatch_testing setelah pemeriksaan tujuan; tidak ada migrate:fresh atau migration database kerja. Pengulangan menghasilkan **30 tes, 29 lulus, 1 gagal / 197 assertion**. Kasus A3ApplicationTest.test_submit_rechecks_registration_without_consuming_quota (dataset unpublished) mendapat 302, mengharapkan 422; perlu pemeriksaan fixture/transisi oleh PIC A3. Test tersebut memakai instance Event lama setelah publish mengembalikan instance baru, sehingga penyetelan kembali unpublished berpotensi tidak dianggap dirty; ini analisis kode, belum pembuktian terpisah. Tes draft/kota/paket lulus, tetapi seluruh integrasi A3 tidak diklaim lulus dan kasus ini tidak diubah oleh perbaikan kota.

## Klik pertama loading lalu tetap di halaman asal - 10 Oktober 2026

Koreksi diagnosis setelah pengguna menjelaskan bahwa masalah terjadi pada tautan navigasi biasa, bukan dropdown. Rekaman Chrome melalui DevTools Protocol pada localhost:8000 membuktikan urutan: klik native tanpa preventDefault, request Document ke /events atau /register, pesan WebSocket Vite full-reload dengan triggeredBy storage/framework/views/*.php, net::ERR_ABORTED pada request tujuan, lalu request/reload ke halaman asal. Cache Blade kosong mereproduksi kegagalan tiga tujuan publik; setelah cache terbentuk kunjungan berikutnya berhasil. Bukti ini menjelaskan pola klik kedua, berbeda dari masalah stylesheet font yang diuji sebelumnya.

tailwind.config.js sebelumnya memasukkan storage/framework/views/*.php sebagai content. Cache yang ditulis Laravel saat request masuk menjadi dependensi frontend dan memicu reload. Konfigurasi sekarang hanya memindai sumber Blade aplikasi dan pagination framework; vite.config.js mengabaikan storage/** serta bootstrap/cache/** sebagai berkas runtime. Refresh sumber tetap aktif. Tidak ada perubahan route, autentikasi, Policy, database, migration, manifest/lockfile atau driver sesi pada perbaikan ini.

Bukti setelah perbaikan: empat navigasi publik (/events, /login, /register, /) dengan cache Blade kosong berhasil satu klik pada server kerja; delapan navigasi panel Admin/Organizer/Volunteer dengan cache kosong berhasil satu klik pada server terpisah port 8011/MySQL skillmatch_testing 8.0.30/InnoDB. Tidak ada full-reload atau request Document dibatalkan dalam rangkaian tersebut. Penulisan fixture cache menghasilkan nol reload, penambahan fixture sumber Blade menghasilkan satu reload: refresh pengembangan masih berfungsi. NavigationAssetsTest + HomeTest **7 tes / 101 assertion lulus** dan npm run build Vite **berhasil**. Ini bukti navigasi desktop Chrome terisolasi, bukan seluruh tombol submit/UAT, semua browser atau pengukuran kinerja umum. Pengujian memakai tiga akun sementara pada database tes dan override sesi database hanya pada proses tes; .env/.env.testing tidak berubah. Cache view sempat dibersihkan untuk reproduksi, tanpa reset schema/data kerja. Akun/sesi/profil dan helper/profil browser sementara dibersihkan setelah pengujian.

Untuk instalasi lain, hentikan dan jalankan kembali npm run dev, lalu Ctrl+F5 satu kali agar browser memakai konfigurasi baru. npm run build juga memakai content source tanpa cache Blade. Jangan menjadikan view:cache atau klik dua kali sebagai solusi; cache kosong harus tetap dapat dinavigasi. Branch fix/event-moderation-city, basis 12d229b; belum commit/push/PR/merge. Review di browser pengguna dan UAT lintas modul masih diperlukan.

## Font lokal dan pengujian dropdown terpisah - 10 Oktober 2026

Keluhan berlanjut pada semua role setelah perbaikan payment. Browser Chrome headless dengan profil dan MySQL tes terpisah mereproduksi pola klik dua kali pada dropdown Admin, Organizer, Volunteer ketika request stylesheet fonts.bunny.net ditahan: Alpine belum dimulai, klik pertama tidak membuka, setelah CSS dilepas klik berikutnya membuka. Tautan sidebar setelah runtime tersedia berjalan; masalah ini terpisah dari verifikasi pembayaran. Ini reproduksi terkendali dengan font tertunda, bukan rekaman jaringan profil browser pengguna.

layouts/fonts.blade.php kini menyediakan preload dan @font-face dengan font-display swap/asset URL lokal. public/fonts/inter berisi WOFF2 Latin asli bobot 400/500/600/700, sekitar 97 KB total, dari URL font Bunny yang digunakan halaman sebelumnya, serta OFL.txt dari repositori Google Fonts/Inter. Header wOF2 diverifikasi. Auth-layout menyertakan partial bersama. Tidak ada stylesheet font eksternal, dependency/migration/route/status/auth/Policy baru. CSS global tetap existing; deklarasi font pada partial menghindari asumsi URL root pada instalasi dengan asset URL berbeda. Berkas/font/lisensi wajib ikut distribusi.

Bukti terbaru: NavigationAssetsTest + HomeTest **7 tes / 101 assertion lulus**, MySQL skillmatch_testing 8.0.30/InnoDB, DatabaseTransactions; tiga layout role dan halaman guest memakai preload lokal, tidak memakai stylesheet font internet, dan file WOFF2/lisensi tersedia. Pint, Blade cache, diff check dan build Vite berhasil tanpa warning asset font pada hasil akhir. Tes awal menemukan auth-layout belum menyertakan partial preload; telah diperbaiki dan diulang lulus.

Browser sebelumnya menahan empat file font lokal pada setiap role: Alpine tetap siap dan klik pertama dropdown membuka. Delapan perpindahan sidebar desktop pada pengulangan akhir berhasil satu klik, sekitar 262-314 ms termasuk jeda penstabilan posisi pointer pada runner; tiga item menu mobile berhasil sekali setelah drawer dibuka. Pengulangan awal sempat timeout dan belum merekam jaringan/reload sehingga penyebabnya saat itu belum terbukti; keberhasilan pengulangan bukan bukti keluhan navigasi pengguna selesai. Diagnosis navigasi dengan cache dingin ada pada bagian di atas. Angka ini bukan benchmark data kerja atau semua perangkat. Server tes port 8011 memakai override SESSION_DRIVER database/APP_URL hanya di proses tes; .env/.env.testing dan database kerja tidak diubah. Akun, sesi, profil dan browser/helper sementara dibersihkan setelah pemeriksaan. Review browser pengguna, keyboard menyeluruh, Safari/Firefox dan UAT penuh belum dilakukan.

## Perbaikan status pembayaran dan tombol - 10 Oktober 2026

Penyempurnaan navigasi pada hari yang sama: sync otomatis kini hanya pada finish checkout baru dengan check_payment=1; detail biasa tidak menambah request gateway. paymentStatus membatalkan fetch saat klik navigasi native/pagehide/destroy, tidak mencegah klik, dan mengabaikan respons terlambat sebelum/sesudah parsing JSON. Batas status HTTP server lima detik, koneksi dua detik; browser delapan detik, checkout tetap dua puluh detik. Abort browser tidak menjamin pekerjaan PHP berhenti, sehingga batas server tetap diperlukan. Pengguna lokal memakai PHP -S/artisan serve yang menangani satu request pada satu waktu; tidak diubah atau dihentikan. Tidak mengganti .env, driver sesi/database, framework atau dependensi.

Bukti terbaru: A2PaymentTest **21 tes / 129 assertion lulus** pada MySQL skillmatch_testing; payment-status.test.mjs **8 tes lulus**, termasuk klik sekali saat fetch/parsing tertunda, no reload setelah navigasi, modifier/new tab/anchor, timeout tanpa paid dan kunjungan biasa tanpa auto sync. Pint, Blade cache, diff check dan build Vite berhasil. Chrome headless dengan profil sementara dan server tes port 8011 memakai MySQL tes/sesi database tersendiri; env override hanya pada proses tes, .env.testing tetap unchanged. Navigasi desktop Dashboard ke Event Saya/Profil/Dashboard terakhir berhasil sekali, 145-164 ms; item menu mobile setelah drawer dibuka berhasil sekali. Simulasi respons paid terlambat di browser menghasilkan satu navigasi ke /organizer/events, tanpa reload halaman sumber dan tanpa error JavaScript; mock ini tidak mengubah pembayaran/database. Probe awal berhasil 69-174 ms; satu pengulangan diagnosis sempat melewati batas tunggu, pengulangan terakhir berhasil. Keluhan klik dua kali yang terus terjadi pada profil browser pengguna belum direproduksi secara konsisten; angka ini bukan jaminan kinerja semua perangkat/data. Respons login kerja diukur sekitar 0,26 detik, modul Vite 0,003 detik, CSS font eksternal 0,30 detik. Tidak ada klaim penyedia font terbukti sebagai penyebab.

Branch `fix/event-moderation-city`, basis `12d229b`; perubahan kota/paket sebelumnya dipertahankan. Tombol secondary Sinkronkan status semula default type button sehingga tidak mengirim POST. Partial orders.detail kini memakai submit eksplisit untuk Organizer/Admin dan tautan Kembali ke event bergaya tombol sekunder dengan ikon/fokus keyboard.

Alpine paymentStatus dari resources/js/payment-status.js memeriksa sekali saat halaman Organizer pending dengan checkout dibuka. Request memakai POST existing, CSRF/session dan Accept JSON; Organizer OrderController mengembalikan hanya status/paid/activated/requires_follow_up dari PaymentService.sync. GET tetap baca-saja dan parameter redirect diabaikan. Loading mencegah request bertumpuk; pending/failure menyediakan retry manual; status final memuat ulang halaman. Tanpa JavaScript form submit tetap bekerja. Tidak ada migration, dependency atau route baru. Backend verifikasi/idempotensi/audit existing dipakai kembali.

Bukti: A2PaymentTest **19 tes / 110 assertion lulus** pada MySQL skillmatch_testing 8.0.30/InnoDB, termasuk submit DOM Organizer/Admin, fallback HTML, JSON pending/paid, replay, owner/role/status, rollback audit dan respons gateway tidak valid. `node --test tests/Frontend/payment-status.test.mjs`: **5 tes lulus**, mencakup CSRF POST sekali, disable saat busy, respons pending, status server final, sesi/akses/throttle/network dan respons malformed. Pint file PHP terkait, Blade cache, diff check dan build Vite 8.3.1 berhasil. Node/Vite awal ditolak sandbox spawn EPERM, kemudian dijalankan dengan izin yang sesuai.

Order yang dilaporkan pengguna diperiksa terhadap API Midtrans Sandbox nyata: settlement. Melalui PaymentService.sync pembayaran tersimpan paid, hak paket aktif, dan pemeriksaan ulang database mengonfirmasi event published, satu entitlement dan satu receipt. Ini perubahan terarah pada transaksi pengguna yang diminta, bukan tes/reset database kerja. Key, token checkout dan respons gateway mentah tidak dicatat. Pemeriksaan API awal tertahan jaringan sandbox; berhasil setelah izin jaringan. Tidak menyatakan webhook publik, interaksi/visual browser, konkurensi dua proses, seluruh UAT atau review tim lulus. Tidak ada commit/push/PR/merge pada tahap ini.

## Paket terlebih dahulu - keputusan 10 Oktober 2026

Dilanjutkan pada branch `fix/event-moderation-city`, basis `12d229b`, dengan perubahan lokal perbaikan kota dipertahankan; belum commit/push/PR/merge. Popup dan halaman buat/edit memilih package_id sebelum informasi/jadwal. Komponen event-package-selector/event-package-warning memakai Alpine eventPackageForm dari resources/js/event-package-form.js. Warning berada dekat jadwal dan memblokir simpan bila rentang melebihi max_registration_days x 24 jam, dengan parsing WIB eksplisit. Batas dibaca dari master/snapshot; angka Free 7, Standard 30, Premium 60 hanya konfigurasi awal yang bisa diedit Admin.

EventService.save memilih snapshot server di bawah lock dan menyimpan bersama draft/audit. PackageService.assertFits dipakai save/select untuk memeriksa jumlah posisi dan durasi. Request snapshot/harga diabaikan. Paket sama pada edit mempertahankan snapshot historis (termasuk master nonaktif); ganti ID eksplisit mengambil master aktif dan memvalidasi seluruh konfigurasi sebelum write. Edit legacy tanpa snapshot wajib memilih paket; tidak ada mapping/reset/perubahan data kerja atau migration baru. Pemilihan berbayar tidak membuat order/entitlement; pembayaran tetap setelah approved. Contract A3/A4 dan freeze published/transaksi tetap berlaku. Fixture/seeder demo diperbarui agar create memilih paket lebih dahulu; seeder tidak dijalankan pada database kerja.

Bukti pada MySQL skillmatch_testing 8.0.30/InnoDB, PHP 8.4.24: EventPackageFirstTest + EventCityTest terbaru **12 tes / 154 assertion lulus**, termasuk batas tepat/lebih satu menit, upgrade, snapshot admin, akses owner, rollback, legacy dan rendering kedua form beserta binding tanggal. `node --test tests/Frontend/event-package-form.test.mjs`: **4 tes lulus**, untuk warning/submit, pergantian paket, batas Standard/Premium dan konfigurasi dinamis. Runner Node awal ditolak spawn EPERM, lalu berhasil dengan izin di luar sandbox. Pint, Blade cache dan build Vite berhasil.

Pemeriksaan lebih luas sebelum tambahan tes legacy: EventPackageFirstTest, EventCityTest, OrganizerAssessmentTest, PositionScheduleModeTest, HomeTest, DefaultPackageTest, A2PaymentTest, A3ApplicationTest: **47 tes, 46 lulus, 1 gagal / 373 assertion**. Kegagalan ada pada A3ApplicationTest.test_volunteer_can_create_draft_application_for_published_event: halaman detail lamaran memakai layouts.user yang tidak tersedia; dicatat untuk PIC A3/SF, tidak diubah oleh scope paket. Mock payment tidak membuktikan Sandbox end-to-end. Interaksi browser popup/keyboard/viewport, konkurensi pergantian paket dua proses, UAT penuh dan review tim belum diuji. Tiga temuan A2/SF pada bagian perbaikan kota tetap perlu tindak lanjut.

## Perbaikan pengajuan moderasi kota - 10 Oktober 2026

Branch `fix/event-moderation-city` dari `12d229b` (main/origin/main yang tersedia, working tree awal bersih). Perubahan belum di-commit/push/PR/merge. Relasi Event.cityRecord dipulihkan karena EventConfiguration, katalog, beranda dan A3 masih memakainya. EventService/form memakai master city_id aktif dan menurunkan teks city di server; request teks kota tidak menentukan lokasi matching. Model/route/service/status existing tetap dipakai; tidak ada migration baru. Layout Organizer menampilkan flash SF untuk pesan validasi/sukses.

Draft existing dengan city_id NULL tidak dimapping otomatis: pemilik membuka Edit Event, memilih kota aktif, menyimpan dan mengajukan kembali setelah posisi, assessment published dan paket lengkap. Data kerja db_skillmatch hanya diperiksa baca-saja. `.env.testing` lokal disiapkan melalui tools/sf-prepare-testing.php (tidak masuk Git); tiga migration pending dijalankan hanya pada skillmatch_testing setelah pemeriksaan driver/database/InnoDB. Tes memakai DatabaseTransactions, tanpa reset database.

Bukti terbaru: `php vendor/bin/phpunit tests/Feature/EventCityTest.php tests/Feature/OrganizerAssessmentTest.php tests/Feature/PositionScheduleModeTest.php tests/Feature/HomeTest.php`: 17 tes / 175 assertion lulus pada MySQL 8.0.30/InnoDB, PHP 8.4.24. Termasuk relasi/simpan-baca/perubahan kota, teks palsu, kota kosong/hilang/nonaktif, rollback state/audit, pesan redirect dengan cookie sesi, readiness assessment nyata dan pengajuan menuju pending, jadwal serta beranda. Popup Buat Event pada index sempat terlewat pada perbaikan pertama: kini popup dan halaman buat/edit memakai event-city-field yang sama dan controller index menyediakan kota aktif. Tes tambahan memeriksa kedua form, POST create yang menyimpan city_id, serta render popup terbuka/old input setelah gagal validasi tanpa event parsial. Pesan validasi kota berbahasa Indonesia. Pint file PHP terkait, Blade cache dan build Vite berhasil. Review visual/interaksi popup browser dan moderasi sampai keputusan admin pada data pengguna belum dilakukan.

Pemeriksaan tambahan A2EventTest: 14 dari 17 tes lulus / 113 assertion; tiga gagal: Organizer unverified mendapat 200 di daftar event (tes mengharapkan redirect verifikasi), view admin.packages.form tidak ditemukan, intended URL katalog sesudah login menuju volunteer.aktivitas. Ketiga temuan perlu tindak lanjut pemilik A2/SF/A1; tidak diperbaiki atau dinyatakan lulus oleh perbaikan relasi kota. UAT penuh dan review tim belum selesai.

## Pembagian pekerjaan

| Bagian | Lingkup |
|---|---|
| SF | Auth bersama, profil dasar, kota/skill/availability, layout, audit, seed dan setup awal |
| A1 | Verifikasi organisasi, moderasi event, pengguna, master UI, analitik admin, konten/settings dan audit UI |
| A2 | Beranda publik, event/posisi/jadwal, katalog, paket, pembayaran dan admin paket/transaksi |
| A3 | Dokumen privat, lamaran, seleksi, attendance, riwayat dan retensi |
| A4 | Screening, assessment, matching, notifikasi dan ringkasan aktivitas |

Penugasan pengguna setelah SF adalah A2. Kepemilikan modul A4 tetap mengikuti brief tim. Para anggota dapat bekerja paralel; integrasi akhir tetap bergantung pada interface dan layanan pemilik terkait.

## Dokumen acuan

- [Aturan kerja repository](../AGENTS.md).
- [Indeks dokumen tim](../SkillMatch_Dokumen_Tim_v1.4/README.md): PRD, desain, kontrak integrasi, baseline, UAT dan brief anggota.
- [Serah-terima SF dan bukti pengujian](SF_HANDOFF.md).
- [Interface SF untuk integrasi](SF_INTERFACES.md).

Basis implementasi SF berasal dari commit `8fba603`. Gunakan commit fondasi yang disepakati tim saat memulai branch modul; jangan mengasumsikan perubahan sudah tersedia pada main. Status implementasi dan review mengikuti handoff, bukan keberadaan file saja.

## Status modul dan kelanjutan pekerjaan

- SF menyediakan fondasi, profil dan layout. Bukti historisnya tetap di SF_HANDOFF.md.
- A2 sudah menyediakan schema event/posisi/jadwal, pengelolaan draft, katalog/detail, paket/order, adapter Midtrans Sandbox dan UI admin paket/transaksi. Lihat [handoff A2](A2_HANDOFF.md) untuk status verifikasi dan blocker; keberadaan adapter bukan bukti transaksi Sandbox nyata.
- A1 tetap melanjutkan verifikasi organisasi, moderasi/analitik/konten/master/audit UI. Route moderasi baseline hanya disesuaikan untuk relasi posisi dan pemanggilan pemeriksaan/aktivasi/publikasi A2; tidak dianggap seluruh A1 selesai.
- A3 menyediakan lamaran, dokumen privat, seleksi, attendance dan EventCancellationService. Pembatalan A2 ditahan sampai layanan ini tersedia.
- A4 menyediakan validator assessment, notifikasi/outbox dan aktivitas lengkap. Pengajuan/publikasi A2 tidak meloloskan assessment yang belum dapat diperiksa. Audit dan payment ledger dapat direplay ke NotificationService setelah tersedia.
- Ringkasan profil pengguna dan beranda baseline tetap dipertahankan. Data kegiatan tidak dipalsukan untuk mengisi halaman.

## Memperbarui baseline berisi data

1. Backup konsisten database dan berkas; uji pada salinan terlebih dahulu. Migration SF wajib diterapkan juga pada database kerja sebelum membuka halaman Volunteer; migration pada database tes tidak memperbarui database kerja. Perbaikan lokal 5 Oktober 2026 sudah menerapkan schema SF pada database kerja (lihat [SF_HANDOFF.md](SF_HANDOFF.md)); anggota lain tetap perlu memeriksa database masing-masing.
2. Jalankan `php artisan migrate` setelah mengecek database tujuan. Migration lama tidak diubah. SF menambah cities, city_id nullable, availability_slots, flag master dan audit_logs; FK skill menjadi restrict.
3. `city` dan `availability` lama dipertahankan. Tidak ada pemetaan kota otomatis dan tidak ada interval buatan dari Weekend/Weekday/Flexibel. Pengguna memilih kota aktif dan mengisi interval nyata.
4. `is_active=false` lama tidak otomatis diubah: belum dapat dibedakan antara suspensi asli dan efek submit organisasi pada baseline. Reviewer/admin mengklasifikasikan data lama sebelum pemulihan terkontrol A1.
5. Konversi konfigurasi SQLite ke MySQL tidak memindahkan data. Ekspor/impor terkontrol perlu mempertahankan ID/FK/jumlah record/timestamp; belum diuji pada salinan data kerja nyata.
6. Dokumen organisasi lama masih mungkin ada di disk public. SF menghentikan upload/preview publik, tetapi tidak memindahkan/menghapus berkas. A1/A3 wajib backup, copy/checksum, migrasi metadata, lalu menutup akses publik sebelum penggunaan nyata.

Untuk backup MySQL, gunakan `mysqldump --single-transaction` dengan kredensial lokal melalui mekanisme aman, koordinasikan snapshot berkas, dan hindari DDL selama dump. Restore ke database terpisah, jangan menimpa kerja. Jangan commit dump, token, atau dokumen pribadi. Prosedur restore lengkap dan purge ledger menjadi integrasi A1/A3 yang belum tersedia.

## Verifikasi dan serah-terima

Bukti historis suite SF, build, pemeriksaan akses, perbaikan database lokal dan batas pengujian tersedia di [SF_HANDOFF.md](SF_HANDOFF.md). Angka hasil tes bukan jaminan seluruh modul aplikasi selesai. Review tim, uji visual, SMTP nyata dan integrasi modul yang masih tertunda harus tetap dicatat sebagai belum selesai.

README utama menjelaskan perilaku dan perintah yang dapat digunakan. Pembagian tugas, milestone, bukti per tahap dan blocker integrasi dipelihara di dokumen ini, brief anggota dan handoff masing-masing modul.

## Upgrade event dan pembayaran

Dua migration `2026_10_05_000001_extend_events_for_a2` dan `2026_10_05_000002_create_a2_payments` menambah kolom/tabel sesuai kontrak. `events.status` dipertahankan, event lama diberi unpublished/upcoming. Kota tidak dipetakan otomatis, akhir kegiatan kosong tetap null, jadwal posisi tidak dibuat dari dugaan. Data tersebut perlu diperiksa sebelum pengajuan/publikasi.

Kolom tanggal/jam lama tidak dihapus. `starts_at`/`ends_at` digabung dengan asumsi WIB yang ditetapkan kontrak dan dikonversi ke UTC; deadline lama disalin ke `legacy_registration_deadline` sebelum dikonversi. Jangan menerapkan konversi dua kali secara manual. Minimum skill yang sudah ada pada position_skills tetap menjadi acuan; tidak ada backfill level yang ditebak. FK pemilik event dan induk posisi kini restrict; histori transaksi/entitlement juga restrict.

Backup/restore dan migration telah diuji pada salinan database kerja terpisah, termasuk probe legacy approved dengan kota belum dipetakan dan akhir kosong. Salinan dan dump berisi data lokal tetap privat, tidak di-commit. Database kerja tidak dipakai oleh PHPUnit atau skrip konkurensi. Instruksi instalasi tetap di README utama.

Penambahan interface `AssessmentReadiness::publishedVersion(position)` dijabarkan dalam kontrak dan A2_HANDOFF.md; A4 perlu meninjau/bind implementasinya. Harga Demo Free/Standard hanya fixture lokal, bukan keputusan harga produk.

Setelah pemeriksaan salinan, kedua migration A2 juga sudah diterapkan pada database lokal pemilik workspace dengan backup baru dan pemeriksaan pelestarian data. Anggota lain tetap menjalankan migration pada database masing-masing; lihat bukti akhir A2_HANDOFF.md.

## Beranda publik informatif

Pengembangan beranda tetap milik A2. HomeController menggunakan schema/service publikasi existing untuk pratinjau enam event dengan pendaftaran terbuka dan kapasitas paket. welcome.blade.php menjelaskan produk, manfaat skill/waktu/lokasi, peran, cara mulai dan FAQ; event-card dipakai bersama katalog. Tidak menunggu A3/A4 untuk konten informatif, tetapi tetap menjelaskan status alur lamaran/assessment/matching yang belum terintegrasi. Lihat A2_HANDOFF untuk bukti tes/build/render dan batas review.

## Penyempurnaan tampilan auth

Permintaan pengguna setelah beranda: login/register memakai auth-layout dan auth-password-field, kartu pilihan peran hanya pada registrasi, copy Indonesia, state submit dan toggle password. Backend SF tidak berubah. A1 tetap memiliki pengembangan akun/admin berikutnya. Bukti pemeriksaan khusus di A2_HANDOFF; reset/verifikasi masih menggunakan guest-layout existing.

## Konfigurasi awal paket

DefaultPackageSeeder menambahkan Free, Standard dan Premium sesuai keputusan pengguna (lihat PRD/README) setelah UserSeeder, hanya local/testing. Nilai dapat diubah admin; seeding ulang memakai marker audit agar edit, rename dan nonaktif tidak di-reset. Paket demo tetap fixture terpisah. HomeController/package-card menampilkan harga/limit aktual. Tidak ada migration baru; integrasi assessment, lamaran dan attendance tetap milik A3/A4.
