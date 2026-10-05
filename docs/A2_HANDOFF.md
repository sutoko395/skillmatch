# Serah-terima A2 - event, katalog dan pembayaran

Tanggal: 5 Oktober 2026 (WIB). Status: implementasi A2 tersedia untuk ditinjau; integrasi assessment, cancellation, notifikasi, pengujian browser dan transaksi Sandbox nyata belum dinyatakan selesai.

## Dasar Git dan tahapan

- Branch: `feature/a2-events-payment`, sudah tersedia saat mulai; working tree awal bersih.
- Dasar: `3b9ba05` (main/origin/main lokal). Ancestor `7eab4e1` adalah merge SF; pemisahan README/DEVELOPMENT sudah di-commit sebelum A2. Tidak melakukan fetch, reset atau menebak branch remote.
- `8783ac6`: schema/model event, jadwal, paket/order/payment ledger/entitlement.
- `90785b7`: layanan domain, Policy, adapter Sandbox, snapshot dan titik integrasi.
- `46ad09c`: UI Organizer, katalog, admin paket/transaksi dan integrasi route.
- `f282259`: tes, fixture demo, pemeriksaan konkurensi dan perintah replay notifikasi.
- `b43cb69`: penanganan status Snap belum tersedia, tes regresi, dan alat checkout/sync Sandbox nyata khusus database tes.
- `e0731df`: beranda informatif lengkap, HomeController, event-card bersama katalog, serta regresi beranda/auth/event.
- Commit dokumentasi setelah kedua tahap tersebut tercatat pada log branch; gunakan tip A2 saat review, bukan commit tahap antara.
- Tidak melakukan push, PR, merge atau deployment pada pekerjaan A2 ini. Review tim belum dilakukan.

## Hasil per tahap

| Tahap | Hasil dan batas |
|---|---|
| Schema | Migration tambahan, field UTC/legacy, FK histori restrict, unique order reference/receipt/entitlement, CHECK jadwal positif. |
| Event/posisi | Draft/revisi, simpan-baca WIB/UTC, Policy pemilik, skill/level/wajib, jadwal, syarat manual/dokumen, kuota, validasi deadline dan penguncian konfigurasi. Koreksi judul/deskripsi published memakai endpoint terpisah dengan allowlist dan audit, tanpa mengubah aturan seleksi. Hapus event terbatas draft awal kosong yang belum pernah diajukan/tanpa transaksi; histori tetap terlindungi. Posisi draft dapat dihapus secara terotorisasi. |
| Pengajuan/publikasi | Pemeriksaan konfigurasi dan assessment, state pending, approval baseline terintegrasi, Free entitlement sekali, publikasi idempoten. Validator assessment belum tersedia di runtime sehingga alur penuh ditahan. |
| Website publik | Hero/CTA/footer dipertahankan, navbar katalog/Event Saya, pencarian judul, filter kota/kategori/tanggal, pagination, detail posisi/syarat/WIB. Draft/unpublished/suspended/cancelled/completed serta Organizer nonaktif tidak tampil. CTA lamaran menunggu route A3. |
| Paket/order | Admin mengatur harga/limit/nonaktif, snapshot pilihan untuk moderasi disalin menjadi snapshot order, satu order pending/paid/review per event melalui lock, batas posisi/durasi dan penghitung lamaran. Tidak ada edit paid manual. |
| Sandbox | Hosted Snap checkout, signature dan GET status server, validasi ID/amount/IDR, status mapping, retry/dedupe, encrypted checkout URL, transaksi pendek tanpa HTTP gateway di dalamnya. Tes deterministik memakai fake HTTP; konfigurasi Sandbox sudah tersedia, hasil percobaan nyata dicatat di bukti akhir. |
| Pembatalan/notifikasi | Service A2 memanggil layanan A3/A4. Cancellation menolak jika dependensi belum ada. Payment tetap dicatat ketika notifikasi belum tersedia; payment ledger/audit dapat direplay ke outbox A4 tanpa membuat outbox tandingan. |

## Migration dan data

1. `2026_10_05_000001_extend_events_for_a2.php`: tiga dimensi status, waktu UTC dan legacy deadline, kota nullable, snapshot paket/revision, flag posisi, kind/document_type, position_schedules, FK restrict.
2. `2026_10_05_000002_create_a2_payments.php`: packages, orders, payment_events, event_entitlements. Entitlement unik per event/order; receipt unik berdasarkan hash respons gateway yang telah diverifikasi.

