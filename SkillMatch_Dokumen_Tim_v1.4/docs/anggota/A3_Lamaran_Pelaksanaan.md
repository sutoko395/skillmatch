## Penutupan seleksi di Kelola Pelamar - 15 November 2026

Penutupan seleksi beralasan kini ditampilkan di daftar pelamar event. Detail
pelaksanaan hanya memberi tautan Ke Kelola Pelamar saat pending. Route close
existing, serviceguarddeadline/terminal/konfirmasi, alasan, audit dan notifikasi
tetap; tidak memindahkan kepemilikan A3 atau mengubah aturan completion.

## Persiapan sesi otomatis untuk absensi belum dicatat - 15 November 2026

Unrecorded/missing legacy accepted pada event nonterminal disiapkan otomatis via
POST CSRF saat detail dibuka; GET tetap readonly. Endpoint initialize dan service
initializeUnrecorded mengikuti kontrak terbaru; idempoten dengan lock Event/App/
Attendance. Legacy recorded dan terminal tidak dikonversi otomatis. Sesi baru
Belum dicatat; snapshot/arsip lama dipertahankan, tanpa migration baru.

## Check-in peserta dan catatan terpisah - 15 November 2026

Keputusan pengguna: kartu peserta dapat dibuka/tutup, check-in satu klik dan
catatan opsional disimpan terpisah tanpa mengubah kehadiran. Endpoint/layanan
updateNote milik A3 ditambah mengikuti kontrak terbaru; tanpa migration. Catatan
terlihat oleh volunteer, bukan catatan privat panitia. Statusfinal dan syaratwaktu
existing tetap. Bukti terkini ada di ORGANIZER_UI_REFINEMENT_HANDOFF.md aplikasi.

## Absensi berhenti saat completed - keputusan pengguna 15 November 2026

Selesai mengunci semua pencatatan/koreksi absensi, termasuk reasoned correction
legacy maupun sesi. Service memeriksa terminal setelah lock dan sebelum no-op.
Prepare terminal juga ditolak. Data/histori koreksi lama tetap; tidak ada migration.
Syarat completion/awal sesi/role/owner/revision tetap. Form koreksi selesai hilang,
checkbox completion menjelaskan penguncian. PRD/kontrak/UAT terbaru menggantikan
izin koreksi completed sebelumnya; histori handoff lama tetap catatan saat dibuat.

## Hub kerja EO mengikuti tahap - keputusan pengguna 15 November 2026

Kelola Pelamar hanya kegiatan dengan published_at terisi, termasuk riwayat;
search dan recruitment=open/closed/history dengan pagination12. Pelaksanaan
memuat kegiatan pernah published sejak tanggal mulai00.00 WIB, terminal tetap
riwayat meski dibatalkan dini. Tampilan Hari ini/Berlangsung/Riwayat mengikuti
jadwal, tanpa mengubah lifecycle atau melonggarkan seleksi/absensi/sesi/completion.
Endpoint detail dan akses riwayat tetap, count submitted/accepted existing tetap.
Rincian ada pada PRD/kontrak/UAT pembaruan15 November2026; tidak ada schema baru.

# Brief A3 — lamaran, dokumen, seleksi, pelaksanaan

## Kehadiran per sesi/hari - keputusan pengguna 3 November 2026

Organizer mencatat setiap sesi/hari dari snapshot jadwal tugas peserta accepted.
Interval melewati tengah malam dipisah menurut tanggal WIB. Completion menunggu
seluruh sesi selesai dan tercatat; partisipasi berhasil jika semua sesi hadir.
Absensi lama tetap berlabel per kegiatan, tidak disalin sebagai hadir setiap hari.
Konversi legacy pada event nonterminal meminta konfirmasi/alasan dan menyimpan arsip.
Halaman, filter, rekap dan route lama dipertahankan dengan kompatibilitas terbatas
untuk satu sesi; beberapa sesi harus dicatat melalui endpoint per sesi.
Schema, aturan, endpoint, demo dan bukti tes: [handoff sesi](../../../docs/SESSION_ATTENDANCE_HANDOFF.md).
Ketentuan lama yang membatasi attendance satu catatan per event digantikan keputusan ini.


