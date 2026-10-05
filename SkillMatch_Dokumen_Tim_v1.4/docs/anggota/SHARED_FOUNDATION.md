# Shared foundation — dikerjakan kamu sebelum A2

Versi 1.4.2 • 4 Oktober 2026 • Kode tugas: SF • Branch: `feature/shared-foundation`.

Baca [PRD](../../PRD_SkillMatch_Tim.md), [kontrak](../KONTRAK_INTEGRASI.md), [design](../../design.md) dan [baseline](../BASELINE_DAN_TOOLS.md). SF merupakan pengalihan pekerjaan fondasi A1 kepada kamu; bukan anggota kelima dan bukan penambahan seluruh modul A1 ke A4.

## Urutan kerja dan hasil

| ID | Pekerjaan | Hasil konkret dan pemeriksaan |
|---|---|---|
| SF-01 | Baseline dan MySQL | Periksa Git, manifest/lockfile dan kode existing; pakai MySQL/InnoDB, `.env.example` tanpa secret, database test terpisah. Migration existing dan tambahan berjalan pada database kosong. Jangan upgrade stack tanpa kebutuhan. |
| SF-02 | Auth dan akses | Satu `/login`, satu guard `web`, logout bersama, registrasi Volunteer/Organizer, verifikasi email/reset password, sesi dan middleware akun aktif/role. Akun nonaktif ditolak pada request berikutnya; intended URL hanya lokal dan sah. |
| SF-03 | Profil dan master dasar | `city_id`, skill distinct dan empat level ZIP, `availability_slots` UTC dengan input WIB. Migration, relasi, form simpan/baca dan ProfileEligibilityService berfungsi. Master kota/skill/kategori memiliki seed dan aturan nonaktif/referensi. CRUD admin lengkap tetap A1. |
| SF-04 | Status organisasi | Pisahkan `is_active` dari `organizer_status`; pending/inactive organisasi tetap boleh melengkapi profil ketika akun aktif. Dasar profil/kontak dan halaman pending tersedia; review dokumen, approval, penolakan dan verifikasi ulang A1. |
| SF-05 | Layout dan route | Shell publik/pengguna serta admin, tombol/input/badge/error/empty state, Inter tunggal dan Alpine satu kali. Semua formulir login/logout mengikuti kontrak bersama. Role default mengarah ke route final yang sah. |
| SF-06 | Audit | Migration/model audit_logs dan AuditService append-only dapat dipanggil modul lain; actor/action/subject/reason/before-after aman; tidak menyimpan secret. Uji log untuk transaksi commit dan tidak ada log aksi sukses palsu saat rollback. UI pembaca audit tetap A1. |
| SF-07 | Seeder, README, serah-terima | Akun demo semua role/status, master data, data availability, panduan setup dan test/build yang benar-benar dijalankan; daftar commit, interface, migration, serta pekerjaan A1 berikutnya. |

## Batas implementasi agar tidak mengambil tugas anggota lain

- A2 tetap pemilik event/posisi/jadwal posisi/paket/payment; SF tidak membuat migration duplikat.
- A3 tetap pemilik DocumentStorageService, penyimpanan privat, seleksi dan attendance. A1 mengintegrasikan dokumen organisasi dengan A3. SF tidak membuat layanan upload kedua atau menambahkan ClamAV.
- A4 tetap pemilik screening, assessment, matching, notifikasi, ActivityReadService dan halaman Ringkasan Aktivitas. PIC A4 mengerjakannya setelah fondasi tersedia. Tidak otomatis memindahkan halaman aktivitas ke A1.
- A1 tetap pemilik fitur admin lanjutan. Sesuai keputusan pengguna 5 Oktober 2026, SF mempertahankan dashboard dasar `/admin/dashboard` yang sudah membaca angka nyata dari database, beserta aksi cepatnya. Bagian analitik yang belum ada diberi keterangan belum tersedia; shell tidak menggantikan fitur baseline yang sudah berfungsi. A1 memperluas dashboard ini sesuai PRD, tanpa angka statistik palsu.
- Bila halaman aktivitas A4 belum ada, SF menyediakan route/view sementara pada path final dengan keterangan fitur belum tersedia dan tautan profil. Catat file tersebut sebagai pengganti sementara yang nanti dilengkapi A4. Halaman ini bukan bukti FR-18/19 selesai.
- Fondasi tidak dianggap selesai hanya karena migration tersedia: simpan/baca profil, role middleware, audit dan layout harus dapat digunakan.

