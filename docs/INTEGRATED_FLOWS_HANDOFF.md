# Perbaikan alur terintegrasi — 3 November 2026

Commit bertahap sesuai tugas; branch fix/integrated-application-flows, basis 4067a6c.

`2026_11_03_000001_complete_application_flows`: kolom catatan/revision organisasi
pada users; marker/pelaku/alasan pelengkapan tenggat pada events; application_id
nullable dan read_at/index pada notification_outbox. Tidak mengubah histori migration.
Tidak ada backfill, seed atau reset data kerja. Rollback membiarkan application_id
nullable agar notifikasi tanpa lamaran tidak dihapus paksa.

Migration bersama dipertahankan. Pengujian lengkap pada worktree terintegrasi sudah dijalankan; bukti akhir dicatat setelah pemisahan tugas selesai. Tidak push/merge.
