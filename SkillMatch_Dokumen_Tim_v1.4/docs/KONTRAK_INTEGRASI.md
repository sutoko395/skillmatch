# Kontrak integrasi empat anggota

Versi 1.4.2 • Pasangan [PRD induk](../PRD_SkillMatch_Tim.md). Nama di bawah merupakan target implementasi; tabel baru belum ada pada ZIP.

## 1. Aturan kerja bersama

- Satu repository, branch modul `feature/a1-foundation`, `feature/a2-events-payment`, `feature/a3-applications-attendance`, `feature/a4-assessment-matching`.
- Pemilik schema membuat migration; anggota lain mengusulkan kebutuhan lewat PR. Jangan mengedit migration lama yang sudah diterapkan anggota lain.
- SF mengoordinasikan file bersama sampai serah-terima; sesudah itu A1 mengoordinasikan merge `routes/web.php`, bootstrap/app.php, config/auth.php, CSS dan layout. Anggota menaruh route baru dalam `routes/admin.php`, `routes/organizer.php`, `routes/volunteer.php`, `routes/public.php`, atau file modul yang dimuat root. Perubahan kelompok route disepakati, bukan copy seluruh web.php.
- Gunakan namespace controller yang sudah ada: Admin, Organizer, Volunteer. Layanan bisnis dalam app/Services, Policy dalam app/Policies, Form Request dalam app/Http/Requests.
- Masing-masing modul menyertakan fixture dan tes; integrasi dilakukan per alur kecil. Jangan menunggu seluruh halaman selesai.
- Public interface layanan tidak menerima status/owner/harga dari input bebas. Controller memeriksa actor, layanan memeriksa ulang aturan bisnis.

## 2. Kepemilikan schema dan kompatibilitas ZIP

| Tabel / model | Kondisi baseline | Pemilik target dan perubahan |
|---|---|---|
| users / User | Ada, role + is_active + organizer_status | SF; guard web bersama/verification/account middleware; A1 UI suspension. |
| volunteer_profiles / VolunteerProfile | Ada, city teks, availability label | SF; tambah city_id dan availability_slots; field lama untuk transisi. |
| volunteer_skills / VolunteerSkill | Ada, level dan unique(user_id,skill_id) | SF; pertahankan nama ini, bukan user_skills. Validasi distinct skill. |
| organizer_profiles / OrganizerProfile | Ada | SF struktur/edit kontak dasar; A1 alur dokumen dan verifikasi ulang terkontrol. |
| organizer_documents / OrganizerDocument | Ada, file_path publik | A1 schema, utilitas file A3; tambah disk/storage_key/metadata/status. Migrasi berkas lama sesudah backup. |
| skills, categories | Ada | SF schema/seed dan proteksi referensi; A1 halaman CRUD/nonaktifkan. |
| cities, availability_slots | Baru | SF; master kota dan interval UTC milik user. |
| events / Event | Ada, status draft/pending/approved/rejected | A2; pertahankan status untuk moderasi; tambahkan publication_status, lifecycle_status, city_id, published_at, completed_at, cancelled_at, ends_at/starts_at kanonik. |
| event_positions / EventPosition | Ada | A2; jangan membuat tabel positions baru; tambah aturan required_full_availability, required_same_city bila dipakai. |
| position_skills / PositionSkill | Ada, event_position_id, minimum_level | A2; tambah is_required; unique posisi/skill. |
| position_requirements / PositionRequirement | Ada, nama/deskripsi/is_required | A2; tambah kind dan document_type untuk syarat yang dapat diproses. Teks deskripsi saja bukan aturan otomatis. |
| position_schedules | Baru | A2; event_position_id, starts_at, ends_at. |
| applications | Baru | A3; event_id, event_position_id, volunteer_id, status, snapshot_json, submitted_at, decision_at, decision_by, decision_reason, revision. |
| application_documents | Baru | A3; application_id, uploader_id, document_type, disk, storage_key, original_name, mime_type, size_bytes, checksum_sha256, status, ready_at/purged_at. |
| screening_results | Baru | A4; application_id unik, passed, checks_json, rule_version, evaluated_at. |
| match_results | Baru | A4; application_id unik, skill_score, availability_score, location_score, total_score, rule_version, breakdown_json. |
| assessments, assessment_questions, assessment_options | Baru | A4; event_position_id, version, duration_minutes, is_published; opsi benar hanya server. |
| assessment_attempts, assessment_answers | Baru | A4; application_id unik, assessment_id/version, status, started_at, expires_at, submitted_at, score; unique attempt/question untuk jawaban. |
| packages, orders, payment_events, event_entitlements | Baru | A2; snapshot paket, nominal integer rupiah, order_ref unik, paid_at/activated_at stabil, gateway_event_key unik. |
| attendances | Baru | A3; application_id unik, status, recorded_by, recorded_at, note. |
| notifications, notification_outbox | Baru | A4; recipient_id/tipe/data, dedupe_key unik, retry dan delivered_at. |
| audit_logs | Baru | SF; actor_id, action, subject_type/id, before/after aman, reason, timestamp. |
| content_pages, operational_settings | Baru | A1; teks terbatas dan allowlist settings, bukan secret atau kode. |

