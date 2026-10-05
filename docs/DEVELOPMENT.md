# Panduan pengembangan tim SkillMatch

Dokumen ini memuat pembagian pekerjaan, dependensi, dan panduan transisi database. Untuk pengenalan aplikasi, instalasi, akun demo dan tes, lihat [README utama](../README.md).

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
