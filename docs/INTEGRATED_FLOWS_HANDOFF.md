# Perbaikan alur terintegrasi — 3 November 2026

Commit bertahap sesuai tugas; branch fix/integrated-application-flows, basis 4067a6c.

- Penghapusan akun tetap tersedia bagi akun tanpa data terkait. Pemeriksaan owner,
  relasi kegiatan/lamaran/dokumen/audit/kehadiran/notifikasi dilakukan sebelum delete.
  Penolakan 403 tidak menghapus profil atau berkas. Audit dan delete atomik; file
  hanya dihapus setelah commit. Akun bersejarah dinonaktifkan. Admin dapat mengatur
  `is_active` Organizer secara terpisah dari `organizer_status` melalui modal existing.


- Revisi nama organisasi, alamat, kota atau KTP dari organisasi active memerlukan
  konfirmasi eksplisit sebelum menjadi pending. Kontak/website/deskripsi biasa tidak
  mereset approval. Perbaikan inactive dikirim ulang menjadi pending. Admin wajib
  catatan bila inactive dan wajib revision terbaru; review stale ditolak. Persetujuan
  membutuhkan profil dan metadata KTP. Catatan ditampilkan pada profil/pending.
  Upload/preview tetap disk local privat; berkas lama dihapus setelah commit.


- `POST organizer/events/{event}/recruitment-repair` melengkapi kedua tenggat yang
  kosong pada event pending/approved upcoming. Hanya pemilik; konfirmasi dan alasan
  wajib; pendaftaran < assessment (masa depan) < keputusan < awal kegiatan.
  Pelengkapan sekali, replay nilai sama idempoten, perubahan selanjutnya ditolak.
  Tidak membuka ulang pendaftaran, mengubah jadwal/biaya atau menulis snapshot lama.
  `RecruitmentScheduleService.deadlineFor` memakai tanggal snapshot bila tersedia;
  hanya tanggal snapshot yang kosong dapat membaca tenggat event dengan marker
  perbaikan eksplisit. Ini pengecualian migrasi metadata yang diaudit, bukan backfill
  snapshot atau tebakan tenggat. Snapshot lain yang tidak lengkap tetap ditolak oleh
  screening/assessment existing; tidak ada bypass untuk data lama yang tidak valid.


- Endpoint tambah/edit paket kini mempunyai view form mandiri. Modal daftar existing
  tetap tersedia. Validasi, Policy, dan snapshot harga/manfaat tetap memakai A2.


- `NotificationService.enqueue` memakai notification_outbox existing, channel database,
  di transaksi bisnis. Notifikasi website tersedia segera setelah commit, tanpa SMTP
  atau worker delivery tambahan. Email tetap channel mail dan worker existing.
  Dedupe unique key, replay dan current locking read melindungi race InnoDB
  REPEATABLE READ. Payload hanya ID/type/status, tanpa KTP/snapshot/kunci/tautan bebas.
  GET /notifications dan PATCH /notifications/{notification}/read memeriksa pemilik;
  link dihitung dari kepemilikan objek saat dibuka, bukan payload URL pengguna.
  Hook: review organisasi/event, publish/payment, screening/matching/assessment,
  lamaran, attendance, cancel dan completion. Replay A2/A3 tetap tersedia.


- Scheduler kini juga menjalankan retry screening/matching setiap menit. Tidak
  menjalankan replay historis atau pemrosesan massal data kerja saat implementasi.

`2026_11_03_000001_complete_application_flows`: kolom catatan/revision organisasi
pada users; marker/pelaku/alasan pelengkapan tenggat pada events; application_id
nullable dan read_at/index pada notification_outbox. Tidak mengubah histori migration.
Tidak ada backfill, seed atau reset data kerja. Rollback membiarkan application_id
nullable agar notifikasi tanpa lamaran tidak dihapus paksa.

Migration bersama dipertahankan. Pengujian lengkap pada worktree terintegrasi sudah dijalankan; bukti akhir dicatat setelah pemisahan tugas selesai. Tidak push/merge.