Semua FK memakai nama konsisten di atas. Unique applications(volunteer_id,event_id) mencakup draft dan withdrawn. Server menurunkan event_id dari posisi dan memeriksa kesesuaian; dapat diperkuat unique composite pada posisi dan FK pasangan. Jangan mengizinkan mass assignment event_id/volunteer_id mentah.

Histori pembayaran/lamaran/attendance memakai referensi yang tidak dihapus berantai oleh UI. A1 mengganti aksi delete akun menjadi suspend. Migration perubahan FK dilakukan terkontrol dengan pemeriksaan orphan; hanya menonaktifkan tombol tanpa menutup endpoint tidak cukup.

### Migrasi baseline tanpa kehilangan data

1. Backup DB dan berkas; jalankan di salinan sebelum database kerja.
2. Tambah kolom nullable/master kota; normalisasi city teks ke city_id lewat daftar mapping yang ditinjau. Kota tidak dikenal tetap menuntut koreksi pengguna, jangan menebak.
3. Availability lama tetap ditampilkan sebagai preferensi lama, tetapi pengguna wajib memasukkan interval nyata sebelum lamaran pertama.
4. Pertahankan events.status; event approved lama menjadi publication_status=unpublished sampai syarat assessment/paket/jadwal diverifikasi. Jangan otomatis mengekspos data lama.
5. Gabungkan start_date/start_time/end_date/end_time ke timestamp UTC berdasarkan WIB secara eksplisit. Data akhir yang kosong ditandai perlu dilengkapi; jangan mengarang durasi. registration_deadline cast diperbaiki dari date menjadi datetime.
6. Minimum skill per posisi lama menjadi fallback hanya saat data per-skill belum ada; setelah backfill, position_skills.minimum_level menjadi acuan.
7. Pindahkan dokumen organisasi public ke private setelah copy/checksum diverifikasi; update metadata, hapus berkas publik lama dan endpoint preview public. File hilang diberi penanda perlu unggah ulang.
8. Hapus ketergantungan pada controller profil lama yang tidak terhubung setelah memastikan tidak ada route pemanggil. Dokumentasikan legacy field yang masih tersisa.

## 3. Status kanonik

### Event: tiga dimensi, tidak dicampur

| Kolom | Nilai | Pemilik perubahan |
|---|---|---|
| status (moderasi lama) | draft, pending, approved, rejected | A2 draft/submit; A1 approve/reject |
| publication_status | unpublished, published, suspended | EventPublicationService A2; Admin memanggil suspend/resume terotorisasi |
| lifecycle_status | upcoming, ongoing, completed, cancelled | A3 begin/complete; A2 cancel menggunakan pemeriksaan peserta |

Alur: draft → pending → approved atau rejected → draft (revisi) → pending. Approved tidak sama dengan published. Publish memerlukan organizer terverifikasi/akun aktif, event approved, konfigurasi valid, entitlement sesuai dan jadwal belum berakhir. Free entitlement diaktifkan sekali setelah approved. Paid entitlement diaktifkan setelah pembayaran valid. Katalog mengecek dimensi dan akses pemilik, bukan status approved saja.

