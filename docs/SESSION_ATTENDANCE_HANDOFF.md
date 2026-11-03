# Kehadiran per sesi/hari - 3 November 2026

Keputusan pengguna: kehadiran dicatat manual Organizer per sesi/hari, menggantikan
unit pencatatan per kegiatan untuk penerimaan baru. Implementasi dilanjutkan di
branch `fix/integrated-application-flows`, basis Git `4067a6c`, bersama perubahan
integrasi sebelumnya yang belum commit. Tidak membuang/stash perubahan sebelumnya.
Baseline: Attendance satu per application dan satu form per peserta pada halaman
Pelaksanaan; riwayat membaca present/absent. Riwayat kode: commit `6798ed4`.
Tabel/filter/rekap/pencarian/aksi seleksi/completion, layout dan halaman tetap dipakai.

## Aturan baru

- Sumber sesi adalah `snapshot_json.schedules` pada lamaran, bukan jadwal master
  terkini. Setiap interval tugas menjadi sesi. Interval melewati tengah malam WIB
  dipecah pada pukul 00.00; interval berakhir tepat tengah malam tidak membuat sesi
  kosong. Interval overlap digabung supaya tidak ada absensi ganda; interval yang
  hanya bersinggungan tetap sesi berbeda. Tidak membuat jadwal dari tebakan.
- Saat accepted, Attendance tetap menjadi satu rekap peserta. AttendanceSession
  dibuat untuk setiap sesi/hari tugas dengan status unrecorded. Unik berdasarkan
  attendance_id dan hash interval UTC. Tidak mengubah snapshot, kuota atau hasil A4.
- Hanya Organizer aktif pemilik event untuk application accepted. Pencatatan
  present/absent baru dibuka setelah awal event dan awal sesi masing-masing.
  Jam disimpan UTC, tanggal sesi dan tampilan WIB. recorded_at bukan jam datang.
- Form dan revision berlaku per sesi. Replay identik idempoten; revision stale
  dengan perubahan berbeda ditolak. Audit dan notifikasi berada dalam transaksi
  yang sama. Akun Volunteer/Admin/owner lain ditolak melalui endpoint langsung.
- Rekap peserta: unrecorded bila masih ada sesi belum dicatat; present bila semua
  hadir; absent bila seluruh sesi final dan sedikitnya satu tidak hadir. Tidak
  ada status baru atau pengisian otomatis sesi lain saat mencatat satu sesi.
- Completion menunggu akhir event, akhir seluruh sesi, seleksi/assessment final,
  kecocokan seluruh definisi sesi dengan snapshot, dan semua sesi tercatat.
  Cache rekap peserta saja tidak cukup. Riwayat berhasil hanya jika semua sesi
  hadir; campuran hadir/tidak hadir ditampilkan Partisipasi tidak lengkap beserta
  hitungan/rincian sesi; seluruh absent ditampilkan Tidak hadir. Cancellation tetap
  Dibatalkan. Koreksi setelah completion wajib alasan dan audit, tanpa membuka event.

## Data dan endpoint lama

Migration tidak menggandakan catatan hadir lama ke setiap hari. Attendance existing
memiliki uses_sessions=false dan tetap ditampilkan sebagai Catatan lama per kegiatan.
Event completed lama tetap dapat dibaca serta dikoreksi melalui alur lama beralasan.

Untuk kegiatan nonterminal, Organizer dapat menekan Siapkan absensi per sesi.
Konfirmasi wajib; bila catatan lama sudah terisi, alasan wajib dan seluruh status,
pelaku/waktu/catatan/revision lama disimpan di legacy_record. Sesi baru dimulai
unrecorded dan harus dicatat berdasarkan kehadiran sebenarnya. Konversi replay
tidak menimpa arsip atau menggandakan sesi. Snapshot invalid menolak konversi,
termasuk rollback parent Attendance yang baru dibuat. Completed/cancelled tidak
dikonversi diam-diam. Application accepted lama tanpa Attendance juga dapat disiapkan.

PATCH attendances existing dipertahankan untuk koreksi catatan legacy dan satu sesi
tunggal (kompatibilitas). Bila ada beberapa sesi, endpoint tersebut menolak update
ambigu; gunakan session ID. Pengunduran accepted sebelum awal menghapus hanya sesi
unrecorded; sesi yang sudah dicatat tidak boleh dihapus lewat pengunduran.

## Schema dan interface A3