Tidak mengubah migration SF/baseline atau membuat tabel positions tandingan. Semua event lama tetap unpublished. Kota tidak dipetakan otomatis; akhir kosong tidak diisi durasi buatan. Approved legacy yang belum lengkap memerlukan keputusan koreksi/revisi bersama A1; jangan memublikasikannya dengan update SQL sebagai bypass.

Backup lokal terbaru saat pemeriksaan salinan: `storage/app/private/a2-before-upgrade-20261005_103247.sql`; restore ke `skillmatch_a2_upgrade_20261005_103247`. Perbandingan kolom lama pada salinan cocok; deadline asli dipertahankan lalu dikonversi WIB ke UTC. Probe sintetis legacy approved membuktikan starts_at UTC, ends_at/city_id tetap null, publication_status tetap unpublished. Dump/salinan tidak masuk Git. Database kerja belum dimigrasikan pada tahap pemeriksaan ini.

## Route, Policy dan interface

- `routes/public.php`: events.index/show dan events.join (kembali dari login Volunteer ke detail yang masih publik); callback POST payments.midtrans.notification. Hanya callback dikecualikan CSRF, wajib signature dan GET status server.
- `routes/a2.php`: organizer.events.*, organizer.events.positions.* (index/show mengarah detail event, create/store/edit/update/destroy), organizer.packages.select/update, organizer.orders.store/show/checkout/sync; admin.packages.* (index/create/store/edit/update), admin.orders.index/show/sync.
- Guard web, email/status/role SF dipakai ulang. EventPolicy memisahkan owner update dari admin manage; OrderPolicy hanya pemilik/admin; PackagePolicy admin aktif/verified. Nested posisi diperiksa terhadap parent event.
- EventService mengunci actor/owner lalu event, memvalidasi dan menyimpan transaksi. EventConfiguration memeriksa publikasi; PositionSnapshotService.build mengembalikan DTO kontrak termasuk versi assessment dan match-v1.4.
- EventPublicationService.publish/suspend/resume/cancel mempertahankan published_at pertama dan menolak state terminal/syarat tak lengkap. Suspend/resume adalah interface untuk A1; UI moderasi lanjut tetap A1.
- PackageService mengelola paket dan pilihan snapshot. Paket dibekukan setelah pengajuan; perubahan admin tidak mengubah quote yang sudah disetujui atau pembelian lama. Revisi sebelum pengajuan dapat memilih ulang paket aktif.
- PaymentService.createOrder/checkout/sync/reconcile memakai VerifiedGatewayResult internal dari MidtransGateway; jangan membangun DTO ini dari input browser. Order_ref dibuat server, amount IDR integer dari snapshot. Tidak ada server key, respons gateway mentah atau signature disimpan pada ledger/audit.
- EntitlementService.consumeApplication(event) dipanggil A3 **dalam transaksi submit yang sama**, setelah pemeriksaan submitted_at/unique application dan urutan lock Volunteer -> event -> entitlement. Penghitung tidak dikurangi ketika withdrawn; rollback submit membatalkan increment. Uji reserve ini bukan bukti layanan submit A3 sudah terintegrasi.
- AuditService menambah allowlist angka manfaat/harga paket saja; tidak menerima seluruh request. Audit dan event/payment state commit/rollback bersama.

## Kontrak dependensi dan reviewer

| Pemilik | Kebutuhan dan pekerjaan lanjutan |
|---|---|
| A1 | Review integrasi EventVerificationController: relasi positionSkills.skill, lock keputusan, pemeriksaan konfigurasi, audit, Free activation dan pemanggilan publikasi. Lanjutkan seluruh tugas verifikasi organisasi/moderasi/analitik/notifikasi A1; bukan dinyatakan selesai oleh perubahan ini. |
| A3 | EventCancellationService.cancelApplications(event,actor,reason) harus transaksional dengan cancellation event, menghentikan lamaran/attempt melalui A4 dan menaati lock bersama. Integrasikan snapshot, eligibility dan consumeApplication; route CTA ditautkan hanya saat tersedia. |
| A4 | Bind App\Contracts\AssessmentReadiness dengan publishedVersion(EventPosition): int; validasi soal/opsi/kunci/durasi/versi beku di pemilik modul. Tidak ada implementasi sukses palsu di provider. Implementasikan NotificationService.enqueue dengan transaksi/outbox/dedupe; gunakan event_key yang sama saat replay. |
| Tim | Review schema/interface/lock, instalasi MySQL kosong dari README, uji visual 360/768/1280, SMTP dan UAT lintas modul. Reviewer belum ditetapkan. |