Upcoming → ongoing oleh Organizer pada/sesudah starts_at; dapat dipicu sebagai bagian layanan pencatatan pertama, dengan audit. Ongoing → completed sesudah ends_at dan syarat completion lulus. Upcoming/ongoing → cancelled membutuhkan alasan; published berubah unpublished, proses seleksi/assessment dihentikan. Completed/cancelled terminal; tidak dibuka lagi di MVP. Koreksi attendance completed tidak membuka lifecycle.

### Lamaran

| Dari | Ke | Pemicu dan syarat |
|---|---|---|
| draft | submitted | A3 submit valid, snapshot dan submitted_at tersimpan sekali |
| submitted | screening_failed | A4 evaluasi gagal + alasan |
| submitted | assessment_pending | A4 evaluasi lolos, Match Score tersedia |
| assessment_pending | under_review | A4 attempt difinalkan, termasuk timeout |
| under_review | accepted | A3 Organizer sebelum starts_at, pemeriksaan kuota/jadwal/akun/lifecycle |
| under_review | rejected | A3 Organizer dengan alasan |
| submitted / assessment_pending / under_review | withdrawn | Volunteer pemilik sebelum accepted, alasan opsional |
| accepted | withdrawn | Organizer sebelum starts_at, permintaan mundur beralasan; batalkan attendance unrecorded |
| submitted / assessment_pending / under_review | rejected | Penutupan rekrutmen terkontrol A3, alasan wajib, tidak dianggap screening/assessment selesai |
| status nonterminal termasuk accepted | cancelled | Event cancellation, melalui layanan A3; terminal lama tetap tersimpan dengan event cancelled pada UI |

Screening_failed/rejected/withdrawn/cancelled terminal pada MVP. Draft dapat dihapus beserta berkas draft, tidak memengaruhi histori submitted. Job/attempt terlambat tidak boleh mengubah status withdrawn/rejected/cancelled. Gunakan compare expected status/revision dalam transaksi.

### Dokumen, assessment, payment, attendance

- Dokumen: `ready`, `purged`; upload tidak valid ditolak sebelum membuat dokumen aktif. Metadata migrasi yang kehilangan file ditandai `missing` dan tidak dapat diunduh. Tidak ada state antivirus.
- Attempt: `in_progress`, `submitted`, `expired`, `cancelled`. Submitted/expired menghasilkan skor satu kali; cancelled tidak mendorong lamaran under_review.
- Order: `pending`, `paid`, `failed`, `expired`, `cancelled`, `review_required`. Gateway status dipetakan A2 melalui adapter terdokumentasi. Browser tidak menentukan status. Pesan terlambat yang berkonflik memicu sinkronisasi server, tidak overwrite buta.
- Attendance: `unrecorded`, `present`, `absent`. Timestamp recorded_at ketika pencatatan, bukan waktu kedatangan.
- Akun: is_active boolean; organizer_status pending/active/inactive dipertahankan. Inactive organisasi berarti ditolak/revisi/tidak terverifikasi, bukan otomatis is_active=false. Suspensi akun memakai is_active=false dan alasan terpisah.

## 4. Kontrak layanan antaranggota

Nama berikut merupakan interface target internal PHP, bukan API eksternal atau package yang sudah terpasang. DTO dapat berupa object typed atau array tervalidasi dengan field yang sama.

