# UAT dan kriteria selesai lintas anggota

## Regresi navigasi dengan cache Blade kosong - 10 Oktober 2026

Pada server pengembangan dengan Vite, kosongkan cache view saja (php artisan view:clear; jangan reset database), lalu klik tautan sekali dari beranda ke katalog/login/register dan antarhalaman panel Admin/Organizer/Volunteer. Klik harus mencapai URL tujuan tanpa full-reload Vite dari storage/framework/views, tanpa net::ERR_ABORTED pada request Document dan tanpa kembali ke halaman asal. Ulangi setelah cache terbentuk. Penulisan cache/runtime tidak memicu reload; perubahan sumber Blade/CSS/JS tetap diperbarui oleh dev server. Build dengan sumber Blade tanpa cache harus berhasil. Bukti Chrome desktop dan tes MySQL terisolasi dicatat di DEVELOPMENT; seluruh tombol submit, browser pengguna dan UAT lintas modul belum otomatis dinyatakan lulus. Pengujian dropdown/font berikut merupakan kasus terpisah.

## Navigasi dan font lambat semua role - 10 Oktober 2026

UAT navigasi/responsive: pada Admin, Organizer dan Volunteer, tunda/blokir stylesheet penyedia font eksternal dan buka halaman baru dengan cache dingin. Layout tidak meminta stylesheet tersebut. Tunda berkas font lokal; dropdown harus tetap membuka dengan klik pertama karena Alpine tidak bergantung pada selesai unduhan font. Uji tautan sidebar menuju halaman nyata, keyboard fokus, tombol drawer mobile lalu satu klik item, serta login/register/beranda dengan fallback font. Pastikan file WOFF2 400/500/600/700 dan lisensi ikut distribusi. Bukti browser terisolasi dan tes rendering ada di DEVELOPMENT; bukan klaim seluruh UAT atau profil browser pengguna telah diuji.

## Regresi pembayaran pending dan tombol kembali - 10 Oktober 2026

Tambahan navigasi: kunjungan detail biasa tidak mengaktifkan sync otomatis; finish URL checkout baru dengan check_payment=1 hanya memicu POST verifikasi, tidak menandai paid. Simulasikan respons gateway terlambat lalu klik menu sekali: request browser dibatalkan dan hasil lama tidak memuat ulang/membatalkan tujuan navigasi. Modifier/new tab/download/anchor tidak dianggap perpindahan halaman saat ini. Periksa navigasi Dashboard/Event Saya/Profil desktop serta item menu mobile setelah drawer dibuka. Timeout server status dibatasi lima detik; timeout tidak menghasilkan paid/entitlement palsu. Bukti dan batas reproduksi browser pengguna dicatat di DEVELOPMENT.

UAT-10/24/25/26/34: selesaikan pembayaran Sandbox, kembali ke halaman order dan periksa satu POST otomatis ber-CSRF. Status harus mengikuti Get Status server, bukan query redirect. Gateway pending tetap menunggu; settlement/capture valid mencatat paid dan aktivasi sekali, lalu halaman diperbarui. Uji Sinkronkan status tanpa JavaScript (button type submit), klik ganda, timeout, sesi berakhir, throttle, nominal/ref/currency palsu serta owner/role/akun nonaktif. Admin tetap dapat sinkron melalui form POST. Kembali ke event menuju detail event yang benar, mempunyai fokus keyboard dan gaya tombol konsisten. Tes MySQL/mock gateway dan tes JS dicatat di DEVELOPMENT; uji browser visual, webhook publik dan konkurensi dua proses tetap terpisah dari verifikasi Get Status Sandbox nyata.

## Paket dahulu dan warning durasi - 10 Oktober 2026

UAT-07/10/23/34: popup Event Saya dan halaman buat/edit mewajibkan paket lebih dahulu. Uji tepat 7/30/60 hari berhasil dan lebih satu menit ditolak; ubah paket/durasi mengubah warning langsung dan tombol simpan. Pastikan batas dari master/snapshot, timezone browser tidak mengubah WIB, pilihan/tanggal dipertahankan setelah validasi gagal. Endpoint menolak package_id hilang/tidak ada/nonaktif, snapshot/harga palsu, rentang terlalu panjang dan penurunan kuota posisi yang tidak mencukupi; event/audit rollback bersama. Create berbayar tetap draft/unpublished tanpa order/entitlement; bayar hanya setelah approved. Edit ID paket sama tidak mengikuti perubahan master; draft legacy tanpa paket harus memilih. Published/transaksi dan owner lain tetap ditolak. Bukti tes/build dicatat di DEVELOPMENT; mock payment dan tes state JavaScript tidak meluluskan Sandbox end-to-end atau review browser.

