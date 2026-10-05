# Brief A3 — lamaran, dokumen, seleksi, pelaksanaan

Versi 1.4.2 • Baca [PRD](../../PRD_SkillMatch_Tim.md), [kontrak](../KONTRAK_INTEGRASI.md), [design](../../design.md), [UAT](../UAT.md).

## Titik mulai

Belum ada model/tabel/alur lamaran atau attendance di ZIP. Upload organisasi yang ada bukan upload pelamar. Gunakan pemilik event dari relasi posisi, bukan user ID yang dikirim formulir.

Kondisi setelah A2: schema/model event, posisi, jadwal, paket, order dan entitlement sudah tersedia pada feature/a2-events-payment. Gunakan kode tersebut dan [A2_HANDOFF](../../../docs/A2_HANDOFF.md); jangan membuat tabel/model tandingan. Catatan ZIP adalah baseline historis. Pastikan commit A2 yang disepakati tersedia pada branch kerja; belum dinyatakan merge ke main.

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

## Integrasi implementasi A2 terbaru - 5 Oktober 2026

### Submit, paket dan snapshot

- Gunakan App\Services\EntitlementService.consumeApplication(Event) dalam transaksi submit yang sama. Kunci Volunteer lalu event, periksa unique application/submitted_at untuk idempotensi sebelum increment. Commit snapshot, dokumen/transisi dan counter bersama; kegagalan submit harus me-rollback counter.
- Batas max_applications adalah lamaran terkirim per event, bukan kuota accepted per posisi. Withdrawal tidak mengembalikan counter. Kuota posisi dan bentrok jadwal pada seleksi tetap tanggung jawab A3.
- Free/Standard/Premium sudah memiliki konfigurasi awal yang disepakati pada PRD/kontrak. Admin dapat mengedit harga/limit/status; baca event_entitlements dan snapshot event/order yang berlaku. Jangan hardcode nama tier atau membaca limit paket master terbaru untuk histori pembelian lama. Free tidak memerlukan order paid.
- PositionSnapshotService.build(EventPosition) menyediakan position_id/event_id/city_id, skills (id/minimum_level/is_required), schedules (start/end ISO UTC), requirements (name/description/kind/document_type/is_required), flag availability/kota, assessment_version dan rule_version match-v1.4. Binding assessment A4 belum tersedia berarti pembentukan snapshot ditolak; fixture/mock hanya untuk tes.
- Requirement kind manual/document dan document_type cv/supporting mengikuti schema A2. Tetap gunakan DocumentStorageService A3, metadata/disk privat dan Policy per konteks. Struktur kebutuhan dokumen A2 tidak berarti layanan upload sudah tersedia.

### Pembatalan dan UI

Sediakan App\Services\EventCancellationService.cancelApplications(Event, User actor, string reason): void. A2 memanggilnya dalam transaksi pembatalan setelah lock pemilik/event; layanan A3 harus menutup lamaran dan meminta pembatalan attempt A4 secara atomik/idempoten. Review urutan lock bersama A2/A4 terhadap submit/seleksi agar tidak terjadi deadlock; jangan commit terpisah atau menghidupkan kembali state terminal melalui job terlambat. Tanpa layanan ini pembatalan event A2 tetap ditolak.

Katalog/detail dan beranda publik kini sudah tersedia. Integrasikan CTA lamaran pada route kontrak setelah endpoint A3 lengkap dan terotorisasi; jangan menganggap kartu event/CTA menjadi bukti submit berfungsi. Layout/navbar serta login/register baru tetap auth SF satu guard. Aktivitas pengguna tetap A4. Status Dalam pengembangan pada kartu paket hanya diubah setelah fitur nyata tersedia dan dokumen/UAT diselaraskan bersama A2.

### Gate integrasi yang masih diperlukan

Uji submit ulang dan dua proses pada batas lamaran, rollback counter saat dokumen/snapshot gagal, withdrawal tanpa pemulihan limit, perubahan master paket setelah pembelian, akses pemilik/role/status, cancelled menolak submit, serta cancellation bersamaan dengan submit/seleksi/attempt. Mock A3 pada tes A2 dan reserve counter A2 tidak membuktikan semua gate ini lulus. A3 dapat mengerjakan bagian independen dengan fixture kontrak sambil menunggu evaluasi/attempt A4.