| Layanan | Pemilik | Input → hasil / kegunaan |
|---|---|---|
| ProfileEligibilityService.check(user) | SF | `{complete, missing_fields, city_id, skills, availability_slots}`; A3/A4 memakai hasil yang sama. |
| PositionSnapshotService.build(position) | A2 | `{position_id,event_id,city_id,skills:[id,minimum_level,is_required],schedules:[start,end],requirements,assessment_version,rule_version}`. |
| EventPublicationService.publish(event,actor) | A2 | Periksa approval/entitlement/akun/lifecycle, persist published_at sekali, audit/notifikasi; dipanggil setelah approval/payment. |
| PaymentService.reconcile(order,verified_gateway_result) | A2 | Transisi order dan entitlement idempoten; input terverifikasi dari adapter server. |
| DocumentStorageService.store(upload,context,actor) | A3 | Metadata file ready atau validation/storage error; context bertipe application/organization dan scope ID terotorisasi. |
| ApplicationService.submit(draft,actor) | A3 | Snapshot + submitted sekali; panggil evaluasi A4 setelah commit melalui proses dapat diulang. |
| ScreeningService.evaluate(application) | A4 | `{passed, checks:[code,passed,reason],rule_version}`; satu hasil; tidak menimpa terminal. |
| MatchingService.calculate(snapshot) | A4 | `{skill_score,availability_score,location_score,total_score,breakdown,rule_version}`; tanpa query profil terkini. |
| AssessmentService.start/save/finalize(application,actor) | A4 | Satu attempt, expires_at <= starts_at event; finalize `{score,correct,total,finalized_at}`; finalisasi idempoten. |
| SelectionService.accept/reject(application,actor,reason) | A3 | Status baru atau conflict error; transaksi kuota/jadwal; create attendance saat accepted. |
| EventExecutionService.complete(event,actor) | A3 | Validasi akhir/attendance/pending selection; completed_at; riwayat derived. |
| EventCancellationService.cancelApplications(event,actor,reason) | A3 | Transisi lamaran aktif dan attempt melalui kontrak A4; digunakan A2 saat event cancelled. |
| ActivityReadService.forVolunteer/forOrganizer(user) | A4 | DTO aktivitas dengan data pemilik dari A1/A2/A3; tidak membuat salinan status. |
| NotificationService.enqueue(event_key,recipient,payload) | A4 | Outbox/dedupe; insert bersama transaksi bisnis, delivery setelah commit. |
| AuditService.record(actor,action,subject,changes,reason) | SF | Log minimum aman; dipanggil semua modul. |

ApplicationService.submit membuat outbox `application.submitted`; consumer A4 menjalankan screening/matching idempoten. Jika worker belum berjalan, UI menampilkan submitted "Menunggu evaluasi", bukan sukses screening palsu. Recovery membaca outbox yang belum selesai; alternatif eksekusi sinkron boleh dipakai selama tersedia retry terkontrol dan state intermediate yang benar.

Event key notifikasi: `<event_type>:<subject_id>:<transition_revision>:<recipient_id>`. Retry transisi yang sama memakai key sama; keputusan baru memakai revision baru. Audit dan notifikasi harus mencatat transaksi yang benar-benar commit, bukan percobaan gagal.

## 5. Endpoint dan nama route target

Route berikut menjadi kontrak navigasi. Implementasi dapat menambah endpoint pendukung, tetapi tidak mengganti nama di satu anggota saja.

| Method / path | Nama route | PIC |
|---|---|---|
| GET /, GET /events, GET /events/{event} | home, events.index, events.show | A2 |
| GET/POST /login, POST /logout | login, logout + handler auth bersama; guard web | SF |
| GET /admin/dashboard | admin.dashboard | A1 |
| GET /volunteer/aktivitas | volunteer.activity.index | A4 |
| GET /organizer/aktivitas | organizer.activity.index | A4 |
| GET/POST /volunteer/profile | volunteer.profile.edit/update (nama lama dipertahankan) | SF |
| GET/POST /organizer/profile | organizer.profile.edit/update | SF dasar; A1 verifikasi/dokumen |
| GET /organizer/profile/pending | organizer.profile.pending | SF; route lama /organizer/pending redirect |
| Resource /organizer/events | organizer.events.* | A2 |
| POST /organizer/events/{event}/submit, /publish, /cancel | organizer.events.submit/publish/cancel | A2 |
| Resource /organizer/events/{event}/positions | organizer.events.positions.* | A2 |
| GET/PUT /organizer/positions/{position}/assessment | organizer.assessments.edit/update | A4 |
| POST /volunteer/positions/{position}/applications | volunteer.applications.store | A3 |
| GET /volunteer/applications, /volunteer/applications/{application} | volunteer.applications.index/show | A3 |
| POST /volunteer/applications/{application}/submit, /withdraw | volunteer.applications.submit/withdraw | A3 |
| POST /volunteer/applications/{application}/documents | volunteer.documents.store | A3 |
| DELETE /volunteer/documents/{document} | volunteer.documents.destroy | A3 |
| GET /documents/{document}/download | documents.download | A3 |
| GET /admin/organizer-documents/{document}/download | admin.organizer-documents.download | A1 pakai utilitas A3 |
| GET/POST /volunteer/applications/{application}/assessment | volunteer.assessments.show/start | A4 |
| PUT /volunteer/assessment-attempts/{attempt}/answers | volunteer.assessments.answers | A4 |
| POST /volunteer/assessment-attempts/{attempt}/submit | volunteer.assessments.submit | A4 |
| GET /organizer/events/{event}/applications | organizer.applications.index | A3 |
| GET /organizer/applications/{application} | organizer.applications.show | A3 |
| POST /organizer/applications/{application}/accept, /reject | organizer.applications.accept/reject | A3 |
| POST /organizer/events/{event}/close-selection | organizer.events.close-selection | A3 |
| GET /organizer/events/{event}/execution | organizer.execution.show | A3 |
| PATCH /organizer/attendances/{attendance} | organizer.attendances.update | A3 |
| POST /organizer/events/{event}/complete | organizer.events.complete | A3 |
| GET /volunteer/history | volunteer.history.index | A3 |
| GET /organizer/events/{event}/package | organizer.packages.select | A2 |
| POST /organizer/events/{event}/orders | organizer.orders.store | A2 |
| GET /organizer/orders/{order}, POST /organizer/orders/{order}/sync | organizer.orders.show/sync | A2 |
| POST /payments/midtrans/notification | payments.midtrans.notification | A2; khusus verifikasi gateway |
| GET /notifications, PATCH /notifications/{notification}/read | notifications.index/read | A4 |
| Resource /admin/packages, GET /admin/orders | admin.packages.*, admin.orders.index | A2 |

