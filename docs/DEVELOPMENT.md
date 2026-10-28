# Panduan pengembangan tim SkillMatch

## Daftar pekerjaan tersisa — pembaruan 28 Oktober 2026

Daftar ini merangkum temuan yang sudah diperiksa pada branch
`fix/volunteer-email-verification-flow` dari `89bb3e8` dan dependensi pada handoff.
Ini bukan audit kelengkapan seluruh tugas A1–A4; PIC perlu mencocokkan checklist
modul dengan kode terbaru sebelum menyatakan selesai. Catatan baseline/tahap lama
di bawah tidak mengubah implementasi yang sudah ada menjadi tugas membuat ulang.

| Pekerjaan | PIC | Status / langkah tersisa |
|---|---|---|
| Alur email Volunteer dan pesan error upload KTP | SF/A1, integrasi A3 | Selesai lokal dengan tes terarah; belum commit/push/PR/merge dan belum review tim. |
| Penanganan kegagalan SMTP pada reset password | A1 | Belum diselaraskan dengan penanganan gagal kirim pada verifikasi; implementasikan pesan gagal/retry dan tes tanpa membocorkan kredensial. |
| Pengiriman email nyata | A1 + pemilik konfigurasi lokal | SMTP terbaca dan mailer terinisialisasi; autentikasi SMTP, penerimaan inbox, dan klik tautan dari email nyata belum diuji. |
| Upload/preview KTP pada browser pengguna | A1 + pengguna lokal | Folder sementara PHP sudah dibuat dan uji tulis berhasil; tes upload/preview sintetis lulus. Upload ulang berkas pengguna dan konfirmasi preview masih menunggu. |
| Integrasi utilitas dokumen organisasi A3 ke A1 | A1 dengan A3 | Validator/download/Policy A3 tersedia; endpoint profil/preview existing masih memakai Storage langsung. Integrasi metadata, audit dan transisi berkas lama perlu dikerjakan sesuai kontrak, tanpa mengganti UI di luar persetujuan. |
| NotificationService, outbox, delivery/deduplikasi dan ActivityReadService | A4; hook oleh masing-masing PIC | Layanan belum tersedia pada checkout ini; hook/replay A2/A3 bukan bukti delivery/aktivitas lengkap. Screening dan matching yang sudah ada tidak dibuat ulang. |
| Backup/restore penuh dan kebijakan retensi publik | A1/A3 + reviewer/operator | Restore MySQL beserta file dan purge ledger dengan reviewer lain belum dibuktikan penuh; angka retensi demo belum menjadi kebijakan publik. |
| Review visual dan penerimaan alur lengkap | PIC terkait + reviewer tim | Review browser/mobile perubahan auth, perjalanan pengguna lintas modul, performa dan seluruh UAT belum dinyatakan lulus. Gunakan bukti per tahap pada handoff, bukan hanya hasil build. |
| Sisa checklist admin/event dan handoff modul | A1/A2 serta PIC masing-masing | Belum diaudit ulang secara menyeluruh pada perbaikan auth/upload ini. Status historis atau keberadaan berkas tidak cukup untuk menyatakan semua tugas selesai maupun belum dibuat. |

Setiap penyelesaian memperbarui status dan bukti pada tabel serta handoff modul.
Bedakan implementasi lokal, pengujian, integrasi, review dan publikasi Git;
persetujuan perubahan tampilan tetap mengikuti bagian berikut.

## Batas perubahan dan persetujuan pengguna — 28 Oktober 2026

