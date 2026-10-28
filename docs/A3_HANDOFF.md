# Serah-terima A3 — seleksi dan pelaksanaan, 28 Oktober 2026

Perbaikan integrasi auth Volunteer setelah merge A3: registrasi/pengiriman ulang
email dan redirect verifikasi disambungkan kembali dengan gate ApplicationService.
Browser mendapat alur verifikasi sebelum store/submit/upload/hapus dokumen,
bukan pesan akun tidak aktif. Pengecualian Organizer di bawah tetap berlaku.
Bukti terbaru dan batas SMTP/browser dicatat pada DEVELOPMENT aplikasi.

Penyesuaian pesan melamar ulang 28 Oktober 2026: ApplicationService.storeDraft
menampilkan “Lamaranmu sudah ditarik dan tidak dapat diajukan ulang pada event ini.”
untuk status withdrawn. Status lain tetap memakai pesan lamaran existing. Ini hanya
perubahan teks; aturan satu Volunteer/event, status, counter dan snapshot tidak berubah.

## Penyebab redirect dashboard Terima Pelamar — 28 Oktober 2026

Diagnosis baca-saja lamaran pengguna #7: owner aktif, organizer_status active,
email_verified_at kosong, lamaran under_review, tabel attendance tersedia. Middleware
verified A3 mengarahkan ke verification.notice; EmailVerificationPromptController
existing (commit 9dc9886) sengaja mengarahkan Organizer kembali ke dashboard. Controller
accept tidak pernah dijalankan, sehingga perbaikan redirect controller saja tidak cukup.

A3 Organizer kini mengikuti alur organisasi existing A2: auth/account.active/
role:organizer/organizer.active, tanpa syarat email tambahan. ApplicationPolicy.select
tetap memeriksa akun aktif, role, organisasi active, submitted_at dan owner event.
Email verified Volunteer dan seluruh pemeriksaan seleksi/kuota/jadwal tetap berlaku.
Tidak mengubah akun pengguna atau mengisi email_verified_at secara manual.
Perubahan ini menyelaraskan keputusan auth yang sudah terintegrasi, bukan mengembalikan
auth ke baseline lama. Berlaku juga untuk attendance/completion di grup A3 Organizer.

Verifikasi A3ExecutionTest dan ApplicationDocumentPolicyTest: **21 tes/156 assertion
lulus** di MySQL tes, termasuk approved Organizer tanpa email verified berhasil,
pending/nonaktif/lintas owner tetap ditolak dan email_verified_at tidak berubah.
Pint lulus. Tidak ada perubahan Blade/JS sejak build sebelumnya; tidak ada migration,
commit/push atau penerimaan kandidat kerja oleh agent. Status kandidat kerja dibiarkan
untuk pengguna mencoba kembali melalui tombol. Catatan sebelumnya tentang kebutuhan
email verified Organizer A3 digantikan oleh penyesuaian ini.

## Perbaikan tombol Terima Pelamar — 28 Oktober 2026

Keluhan klik Terima kembali ke dashboard/status tetap under_review ditindaklanjuti.
ExecutionController.accept sekarang redirect eksplisit ke organizer.applications.show,
bukan back() yang bergantung halaman sebelumnya. ValidationException HTML diarahkan
ke detail yang sama dengan flash error alasan; JSON tetap 422, otorisasi tetap 403.
Form native POST/CSRF dipertahankan, busy/disabled ditunda lewat Alpine nextTick dan
direset saat pageshow. Aturan seleksi, kuota, deadline dan konflik tidak diubah.

Verifikasi: A3ExecutionTest **15 tes/103 assertion lulus** pada MySQL tes terpisah,
termasuk referer dashboard, sukses accepted/attendance unrecorded dan kegagalan kuota
yang tampil di detail. Pint dan build Vite lulus. Chrome headless dengan Alpine
memastikan klik mengirim POST/CSRF ke probe HTTP lokal terisolasi melalui
`node tools/a3-browser-review.mjs --accept-submit`. Probe tidak menerima kandidat nyata;
pengujian perubahan domain dilakukan melalui PHPUnit. Tidak ada migration baru,
perubahan data kerja, commit atau push untuk perbaikan ini. Penyebab spesifik penolakan
kandidat pengguna belum dibuktikan dari request aktual; jangan mengklaim kandidat
tersebut sudah accepted.