Route admin master/users/verifikasi pada ZIP dipertahankan namanya jika masih relevan. /dashboard lama hanya mengarahkan role ke tujuan sah; /volunteer/dashboard dan /organizer/dashboard redirect ke aktivitas tanpa view dashboard baru. Semua object nested divalidasi cocok dengan parent URL.

### Login bersama dan otorisasi (revisi 1.4.2)

Semua role masuk melalui `/login`, tanpa pemilih role. Role dibaca dari database; registrasi publik tidak menerima role admin. Akun admin dibuat melalui seeder lokal atau prosedur internal yang terdokumentasi. Satu guard session `web` dipakai seluruh aplikasi; akses admin ditegakkan dengan middleware akun aktif dan role admin, serta Policy per objek.

Tujuan default: Admin `/admin/dashboard`, Volunteer `/volunteer/aktivitas`, Organizer `/organizer/aktivitas`. Akun yang wajib verifikasi email diarahkan ke alur verifikasi dahulu; Organizer pending/inactive yang akunnya aktif diarahkan ke `/organizer/profile/pending` untuk melengkapi profil. Status organisasi tidak sama dengan suspensi akun. Intended URL hanya boleh lokal dan sesuai hak akses; tujuan eksternal/lintas role yang tidak sah dibuang dan memakai tujuan default. Guest menuju route terproteksi diarahkan ke `/login`; non-admin yang sudah login meminta `/admin/*` ditolak 403.

Semua tombol keluar memakai POST `/logout` dengan CSRF, invalidasi sesi dan regenerasi token, lalu kembali ke `/login`. Tidak dibuat route autentikasi `/admin/login` atau `/admin/logout`; jika pernah ada, hapus referensi form/route lama saat migrasi. Pemisahan layout dashboard admin tetap berlaku.

## 6. Hak akses dokumen dan aksi sensitif

| Objek/aksi | Volunteer | Organizer terkait | Organizer lain | Admin |
|---|---|---|---|---|
| Draft lamaran/berkas | Pemilik aktif | Tidak | Tidak | Metadata operasional minimum bila diperlukan |
| Lamaran submitted dan berkas ready | Pemilik aktif | Ya, selama hak akses masih berlaku | Tidak | Metadata; tidak otomatis isi CV |
| Berkas lamaran withdrawn/cancelled/purged | Pemilik untuk ready tersisa; purged tidak | Tidak | Tidak | Metadata audit saja |
| Berkas verifikasi organisasi | Tidak | Organisasi sendiri aktif | Tidak | Ya untuk verifikasi; diaudit |
| Keputusan seleksi/attendance | Lihat sendiri | Ya | Tidak | Tidak mengambil alih rutin |
| Transaksi | Tidak | Event sendiri | Tidak | Lihat/sinkron gateway; bukan edit paid |

