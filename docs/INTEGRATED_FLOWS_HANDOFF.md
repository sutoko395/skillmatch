# Perbaikan alur terintegrasi — 3 November 2026

Commit bertahap sesuai tugas; branch fix/integrated-application-flows, basis 4067a6c.

- Penghapusan akun tetap tersedia bagi akun tanpa data terkait. Pemeriksaan owner,
  relasi kegiatan/lamaran/dokumen/audit/kehadiran/notifikasi dilakukan sebelum delete.
  Penolakan 403 tidak menghapus profil atau berkas. Audit dan delete atomik; file
  hanya dihapus setelah commit. Akun bersejarah dinonaktifkan. Admin dapat mengatur
  `is_active` Organizer secara terpisah dari `organizer_status` melalui modal existing.


- `NotificationService.enqueue` memakai notification_outbox existing, channel database,
  di transaksi bisnis. Notifikasi website tersedia segera setelah commit, tanpa SMTP
  atau worker delivery tambahan. Email tetap channel mail dan worker existing.
  Dedupe unique key, replay dan current locking read melindungi race InnoDB
  REPEATABLE READ. Payload hanya ID/type/status, tanpa KTP/snapshot/kunci/tautan bebas.
  GET /notifications dan PATCH /notifications/{notification}/read memeriksa pemilik;
  link dihitung dari kepemilikan objek saat dibuka, bukan payload URL pengguna.
  Hook: review organisasi/event, publish/payment, screening/matching/assessment,
  lamaran, attendance, cancel dan completion. Replay A2/A3 tetap tersedia.


`2026_11_03_000001_complete_application_flows`: kolom catatan/revision organisasi
pada users; marker/pelaku/alasan pelengkapan tenggat pada events; application_id
nullable dan read_at/index pada notification_outbox. Tidak mengubah histori migration.
Tidak ada backfill, seed atau reset data kerja. Rollback membiarkan application_id
nullable agar notifikasi tanpa lamaran tidak dihapus paksa.

Migration bersama dipertahankan. Pengujian lengkap pada worktree terintegrasi sudah dijalankan; bukti akhir dicatat setelah pemisahan tugas selesai. Tidak push/merge.
