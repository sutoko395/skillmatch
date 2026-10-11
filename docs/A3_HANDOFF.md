# Serah-terima A3 — Tahap 2: Fondasi Lamaran dan Dokumen Privat

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