Download selalu attachment dari controller. ID bukan bukti izin. Jangan menerima disk/storage_key/path lewat request. Rate limit auth/reset dikonfigurasi SF; upload/download dikonfigurasi dan diterapkan A3 sesuai endpoint.

## 7. Konkurensi, kesalahan dan notifikasi

- HTTP browser sukses: redirect dengan flash. Validasi: 422/validation errors; akun/objek tidak berizin: 403 atau 404 konsisten tanpa membocorkan metadata; state stale/kuota habis/deadline: 409 atau error form dengan pesan dan reload status terkini.
- MySQL/InnoDB: transaksi dan locking reads; urutan kunci Volunteer → event/entitlement → posisi, ID konsisten. Baca ulang status/kuota/bentrok menggunakan pembacaan terkini sesudah lock. Semua jalur perubahan acceptance mengikuti kontrak yang sama. Uji dua koneksi/proses pada satu database MySQL khusus tes; retry deadlock terbatas.
- Gateway HTTP dilakukan di luar transaksi tulis. Reconcile hasil ke database dalam transaksi singkat. Unique order reference/event key/entitlement mencegah duplikasi.
- Hanya server menentukan deadline. UI timer, tombol disabled, dan hidden field bukan kontrol keamanan.
- Fitur yang gagal menyimpan harus menampilkan pesan gagal dan mempertahankan input relevan. Jangan menampilkan status berhasil sebelum commit.

## 8. Fixture integrasi bersama

SF menyediakan Admin, Organizer A aktif, Organizer B aktif, Organizer pending, Volunteer A/B/C, master kota Malang/Surabaya dan empat skill. Password demo hanya untuk lokal dan disebut jelas.

A2 menyediakan event gratis valid, event berbayar approved-unpublished, event draft, event lain yang jadwalnya bertumpang tindih, posisi berkuota satu, serta paket demo berlabel.

A3 menyediakan draft/under_review/accepted/withdrawn, berkas PDF/JPG/PNG uji tanpa data pribadi dan attendance unrecorded. A4 menyediakan assessment valid serta snapshot S=75,A=75,L=100 yang hasilnya 80,00.

Skenario bersama tidak bergantung urutan ID hardcoded; cari fixture melalui key/nama seed yang stabil. Nilai fixture bukan statistik yang ditampilkan hardcoded pada aplikasi.

## 9. Konfigurasi database dan README

Target MySQL/InnoDB. SF menyelaraskan .env.example, config/database.php dan phpunit.xml/.env.testing; pemilik modul menguji migration pada MySQL. Database test terpisah dari data kerja. Konversi konfigurasi SQLite tidak otomatis memindahkan data lama.

README aplikasi adalah hasil wajib M6. A1 menyatukan kontribusi setup semua PIC. Panduan diuji anggota lain melalui UAT-35; README bawaan Laravel bukan bukti kebutuhan ini terpenuhi.

## 10. Serah-terima shared foundation

Pemilik awal auth, profil dasar, cities/availability, master schema/seed, layout, AuditService dan konfigurasi MySQL adalah SF (kamu). A1 melanjutkan pemeliharaan fondasi sesudah commit serah-terima disepakati; tidak membuat migration/model versi kedua. A2/A3/A4 tetap memiliki tabel modul masing-masing. Perubahan interface setelah serah-terima disepakati semua pemakai sebelum merge. Lihat [brief SF](anggota/SHARED_FOUNDATION.md).

## Penegasan integrasi UI - 5 Oktober 2026

GET `/` tetap bernama `home` dan memakai `resources/views/welcome.blade.php`: beranda baseline dipulihkan melalui layout SF; A2 menjadi pemilik pengembangan lanjut, katalog dan detail. CTA login/daftar tetap memakai auth SF; CTA pengguna terautentikasi melalui `dashboard` yang memeriksa role/status/verifikasi. Jangan membuat auth/layout tandingan.