## Dasar dan status

Branch aktual `feature/a3-applications-attendance`, dasar `34ae483` dari main yang
benar-benar tersedia dan memuat SF/A2/A4. Branch A3 lama ca3afbf adalah ancestor,
diperbarui fast-forward, tanpa reset/stash. Perubahan lokal .gitignore tidak diikutkan.
Commit hasil dicatat di bagian penutupan. Tidak push/PR/review tim/merge/deploy.
Catatan tahap 2 di bawah dipertahankan sebagai histori, bukan status implementasi terbaru.

## Hasil implementasi

- Draft berserial pada Volunteer/event, satu lamaran/event dan perubahan posisi draft
  mencabut berkas lama melalui purge ledger. Submit memeriksa akun/role/email/status
  terkini, periode/publikasi, snapshot A2/A4 dan entitlement dalam transaksi. Replay
  membaca status tersimpan sesudah evaluasi, bukan mengembalikan model submitted stale.
  Keputusan existing tentang profil yang berubah setelah draft tetap berlaku: snapshot
  profile_eligibility mencatat kenyataan untuk screening, tanpa mengarang bukti lengkap.
- Upload serial dengan submit, penggantian CV tidak menghapus file lama sebelum metadata
  baru aman. CV PDF; pendukung PDF/JPG/PNG; 5 MB/file dan lima ready/lamaran. Disk baru
  private_documents berbagi root privat dengan alias private lama. Nama acak, MIME isi,
  checksum, attachment, nosniff dan audit download. Akses EO berakhir saat withdrawal,
  cancellation, akun Volunteer nonaktif, atau masa akses terminal 90 hari terlampaui.
- Tab Semua/Siap Ditinjau, ready hanya under_review; urut matching DESC, assessment DESC,
  submitted_at ASC, id ASC. Detail membaca snapshot kandidat; city_name/bio ditambahkan
  hanya untuk submit baru, legacy tanpa data tidak di-backfill dari profil sekarang.
- SelectionService.accept/reject: owner terverifikasi, under_review, sebelum starts_at
  dan decision_deadline bila tersedia, accepted memeriksa eligibility publikasi/
  entitlement, akun Volunteer, kuota posisi dan interval snapshot setengah terbuka.
  Jadwal bersinggungan tepat di batas diperbolehkan; snapshot rusak ditahan.
  Replay keputusan sama idempoten; keputusan final tidak diganti. Reject wajib alasan.
- Accepted membuat attendance unrecorded unik. EventExecutionService.record hanya oleh
  owner untuk accepted sejak awal kegiatan; present/absent, catatan, recorded_at sebagai
  waktu pencatatan, revision stale, audit. Pencatatan pertama memulai ongoing.
- Completion sesudah ends_at, tidak ada lamaran evaluasi/assessment/review tertunda atau
  attempt in_progress, seluruh accepted mempunyai attendance final. Nol peserta wajib
  konfirmasi. Close-selection setelah pendaftaran tutup menolak pending dengan alasan,
  menghentikan attempt, mempertahankan terminal. Koreksi completed beralasan dan audit.
- Riwayat derived dari event/application/attendance: completed+accepted+present berhasil,
  absent tidak berhasil, cancelled dibatalkan. Tidak ada tabel status histori tandingan.
- Volunteer menarik pending; Organizer hanya mencatat pengunduran accepted sebelum
  starts_at dengan alasan, menghapus attendance unrecorded. Counter submit tidak turun.
  EventCancellationService.cancelApplications dipanggil A2 di transaksi yang sama;
  menutup draft/pending/accepted, mempertahankan terminal, menghentikan attempt nyata A4.
