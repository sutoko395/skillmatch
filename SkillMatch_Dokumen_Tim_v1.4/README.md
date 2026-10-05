# Paket dokumen pengembangan SkillMatch — empat anggota

Versi 1.4.2 • 4 Oktober 2026 • Basis kode: `skillmatch-main.zip`

## Cara memakai

Gunakan **satu PRD induk sebagai sumber kebutuhan**, satu kontrak integrasi, dan satu design.md. Empat brief anggota merupakan turunan tugas, bukan empat PRD independen. Cara ini menjaga status, database, hak akses, dan tampilan tetap sama saat hasil digabungkan.

1. Semua anggota membaca [PRD induk](PRD_SkillMatch_Tim.md), [kontrak integrasi](docs/KONTRAK_INTEGRASI.md), dan [design.md](design.md).
2. Periksa [baseline dan tools](docs/BASELINE_DAN_TOOLS.md) sebelum memasang atau mengganti dependensi.
3. Masing-masing membaca briefnya: [A1](docs/anggota/A1_Akun_Admin.md), [A2](docs/anggota/A2_Event_Pembayaran.md), [A3](docs/anggota/A3_Lamaran_Pelaksanaan.md), [A4](docs/anggota/A4_Screening_Assessment_Matching.md).
4. Jalankan alur bersama berdasarkan [UAT](docs/UAT.md); jangan menilai selesai hanya dari tampilan.
5. Salin dokumen ke repository tim. Isi nama anggota pada tabel berikut. Pembagian A1 menghitung pekerjaan yang sudah ada; A4 disediakan untuk pengguna yang meminta dokumen ini.

| Kode | Nama | Tanggung jawab utama |
|---|---|---|
| SF | Kamu sebelum A4 | MySQL, login bersama, profil dasar, layout, AuditService, seed, README awal |
| A1 | Teman yang mengerjakan ZIP | Melanjutkan fondasi: moderasi, pengguna, master UI, audit UI, konten dan analitik |
| A2 | Anggota kedua | Beranda publik, event, posisi, katalog/detail, paket, pembayaran |
| A3 | Anggota ketiga | Lamaran, dokumen, seleksi, attendance, riwayat |
| A4 | Kamu | Screening, assessment, matching, aktivitas, notifikasi bersama |

## Urutan otoritas dan perubahan

Keputusan pengguna terbaru → PRD v1.4.2 ini → kontrak integrasi → design.md → brief anggota. Konflik ditemukan sebelum coding: ubah dokumen pusat dahulu, tulis dampaknya pada brief terkait, lalu implementasikan. Jangan mengganti nama status, kolom, atau rumus hanya di satu modul.

PRD v1.3 tanggal 1 Oktober 2026 menjadi sumber kebutuhan awal. Paket v1.4.2 ini merupakan turunan untuk tim berdasarkan ZIP, bukan penggantian diam-diam terhadap file PRD lama. Revisi 1.4.1 menetapkan MySQL/InnoDB sesuai keputusan pengguna dan mempertahankan penghapusan scanner dari v1.4. P0 lain tetap dipertahankan.

## Perubahan utama

- Mengikuti dependensi yang benar-benar ditemukan pada manifest/lockfile ZIP.
- MySQL/InnoDB menjadi database pengembangan, demo, dan pengujian integrasi. SQLite hanya dicatat sebagai konfigurasi awal ZIP.
- Upload tanpa ClamAV, daemon antivirus, karantina, atau job scan. Pemeriksaan format/ukuran dan akses privat tetap wajib. Status `ready` berarti tersimpan serta lolos validasi format, bukan bebas malware.
- Attendance dicatat manual oleh Organizer; Volunteer hanya melihat hasil. Tidak ada self check-in atau QR pada MVP.
- Dashboard analitik hanya Admin; Volunteer/Organizer memakai Ringkasan Aktivitas pada web utama.
- Mempertahankan indigo, sidebar admin gelap, kartu putih, Inter, Blade, dan komponen dasar ZIP.

## Status paket