GET `/admin/dashboard` tetap bernama `admin.dashboard` dan memakai AdminDashboardController@index + admin/dashboard.blade.php. A1 melanjutkan hitungan database/aksi cepat tersebut. Kekurangan analitik lengkap tidak menjadi alasan mengganti dashboard yang berfungsi dengan shell. Shell hanya untuk fitur belum tersedia, terutama aktivitas A4; tidak menandakan modul selesai. Pembagian schema/service dan kontrol akses tidak berubah.

## Rincian implementasi A2 - 5 Oktober 2026 (menunggu review pemilik integrasi)

Implementasi berada pada `feature/a2-events-payment`; bukti/status di [A2_HANDOFF](../../docs/A2_HANDOFF.md). Kontrak status, nama tabel baseline dan guard web dipertahankan. Penugasan pengguna setelah SF adalah A2, tanpa memindahkan kepemilikan A4.

- `AssessmentReadiness::publishedVersion(EventPosition): int` pada App\Contracts adalah titik binding validasi kesiapan A4; mengembalikan versi assessment sah atau melempar ValidationException. Binding belum tersedia berarti submit/publish ditolak, bukan lolos default.
- A2Dependencies memanggil `EventCancellationService.cancelApplications` A3 secara transaksional. Belum ada layanan berarti cancellation ditolak. A3 meninjau urutan lock dan pengakhiran attempt A4 sebelum integrasi.
- `EntitlementService.consumeApplication(Event)` memerlukan transaksi submit A3 yang sama, unique application/idempotensi submitted_at diperiksa oleh A3 terlebih dahulu. Lock Volunteer -> event -> entitlement; counter submitted_applications tidak dikurangi ketika withdrawn. Ini batas paket, bukan seleksi kuota posisi.
- Snapshot paket dipilih dari server untuk konfigurasi/moderasi event dan disalin ke order setelah approved. Event yang sudah diajukan/bertransaksi tidak dapat mengganti paket tanpa alur revisi yang sah; harga/manfaat snapshot tidak berubah ketika admin mengubah paket.
- `orders`: event_id/package_id, order_ref unik, package_snapshot, amount rupiah integer, currency IDR, status kanonik, paid_at/activated_at, requires_follow_up, revision, checkout_url terenkripsi, checkout_claim/checkout_started_at dan gateway_checked_at. Checkout claim mencegah pemrosesan serentak; tidak menyimpan server key atau respons gateway mentah.
- `event_entitlements`: event_id unik, order_id nullable unik, package_snapshot, max_positions/max_applications/max_registration_days, submitted_applications dan activated_at. Free memakai order_id null, tidak membuat paid palsu.
- `payment_events`: order_id, gateway_event_key unik, gateway_status, verified_summary minimum dan notified_at. Replay pembayaran memakai key `payment.verified:<payment_event_id>:1:<recipient_id>`; published/cancelled memakai revision event dari audit. Outbox/delivery tetap A4.
- Route pendukung baru: GET `/events/{event}/join`, POST `/organizer/events/{event}/package`, POST `/organizer/orders/{order}/checkout`, GET `/admin/orders/{order}`, POST `/admin/orders/{order}/sync`. Callback saja dikecualikan CSRF; tetap wajib signature dan verifikasi status server.
- Resource posisi menyediakan index/show sebagai redirect terotorisasi ke detail event/anchor posisi. Hapus event terbatas draft awal kosong, belum pernah diajukan, tanpa order/entitlement; histori event tidak dihapus oleh endpoint ini. Paket dinonaktifkan melalui update, bukan hard-delete.
- Persyaratan baru menggunakan kind manual/document; document_type cv/supporting untuk selaras upload CV/lampiran. Kind manual bukan aturan otomatis membaca isi CV.

Migration tambahan mempertahankan tanggal/jam lama, menyalin legacy_registration_deadline, lalu mengonversi WIB ke UTC secara eksplisit. City dan akhir/jadwal yang tidak diketahui wajib dikoreksi, bukan ditebak. Event approved lama tetap unpublished. Review schema/interface ini bersama A1/A3/A4 belum dinyatakan selesai.

Koreksi judul/deskripsi event published memakai GET/PATCH `/organizer/events/{event}/text` (organizer.events.text/correct-text), allowlist ketat dan audit changed_fields; field jadwal/posisi/paket tidak diterima. Event terminal tidak dapat dikoreksi melalui endpoint ini.