Notifikasi event published/cancelled direplay dari audit ledger dengan key event_type:event_id:revision:recipient_id. Pembayaran memakai payment.verified:payment_event_id:1:recipient_id dan notified_at. Callback ulang tidak menggandakan receipt. Setelah A4 tersedia jalankan `php artisan a2:retry-notifications`; outbox/worker tetap milik A4. Tidak menganggap notifikasi terkirim sebelum implementasi/delivery tersedia.

## Mapping gateway

- settlement tanpa fraud bermasalah, atau capture dengan fraud accept: paid.
- pending: pending; deny/failure: failed; expire: expired; cancel: cancelled.
- challenge/refund/partial_refund/status lain: review_required; tidak otomatis refund.
- Pesan pending/failed/expired terlambat tidak menurunkan paid. Refund/ambigu setelah paid mempertahankan paid_at, menandai review dan menangguhkan publikasi. Pembayaran pertama yang challenge dapat dilanjutkan ketika GET status kemudian mengonfirmasi settlement/accept.
- Pembayaran valid pada cancelled/completed atau order kedua yang terlambat tetap tercatat dan perlu tindak lanjut, tanpa entitlement/publication tambahan. Browser redirect hanya membaca status.
- Checkout timeout menggunakan reference yang sama; claim lima menit mencegah request serentak. Jika gateway sudah menerima request tetapi URL tidak tersimpan, sinkronkan status; pemulihan URL Snap yang hilang memerlukan pemeriksaan gateway dan tidak boleh membuat order baru secara buta.