## Proteksi transisi

Jangan menghapus data kerja untuk memudahkan migration. Perubahan schema menggunakan migration tambahan; data lama dicoba pada salinan dan dicatat yang memerlukan koreksi. Label availability lama tidak diubah menjadi interval buatan. Koordinasikan file bersama sebelum anggota lain mengubahnya. Risiko dokumen organisasi publik dari baseline dicatat sebagai blocker integrasi A1/A3 sebelum aplikasi dipakai sungguhan, bukan disembunyikan sebagai pekerjaan selesai SF.

Gunakan branch dasar tim yang benar-benar ada dan telah disepakati; jangan mengasumsikan `develop`. Periksa perubahan lokal sebelum membuat branch. Simpan pekerjaan kecil per tahap; jangan menimpa pekerjaan anggota lain. Tidak perlu menunggu seluruh admin A1 selesai untuk membagikan SF.

## Gerbang serah-terima

1. Anggota lain mengikuti README pada database MySQL kosong dan database pengujian terpisah; migration dan seeder berhasil.
2. Semua role login dari `/login`; redirect/default/pending/email verification sesuai kontrak. Registrasi tidak dapat membuat admin, non-admin ditolak langsung dari endpoint admin, dan logout mengakhiri sesi.
3. Akun nonaktif yang sebelumnya sudah login kehilangan akses pada request terproteksi berikutnya.
4. Profil kota, skill/level, dan availability dapat disimpan serta dibaca oleh ProfileEligibilityService dengan hasil yang sesuai.
5. Layout/komponen dapat dipakai ulang dan frontend build berhasil; shell sementara diberi label jelas.
6. AuditService dapat dipanggil dan mengikuti transaksi; tidak bocor secret.
7. Tes fondasi dan build memiliki hasil nyata. Jika alat/database belum tersedia, tulis “belum diuji” beserta blocker; gerbang tidak diklaim lulus.
8. Catat commit serah-terima dan reviewer di template berikut. Setelah ditinjau/diintegrasikan sesuai proses tim, A1 melanjutkan dari commit ini dan kamu melanjutkan A2.

| Item | Isian saat implementasi |
|---|---|
| Commit dasar / commit SF | Belum diisi |
| Migration baru dan urutan | Belum diisi |
| Route/model/service bersama | Belum diisi |
| Tes, build, lingkungan dan hasil | Belum diuji |
| Reviewer dan tanggal | Belum diisi |
| Batas pekerjaan / blocker A1–A4 | Belum diisi |

Kriteria SF berfokus fondasi. UAT keseluruhan dan README final tetap kewajiban semua pemilik modul, dikoordinasikan A1. Jangan menandai seluruh UAT-35 lulus hanya karena setup fondasi berhasil.

## Pelestarian beranda - keputusan 5 Oktober 2026

SF mempertahankan beranda baseline yang sudah berfungsi: hero, CTA Masuk/Daftar dan footer tim di `welcome.blade.php`, dengan layout/navbar SF. Katalog/matching yang belum tersedia cukup dijelaskan sebagai keterangan bagian; jangan mengganti seluruh beranda dengan shell. A2 melanjutkan beranda/katalog/detail, A1 melanjutkan dashboard admin, A4 mengisi shell aktivitas. Catat pemulihan dan bukti regresi pada handoff; tidak ada perubahan schema atau reset database.

## Pemulihan ringkasan pengguna - 5 Oktober 2026

SF memulihkan gaya kartu dan informasi profil berguna dari dashboard pengguna pada route Aktivitas final. Kelengkapan Volunteer memakai ProfileEligibilityService; kota/skill/availability dan kontak/status Organizer dibaca dari pemilik sesi. Sidebar/route dashboard lama tidak dihidupkan kembali; route lama tetap redirect. Ringkasan kegiatan penuh tetap A4. Angka nol hardcoded, link kosong dan query Event approved sebagai published tidak dipakai.
