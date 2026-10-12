# Handoff screening A4 — 11 Oktober 2026

Branch implementasi: `feature/screening`, dari commit snapshot `824e7e2`.

## Perilaku

ScreeningRules mengevaluasi snapshot saja: bukti kelengkapan profil, skill wajib/minimum
level, tipe dokumen wajib dari daftar dokumen ready saat submit, availability penuh
dan kota hanya jika wajib. Interval UTC/offset digabung termasuk adjacency; celah tetap
gagal bila cakupan penuh wajib. Manual/preferensi tidak menggagalkan. Isi/keaslian CV
tidak dinilai. Snapshot tidak lengkap/malformed menahan evaluasi untuk investigasi;
tidak memalsukan hasil gagal/lolos atau membaca ulang profil historis.

ScreeningService.evaluate dipanggil melalui callback submit existing. Lock mengikuti
Volunteer → event → application; hasil unik, revision/status/audit dalam satu transaksi.
Replay membaca hasil existing tanpa audit atau revision tambahan. Hanya submitted baru
dievaluasi; event cancelled/completed dan lamaran terminal tidak diaktifkan kembali.
Gagal → screening_failed. Lolos tetap submitted dengan hasil passed, menunggu matching;
assessment_pending hanya boleh sesudah hasil matching nyata tersimpan. Tidak ada status
bisnis baru. Notifikasi/outbox A4 dan integrasi matching/attempt tetap tahap berikutnya.

Retry: `php artisan a4:retry-screening --limit=100` (1–1000), memilih submitted tanpa hasil.
Kegagalan callback meninggalkan submitted dan warning ID operasional. Retry mengembalikan
exit gagal bila masih ada evaluasi tertunda. Lamaran lama tanpa profile_eligibility tidak
di-backfill; batch tertua yang gagal perlu ditinjau, bukan dibypass.

## Integrasi dan UI

- Migration baru `2026_10_11_000001_create_screening_results_table.php`: FK application,
  unique application_id, passed, checks_json, rule_version, evaluated_at dan timestamps.
- Application.screeningResult dan ScreeningResult.evaluation untuk consumer berikutnya.
- Kontrak hasil `{passed, checks, rule_version}`; checks berisi code, label, required,
  passed, reason, manual. Passed pada syarat nonwajib berarti tidak memblokir, bukan
  klaim bahwa syarat preferensi/manual sudah dipenuhi.
- Detail/daftar existing melakukan eager load hasil. Policy Application dan middleware
  role/akun existing tetap menentukan akses; tidak ada endpoint evaluasi publik baru.
- Komponen application-screening (Volunteer ringkas, EO tabel) dan application-status.
  Pesan gagal Volunteer: “Lamaranmu belum bisa dilanjutkan ke tahap selanjutnya.”
  Belum dievaluasi menampilkan pending, tidak ada hasil fixture sebagai data aplikasi.

## Verifikasi

- PHPUnit ScreeningRulesTest: 16 tes, 50 assertion lulus (tanpa akses database).
  Mencakup syarat gagal, preferensi/manual, merge/celah/offset, legacy/malformed,
  rendering ringkas/tabel, escaping, dan Policy owner/role/nonaktif.
- Pint, lint PHP seluruh file terkait, kompilasi Blade dan build Vite berhasil.
  Perintah a4:retry-screening terdaftar di Artisan.
- ScreeningTest ditambahkan untuk hasil/replay, revision/audit rollback, terminal,
  event cancelled, legacy/retry dan akses langsung halaman detail lintas akun.
  A3ApplicationDocumentsTest diselaraskan untuk badge lolos setelah submit dengan CV.
- Tes transaksi/endpoint MySQL **belum dijalankan**: akses akun lokal ke
  skillmatch_testing ditolak (1044). Race dua koneksi dan uji browser 360/768/1280
  **belum diuji**. Tes Policy/rendering dalam memori bukan bukti endpoint/konkurensi.
- Migration database kerja belum dijalankan; tidak ada reset database, seed, atau
  perubahan .env. Jalankan migrate sebelum membuka daftar/detail yang memuat relasi baru.

## Demo dan langkah berikutnya

Sesudah migrate, gunakan event published dengan assessment Organizer valid. Volunteer
dengan profil lengkap membuat draft, memasang dokumen sesuai requirement lalu submit.
Lihat alasan gagal atau hasil lolos pada detail Volunteer dan EO. Replay command harus
tidak menggandakan hasil. Lengkapi akses database tes terpisah untuk suite transaksi,
lalu matching dan assessment peserta agar alur hingga under_review dapat diuji penuh.