Sumber resmi diverifikasi 5 Oktober 2026: [Snap integration](https://docs.midtrans.com/docs/snap-snap-integration-guide), [HTTP notification](https://docs.midtrans.com/docs/https-notification-webhooks), [GET transaction status](https://docs.midtrans.com/reference/get-transaction-status). Gateway melalui Laravel HTTP client, tanpa SDK baru.

## Verifikasi dan batas bukti

- MySQL 8.0.30/InnoDB, database tes `skillmatch_testing`, PHP 8.4.24. Lockfile tetap Laravel 13.33.0, Tailwind 3.4.19, Alpine 3.17.4, Vite 8.3.1.
- Migration kedua tahap berhasil di MySQL tes dan salinan database kerja; tidak ada migrate:fresh/RefreshDatabase/reset database kerja.
- A2EventTest/A2PaymentTest mencakup akses role/status/pemilik, nested ID, validasi/UTC, snapshot/freeze, katalog, pengajuan tanpa assessment, moderasi, intended lokal, fake callback/status gateway, duplicate/late payment, refund/challenge, cancellation mock, audit rollback dan notification replay mock.
- `tools/a2-concurrency-check.php --env=testing`: dua proses create order -> satu order; dua reconcile -> satu receipt/entitlement; dua publish -> satu audit/timestamp; dua reserve lamaran batas satu -> satu sukses/satu ditolak. Fixture sintetis tetap di database tes karena audit append-only. Tidak membuktikan konkurensi selection A3.
- Build Vite berhasil. Percobaan dalam sandbox gagal spawn EPERM, kemudian build di luar sandbox berhasil. Blade compile dan route list berhasil. Uji browser visual/keyboard belum dilakukan karena tool browser/Playwright tidak tersedia.
- Transaksi Sandbox nyata, callback HTTPS publik, integrasi nyata assessment/cancellation/notifikasi dan UAT lintas modul: **belum diuji**. Fake HTTP/collaborator adalah bukti bagian A2 saja.
- Hasil suite final dan commit UI/tes dicatat pada bagian bukti akhir di bawah. Checklist UAT pusat tetap belum lulus penuh.

## Menjalankan dan demonstrasi

1. Ikuti README; backup dan migrate database aplikasi setelah meninjau konversi WIB. Buat/cek database tes terpisah. Jangan menggunakan PHPUnit pada database aplikasi.
2. Seed dasar local/testing. Opsional A2DemoSeeder menambah paket Demo dan tiga event unpublished, termasuk approved-unpublished berbayar dan jadwal overlap/kuota satu. Assessment tidak disediakan seeder A2.
3. Login Organizer aktif, buka Event Saya, buat/edit draft dan posisi, pilih paket, periksa bahwa pengajuan ditolak dengan pesan jelas selama validator assessment belum tersedia.
4. Login Admin, kelola paket; perubahan harga/nonaktif tidak mengubah snapshot event/order lama. UI transaksi hanya baca/sync gateway.
5. Konfigurasi Sandbox lokal tanpa membagikan secret. Pada fixture approved berbayar, buat order -> siapkan checkout -> bayar via halaman Sandbox -> callback atau sinkron server. Pastikan entitlement sekali dan publikasi tetap menunggu assessment yang sah.
6. Jalankan PHPUnit, skrip konkurensi, build, lalu review kontrak dengan A1/A3/A4 sebelum menyatakan integrasi selesai. Panduan instalasi ringkas tetap di README utama, pembagian tugas di DEVELOPMENT.md.

## Bukti akhir pemeriksaan lokal

- Suite final: **86 tes / 480 assertion lulus** pada MySQL `skillmatch_testing`. Di dalamnya terdapat **32 tes A2**; pengujian HTTP gateway dan kolaborator A3/A4 tetap menggunakan fake/mock.
- Empat skenario dua proses pada skrip konkurensi lulus setelah formatting akhir. Temuan awal pembacaan snapshot lama pada reconcile diperbaiki dengan locking read untuk receipt/entitlement; verifikasi ulang membuktikan tidak ada aktivasi/receipt ganda.
- A2DemoSeeder berhasil dijalankan dua kali pada database tes. Fixture tetap berlabel demo dan tidak dipublikasikan; akun demo dasar yang sudah ada dipakai ulang.
- Build frontend final sukses: Vite 8.3.1, CSS 55.88 kB dan JS 55.09 kB; manifest dibuat. Tidak ada upgrade dependensi.
- Setelah salinan lulus, migration diterapkan juga ke `db_skillmatch` lokal untuk menjalankan modul, dengan backup baru `storage/app/private/a2-working-before-20261005_105749.sql`. Hash data akun/profil/master dan kolom lama cocok sebelum/sesudah; hanya transformasi deadline yang memang ditetapkan migration diperiksa terpisah. Tidak ada seed fixture A2 ke database kerja pada langkah ini.
- Pemeriksaan HTTP kernel dengan akun existing aktif/verified: `/events`, `/organizer/events`, `/organizer/events/create`, `/admin/packages`, `/admin/orders` dan `/volunteer/aktivitas` semuanya **200**. Ini render server, bukan pemeriksaan tampilan browser.
- Konfigurasi server key Sandbox sudah terbaca pada pemeriksaan lanjutan, mode produksi false. Uji checkout/status nyata dilakukan terpisah pada fixture database tes; hasil akhir dicatat di bawah. Callback HTTPS publik belum diuji.
- Checkout Snap Sandbox nyata berhasil untuk order lokal tes **108** dengan fixture `Demo A2 - Berbayar approved` di `skillmatch_testing`. Koneksi pertama terhalang sandbox eksekusi; setelah izin jaringan dan masa pengunci lima menit, reference yang sama berhasil digunakan. Key/token tidak dicetak atau masuk Git. Tautan checkout tersimpan hanya pada `storage/app/private/a2-sandbox-checkout.html`.
- Penyelesaian simulasi oleh pengguna dan verifikasi status paid/entitlement nyata masih tertunda. Checkout berhasil bukan bukti pembayaran selesai. Jalankan `php tools/a2-sandbox-check.php --env=testing --sync=108` setelah simulasi, dengan target MySQL tes, lalu ulangi untuk memeriksa receipt/entitlement tetap satu. Script tidak memasang validator assessment palsu; event tetap unpublished sampai syarat A4 terpenuhi.

## Pengembangan lanjutan beranda publik

Beranda kini menjelaskan tujuan produk, prinsip skill/waktu/lokasi, langkah Volunteer/Organizer, FAQ, pencarian ke katalog, CTA auth/akun, dan pratinjau event aktual. Hero indigo dengan ilustrasi SVG komunitas meneruskan identitas dan judul baseline. FAQ memakai details/summary native, tanpa tambahan runtime JavaScript atau dependensi.

GET `/` tetap `home`, kini HomeController, dengan maksimal enam event publiclyVisible yang periode pendaftarannya terbuka dan kapasitas paket masih ada. Urutan starts_at lalu ID; relasi dan positions_count dimuat bersama. Blade `event-card` milik A2 dipakai juga oleh katalog. Keadaan kosong tampil informatif; lamaran/assessment/matching belum diklaim berfungsi. Tidak ada migration atau perubahan data kerja untuk pengembangan beranda ini.

Bukti khusus perubahan beranda:

- **43 tes / 312 assertion lulus**: HomeTest (3 kasus), ExampleTest, FoundationAccessTest dan A2EventTest, pada MySQL skillmatch_testing. Pemeriksaan mencakup event tersembunyi, periode, kapasitas, pemilik nonaktif, batas enam, urutan, serta regresi auth/catalog. Tes beranda menggunakan waktu sintetis 2027 untuk mengisolasi fixture lama dan tetap dalam rentang TIMESTAMP MySQL. Suite A2 sebelumnya 86 tes merupakan bukti tahap sebelum tambahan HomeTest; suite penuh tidak dijalankan ulang karena perubahan terarah.
- Build Vite sukses, CSS 59.07 kB / JS 55.09 kB; Blade cache berhasil; Pint dan git diff --check lulus.
- Chrome headless lokal diperiksa memakai viewport eksplisit **360/768/1280 px**: tidak ada overflow horizontal. Screenshot seluruh halaman pada keadaan event kosong tersimpan privat di storage/app/private/a2-home-360.png, a2-home-768.png, a2-home-1280.png. Screenshot 360 awal tanpa emulasi viewport terpotong dan tidak digunakan sebagai bukti mobile; pemeriksaan berikutnya memakai Chrome DevTools Protocol.
- Fokus summary terverifikasi; **Space membuka FAQ**; seluruh anchor section memiliki target. Pemeriksaan ini bukan audit aksesibilitas lengkap atau UAT browser seluruh modul. Tampilan kartu berisi event diuji melalui render MySQL fixture; pemeriksaan visual browser memakai data lokal dengan keadaan kosong.
- PRD, design, kontrak, brief A2, UAT, README dan DEVELOPMENT diperbarui. Review tim dan integrasi A3/A4/payment final tetap tertunda sesuai bagian sebelumnya. Tidak melakukan push, PR, merge atau deployment.

## Penyempurnaan login dan registrasi

Commit implementasi: `07f8fe0` pada feature/a2-events-payment. Commit dokumentasi berikutnya tersedia di log branch; belum push/PR/review/merge/deploy.

Permintaan pengguna memperbarui UI masuk/daftar: panel editorial indigo desktop, logo/navigation SkillMatch, form responsif, label Indonesia, kartu role registrasi, toggle password dan status submit. Komponen baru auth-layout/auth-password-field memuat Vite/Alpine sekali. Backend, route, guard, Policy, migration dan data kerja tidak berubah. Form login tanpa pemilih role; registrasi hanya Volunteer/Organizer dan mempertahankan old input non-password. A1 melanjutkan modul akun dengan auth SF existing.

Bukti: **40 tes / 183 assertion lulus** (tests/Feature/Auth dan FoundationAccessTest), Blade cache dan build sukses (CSS 61.97 kB / JS 55.09 kB). Chrome headless diperiksa pada login/register dengan viewport **360 dan 1280 px**, tanpa overflow horizontal. Toggle kata sandi berfungsi dua arah; jumlah role pada login nol dan pada registrasi dua. Screenshot tersimpan privat di storage/app/private/a2-auth-*.png. Tidak menjalankan suite penuh ulang; SMTP, aksesibilitas lengkap, serta review tim tetap tertunda. Reset/verifikasi memakai guest-layout lama.

## Koreksi navigasi admin

Kelola Paket dan Transaksi Sandbox dipindahkan dari navigasi atas konten ke sidebar admin, sebelum Lihat Website Utama. Ikon, state aktif wildcard dan aria-current mengikuti layout existing; drawer mobile memakai sidebar yang sama. Route/Policy/backend tidak berubah. Blade cache dan build berhasil; A2EventTest lulus 17 tes / 121 assertion. Review visual drawer mobile perubahan ini belum dilakukan.