Ini paket spesifikasi dan pembagian implementasi. Catatan pemeriksaan ZIP berikut adalah kondisi awal, bukan status aplikasi setelah SF. Fitur yang direncanakan belum otomatis tersedia; bukti implementasi terkini ada di [handoff aplikasi](../docs/SF_HANDOFF.md). Audit ZIP dilakukan melalui kode, bukan pengujian runtime karena PHP/Composer tidak tersedia pada lingkungan pemeriksaan. Dependensi yang dicatat merupakan isi lockfile, bukan rekomendasi versi terbaru.

## Definition of Done tim

Seluruh P0 terhubung, UAT kritis lulus pada database MySQL/InnoDB yang dipakai tim, bukti pengujian tersedia, setiap anggota mendemonstrasikan modulnya, dan README aplikasi memuat instalasi, sandbox payment, akun demo, backup/restore, serta ERD. Pekerjaan integrasi dan perbaikan dibagi sesuai pemilik modul, tidak dibebankan seluruhnya kepada A4.

## Identitas sumber

SHA-256 ZIP yang diperiksa: `ae96347803889a39864f60e24eab3da40d7f18f5440200053bbb34e2033ebedf`. Versi framework dan dependensi ditulis dari lockfile ZIP tersebut.

## Keputusan MySQL dan panduan menjalankan (sejak revisi 1.4.1)

Keputusan pengguna 4 Oktober 2026: database memakai MySQL. SF menyelaraskan konfigurasi database dan README awal; A1 menggabungkan README akhir; semua anggota memperbarui migration/tes modulnya. Panduan instalasi dan menjalankan aplikasi merupakan deliverable wajib setiap implementasi, bukan dibuat otomatis oleh Laravel. A1 menyatukan panduan; A2 menulis payment, A3 storage/retensi, A4 worker/scheduler/assessment. Anggota lain harus berhasil mengikuti README pada lingkungan baru sebelum tugas selesai.

Panduan operasional yang harus ditulis tersedia pada bagian 4–5 [baseline dan tools](docs/BASELINE_DAN_TOOLS.md). README dalam paket ini adalah petunjuk dokumen; README repository aplikasi nanti wajib berisi perintah yang benar-benar telah diuji pada kode akhir.

## Revisi 1.4.2 — login bersama dan shared foundation

- Semua role memakai `/login` dan `/logout`, satu guard `web`; layout/dashboard admin tetap terpisah dan terlindungi role.
- Kamu menyelesaikan [SHARED_FOUNDATION.md](docs/anggota/SHARED_FOUNDATION.md) pada `feature/shared-foundation`, lalu kembali ke A4. SF bukan anggota kelima.
- A1 melanjutkan admin setelah menerima commit SF. A2/A3 dapat mulai dengan kontrak dan fixture; mereka tidak membuat ulang fondasi.
- [PROMPT_SHARED_FOUNDATION.md](docs/PROMPT_SHARED_FOUNDATION.md) adalah instruksi eksekusi yang siap disalin. Prompt tidak menggantikan aturan pusat.
- Gerbang serah-terima SF berbeda dari UAT lengkap aplikasi. Semua status pengujian masih belum diuji sampai implementasi dijalankan.
- Ringkasan Aktivitas dan notifikasi tetap A4; belum dialihkan ke A1.

Paket kini berisi 12 dokumen Markdown. Nama ZIP dipertahankan untuk kesinambungan; versi isi yang berlaku adalah **1.4.2**. Gunakan paket ini sebagai acuan tim; PRD/desain lama di luar paket tetap arsip, bukan aturan yang dicampur saat coding.

## Keputusan lanjutan 5 Oktober 2026

Beranda publik dan dashboard admin baseline dipertahankan, bukan diganti shell penuh. SF memulihkan beranda dan mempertahankan auth/layout bersama; A2 melanjutkan beranda/katalog/detail, A1 melanjutkan dashboard dasar menjadi analitik. A3/A4 tetap sesuai brief. Shell hanya untuk fitur yang belum ada. Penegasan ini diselaraskan pada PRD, kontrak, desain, baseline, prompt SF, UAT dan seluruh brief. Versi dasar tetap 1.4.2 dengan keputusan bertanggal ini.