- Retensi demo draft 7 hari dan dokumen 90 hari sesudah completed/cancelled, configurable.
  Cleanup publik memerlukan A3_RETENTION_ENABLED. Purge ledger SQL dan salinan privat
  dibuat sebelum penghapusan fisik. Recovery idempoten menerapkan tombstone setelah
  restore; file hilang/checksum salah missing, file pulih yang checksum-nya tepat dapat
  ready lagi kecuali sudah dipurge. Jurnal upload memulihkan orphan setelah 24 jam.

## Schema, route, interface

Migration tambahan (histori lama tidak diubah):
`2026_10_28_000001_create_a3_execution` membuat attendances (unique application_id,
recorded_by, recorded_at, note, correction_reason) dan document_purges (unique disk/key,
reason, purged_at). `2026_10_28_000002_add_attendance_revision` menambah revision setelah
migration pertama telah dicoba di DB tes. Tidak memigrasi/backfill data kerja.

Route baru di routes/a3.php, dimuat web.php: organizer.applications.accept/reject/withdraw,
organizer.events.close-selection/complete, organizer.execution.show,
organizer.attendances.update, volunteer.applications.withdraw, volunteer.history.index.
Route lama dipertahankan. Upload throttle 20/menit, download 60/menit dan verified.
Policy Application, Attendance dan ApplicationDocument memeriksa objek/pemilik;
OrganizerDocumentPolicy terpisah dan tidak memberi Admin hak CV pelamar.

Layanan yang dipakai anggota lain:
SelectionService.accept/reject/withdraw/close; EventExecutionService.record/complete;
EventCancellationService.cancelApplications(Event, User, string): void;
AssessmentService.cancelLocked(Application, User): void (caller wajib transaksi dan
lock event/application; hanya attempt in_progress, tanpa finalize/skor palsu).
DocumentStorageService.validateOrganizationUpload mengembalikan metadata tervalidasi
untuk A1, tanpa mengklaim ready sebelum A1 menulis metadata; downloadOrganization
memakai Policy terpisah dan disk privat, tidak fallback ke disk publik.
DocumentRetentionService.queue/cleanup/recover serta command a3:documents.

Notifikasi memakai A2Dependencies → NotificationService A4 dengan key
application.<status>:application_id:revision:recipient_id,
attendance.recorded:attendance_id:revision:recipient_id,
event.completed:event_id:revision:recipient_id. a3:retry-notifications membaca audit
sesudah --after, limit 1–1000, key sama. Ini replay produsen, bukan outbox baru.

## Verifikasi aktual

Lingkungan PHP 8.4 Herd; MySQL tes 8.4.3/InnoDB. tools/sf-db-check.php --env=testing
berhasil. TestCase/tool menolak database selain skillmatch_testing atau database yang
sama dengan .env kerja. Migration tes berhasil tanpa reset/fresh/RefreshDatabase.
Perintah/hasil akhir dicatat di bagian penutupan setelah pemeriksaan terakhir.

tools/a3-concurrency-check.php --env=testing **lulus tujuh skenario**, masing-masing
dua proses/koneksi PHP/MySQL: kuota satu, Volunteer pada dua event overlap, paket
limit satu saat submit, duplicate accept, serta cancellation versus accept/submit/
attempt. Satu attendance/audit dan terminal state diperiksa. Fixture sintetis dipertahankan
di DB tes; bukan data demo kerja. Tes A4 existing yang memakai sole/count seluruh tabel
perlu isolasi fixture bila diulang setelah tool konkurensi.

Regresi A4 yang dijalankan sebelum fixture konkurensi: A3RetentionTest,
ParticipantAssessmentTest, MatchingTest, ScreeningTest: **47 tes/250 assertion lulus**.
Ini bukti tahap tersebut, bukan klaim seluruh suite sesudah semua perubahan.