Migration `2026_11_03_000002_add_session_attendance` menambah uses_sessions dan
legacy_record pada attendances serta tabel attendance_sessions: attendance_id FK
restrict, session_key unik per parent, session_date WIB, starts_at/ends_at UTC,
status, revision, recorded_by/at, note, correction_reason. Tidak mengubah histori
migration. Tidak ada seed/reset atau backfill kehadiran lama. Rollback migration
menghapus tabel sesi; jangan rollback instalasi yang sudah berisi sesi tanpa backup.

- `AttendanceSessionService.definitions(application)` membentuk interval harian.
- `prepare/prepareApplication` menangani konfirmasi, lock, arsip legacy dan rollback.
- `prepareLocked` dipakai SelectionService di transaksi acceptance.
- `record` dipanggil controller sesi; EventExecutionService memvalidasi completion.
- Lock pencatatan: event -> application -> attendance -> session. Parent attendance
  menyelaraskan rekap saat dua sesi diperbarui bersamaan; completion memakai event lock.
- POST `/organizer/attendances/{attendance}/sessions`: organizer.attendances.prepare-sessions.
- POST `/organizer/applications/{application}/attendance-sessions`: organizer.applications.prepare-attendance-sessions.
- PATCH `/organizer/attendance-sessions/{session}`: organizer.attendance-sessions.update.
- Notifikasi `attendance.session_recorded:<session_id>:<revision>:<volunteer_id>`;
  replay A3 mengenali subject AttendanceSession. AccountManagementService melindungi
  FK pencatat sesi. Policy parent Attendance tetap dipakai; tidak menambah gate email EO.

## Menjalankan dan mencoba

```powershell
php artisan migrate
npm.cmd run build
```

Organizer: Event Saya -> detail -> Pelaksanaan dan kehadiran. Gunakan filter posisi,
status sesi dan tanggal tugas WIB. Rekap atas menunjukkan peserta dan total catatan sesi peserta
seluruh event; filter tidak mengubah makna total keseluruhan. Setiap sesi menampilkan
tanggal/jam, status/waktu pencatatan, catatan dan form sendiri. Sesi mendatang diberi
penjelasan waktu dibukanya pencatatan. Data legacy memiliki tombol persiapan eksplisit.

Volunteer: detail lamaran dan Riwayat Partisipasi menampilkan jumlah hadir/total sesi,
status tiap hari/sesi dan arsip lama jika dikonversi. Volunteer tidak dapat mencatat.

## Verifikasi

`SessionAttendanceTest` menguji split WIB, beberapa shift per hari, overlap/adjacency,
replay acceptance, sesi masa depan, filter, otorisasi, stale revision, partial attendance,
completion, koreksi, notifikasi replay, konversi legacy, snapshot invalid dan rollback.
`tools/session-attendance-concurrency-check.php --env=testing` menguji dua koneksi
untuk sesi berbeda, replay sesi sama dan race completion versus sesi terakhir;
fixture sintetis tersimpan hanya pada skillmatch_testing dengan mailer array.
`tools/session-attendance-ui-review.php --env=testing` merender tiga halaman dengan
fixture yang di-rollback; `node tools/flow-browser-review.mjs --sessions` memeriksa
360/768/1280 px dan menyimpan screenshot privat di storage/app/private/session-review.
Ini bukti render/layout, bukan submit browser end-to-end atau review anggota lain.

## Bukti akhir

- Suite PHP penuh: 316/316 tes, 2.130 assertion lulus pada MySQL terpisah.
  Regresi sesudah perbaikan form: 26 tes SessionAttendanceTest/A3ExecutionTest,
  189 assertion lulus. Pint serta git diff --check lulus.
- Build Vite berhasil; 12/12 tes JavaScript existing lulus.
- Tiga skenario dua koneksi sesi berbeda/replay/race completion lulus.
- Tiga halaman Laravel HTTP 200; sembilan kombinasi 360/768/1280 px lulus,
  tanpa overflow halaman dan Alpine aktif. Screenshot execution desktop dan riwayat
  mobile ditinjau langsung. Klik Chrome ke probe HTTP lokal mengirim POST/PATCH,
  CSRF, revision dan status. Probe tidak menulis data aplikasi dan bukan submit
  browser end-to-end pada instalasi pengguna.
- Migration diterapkan pada skillmatch_testing dan db_skillmatch lokal (batch 10).
  Perbandingan sebelum/sesudah seluruh field attendance lama (status, pelaku/waktu,
  catatan, correction_reason, revision, ID dan timestamps) identik. Tidak menyalin
  hadir lama ke tiap hari, tidak backfill, seed atau reset data kerja.
- .env, manifest dan lockfile tetap. Perubahan lama pada branch tetap dipertahankan.
  Belum commit/push/merge, belum review anggota lain atau uji beban.

