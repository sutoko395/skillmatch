# Brief A3 — lamaran, dokumen, seleksi, pelaksanaan

Versi 1.4.2 • Baca [PRD](../../PRD_SkillMatch_Tim.md), [kontrak](../KONTRAK_INTEGRASI.md), [design](../../design.md), [UAT](../UAT.md).

## Titik mulai

Belum ada model/tabel/alur lamaran atau attendance di ZIP. Upload organisasi yang ada bukan upload pelamar. Gunakan pemilik event dari relasi posisi, bukan user ID yang dikirim formulir.

## Pekerjaan berurutan

| Tahap | Deliverable | Penerimaan |
|---|---|---|
| 1 | Draft dan submit | Satu Volunteer/event, deadline timestamp, unique constraint, snapshot, idempotensi, entitlement limit. |
| 2 | Dokumen privat | Format/ukuran/jumlah tervalidasi; ready setelah file+metadata berhasil; download Policy; tanpa ClamAV. |
| 3 | Daftar/detail lamaran | Volunteer melihat miliknya; Organizer melihat lamaran submitted event sendiri; tab Semua/Siap Ditinjau. |
| 4 | Seleksi | Tampilkan hasil A4; menerima hanya under_review; kuota/bentrok jadwal dipastikan di transaksi MySQL/InnoDB; alasan reject. |
| 5 | Pelaksanaan | Accepted otomatis memiliki attendance unrecorded; Organizer mencatat present/absent dan audit. |
| 6 | Completion/riwayat | Semua attendance final dan seleksi tidak menggantung; selesai sesudah akhir; riwayat derived; koreksi beralasan. |
| 7 | Retensi dan recovery | Cleanup draft/berkas kedaluwarsa, purge ledger, kompensasi simpan gagal, integrasi pembatalan dan penarikan. |

## Upload yang disepakati

CV PDF; pendukung PDF/JPG/PNG; 5 MB per file, maksimal lima file per lamaran. Ekstensi dan MIME berdasarkan isi harus cocok. File tidak valid tidak menjadi ready. Tidak menambahkan scanner, daemon, karantina atau job antivirus. Label UI "Dokumen tersimpan", bukan "Bebas virus".

Gunakan private_documents dan nama acak. Download sebagai attachment dari controller. Jangan preview PDF pelamar lewat iframe publik; metadata tetap terlihat. Simpan ukuran/MIME/checksum dan log download tanpa isi file. Utility penyimpanan diberikan juga ke A1 untuk dokumen organisasi; Policy tetap berbeda per konteks.

## Attendance yang disepakati

- Hanya Organizer event; tidak ada self check-in Volunteer.
- Satu catatan per accepted, status awal unrecorded.
- Tabel: nama, posisi, status, waktu pencatatan, catatan, aksi. Waktu pencatatan tidak diberi label jam datang.
- Filter posisi/status dan pencarian; bulk action boleh untuk peserta yang dipilih dengan konfirmasi jumlah.
- Completed memerlukan akhir jadwal sudah lewat dan tidak ada unrecorded. Koreksi setelah completion beralasan dan diaudit.
- Tidak membuat sesi/QR/GPS/performance sebagai P0.

## Serah-terima

Menerima profil dari SF (dipelihara A1 sesudah serah-terima), snapshot/eligibility posisi dan paket dari A2; menyerahkan `application.submitted` ke A4. A4 mengisi hasil screening/matching/assessment; A3 merendernya dan mengubah status seleksi melalui service sendiri. Cancellation A2 memanggil service penutupan lamaran A3 lalu pembatalan attempt A4; worker terlambat tidak menghidupkan lamaran kembali.

## Uji minimum

UAT-11, 12, 13, 14, 15, 16, 21, 22, 27, 28, 31, 33; ikut 17–20. Uji kuota/conflict menggunakan dua koneksi/proses pada satu database MySQL uji. Uji akses melalui manipulasi ID langsung, bukan hanya tombol disembunyikan.

## Demo selesai

Volunteer mengunggah dokumen dan melamar, Organizer A dapat mengunduh sementara B ditolak, hasil A4 tampil, penerimaan kuota satu aman, peserta tercatat hadir/tidak hadir, event selesai dan hasil masuk riwayat benar.

## Batas tugas

Tidak menghitung matching/assessment ulang di controller seleksi. Tidak menambah email dokumen atau integrasi Drive. Tidak menampilkan draft kepada perekrut atau memberi Admin akses seluruh CV.

## Dependensi SF

Gunakan ProfileEligibilityService, middleware akun/role dan AuditService dari SF. Fondasi SF tidak mencakup layanan upload privat; DocumentStorageService tetap A3 dan diserahkan ke A1 untuk dokumen organisasi. A3 boleh mulai modul dengan fixture sesuai kontrak dan mengambil migration event/posisi A2 lebih awal.

## Batas UI setelah koreksi baseline - 5 Oktober 2026

Beranda publik dipertahankan dan dilanjutkan A2; dashboard admin dilanjutkan A1. A3 mengintegrasikan CTA lamaran/dokumen/riwayat ke halaman A2 dan layout SF tanpa mengganti beranda atau mengambil tugas Aktivitas A4. Pemulihan beranda tidak mengubah kontrak submit, Policy dokumen, seleksi atau attendance.