Pembaruan integrasi 3 November 2026: lihat [handoff perbaikan alur](../../../docs/INTEGRATED_FLOWS_HANDOFF.md) untuk perilaku, interface, migration dan batas verifikasi terbaru. Tampilan dan fitur existing dipertahankan.

Status lintas modul terkini: [daftar pekerjaan tersisa](../../../docs/DEVELOPMENT.md#daftar-pekerjaan-tersisa--pembaruan-28-oktober-2026).
Pisahkan pekerjaan belum dibuat, belum diuji, dan selesai lokal yang belum diintegrasikan.

Batas perubahan hasil kerja: ikuti [persetujuan pada AGENTS.md](../../../AGENTS.md#persetujuan-perubahan-hasil-kerja-dan-tampilan).
Pertahankan fitur dan tampilan yang sudah dibangun. Permintaan pengguna memberi
izin dalam lingkupnya; penggantian, penghapusan atau redesign di luar lingkup
memerlukan usulan sebelum-sesudah dan persetujuan terlebih dahulu. Brief ini
bukan izin untuk menimpa hasil anggota lain atau mengembalikan baseline lama.

Integrasi auth Volunteer 28 Oktober 2026: middleware verified dipasang pada
store/submit lamaran serta upload/hapus dokumen agar browser menuju verifikasi,
sementara ApplicationService tetap memeriksa email dan status akun. Registrasi
mengirim tautan, akun lama dapat kirim ulang, profil tetap dapat diisi. Jangan
menghapus gate service sebagai solusi 403 atau menambah gate email Organizer A3.
Rincian alur dan pengujian di DEVELOPMENT aplikasi.

## Status implementasi 28 Oktober 2026

Tahap 1–7 sudah memiliki implementasi di feature/a3-applications-attendance dari
34ae483: draft/submit/dokumen existing dipertahankan dan diperkuat, seleksi,
withdrawal/close-selection, attendance/completion/riwayat, cancellation nyata A2/A4,
serta retensi/recovery. Ini bukan klaim semua UAT selesai. Bukti tes/race/build/browser,
schema/route, demo dan blocker tercatat di docs/A3_HANDOFF.md aplikasi.
NotificationService/outbox/ActivityReadService A4 belum ada; hook/replay siap, delivery
dan aktivitas lengkap tertunda. Validator/download/Policy organisasi tersedia untuk
A1, pemasangan ke schema/endpoint A1 masih tertunda. Catatan baseline ZIP di bawah
adalah histori; tidak memerintahkan penghapusan implementasi baru.

## Konsumsi jadwal posisi — 10 Oktober 2026

A2 menyediakan opsi mengikuti event atau jadwal khusus; kedua mode tetap menghasilkan interval UTC pada `position_schedules` dan snapshot `[start,end]` yang sama. A3 tetap memakai snapshot interval pada submit dan pemeriksaan konflik, tanpa membaca ulang mode/profil untuk histori. Jadwal existing dan snapshot submitted tidak ditimpa. Tidak ada perubahan kepemilikan seleksi/attendance.

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
| 6 | Completion/riwayat | Semua attendance final dan seleksi tidak menggantung; selesai sesudah akhir; riwayat derived; absensi final terkunci setelah selesai. |
| 7 | Retensi dan recovery | Cleanup draft/berkas kedaluwarsa, purge ledger, kompensasi simpan gagal, integrasi pembatalan dan penarikan. |

## Upload yang disepakati

CV PDF; pendukung PDF/JPG/PNG; 5 MB per file, maksimal lima file per lamaran. Ekstensi dan MIME berdasarkan isi harus cocok. File tidak valid tidak menjadi ready. Tidak menambahkan scanner, daemon, karantina atau job antivirus. Label UI "Dokumen tersimpan", bukan "Bebas virus".

Gunakan private_documents dan nama acak. Download sebagai attachment dari controller. Jangan preview PDF pelamar lewat iframe publik; metadata tetap terlihat. Simpan ukuran/MIME/checksum dan log download tanpa isi file. Utility penyimpanan diberikan juga ke A1 untuk dokumen organisasi; Policy tetap berbeda per konteks.

## Attendance yang disepakati

- Hanya Organizer event; tidak ada self check-in Volunteer.
- Satu rekap per accepted, dengan rincian sesi/hari dari snapshot; setiap sesi berstatus awal unrecorded.
- Tabel: nama, posisi, status, waktu pencatatan, catatan, aksi. Waktu pencatatan tidak diberi label jam datang.
- Filter posisi/status dan pencarian; bulk action boleh untuk peserta yang dipilih dengan konfirmasi jumlah.
- Completed memerlukan akhir jadwal sudah lewat dan tidak ada unrecorded. Setelah completion absensi terkunci, tidak ada koreksi meski beralasan.
- Sesi/hari menjadi unit kehadiran sesuai keputusan terbaru; QR/GPS/performance tetap di luar lingkup.

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

## Tambahan A3 - akses koordinasi peserta

Detail lamaran menggunakan EventCoordinationService.forParticipant untuk status accepted aktual milik Volunteer aktif dan event non-cancelled. Data privat tidak berada pada snapshot atau katalog. Penerimaan menampilkan koordinasi terbaru; withdrawn/cancelled kehilangan akses. Jadwal tugas, absensi per sesi dan completion tetap berlaku tanpa syarat isi koordinasi. Tidak ada input arahan, titik kumpul atau waktu kedatangan. Keanggotaan grup dikelola eksternal oleh organizer. Lihat docs/PARTICIPANT_COORDINATION_HANDOFF.md root aplikasi.


## Usia pelamar pada detail EO ? 15 November 2026
ApplicationService::submit menambah profile.birth_date (YYYY-MM-DD nullable) ke snapshot lamaran baru. Ini data pribadi untuk owner terotorisasi, bukan bagian audit/notifikasi atau rumus evaluasi. Detail lamaran EO menghitung tahun penuh pada tanggal starts_at dalam WIB dan menampilkan usia bersama saran usia posisi. Tidak ada eliminasi otomatis atau perubahan bobot/score.
Lamaran lama tanpa key birth_date memakai tanggal lahir profil saat ini dan menampilkan sumbernya secara eksplisit; snapshot lama tidak ditulis ulang. Snapshot dengan tanggal kosong/tidak valid atau tanggal lahir setelah tanggal kegiatan menampilkan Belum tersedia. Tidak ada migration atau route baru untuk usia pelamar.
UAT: ulang tahun tepat hari kegiatan, sehari sesudahnya, starts_at UTC yang masuk tanggal WIB berikutnya, data lama/missing/invalid, perubahan profil setelah submit tidak mengubah usia dari snapshot, owner lain ditolak. Alur terintegrasi diuji dari fixture pembuatan event/posisi, asesmen terbit, ajukan moderasi, admin approve/publikasi paket gratis, submit lamaran di luar saran usia, penyaringan/pencocokan/asesmen, accepted, absensi sesi, completed, riwayat dan final lock. Pada DatabaseTransactions, afterCommit tertunda sampai rollback; evaluasi nyata dipanggil eksplisit dalam tes, bukan bukti worker/queue produksi.


## Konsistensi panel Volunteer - 15 November 2026
Presentasi volunteer disamakan dengan EO/Admin sesuai permintaan pengguna.
Komponen/partial/alur existing tetap; enum, snapshot, endpoint, otorisasi,
rumus evaluasi, asesmen dan absensi tidak berubah. Label UI/notifikasi memakai
Volunteer, Kegiatan, Keahlian, Penyaringan, Asesmen dan Draf. Lihat
[handoff](../../../docs/VOLUNTEER_EO_STYLE_HANDOFF.md) untuk lingkup, integrasi dan
bukti pengujian; tidak ada dependensi/migration baru.
