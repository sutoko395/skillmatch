# Serah-terima A3 — Tahap 1: Draft dan Submit Lamaran

Tanggal: 7 Oktober 2026 (WIB).
Status: Implementasi Tahap 1 A3 (Draft dan Submit Lamaran) telah selesai dan terverifikasi 100% lulus pengujian unit/feature test.

## 1. Branch dan Lingkup Pekerjaan

- **Branch**: `feature/a3-applications-attendance`
- **Lingkup Tahap 1**: Fitur pembuatan draft lamaran dan submit lamaran idempoten/transaksional oleh Volunteer.
- **Tugas Tahap Berikutnya** (belum dikerjakan pada tahap ini): Dokumen privat, seleksi, attendance, completion/riwayat, retensi dan recovery.

---

## 2. Schema dan Model

### Migration
`database/migrations/2026_10_06_000001_create_applications_table.php`

### Tabel `applications`
| Kolom | Tipe Data | Atribut / Constraint | Keterangan |
|---|---|---|---|
| `id` | bigint unsigned | Primary Key, Auto Increment | ID unik lamaran |
| `event_id` | bigint unsigned | Foreign Key (`events.id`), `restrictOnDelete` | ID Event |
| `event_position_id` | bigint unsigned | Foreign Key (`event_positions.id`), `restrictOnDelete` | ID Posisi Event |
| `volunteer_id` | bigint unsigned | Foreign Key (`users.id`), `restrictOnDelete` | ID Volunteer |
| `status` | varchar(32) | Default `'draft'` | Status kanonik (`draft`, `submitted`, `under_review`, dsb.) |
| `snapshot_json` | json | Nullable | Snapshot posisi & syarat saat submit |
| `submitted_at` | timestamp | Nullable | Timestamp saat lamaran dikirim |
| `decision_at` | timestamp | Nullable | Timestamp keputusan seleksi |
| `decision_by` | bigint unsigned | Foreign Key (`users.id`), `nullOnDelete` | Admin / Organizer pengambil keputusan |
| `decision_reason` | text | Nullable | Alasan keputusan seleksi |
| `revision` | int unsigned | Default `0` | Versi revisi lamaran |
| `created_at` / `updated_at` | timestamp | Nullable | Timestamps Laravel |

- **Unique Constraint**: `unique(['volunteer_id', 'event_id'])` (Satu Volunteer maksimal memiliki 1 lamaran per event).

### Model
- `App\Models\Application`
  - Relasi: `event()`, `position()`, `volunteer()`, `decisionBy()`.
  - Relasi tambahan di model `Event`, `EventPosition`, dan `User`: `applications()`.

---

## 3. Layanan Domain (ApplicationService)

File: `app/Services/ApplicationService.php`

### 1. `storeDraft(User $volunteer, EventPosition $position): Application`
- Memeriksa kelengkapan profil volunteer melalui `ProfileEligibilityService::check($volunteer)`. Menolak dengan `ValidationException` jika profil belum lengkap.
- Memeriksa apakah event dalam status `publiclyVisible()` (approved & published) serta dalam periode pendaftaran (`registration_opens_at <= now < registration_deadline`).
- Menjamin aturan **1 Draft per Volunteer per Event**:
  - Jika draft untuk event yang sama sudah ada dengan status `'draft'`, mengembalikan draft tersebut (dan memperbarui posisi jika berpindah posisi).
  - Jika lamaran sudah dalam status `'submitted'` atau setelahnya, melempar `ValidationException`.

### 2. `submit(Application $draft, User $actor): Application`
- Menjamin **Idempotensi & Transaksional** dengan locking MySQL dalam urutan ketat:
  `Volunteer -> Event -> Entitlement`.
- Jika lamaran sudah berstatus `'submitted'` / beyond draft, fungsi mengembalikan objek lamaran secara idempoten tanpa menambah counter entitlement ulang.
- Membentuk snapshot posisi via `PositionSnapshotService::build($position)`. Memeriksa kesiapan assessment (`AssessmentReadiness`).
- Memanggil `EntitlementService::consumeApplication($event)` dalam transaksi MySQL yang sama untuk memverifikasi kuota paket (`submitted_applications < max_applications`) dan menaikkan counter secara atomik.
- Mengubah status lamaran menjadi `'submitted'`, mencatat timestamp `submitted_at`, dan menaikkan `revision`.
- Mencatat log audit melalui `AuditService::record` dengan aksi `'application.submitted'`.
- **Rollback otomatis**: Jika terjadi kegagalan di langkah mana pun dalam transaksi (misal kuota paket habis atau assessment belum siap), seluruh perubahan database di-rollback termasuk increment counter entitlement dan audit log.

