# Brief A2 — event, katalog, paket, pembayaran

Versi 1.4.2 • Baca [PRD](../../PRD_SkillMatch_Tim.md), [kontrak](../KONTRAK_INTEGRASI.md), [design](../../design.md), [UAT](../UAT.md).

## Titik mulai ZIP

Model Event, EventPosition, PositionSkill dan PositionRequirement sudah ada. CRUD organizer, katalog, payment dan entitlement belum tersedia. Tautan event organizer masih placeholder; `approved` belum merupakan alur publikasi lengkap.

## Pekerjaan berurutan

| Tahap | Deliverable | Penerimaan |
|---|---|---|
| 1 | Migration event/posisi | Pertahankan event_positions dan status moderasi ZIP; tambah publication/lifecycle, timestamp, city, schedule, flags syarat dan paket. |
| 2 | Form event/posisi | Validasi server jadwal/deadline/kuota/skill; draft dapat diedit; published mengunci aturan substantif. |
| 3 | Pengajuan dan publikasi | Submit hanya jika posisi, assessment dan paket lengkap; integrasi approve/reject A1; publikasi melalui satu service. |
| 4 | Katalog dan detail | Pencarian/filter/pagination; published dan eligible saja; posisi, jadwal, syarat, tenggat dan CTA jelas. |
| 5 | Paket/order | Snapshot harga/manfaat; limit posisi/lamaran/periode; Free tanpa paid palsu; UI admin paket/transaksi. |
| 6 | Midtrans Sandbox | Checkout, endpoint notifikasi, verifikasi dan sinkron server, nominal/ref sesuai, idempotensi, entitlement sekali. |
| 7 | Pembatalan dan aktivitas | Event cancelled tidak menerima lamaran; panggil kontrak A3/A4 untuk lamaran/attempt; data status siap dibaca A4. |

## Aturan payment yang tidak boleh dilompati

- Gateway belum terpasang pada ZIP; integrasi baru memakai Laravel HTTP client, konfigurasi Sandbox dan kredensial lingkungan.
- Implementasi mengikuti dokumentasi gateway yang diverifikasi saat coding; dokumen ini tidak mengarang API secret, endpoint produksi atau versi SDK.
- Checkout return menampilkan status dari server. Tidak mengubah order paid karena query string/callback browser.
- Callback memvalidasi identitas order, nominal/currency dan autentisitas; status ambigu disinkronkan server.
- Unique event key/order/entitlement dan transaksi singkat. Jangan menahan transaksi MySQL/InnoDB selama HTTP call.
- Paid_at/activated_at dipertahankan saat retry. Event cancelled tidak dipublikasikan akibat pembayaran terlambat.
- Admin melihat/sinkron status, tidak memiliki tombol "jadikan lunas".

## Serah-terima

SF menyediakan aturan akun/organisasi dan layout/admin/audit; A1 menyediakan keputusan verifikasi organisasi dan moderasi event. A2 menyediakan PositionSnapshotService dan EventPublicationService. A4 menyediakan assessment tab dan validasi kesiapan assessment. A3 memakai event eligibility dan entitlement untuk submit, serta menyediakan cancellation service. Semua perubahan ketiga status event mengikuti kontrak.

## Uji minimum

UAT-07, 08, 09, 10, 23, 24, 25, 26, 31; ikut UAT-11 dan 22. Gunakan gateway mock untuk tes deterministik dan catat satu transaksi sandbox end-to-end ketika kredensial tersedia. Mock saja tidak diklaim sebagai pembayaran sandbox yang telah diuji.

## Demo selesai

Organizer membuat event, Admin meminta revisi lalu approve, paket dipilih, payment diverifikasi bila berbayar, event muncul publik, dan Volunteer bisa melanjutkan ke draft A3. Kasus belum bayar tetap unpublished. Sediakan label jelas "Sandbox — simulasi pembayaran".

## Batas tugas

A4 mengelola isi assessment, A3 mengelola seleksi/attendance. A2 menyediakan tab/entry point agar keduanya terasa satu alur. Tidak menambah React, dashboard Organizer, atau harga produksi yang belum disepakati.

## Dependensi SF

Gunakan commit shared foundation untuk auth bersama, kota, master skill, layout dan audit. A2 boleh mulai schema/event dengan kontrak dan fixture sebelum SF selesai; jangan membuat auth/master tandingan. UI admin paket memakai guard web dan role admin, serta logout bersama.

## Penugasan beranda publik - 5 Oktober 2026

A2 adalah PIC website utama untuk pengunjung: beranda/landing page, katalog, pencarian/filter dan detail event. SF mengembalikan hero, CTA auth dan footer baseline pada `resources/views/welcome.blade.php`, route `home`, menggunakan layout/navbar bersama. Lanjutkan file itu; jangan membuat ulang beranda atau menggantinya dengan halaman kosong ketika katalog belum selesai.

Pertahankan kontribusi yang berfungsi. Tambahkan tautan Jelajahi Event dan konten kegiatan nyata setelah katalog eligible published tersedia. CTA autentikasi tetap memakai `/login`, `/register` dan redirect role SF. Jangan menampilkan statistik atau kartu event fixture seolah data nyata. A1 mengerjakan panel admin; A4 mengerjakan Aktivitas pengguna, bukan beranda publik. Sertakan regresi beranda guest/login/mobile pada UAT-09/34.
