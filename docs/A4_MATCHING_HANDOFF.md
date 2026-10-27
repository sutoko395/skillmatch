# Matching A4 — 27 Oktober 2026

Branch: feature/matching, dasar d2f157d (perbaikan Policy dokumen).

## Batas pekerjaan saat ini

Implementasi matching dengan tambahan integrasi terarah yang disetujui pengguna.
ScreeningService mempertahankan logika pemeriksaan existing, lalu memicu matching
setelah commit untuk hasil passed. Application mendapat relasi matchResult;
controller/detail/daftar EO memuat skor dan komponen. Volunteer hanya melihat status
assessment_pending/pesan pencocokan selesai. Login, dashboard, upload, navigasi dan
aturan screening tidak diubah. Assessment peserta/notifikasi/rekomendasi event
tetap di luar pekerjaan ini.

## File dan perilaku baru

- MatchingService.calculate(snapshot) menghitung skill dari seluruh skill posisi
  (wajib dan preferensi), level minimum beginner/intermediate/advanced/expert.
- Availability memakai durasi irisan gabungan interval dengan gabungan jadwal posisi;
  interval bersinggungan digabung, batas akhir sama dengan awal tidak menghasilkan
  irisan. Offset timezone wajib ada. Lokasi memakai city_id sama=100, berbeda=0.
- Total = 0,50 skill + 0,30 availability + 0,20 lokasi, match-v1.4. Snapshot invalid
  ditolak, tidak disamarkan menjadi skor nol. Tidak membaca profil/master terbaru.
- MatchResult dan migration 2026_10_27_000001_create_match_results_table.php menyimpan
  satu hasil per application_id (unique FK), komponen, total, breakdown dan rule_version.
  Skor DOUBLE disimpan tanpa pembulatan tambahan, UI dua desimal; bukan aritmetika
  desimal eksak. Tidak mengubah migration lama.
- MatchingEvaluationService.evaluate mengunci Volunteer → event → application.
  Hanya submitted dengan screening passed dan event noncancelled/noncompleted
  diproses. Skor, assessment_pending, revision dan audit ditulis dalam satu transaksi.
  Hasil existing dibaca tanpa menghitung ulang atau mengganti state terminal.
- a4:retry-matching --limit=100 --after=0 memproses submitted lolos screening tanpa
  hasil. Limit 1–1000; after dapat dipakai melanjutkan batch setelah ID yang disebutkan.
  Kegagalan hanya mencetak ID operasional, bukan snapshot/kredensial/file.
- Komponen application-matching menampilkan total dan rincian pada detail EO, dengan
  kolom Match Score di daftar pelamar. Skor hanya untuk EO; Volunteer hanya status/progres.
  Nilai assessment tetap terpisah; tidak ada rekomendasi event, auto-accept atau
  penolakan berdasarkan skor. Akses tetap melalui middleware/Policy halaman existing.

## Verifikasi

MatchingServiceTest: 14 tes / 32 assertion lulus, mencakup contoh 80,00, level minimum,
semua skill posisi, kota berbeda, union interval/offset/adjacency, batas irisan,
presisi sebelum rendering dan data invalid. Tes ini menggunakan fixture dalam memori.
Pint, lint file matching, kompilasi Blade dan build Vite lulus pada akhir pengerjaan.
Regresi ApplicationDocumentPolicyTest: 4 tes / 42 assertion lulus; total kedua file
unit yang dijalankan 18 tes / 74 assertion. Perintah a4:retry-matching terdaftar di Artisan.

MatchingTest disiapkan untuk persistence, revision/audit, replay, syarat screening,
terminal/event cancelled, input invalid, rollback audit, retry, pemicu sesudah commit,
outer rollback, kegagalan matching tetap menyisakan screening passed, dan HTTP owner/
lintas akun serta skor tidak tampil di Volunteer. Setelah pengguna memperbaiki
.env.testing dan akses database, seluruh migration pada skillmatch_testing berhasil.
Suite MatchingTest, ScreeningTest, A3ApplicationTest, A3ApplicationDocumentsTest,
MatchingServiceTest dan ApplicationDocumentPolicyTest: **70 tes / 341 assertion lulus**.
Tes existing diselaraskan untuk urutan key JSON MySQL yang dinormalisasi (isi tetap
dibandingkan), refresh event sebelum fixture unpublished, dan status Menunggu assessment.
Tidak ada perubahan kode login, publikasi event, atau aturan submit untuk meluluskan tes.

`php tools/a4-matching-concurrency-check.php --env=testing` lulus dua skenario, masing-masing
dengan dua proses PHP/koneksi independen: evaluasi matching ganda serta screening →
matching ganda menghasilkan satu hasil matching, satu audit matching, revision 3 dan
assessment_pending. Pada skenario screening hanya satu hasil/audit screening. Tool
menolak target selain skillmatch_testing dan mempertahankan fixture sintetis di sana.
Race pembatalan lewat layanan A3 serta review browser 360/768/1280 belum diuji.

Migration baru sudah dijalankan pada database lokal kerja, hanya menambah match_results
dengan unique/FK InnoDB; tidak reset, seed atau mengubah snapshot/status lamaran existing.
Pemeriksaan baca-saja pada snapshot lamaran #4 menghitung S=100,A=100,L=100,total=100.
GET detail EO dan Volunteer melalui HTTP kernel/sesi array sama-sama 200; kartu matching
hanya ada di EO. Ini belum membuktikan penyimpanan skor/submit end-to-end atau browser.
Database tes sekarang sudah dimigrasi dan suite/konkurensi di atas lulus. Database kerja
tidak digunakan untuk suite otomatis atau fixture konkurensi.

## Menjalankan dan demo

Sesudah pull jalankan php artisan migrate. Untuk lamaran baru, submit → screening
passed → callback matching sesudah commit → hasil tersimpan dan assessment_pending.
Matching gagal menyisakan submitted dengan screening passed; retry tidak menimpa hasil.
Lamaran yang sudah lolos sebelum fitur ini: php artisan a4:retry-matching --limit=100.
Command ini belum dijalankan pada data kerja selama implementasi; tidak ada pemrosesan
massal lamaran existing. Buka detail/daftar EO untuk skor dan detail Volunteer untuk
status tanpa angka. Start/timer assessment masih tahap berikutnya.

Rumus/status/schema mengikuti spesifikasi existing. PRD/kontrak/desain/brief/UAT,
README dan catatan screening diselaraskan khusus untuk keputusan skor hanya EO serta
koneksi matching. ScreeningTest kini mengharapkan assessment_pending dan hasil matching
setelah lolos; hasil gagal tetap sama. Tidak ada endpoint publik atau provider baru.