Browser Chrome headless pada HTML sintetis hasil HTTP kernel 200: seleksi, pelaksanaan,
riwayat masing-masing 360/768/1280; readable, fokus keyboard dasar dan tidak ada overflow
horizontal halaman. Screenshot privat storage/app/private/a3-review/*.png. Mobile tabel
digulir di region yang dapat difokuskan. Export tools/a3-ui-review.php --env=testing,
runner node tools/a3-browser-review.mjs. Bukan browser submit end-to-end, audit
aksesibilitas lengkap, atau pengujian browser anggota lain.

Pemetaan bukti: UAT-11/12/13/14/15/16/19 melalui regresi draft/dokumen/retensi;
UAT-21/22 seleksi/urutan/race; UAT-27/28 attendance/completion/koreksi/riwayat;
UAT-31 cancellation A2/attempt A4 nyata; UAT-32 ownership/rollback; UAT-33 simulasi
restore ledger/checksum; UAT-34 build/render/browser terbatas. UAT pusat tidak
ditandai lulus penuh; belum ada reviewer tim atau pengujian setup UAT-35.

## Dependensi dan yang belum diuji

- A4 NotificationService/outbox/ActivityReadService belum ada. Hook dan replay tersedia,
  delivery/deduplikasi nyata/aktivitas belum terintegrasi. Adapter notifikasi pada tes
  hanya mock, bukan bukti A4 berfungsi.
- docs/A4_HANDOFF.md tidak tersedia; memakai tiga handoff A4 assessment/screening/matching.
- A1 perlu memakai validator/download/Policy organisasi di endpoint dan schema metadata
  miliknya, mengaudit/memigrasi berkas organisasi lama sesudah backup. A3 tidak membuat
  schema A1 tandingan dan retensi A3 tidak menghapus berkas organisasi.
- Backup+restore MySQL/file penuh dengan operator/reviewer lain, worker notifikasi,
  browser submit end-to-end, performa p95 100 event/1000 lamaran, dan seluruh UAT belum diuji.
- Database kerja tidak dimigrasi/dibersihkan/diisi fixture. Kebijakan retensi publik
  perlu keputusan; angka 7/90 hanya baseline demo terkonfigurasi.

## Menjalankan/demo

1. Backup DB+berkas, periksa target, jalankan php artisan migrate dan npm run build.
2. Pastikan assessment published, event/entitlement valid; Volunteer lengkap membuat
   draft, upload CV dan submit. Screening/matching nyata membawa assessment_pending;
   kerjakan assessment hingga under_review.
3. Organizer pemilik membuka Pelamar → Siap Ditinjau, tinjau hasil lalu accept/reject.
   Event lain/akun lain tidak berwenang. Kuota paket dan kuota posisi berbeda.
4. Sejak starts_at, Pelaksanaan → Hadir/Tidak hadir. Sesudah ends_at, tutup pending
   beralasan bila ada, selesaikan event, periksa Riwayat Volunteer dan koreksi beralasan.
5. Uji pengunduran/cancellation pada fixture tes, bukan membatalkan event kerja demi demo.
6. Retensi: a3:documents cleanup (tanpa perubahan), --execute hanya setelah target/backup
   diperiksa. Jalankan schedule:work untuk tugas harian/timeout. Setelah restore ke tes,
   pertahankan purge ledger terbaru dan jalankan a3:documents recover --execute sebelum
   akses dibuka. Petunjuk lengkap dan konfigurasi pada README.

## Penutupan verifikasi 28 Oktober 2026

Commit implementasi **27dfcfe**, branch feature/a3-applications-attendance.
Dokumentasi sudah ditulis tetapi belum di-commit: permintaan izin git add dokumentasi
ditolak pengguna, sehingga staging/commit tidak dilanjutkan. Tidak push/PR/merge/deploy;
reviewer tim belum ditetapkan. .gitignore lokal tetap tidak diikutkan.

Perintah akhir `php vendor/bin/phpunit tests/Feature/A3ExecutionTest.php
tests/Feature/A3RetentionTest.php tests/Feature/A3ApplicationTest.php
tests/Feature/A3ApplicationDocumentsTest.php tests/Unit/ApplicationDocumentPolicyTest.php`:
**49 tes / 315 assertion lulus** pada MySQL skillmatch_testing. Verifikasi tambahan
retensi/dokumen setelah audit purge: **17 tes / 105 assertion lulus**, kemudian tercakup
kembali dalam run akhir 49 tes. Angka tersebut tidak dijumlahkan sebagai tes berbeda.
Pint berkas berubah, git diff --check, route attendance dan command dry-run lulus.
`npm run build` akhir Vite 8.3.1 berhasil (CSS 55,41 kB / JS 59,98 kB), Blade cache
berhasil, tiga halaman HTTP 200 dan sembilan pemeriksaan Chrome akhir lulus.
Tujuh race dua proses sebelumnya tetap bukti aturan yang sama; cancellation versus
attempt pada tool menguji start/replay attempt in_progress, bukan race finalisasi skor
yang terpisah. Browser berupa HTML sintetis hasil render, tanpa submit interaktif.

Dokumen PRD/kontrak/desain/brief/UAT/README/DEVELOPMENT diselaraskan; aturan produk lama
yang masih berlaku dipertahankan. Backup operasional perlu menghentikan penulisan/
scheduler dan menjalankan recover untuk menguras purge yang sudah commit **sebelum**
mengambil snapshot DB/file; simpan ledger terbaru secara independen. Simulasi tombstone/
checksum lulus; restore MySQL+filesystem lengkap bersama operator lain belum diuji.

## Catatan tahap 2 terdahulu

## Perbaikan Policy dokumen — 27 Oktober 2026

File ApplicationDocumentPolicy.php sempat mendeklarasikan ApplicationPolicy sejak
commit 649e13b, sehingga Gate tidak menemukan Policy dokumen dan upload menghasilkan
403. Dikembalikan sebagai ApplicationDocumentPolicy dengan create/delete/download.
Create/delete memakai izin update ApplicationPolicy existing (Volunteer aktif pemilik,
hanya draft), download memakai izin view existing (draft hanya pemilik, EO aktif
pemilik event sesudah submit). Tidak mengganti ApplicationPolicy, dashboard, route,
alur login, verifikasi email atau penyimpanan dokumen. PRD/kontrak/desain/brief/UAT
dokumen tetap berlaku; ini pemulihan otorisasi existing, bukan fitur/status baru.

ApplicationDocumentPolicyTest: 4 tes/42 assertion lulus, termasuk auto-discovery kedua
Policy, create/delete, role/owner/nonaktif, status sesudah submit dan download.
Pint dan sintaks PHP lulus. Pemeriksaan Gate baca-saja terhadap lamaran lokal #2:
draft dan pemilik diizinkan upload. Upload/download HTTP penuh serta transaksi MySQL
belum diuji ulang pada perbaikan ini. Tidak ada migration atau perubahan .env.

## Snapshot kelengkapan profil — 11 Oktober 2026

Submit menyimpan `snapshot_json.profile_eligibility` dengan `complete` dan `missing_fields`
dari ProfileEligibilityService saat submit, di transaksi yang sama dengan snapshot dan kuota.
Relasi profil dimuat ulang oleh layanan existing sehingga hasil tidak memakai data draft lama.
A4 membaca bukti ini untuk screening profil lengkap; tidak ada penambahan validasi penolakan
submit, schema, route, UI, atau backfill lamaran historis. Lamaran lama yang belum memiliki
field ini tidak boleh dianggap otomatis lengkap atau dievaluasi dari profil terbaru.

Tes pada A3ApplicationDocumentsTest mencakup hasil profil lengkap, profil menjadi tidak
lengkap setelah draft dibuat, perubahan profil sesudah submit, dan replay yang tidak
mengubah snapshot. Pint, sintaks PHP dan `npm.cmd run build` berhasil. Tes database
**belum dijalankan**: akses akun lokal ke MySQL `skillmatch_testing` masih ditolak (1044).
Setelah akses dan migration database tes terpisah tersedia, jalankan
`php artisan test --filter=A3ApplicationDocumentsTest` dengan PHP kompatibel composer.lock.

## Perbaikan integrasi submit — 11 Oktober 2026

Lingkup perbaikan terbatas pada `ApplicationService.submit`: di bawah lock event/lamaran,
draft diperiksa ulang terhadap publikasi, lifecycle, entitlement, Organizer aktif dan
jendela pendaftaran melalui `Event::publiclyVisible` serta timestamp server. Tepat pada
deadline sudah ditolak. Penolakan tidak menulis snapshot/status/audit atau memakai kuota.
Replay lamaran yang sudah dikirim tetap idempoten, termasuk setelah deadline.
`EntitlementService.consumeApplication` sebelumnya sudah memeriksa jendela pendaftaran;
pemeriksaan baru di submit menolak lebih awal sebelum pembuatan snapshot, tanpa menghapus
pemeriksaan existing pada saat konsumsi kuota.

Pemanggilan evaluasi yang sebelumnya berada setelah `return` dipindahkan ke
`DB::afterCommit` hanya untuk transisi draft → submitted yang baru. Transaksi luar yang
rollback membatalkan callback; replay tidak mendaftarkan evaluasi kedua. ScreeningService
tetap dependensi A4: bila belum tersedia, lamaran tetap Menunggu evaluasi. Outbox/recovery
evaluasi belum diimplementasikan oleh perbaikan ini; belum merupakan bukti integrasi penuh A4.

Tes regresi ditambahkan pada `A3ApplicationTest`: tepat/setelah deadline, belum dibuka,
unpublished/cancelled, callback sesudah commit transaksi luar, stale replay, dan rollback.
Tes transaksi **belum dijalankan** pada lingkungan lokal perbaikan ini: akun database
menolak akses MySQL `skillmatch_testing` (1044). Setelah akses database tes terpisah dan
migration tersedia, jalankan `php artisan test --filter=A3Application` memakai PHP yang
memenuhi composer.lock. Database kerja tidak digunakan untuk tes.

Tidak ada migration, route, perubahan setup atau perubahan UI pada perbaikan ini.

Tanggal: 10 Oktober 2026 (WIB).
Status: Implementasi Tahap 2 A3 (Fondasi Lamaran, Dokumen Privat, Snapshot Immutable, dan Daftar/Detail Volunteer & Organizer) telah selesai dan terverifikasi **100% lulus pengujian feature test** pada MySQL `skillmatch_testing`.

---

## 1. Branch dan Lingkup Pekerjaan

- **Branch**: `feature/a3-applications-attendance`
- **Lingkup Tahap 2**:
  1. Schema dan model dokumen privat pelamar (`application_documents`).
  2. Layanan domain `DocumentStorageService`: penyimpanan disk privat, nama acak, verifikasi MIME isi & ekstensi, kompensasi kegagalan penyimpanan, dan download attachment terotorisasi.
  3. Submit lamaran dengan snapshot immutable profil dan dokumen, timestamp `submitted_at`, counter entitlement atomik, rollback kegagalan, dan fallback status `submitted` ("Menunggu evaluasi") bila evaluator A4 belum tersedia.
  4. Antarmuka UI Volunteer: unggah dokumen, hapus dokumen draft, lihat daftar dokumen tersimpan, unduh attachment, dan submit.
  5. Antarmuka UI Organizer: halaman daftar pelamar (`organizer.applications.index`) dan detail lamaran pelamar (`organizer.applications.show`) dengan preview profil kandidat dan download berkas resmi pelamar.
- **Tugas Tahap Berikutnya**: Tahap 4 Seleksi (accept/reject kuota/bentrok jadwal), Tahap 5 Attendance, Tahap 6 Completion/riwayat, Tahap 7 Retensi & pembatalan event.

---

## 2. Schema dan Model

### Migration
`database/migrations/2026_10_06_000002_create_application_documents_table.php`

### Tabel `application_documents`
| Kolom | Tipe Data | Atribut / Constraint | Keterangan |
|---|---|---|---|
| `id` | bigint unsigned | Primary Key, Auto Increment | ID unik dokumen |
| `application_id` | bigint unsigned | Foreign Key (`applications.id`), `cascadeOnDelete` | ID Lamaran |
| `uploader_id` | bigint unsigned | Foreign Key (`users.id`), `restrictOnDelete` | ID Pengunggah (Volunteer) |
| `document_type` | varchar(32) | `'cv'`, `'supporting'` | Jenis dokumen |
| `disk` | varchar(32) | Default `'private'` | Disk penyimpanan privat |
| `storage_key` | varchar(255) | Path acak relatif di disk privat | Lokasi penyimpanan fisik |
| `original_name` | varchar(255) | Nama file asli saat diunggah | Nama file attachment |
| `mime_type` | varchar(100) | MIME type hasil inspeksi konten | MIME type valid |
| `size_bytes` | bigint unsigned | Ukuran byte (maksimal 5 MB) | Ukuran file |
| `checksum_sha256` | varchar(64) | SHA-256 hash | Integritas file |
| `status` | varchar(32) | Default `'ready'` | `'ready'`, `'purged'`, `'missing'` |
| `ready_at` | timestamp | Nullable | Timestamp siap diakses |
| `purged_at` | timestamp | Nullable | Timestamp retensi pembersihan |
| `timestamps` | timestamp | Nullable | Timestamps Laravel |

- **Indeks**: `['application_id', 'document_type']`, `['application_id', 'status']`.

### Model
- `App\Models\ApplicationDocument`
  - Relasi: `application()`, `uploader()`.
- Update `App\Models\Application`:
  - `documents()`, `cvDocument()`, `supportingDocuments()`.

---

## 3. Layanan Domain (DocumentStorageService & ApplicationService)

### 1. `App\Services\DocumentStorageService`
- **Penyimpanan Privat**: Disk `'private'` (`storage/app/private`), tanpa URL publik, symlink, atau iframe publik.
- **Validasi Ketat**:
  - CV: format file wajib PDF (`application/pdf`).
  - Pendukung: format file wajib PDF, JPG, atau PNG (`application/pdf`, `image/jpeg`, `image/png`).
  - Batas ukuran: maksimal 5 MB per file.
  - Batas kuota file: maksimal 5 file per lamaran. CV otomatis menggantikan CV lama bila diunggah ulang pada status draft.
- **Kompensasi & Resiliensi**: Jika pembuatan entri database gagal, file fisik di disk privat otomatis dihapus seketika agar tidak terjadi file yatim (*orphan*).
- **Download Terotorisasi**: Menggunakan attachment streaming dari controller dengan Policy ketat.

### 2. `App\Services\ApplicationService`
- **Snapshot Immutable**: Saat `submit()`, `snapshot_json` menyimpan posisi, profil volunteer (nama, email, phone, city_id, skills dengan level, availability slots), dan metadata dokumen resmi (id, tipe, nama asli, ukuran, checksum sha256). Perubahan data profil volunteer setelah submit tidak akan mempengaruhi histori snapshot yang sudah tersimpan.
- **Pemicu Evaluasi**: Pasca-commit, sistem memicu proses evaluasi (bila A4 terdaftar). Bila evaluator belum tersedia, status lamaran tetap `submitted` dengan badge tampilan antarmuka **"Menunggu evaluasi"**.

---

## 4. Otorisasi (ApplicationDocumentPolicy)

File: `app/Policies/ApplicationDocumentPolicy.php`

- `create`: Volunteer pemilik lamaran dengan akun aktif dan lamaran berstatus `'draft'`.
- `delete`: Volunteer pemilik lamaran dengan akun aktif dan lamaran berstatus `'draft'`.
- `download`:
  - Volunteer pemilik lamaran aktif.
  - Organizer pemilik event aktif, **hanya jika lamaran berstatus `submitted` atau setelahnya** (draft volunteer ditolak).
  - Pihak lain (termasuk Admin dan organizer event lain) ditolak (HTTP 403) sesuai PRD: *Admin tidak otomatis mendapat akses seluruh dokumen pelamar*.

---

## 5. Controller & Routing

### Routes Baru:
- `POST /volunteer/applications/{application}/documents` -> `volunteer.documents.store`
- `DELETE /volunteer/documents/{document}` -> `volunteer.documents.destroy`
- `GET /documents/{document}/download` -> `documents.download`
- `GET /organizer/events/{event}/applications` -> `organizer.applications.index`
- `GET /organizer/applications/{application}` -> `organizer.applications.show`

---

## 6. Antarmuka UI

1. **Detail Lamaran Volunteer (`resources/views/volunteer/applications/show.blade.php`)**:
   - Status badge dengan label status **"Menunggu evaluasi"** saat berstatus `submitted`.
   - Tabel dokumen tersimpan dengan label "Dokumen tersimpan", ukuran, dan link unduh.
   - Form upload file CV & Dokumen Pendukung saat status masih `draft`.
2. **Daftar Pelamar Organizer (`resources/views/organizer/applications/index.blade.php`)**:
   - Filter posisi, daftar pelamar yang sudah `submitted`, badge "Menunggu evaluasi", dan link detail kandidat. Draft volunteer tidak ditampilkan kepada organizer.
3. **Detail Lamaran Organizer (`resources/views/organizer/applications/show.blade.php`)**:
   - Detail profil kandidat, posisi, kebutuhan skill, jadwal tugas, dan link unduh dokumen resmi kandidat.
4. **Navigasi Sidebar**:
   - Menu `"Lamaran Saya"` ditambahkan pada navigasi sidebar volunteer.
   - CTA `"Kelola Pelamar Event"` ditambahkan pada halaman detail event organizer.

---

## 7. Pengujian & Verifikasi

### Pengujian Otomatis (`tests/Feature/A3ApplicationDocumentsTest.php`)
7 Skenario pengujian terarah dan **100% Lulus**:
1. `test_volunteer_can_upload_cv_and_supporting_documents_to_draft`: Berhasil mengunggah CV dan dokumen pendukung pada draft dengan disk privat dan metadata lengkap.
2. `test_upload_validates_file_extension_and_content_mime_type`: Menolak format tidak sah (bukan PDF untuk CV, bukan PDF/JPG/PNG untuk pendukung) dengan HTTP 422.
3. `test_upload_validates_max_file_size_and_max_documents_count`: Menolak file > 5 MB dan menolak upload melebihi batas 5 file per lamaran.
4. `test_volunteer_can_delete_document_during_draft`: Volunteer berhasil menghapus dokumen saat draft dan file fisik terhapus.
5. `test_authorized_document_download_rules`: Hak unduh terverifikasi (Volunteer pemilik boleh, Volunteer lain ditolak, Organizer ditolak saat draft, Organizer boleh setelah submit, Organizer lain ditolak, Admin ditolak).
6. `test_submit_stores_immutable_snapshot_and_profile_changes_do_not_affect_it`: Perubahan profil volunteer setelah submit terbukti tidak mengubah snapshot historis.
7. `test_organizer_can_view_submitted_applications_list_and_detail_with_status_menunggu_evaluasi`: Organizer dapat melihat daftar kandidat `submitted` dengan label "Menunggu evaluasi" dan mengunduh berkasnya.

### Pengujian Regresi Tahap 1 (`tests/Feature/A3ApplicationTest.php`)
8 Skenario pengujian Tahap 1 tetap **100% Lulus** (32 assertions).

### Hasil Suite Pengujian Modul A3:
- **Total A3 Feature Tests**: 15 tests, 88 assertions passed.
- **Frontend Build**: `npm.cmd run build` (Vite v8.3.1) sukses.
- **Blade Caching**: `php artisan view:cache` sukses.
- **Database Uji**: MySQL `skillmatch_testing`.