---

## 4. Otorisasi (ApplicationPolicy)

File: `app/Policies/ApplicationPolicy.php`

- `viewAny`: Khusus pengguna dengan role `volunteer` yang aktif dan terverifikasi.
- `view`: Hanya pemilik volunteer lamaran atau organizer pemilik event (untuk lamaran yang sudah dikirim).
- `update` & `submit`: Hanya pemilik volunteer lamaran yang aktif dan terverifikasi saat lamaran berstatus `'draft'`.

---

## 5. Controller & Routing

### Controller
`App\Http\Controllers\Volunteer\VolunteerApplicationController`
- `store(Request $request, EventPosition $position)`: Membuat draft lamaran dan redirect ke halaman detail draft.
- `index(Request $request)`: Menampilkan daftar lamaran milik volunteer terautentikasi (paginated).
- `show(Application $application)`: Menampilkan detail lamaran, event, posisi, jadwal, dan status profil.
- `submit(Request $request, Application $application)`: Mengirimkan lamaran draft.

### Routes (`routes/volunteer.php`)
Grup middleware: `['auth', 'account.active', 'role:volunteer', 'verified']`, prefix: `volunteer`, name: `volunteer.`:
- `POST /volunteer/positions/{position}/applications` -> `volunteer.applications.store`
- `GET /volunteer/applications` -> `volunteer.applications.index`
- `GET /volunteer/applications/{application}` -> `volunteer.applications.show`
- `POST /volunteer/applications/{application}/submit` -> `volunteer.applications.submit`

---

## 6. Antarmuka UI

- **Halaman Detail Event (`resources/views/events/show.blade.php`)**:
  - Menampilkan tombol CTA `"Pilih posisi dan mulai lamaran"` saat pendaftaran terbuka dan user login sebagai Volunteer.
- **Halaman Daftar Lamaran (`resources/views/volunteer/applications/index.blade.php`)**:
  - Daftar kartu lamaran volunteer dengan status badge, detail posisi, dan lokasi.
- **Halaman Detail Lamaran (`resources/views/volunteer/applications/show.blade.php`)**:
  - Status lamaran, informasi event/posisi, peringatan kelengkapan profil jika draft, dan tombol `"Kirimkan Lamaran"`.
- **Navigasi Utama (`resources/views/layouts/navigation.blade.php`)**:
  - Menambahkan tautan `"Lamaran Saya"` untuk pengguna role `volunteer`.

---

## 7. Pengujian & Verifikasi

### Pengujian Otomatis (`tests/Feature/A3ApplicationTest.php`)
8 Skenario pengujian yang dibuat dan **100% Lulus**:
1. `test_volunteer_can_create_draft_application_for_published_event`: Berhasil membuat draft lamaran dari tombol CTA detail event.
2. `test_incomplete_profile_blocks_draft_creation`: Menolak pembuatan draft jika profil volunteer belum lengkap (HTTP 422).
3. `test_store_draft_is_single_per_volunteer_per_event`: Menjamin hanya 1 draft per volunteer per event.
4. `test_successful_submit_updates_status_snapshot_and_audit`: Submit berhasil memperbarui status ke `submitted`, menyimpan snapshot, menambah counter entitlement, dan membuat audit log.
5. `test_submit_is_idempotent`: Submit ulang pada lamaran submitted bersifat idempoten tanpa menggandakan counter.
6. `test_package_quota_limit_rejects_subsequent_submissions`: Menolak submit jika kuota lamaran paket event sudah tercapai (`max_applications`).
7. `test_submission_failure_rolls_back_transaction_and_entitlement_counter`: Memastikan seluruh transaksi di-rollback jika submit gagal.
8. `test_other_volunteer_cannot_access_or_submit_others_application`: Akses lintas volunteer ditolak (HTTP 403).

### Hasil Suite Pengujian Penuh
- **Status Suite**: `136 tests passed, 680 assertions passed`.
- **Database Uji**: MySQL `skillmatch_testing`.
