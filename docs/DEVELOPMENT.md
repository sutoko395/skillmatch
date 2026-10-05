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

- `/admin/dashboard` menampilkan dashboard dasar baseline dengan hitungan nyata Volunteer, Organizer, skill, kategori dan event pending serta aksi cepat. Filter periode, tren dan analitik lengkap belum tersedia; A1 melanjutkan controller/view yang sama. Moderasi, audit UI, master UI kota/nonaktifkan, serta suspend dengan alasan/audit lengkap tetap A1. Hard delete akun ditolak server.
- Aktivitas pengguna mempertahankan gaya kartu baseline dan ringkasan profil nyata milik akun sendiri. Volunteer melihat kelengkapan dari ProfileEligibilityService, skill, kota dan availability WIB; Organizer melihat profil/kontak serta status akun/organisasi. Fitur kegiatan belum tersedia diberi keterangan tanpa statistik palsu. A4 melanjutkan ringkasan ini dengan ActivityReadService, notifikasi, screening, assessment dan matching.
- Event/katalog/paket/Midtrans Sandbox belum diimplementasikan oleh SF; konfigurasi payment belum tersedia (A2).
- Beranda `/` mempertahankan hero, tombol Masuk/Daftar dan footer tim dari baseline, memakai navbar SF. Pengguna login diarahkan melalui tujuan role/status yang sah. Katalog dan matching diberi keterangan sedang disiapkan, bukan mengganti seluruh beranda. A2 melanjutkan halaman ini beserta katalog/detail event.
- Dokumen privat, download terotorisasi, lamaran/attendance/retensi belum tersedia (A3 dengan integrasi A1). Tidak menjalankan `storage:link` untuk dokumen pribadi.
- SF mengirim email auth sinkron; tidak memerlukan worker/scheduler. Instruksi `queue:work`/`schedule:work` untuk outbox/attempt/retensi baru ditambahkan pemilik modul saat implementasi.

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