## Regresi kota event - 10 Oktober 2026

Uji kedua jalur pembuatan (popup Event Saya dan halaman Buat Event): keduanya mengirim city_id, menyembunyikan kota nonaktif, dan menyimpan relasi master. Kegagalan validasi membuka kembali popup dan mempertahankan kota yang dipilih; tidak membuat event parsial.

Untuk UAT-06/07/09/19: simpan dan baca ulang city_id/relasi cityRecord, ubah kota melalui master aktif, abaikan teks kota palsu dari request, tolak ID hilang/tidak ada/nonaktif tanpa mengubah event/audit. Draft lama tanpa city_id menampilkan petunjuk koreksi; pengajuan gagal dengan pesan validasi yang terlihat, bukan HTTP 500. Assessment published tetap menjadi syarat pengajuan. Bukti tes terarah dicatat di docs/DEVELOPMENT.md; seluruh UAT/manual lintas modul tidak otomatis lulus.

## Assessment Organizer — tambahan 10 Oktober 2026

Uji draft tanpa soal/durasi dan draft parsial berhasil tetapi tidak membuka readiness; publikasi 1/50 soal, 1/120 menit valid; 0/51 soal, durasi 0/121 dan opsi selain tepat A–D ditolak. Soal/opsi kosong atau tanpa satu kunci ditolak. Preview tidak menulis database, confirmed publish mengunci set, edit setelah publish ditolak dan replay tidak menggandakan audit. Uji stale revision, dua tab, rollback, akses Organizer lain/Volunteer/Admin/guest/nonaktif dan event frozen melalui endpoint. Readiness nyata membuka pengajuan/publikasi hanya jika syarat A2 lainnya terpenuhi; snapshot assessment_version tetap immutable. Posisi draft assessment dapat dihapus; published tidak. UI diuji 360/768/1280, keyboard, error/old input/loading dan konfirmasi. Tes validasi tidak berarti UAT timer/attempt/screening/matching selesai; bukti tiap tahap dicatat terpisah.

## Tambahan UAT jadwal posisi — 10 Oktober 2026

Untuk UAT-07/10/19/32/34: pastikan posisi baru default mengikuti event dengan interval dari server; interval request palsu diabaikan pada mode ini. Mode khusus harus berdurasi positif, dalam rentang event dan setelah deadline. Uji event satu hari (input jam) dan beberapa hari (input tanggal/jam), kegagalan simpan mempertahankan pilihan, serta endpoint lintas pemilik/role. Perubahan event editable menyelaraskan hanya posisi mengikuti event; bila jadwal khusus tidak valid, seluruh transaksi rollback. Migration mempertahankan interval existing sebagai khusus. Published tetap terkunci dan snapshot submitted tidak berubah. Bukti otomatis dan visual harus dicatat terpisah; penambahan ini tidak berarti UAT sudah lulus.

Versi 1.4.2 • ID di bawah khusus paket ini; menggantikan daftar penerimaan lama bila ada perbedaan scanner/DB. P0 wajib lulus. Kolom hasil awal semuanya **Belum diuji** karena paket ini hanya spesifikasi.

Siapkan fixture pada kontrak integrasi. Bukti tiap kasus: tanggal, environment/DB, langkah atau nama tes, hasil aktual, screenshot/log aman, commit, PIC dan penguji anggota lain. Tidak menaruh secret atau CV asli pada bukti.

