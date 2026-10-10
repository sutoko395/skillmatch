# Assessment Organizer — tahap A4

Tanggal: 10 Oktober 2026 (WIB). Branch: `feature/organizer-assessment`, basis `adad7bd` (jadwal posisi). Commit implementasi tercatat pada Git log branch. Belum push/PR/merge. Tahap ini siap ditinjau; integrasi MySQL penuh dan review browser belum lulus.

## Perilaku dan integrasi

- Bagian Assessment pada detail event berada setelah Posisi dan jadwal sebelum Paket. Editor per posisi mendukung draft parsial, beberapa soal dalam satu halaman, empat opsi A–D dan satu kunci, maksimal 50 soal/durasi 120 menit; minimum publikasi satu soal/satu menit.
- Preview publikasi memvalidasi tanpa menulis; halaman konfirmasi menjelaskan posisi/jumlah/durasi dan penguncian. Confirmed publish mengunci soal/opsi/kunci/durasi, versi awal 1. Publikasi assessment tidak menerbitkan event. Tidak ada revisi published pada tahap ini.
- OrganizerAssessmentService mengunci actor -> event -> position -> assessment, memvalidasi owner/email/status/config, menolak revision draft stale dan menangani replay publish tanpa hasil/audit ganda. Audit memakai aksi assessment.draft_saved/assessment.published dalam transaksi bisnis, tanpa isi/kunci soal.
- Model Assessment/AssessmentQuestion/AssessmentOption, EventPosition.assessments. assessment_options.is_correct disembunyikan dari serialisasi; editor/konfirmasi mengambil kunci eksplisit hanya sesudah otorisasi Organizer pemilik. Route organizer memakai verified, account.active/role/organizer.active dan Gate update pada event.
- Binding App\Contracts\AssessmentReadiness -> OrganizerAssessmentService.publishedVersion memeriksa published/version/kelengkapan/kunci/durasi nyata. A2 submit/publish dan PositionSnapshotService A3 tetap memakai interface existing. Snapshot/version historis tidak diubah. Tidak ada readiness konstan di runtime.
- Penghapusan posisi menghapus draft assessment dalam transaksi; published ditolak agar referensi/versi tidak dihapus. EventService.editable tetap mengunci konfigurasi published/transaksi/terminal.

## Migration dan route

`2026_10_10_000002_create_organizer_assessments.php`: assessments (unique position/version, revision, duration nullable, is_published), assessment_questions (text/sort_order), assessment_options (label/text/is_correct). FK posisi restrict; anak draft cascade saat dihapus. Migration ini sudah dijalankan pada database lokal skillmatch saja, tanpa reset atau fixture/soal percobaan. Migration A3 yang masih pending tidak dijalankan oleh tahap ini.

GET/PUT `/organizer/positions/{position}/assessment`: organizer.assessments.edit/update. PUT action draft/preview/publish; publish membutuhkan confirmed; revision untuk stale check. Schema tambahan dan batas input dicatat di PRD/kontrak/desain/UAT/brief A4. View organizer/assessments/edit dan confirm memakai Alpine SF sekali. Tidak ada perubahan auth global/rumus matching/payment.

## Bukti dan blocker

- Unit OrganizerAssessmentValidationTest: **4 tes, 21 assertion lulus**, mencakup draft parsial, batas soal/durasi/opsi/kunci, Policy pemilik/role/nonaktif dan serialisasi kunci. Tes ini tidak membuktikan transaksi database atau endpoint lintas akun.
- `npm.cmd run build`: berhasil. Route assessment terdaftar, `artisan view:cache`: berhasil. Sintaks/Pint file assessment baru dan diff check diperiksa; hasil akhir mengikuti pemeriksaan sebelum laporan.
- Pemeriksaan baca-saja melalui HTTP kernel pada database lokal, sesi array: GET editor assessment posisi existing dan detail event **200**, label Simpan Draft/Publikasikan Assessment tampil. Tidak membuat soal/draft percobaan di database kerja.
- **Tes MySQL integrasi belum dijalankan**: skillmatch_testing belum tersedia, .env.testing belum ada, akun kerja tidak memiliki izin menyiapkan database tes. Siapkan akun khusus dengan akses database tes dan migration, lalu jalankan OrganizerAssessmentTest dan regresi PositionScheduleModeTest/A2/A3 pada MySQL terpisah. Jangan menjalankan tes transaksi pada skillmatch kerja.
- OrganizerAssessmentTest menyiapkan skenario draft, preview tanpa write, confirmed publish, freeze, replay audit, readiness submit nyata, snapshot, stale, endpoint lintas owner/role/nonaktif, serta kunci published rusak. Skenario ini belum bukti lulus. Tambahkan verifikasi konkurensi dua proses untuk race publish/save/submit sebelum klaim integrasi lengkap.
- Browser visual/keyboard 360/768/1280, JavaScript editor, submit aktual, rollback/konkurensi dan publikasi event end-to-end **belum diuji**. Build/render bukan penggantinya.
- Attempt peserta/scheduler/autosave, screening, matching, notifikasi/aktivitas belum dikerjakan pada tahap ini; UAT-17–20 penuh belum lulus. A3 baru draft/submit, dokumen privat/evaluation dispatch/cancellation masih perlu integrasi oleh PIC terkait.

## Demo

1. Ambil kode branch yang disepakati, backup database lalu migrate dan build dengan PHP kompatibel. Jangan migrate:fresh.
2. Login Organizer pemilik event draft, buat posisi bila belum ada, buka Assessment -> Kelola Assessment.
3. Simpan draft parsial; pengajuan event harus tetap tertahan. Lengkapi soal/durasi, lihat konfirmasi, lalu publish.
4. Periksa read-only/freeze; pengajuan event dapat dilanjutkan bila semua syarat A2 terpenuhi. Minta akun Organizer lain/Volunteer mengakses endpoint: harus ditolak.
5. Reviewer mencatat commit, lingkungan, screenshot dan hasil MySQL/UAT; belum ada klaim aplikasi A4 penuh selesai.