Seluruh PIC mengikuti [AGENTS.md](../AGENTS.md#persetujuan-perubahan-hasil-kerja-dan-tampilan):
fitur, alur dan tampilan yang sudah dibangun dipertahankan. Permintaan pengguna
yang jelas memberi izin untuk lingkupnya; penggantian atau redesign di luar
lingkup diajukan dengan alasan, file/halaman terdampak dan perbandingan
sebelum-sesudah, lalu menunggu persetujuan sebelum diterapkan. Jangan meminta
ulang izin yang sudah ada. Catat dasar commit/diff, dampak komponen bersama,
tes dan review visual secara terpisah. Build bukan persetujuan tampilan dan
catatan baseline bukan perintah menghapus hasil baru. PRD, kontrak, desain,
baseline, UAT dan kelima brief SF/A1/A2/A3/A4 merujuk aturan yang sama.

## Diagnosis upload KTP kecil — 28 Oktober 2026

Pengguna melaporkan KTP 43 KB dari folder dokumen biasa tetap menghasilkan pesan
"failed to upload". Batas ukuran bukan penjelasan memadai untuk kasus tersebut.
Pemeriksaan PHP terminal: file_uploads aktif, limit 2M/8M, temp sistem tersedia
dan dapat dibuat berkas sementara. Laravel Storage default local menunjuk
storage/app/private; probe berkas sintetis berhasil write/read lalu dihapus.
Form sudah multipart; controller menyimpan file dan metadata organizer_documents,
preview owner/Admin men-stream file lewat route terotorisasi. Ini storage lokal,
bukan cloud; tidak memerlukan symlink publik. Integrasi penuh utilitas dokumen A3
ke schema/endpoint A1 tetap pekerjaan terpisah, tidak dinyatakan selesai di sini.

OrganizerProfileController kini memeriksa error upload PHP sebelum validasi umum
dan memberi pesan berbeda untuk transfer parsial, temp hilang, gagal tulis,
ekstensi PHP dan batas PHP. Log organizer.ktp.upload_failed hanya menyimpan kode
error dan limit PHP; tidak menyimpan isi/nama/path KTP atau kredensial.
Tidak mengubah disk, schema, route, otorisasi, data pengguna atau php.ini.

OrganizerKtpUploadTest: **5 tes / 42 assertion lulus** di MySQL skillmatch_testing
dengan disk tes terisolasi. Upload PDF sintetis sekitar 300 KB tersimpan dan
preview owner/Admin sama byte-nya; non-owner/Volunteer/guest dan URL publik
ditolak. Empat error transport ditolak sebelum profil/metadata/file ditulis.
Tes memakai UploadedFile fixture, bukan bukti unggahan browser pengguna berhasil.
Pint file tes, sintaks controller dan diff check lulus. Frontend tidak berubah
setelah build sukses pada perbaikan auth di bawah.

Tindak lanjut diagnosis: pengguna mendapat pesan folder sementara tidak tersedia
(UPLOAD_ERR_NO_TMP_DIR). Setelah pengguna mengatur upload_tmp_dir menjadi
D:/php-upload-temp, pemeriksaan PHP menunjukkan path itu terbaca tetapi folder
belum ada. Folder dibuat dengan izin pengguna, uji tulis berhasil dan probe
dihapus. Ini konfigurasi mesin lokal di luar Git; anggota lain harus menyiapkan
folder masing-masing. php.ini diedit pengguna, bukan disalin ke repository.
Upload ulang dan preview berkas pengguna lewat browser belum dikonfirmasi;
keberhasilan probe tidak otomatis berarti alur browser sudah lulus.
Branch tetap fix/volunteer-email-verification-flow, belum commit/push/merge.

## Perbaikan alur email Volunteer — 28 Oktober 2026

Branch `fix/volunteer-email-verification-flow`, dasar `89bb3e8` (main setelah PR #21).
Diagnosis: registrasi tidak lagi mengirim event Registered, tetapi commit `d618abc`
memulihkan hasVerifiedEmail pada ApplicationService. Akun baru aktif/unverified
menemui pesan 403 akun aktif, tanpa onboarding email yang konsisten.

Registrasi Volunteer kini mengirim event Registered dan menuju verification.notice;
login akun lama unverified menuju halaman yang sama. Intended ditahan sampai
verifikasi lalu diperiksa oleh resolver lokal/role existing. Profil tetap terbuka,
sidebar memberi pengingat, detail event memberi CTA verifikasi melalui events.join
agar kembali ke event. POST lamaran tidak diulang otomatis. Middleware verified
pada store/submit lamaran dan upload/hapus dokumen memberikan redirect browser;
service tetap memeriksa ulang akun/email di bawah lock dengan pesan yang sesuai.
Transport email gagal menyisakan akun/sesi dan memberi opsi kirim ulang. Halaman
verifikasi memakai auth-layout dengan area gulir opsional agar konten panjang
dapat dijangkau. Pengecualian Organizer A3 dan default Admin tetap dipertahankan.

Tanpa schema, migration, dependency, guard atau nama route baru. Sebelum tes,
preflight memastikan MySQL `skillmatch_testing`, APP_ENV testing, config uncached,
tanpa DB URL dan berbeda dari `db_skillmatch`; migration pending diterapkan hanya
ke database tes dengan `php artisan migrate --env=testing`. Tidak mereset database
atau mengubah akun/data/config database kerja. Akun yang dilaporkan tetap harus
memverifikasi email lewat tautan miliknya, tanpa update verified massal/manual.

Bukti otomatis:
- RegistrationTest, EmailVerificationTest, AuthenticationTest,
  VolunteerVerificationFlowTest, A3ApplicationTest dan A3ApplicationDocumentsTest:
  **43 tes / 281 assertion lulus** pada MySQL khusus tes.
- Dua tes `A3ExecutionTest --filter=verified_email`: **2 tes / 11 assertion lulus**;
  Organizer approved tanpa verified tetap dapat seleksi, pending/inactive ditolak.
- Pint pada file PHP terkait, `php artisan view:cache`, `git diff --check` dan
  `npm run build` berhasil. Build sandbox awal ditolak spawn EPERM; berhasil setelah
  izin eksekusi di luar sandbox. Tidak mengubah manifest/lockfile.

Tes mencakup notifikasi registrasi/kirim ulang (fake), gagal transport/retry,
throttle, akun aktif/nonaktif, guest/lintas role/pemilik, intended eksternal/lintas
role, signed link valid/expired/hash/id, tanpa replay POST, draft setelah verified
dan replay tanpa duplikasi, serta blokir submit/dokumen sebelum verified. Fixture
assessment memakai kontrak tes existing, bukan bukti integrasi A4 lengkap.
SMTP/inbox nyata, review visual browser/mobile dan UAT penuh belum diuji.
Saat tes awal, mailer lokal log menulis tautan ke storage/logs/laravel.log.
Pengguna kemudian mengganti .env ke SMTP: pemeriksaan runtime menunjukkan mailer
smtp, port 587, kredensial/pengirim terisi, config uncached dan inisialisasi mailer
berhasil. Tidak mencatat nilai kredensial. Koneksi/autentikasi SMTP dan penerimaan
inbox belum diuji; inisialisasi mailer tidak membuktikan pengiriman. Penanganan
kegagalan transport pada reset password masih perlu diselaraskan; perbaikan di
sini menangani registrasi/verifikasi. Gunakan Kirim ulang pada /verify-email untuk
akun lama, jangan membagikan token; setup SMTP/APP_URL mengikuti README.

README, PRD, kontrak, desain, brief A1/A3, UAT dan handoff A3 diselaraskan.
Perubahan tahap ini belum commit/push/merge; review tim belum dilakukan.

## Kelanjutan A3 — 28 Oktober 2026

Branch `feature/a3-applications-attendance` diperbarui fast-forward dari `ca3afbf` ke
dasar tim `34ae483` (main saat mulai; SF/A2 dan assessment peserta A4 sudah tersedia).
Perubahan lokal .gitignore dipertahankan di luar commit. Tidak membuat ulang fondasi.
Implementasi seleksi/attendance/riwayat/retensi, withdrawal dan cancellation ada pada
branch ini. Status, perintah, bukti dan batas integrasi terbaru: [A3_HANDOFF](A3_HANDOFF.md).

Dependency A4 screening, matching dan attempt telah dipakai nyata. Tambahan terarah
AssessmentService.cancelLocked digunakan A3 untuk pembatalan atomik tanpa mengambil
ulang lock Volunteer setelah event. NotificationService/outbox/ActivityReadService
belum tersedia; A3 memakai adapter A2Dependencies dan replay audit, tanpa outbox baru.
Dokumen `docs/A4_HANDOFF.md` tidak ditemukan; pemeriksaan memakai handoff assessment,
screening dan matching yang tersedia. A1 masih perlu mengintegrasikan utilitas/Policy
organisasi ke schema dan endpoint miliknya; migrasi dokumen organisasi lama tidak
dikerjakan otomatis oleh A3.

Lock seleksi/withdrawal: Volunteer → event → posisi bila perlu → application → attendance/
attempt. Jalur cancellation A2 sudah memegang owner/event; close-selection, attendance
dan completion memegang event terlebih dahulu dan **tidak** mengambil Volunteer
sesudahnya. Semua berserial pada event/application; race lintas event acceptance
berserial pada Volunteer. Deadlock ditangani retry transaksi maksimal tiga kali.

Migration hanya pada database tes MySQL 8.4.3/InnoDB. Database kerja belum dimigrasi;
backup/periksa target sebelum memakai halaman baru. Retensi demo 7/90 hari dapat
dikonfigurasi, cleanup publik opt-in. Dokumen publik/paket/payment/aktivitas milik
anggota lain tidak diganti. Layout sidebar terbaru existing dipertahankan sesuai
pelestarian desain pengguna; migrasi shell global bukan pekerjaan A3.

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