| ID | Skenario | Hasil yang harus dibuktikan | PIC | Status |
|---|---|---|---|---|
| UAT-01 | Registrasi, verifikasi, login/reset/logout | Registrasi role umum saja; email unik; email belum verified tidak melamar/publish; reset token berlaku sesuai konfigurasi. | SF; integrasi A2/A3 | Belum diuji |
| UAT-02 | Login bersama semua role dan intended URL | Satu /login, guard web, tanpa pemilih role; redirect sesuai role/status; intended eksternal/tidak berizin dibuang; non-admin ditolak 403 dari /admin/*; semua keluar melalui POST /logout dengan sesi tidak berlaku lagi. | SF | Belum diuji |
| UAT-03 | Akun dinonaktifkan saat masih memiliki sesi | Request berikutnya terproteksi ditolak; pending organisasi masih bisa melengkapi profil jika akun aktif. | SF middleware; A1 UI | Belum diuji |
| UAT-04 | Profil lama hanya Weekend, skill duplikat, kota tak dikenal | Butuh interval nyata; skill duplikat ditolak; city mapping tidak menebak; form membantu koreksi. | SF | Belum diuji |
| UAT-05 | Upload organisasi dan edit profil | Berkas privat bisa ditinjau Admin; perubahan kontak biasa tidak wajib upload semua atau reset tanpa alasan. | A1/A3 | Belum diuji |
| UAT-06 | Master direferensikan dinonaktifkan | Riwayat tetap dapat dibaca, tidak dapat dipilih untuk konfigurasi baru; hard delete berbahaya ditutup. | A1 | Belum diuji |
| UAT-07 | Event draft/revisi/approve | Data tanggal/kuota/assessment valid; catatan revisi tersimpan; detail relasi posisi bekerja. | A2/A1/A4 | Belum diuji |
| UAT-08 | Event approved belum memiliki entitlement | Tidak publik; free aktivasi sekali setelah approval; paid butuh verifikasi pembayaran. | A2 | Belum diuji |
| UAT-09 | Katalog dan manipulasi ID event privat | Filter/pagination benar; draft/unpublished/suspended tidak terbuka publik lewat URL langsung. | A2 | Belum diuji |
| UAT-10 | Ubah aturan event published | Perubahan substantive ditolak; teks ringan diaudit; snapshot pelamar tidak berubah. | A2 | Belum diuji |
| UAT-11 | Submit dua kali, dua posisi satu event, atau terlambat | Satu lamaran per Volunteer/event; submission idempoten; deadline timestamp server; quota paket tidak terlampaui. | A3/A2 | Belum diuji |
| UAT-12 | File >5 MB, file keenam, ekstensi/MIME salah | Ditolak server dan tidak ready; CV non-PDF ditolak; pesan jelas; tidak butuh scanner. | A3 | Belum diuji |
| UAT-13 | Organizer A/B mengakses dokumen draft/submitted | Draft privat; A hanya melihat submitted miliknya; B ditolak meski tahu ID/path. | A3 | Belum diuji |
| UAT-14 | Nama file HTML/traversal dan URL publik langsung | Teks ter-escape, internal name acak, attachment authorized; folder privat tidak dapat diakses publik. | A3 | Belum diuji |
| UAT-15 | File simpan sukses tetapi metadata gagal atau file hilang | Cleanup/kompensasi; tidak ada ready palsu; missing/purged tidak menghasilkan unduhan sukses palsu. | A3 | Belum diuji |
| UAT-16 | Withdraw/suspend lalu download ulang | Hak Organizer berakhir; file tidak dipindah ke akun lain; audit download tersedia. | A3/A1 | Belum diuji |
| UAT-17 | Syarat wajib gagal vs preferensi tak cocok | Screening gagal dengan alasan hanya untuk wajib; assessment tidak bisa dimulai; tidak parsing klaim keaslian CV. | A4 | Belum diuji |
| UAT-18 | S=75,A=75,L=100; interval overlap; level kurang | 80,00 pada contoh; interval tidak double count; level minimum konsisten; nilai 0–100. | A4 | Belum diuji |
| UAT-19 | Edit profil setelah submit | Snapshot dan skor historis tetap; tidak membaca profil terbaru untuk skor lama. | A4/A3 | Belum diuji |
| UAT-20 | Refresh/start ganda, timeout browser ditutup, submit paralel assessment | Satu attempt, timer server tetap; finalisasi sekali; jawaban tersimpan; kunci tidak muncul di response peserta. | A4 | Belum diuji |
| UAT-21 | Urutan kandidat dan keputusan seleksi | Match lalu assessment lalu submit/id; hanya under_review bisa accepted; skor tinggi tidak otomatis diterima. | A3/A4 | Belum diuji |
| UAT-22 | Dua accept berebut satu kuota; volunteer accepted pada jadwal overlap | Satu berhasil; konflik jadwal ditolak; diuji dua proses dengan satu database MySQL uji dengan koneksi independen. | A3 | Belum diuji |
| UAT-23 | Browser mengklaim sukses payment | Order tetap tidak paid tanpa verifikasi server; event belum eligible tidak publik. | A2 | Belum diuji |
| UAT-24 | Webhook ganda/tidak berurutan/nominal salah | Tidak ada aktivasi ganda; paid_at tetap; mismatch ditolak/ditandai; pending lama tidak menurunkan paid. | A2 | Belum diuji |
| UAT-25 | Admin mengubah harga paket setelah order dibuat | Snapshot nominal/manfaat order lama tetap; admin tidak bebas edit paid; entitlement lama konsisten. | A2 | Belum diuji |
| UAT-26 | Sandbox checkout dan status server nyata | Minimal satu transaksi sandbox terverifikasi; log referensi aman; mock terpisah dari bukti E2E. | A2 | Belum diuji |
| UAT-27 | Attendance oleh Volunteer/Organizer lain; peserta belum diterima | Ditolak server; hanya accepted milik event dapat dicatat; unrecorded tidak absent. | A3 | Belum diuji |
| UAT-28 | Completion terlalu dini/ada pending, koreksi setelah selesai | Diblokir bila prasyarat belum terpenuhi; present berhasil, absent bukan berhasil; koreksi butuh alasan/audit. | A3 | Belum diuji |
| UAT-29 | Aktivitas akun A meminta data akun B | Hanya data akun aktif; tidak ada grafik/dashboard pengguna; route lama redirect aman. | A4/A1 | Belum diuji |
| UAT-30 | Retry notifikasi dan filter dashboard | Satu notifikasi per transisi/penerima; indikator periode memakai timestamp benar; gagal query bukan 0. | A4/A1 | Belum diuji |
| UAT-31 | Event cancelled ketika ada assessment/lamaran/payment | Lamaran/attempt aktif ditutup; callback terlambat tidak publish; riwayat menampilkan dibatalkan. | A2/A3/A4 | Belum diuji |
| UAT-32 | Input role/harga/score/owner palsu, CSRF, akses log | Field sensitif server; request ilegal ditolak; log tidak membocorkan secret/berkas. | Semua | Belum diuji |
| UAT-33 | Backup MySQL+private file, retensi dan restore | Data/berkas konsisten; purge ledger diterapkan; file dihapus tidak kembali aktif; instruksi reproducible. | A1/A3 | Belum diuji |
| UAT-34 | Desktop/mobile, keyboard, build, font/Alpine | 360/768/1280 px berfungsi; no body blank; Alpine sekali; npm build dan PHPUnit/Pint sesuai scope. | Semua | Belum diuji |

| UAT-35 | Anggota lain menjalankan aplikasi dari README pada lingkungan baru | Setup MySQL, .env, migration/seeder, frontend, server, worker/scheduler dan login demo berhasil; database test terpisah; semua langkah terdokumentasi. | A1 + semua | Belum diuji |

## Uji kinerja

Catat p95 endpoint katalog, aktivitas dan kandidat pada fixture 100 event/1.000 lamaran/20 pengguna. Target <=2 detik di lingkungan yang dicatat, tidak termasuk gateway. Jika belum diuji tulis Belum diuji, bukan mengklaim lulus.

## Format checklist PR per anggota

- Kebutuhan FR dan UAT yang ditangani.
- Migration tambahan dan dependensi terhadap PR anggota lain.
- Policy/validasi dan alur gagal yang diuji.
- Screenshot desktop/mobile untuk UI yang berubah.
- Langkah demo, fixture dan hasil tes aktual.
- Known limitation yang masih memblokir P0.

Selesaikan satu alur end-to-end sebelum memperluas P1. PRD/kontrak/design diperbarui bersama jika aturan berubah; tidak menerima kondisi "masing-masing modul jalan" ketika integrasi gagal.

## Gerbang SF sebelum integrasi penuh

Gunakan SF-01–07 dan delapan kriteria pada [brief SF](anggota/SHARED_FOUNDATION.md). Reviewer anggota lain mencatat commit, lingkungan, hasil dan blocker. UAT-01–04 diperiksa sejauh fondasi tersedia; langkah melamar/publikasi diverifikasi lagi saat integrasi A2/A3. UAT-35 penuh baru lulus setelah modul, worker/scheduler dan README final tersedia. Seluruh skenario tetap berstatus Belum diuji pada revisi dokumen ini.

## Regresi pelestarian baseline - 5 Oktober 2026

Tambahan pemeriksaan UAT-02/09/30/34, bukan pengganti skenario P0:

- Guest membuka `/`: hero beranda dan identitas tim tampil, Masuk/Daftar menuju route auth bersama; tidak ada angka event palsu atau link katalog yang belum terpasang.
- Admin/Volunteer/Organizer membuka beranda setelah login: CTA menggunakan tujuan sesuai role/status/verifikasi, tanpa login khusus admin atau dashboard analitik pengguna.
- Admin membuka `/admin/dashboard`: hitungan sesuai database dan aksi cepat tetap tampil; non-admin tetap 403. Analitik periode yang belum tersedia tidak diklaim selesai.
- A2 menghubungkan beranda ke katalog setelah aturan publikasi/otorisasi terpenuhi; A1 memperluas dashboard tanpa menghilangkan fungsi dasar.

Hasil browser desktop/mobile/keyboard dan review anggota lain masih perlu dicatat; pemulihan beranda tidak otomatis meluluskan UAT-09/30/34/35 penuh. Bukti tes implementasi ada pada handoff aplikasi.

## Bukti parsial A2 - 5 Oktober 2026

Lihat [A2_HANDOFF](../../docs/A2_HANDOFF.md) untuk environment, commit dan hasil aktual. Checklist penerimaan lintas anggota di atas tidak otomatis berubah menjadi lulus.

| Cakupan | Bukti implementasi saat ini | Yang masih diperlukan |
|---|---|---|
| UAT-07/08/10 | A2EventTest: konfigurasi, moderasi baseline, Free sekali, snapshot, blokir assessment belum tersedia, paid belum entitled tidak publik | Validator assessment nyata dan review moderasi A1 |
| UAT-09/34 | Render beranda/katalog/detail, filter dan endpoint visibilitas, Blade/build, intended login lokal | Browser visual/keyboard/mobile |
| UAT-11/22/31 | Snapshot kontrak, reserve batas paket transaksional dan race dua proses; cancellation mock serta blokir tanpa layanan | Submit/seleksi/cancellation dan attempt nyata A3/A4 |
| UAT-23/24/25/26 | A2PaymentTest: fake signature/status, nominal/IDR/ref, retry, status terlambat, refund/challenge, audit rollback; race order/reconcile/publish | Satu transaksi Sandbox end-to-end dan callback HTTPS |
| Notifikasi | Stable key, replay ledger dan rollback dengan collaborator tes | Outbox/worker/delivery A4 |

Uji konkurensi A2 tidak menggantikan uji kuota acceptance/bentrok jadwal A3 atau pengukuran performa keseluruhan. Review tim dan UAT penuh tetap belum selesai.

## Regresi beranda informatif (UAT-09/34) - 5 Oktober 2026

- Guest memahami tujuan platform melalui hero/pengenalan; panduan kedua peran dan FAQ membedakan fitur tersedia dari yang masih dikembangkan.
- Pencarian beranda menuju katalog menggunakan parameter search; tautan detail, auth, CTA akun dan anchor footer mengarah tujuan yang tersedia.
- Event draft, belum published, suspended, terminal, berakhir, pemilik nonaktif, pendaftaran belum dibuka/tutup atau kapasitas paket habis tidak masuk pratinjau. Maksimal enam event, urut starts_at/ID; detail waktu berlabel WIB.
- Tidak ada event eligible: tampil pesan informatif dan CTA katalog; tidak ada statistik nol/testimoni/fixture promosi.
- Periksa lebar 360/768/1280, fokus dan aktivasi FAQ melalui keyboard, label pencarian dan kontras. Screenshot statis tidak menggantikan seluruh uji keyboard atau UAT lintas modul.

Bukti aktual pemeriksaan dicatat dalam A2_HANDOFF. Checklist UAT keseluruhan belum ditandai lulus.

## Regresi UI masuk/daftar - 5 Oktober 2026

Login dan registrasi diperbarui secara visual. Bukti: 40 tes auth/FoundationAccess lulus (183 assertion), build/Blade sukses, Chrome viewport 360/1280 tanpa overflow. Toggle password terbukti berganti password -> text -> password; login tidak memiliki input role, registrasi hanya Volunteer/Organizer. Periksa juga error server, restore old input, checkbox ingat saya, lupa kata sandi, fokus/radio keyboard dan submit loading pada UAT manual. Pemeriksaan ini tidak meluluskan SMTP atau seluruh audit aksesibilitas.

## Regresi paket awal dan kartu

DefaultPackageTest memeriksa nilai awal sesuai keputusan, seeding ulang setelah rename/edit/nonaktif tanpa reset/duplikasi, perubahan admin tampil pada kartu aktif dan tidak mengubah snapshot order/event lama. Bersama A2EventTest, HomeTest dan RegistrationTest: 24 tes / 183 assertion lulus pada MySQL tes. Pemilihan paket tetap mengikuti limit dan Policy existing; fitur lanjutan yang belum terintegrasi tidak dinyatakan lulus.
