# Perbaikan alur terintegrasi — 3 November 2026

Branch `fix/integrated-application-flows`, basis `main` pada `4067a6c`.
Permintaan pengguna mencakup penghapusan akun, revisi organisasi, event lama,
endpoint paket, aktivitas dan notifikasi, dengan larangan menghapus tampilan/fitur.
Tidak ada perubahan framework, harga, rumus matching, guard login, atau gate email
Organizer. Layout, modal, kartu, preview KTP dan route existing dipertahankan.
Baseline tampilan dicatat dari Blade dan riwayat Git; tidak ada screenshot sebelum
perubahan. Screenshot sesudah perubahan memakai fixture sintetis, bukan data kerja.

## Perilaku dan integrasi

- Penghapusan akun tetap tersedia bagi akun tanpa data terkait. Pemeriksaan owner,
  relasi kegiatan/lamaran/dokumen/audit/kehadiran/notifikasi dilakukan sebelum delete.
  Penolakan 403 tidak menghapus profil atau berkas. Audit dan delete atomik; file
  hanya dihapus setelah commit. Akun bersejarah dinonaktifkan. Admin dapat mengatur
  `is_active` Organizer secara terpisah dari `organizer_status` melalui modal existing.
- Revisi nama organisasi, alamat, kota atau KTP dari organisasi active memerlukan
  konfirmasi eksplisit sebelum menjadi pending. Kontak/website/deskripsi biasa tidak
  mereset approval. Perbaikan inactive dikirim ulang menjadi pending. Admin wajib
  catatan bila inactive dan wajib revision terbaru; review stale ditolak. Persetujuan
  membutuhkan profil dan metadata KTP. Catatan ditampilkan pada profil/pending.
  Upload/preview tetap disk local privat; berkas lama dihapus setelah commit.
- `POST organizer/events/{event}/recruitment-repair` melengkapi kedua tenggat yang
  kosong pada event pending/approved upcoming. Hanya pemilik; konfirmasi dan alasan
  wajib; pendaftaran < assessment (masa depan) < keputusan < awal kegiatan.
  Pelengkapan sekali, replay nilai sama idempoten, perubahan selanjutnya ditolak.
  Tidak membuka ulang pendaftaran, mengubah jadwal/biaya atau menulis snapshot lama.
  `RecruitmentScheduleService.deadlineFor` memakai tanggal snapshot bila tersedia;
  hanya tanggal snapshot yang kosong dapat membaca tenggat event dengan marker
  perbaikan eksplisit. Ini pengecualian migrasi metadata yang diaudit, bukan backfill
  snapshot atau tebakan tenggat. Snapshot lain yang tidak lengkap tetap ditolak oleh
  screening/assessment existing; tidak ada bypass untuk data lama yang tidak valid.
- Endpoint tambah/edit paket kini mempunyai view form mandiri. Modal daftar existing
  tetap tersedia. Validasi, Policy, dan snapshot harga/manfaat tetap memakai A2.
- `ActivityReadService.forVolunteer/forOrganizer` membaca data pemilik, tanpa salinan
  status. Volunteer: jumlah agregat, pagination 10 lamaran, assessment dan jadwal
  diterima. Organizer: kandidat under_review yang masih bisa ditindaklanjuti,
  konfigurasi/moderasi/payment tertunda, serta attendance/completion. Semua kartu
  existing dipertahankan; bagian tindakan dan notifikasi ditambahkan.
- `NotificationService.enqueue` memakai notification_outbox existing, channel database,
  di transaksi bisnis. Notifikasi website tersedia segera setelah commit, tanpa SMTP
  atau worker delivery tambahan. Email tetap channel mail dan worker existing.
  Dedupe unique key, replay dan current locking read melindungi race InnoDB
  REPEATABLE READ. Payload hanya ID/type/status, tanpa KTP/snapshot/kunci/tautan bebas.
  GET /notifications dan PATCH /notifications/{notification}/read memeriksa pemilik;
  link dihitung dari kepemilikan objek saat dibuka, bukan payload URL pengguna.
  Hook: review organisasi/event, publish/payment, screening/matching/assessment,
  lamaran, attendance, cancel dan completion. Replay A2/A3 tetap tersedia.
- Scheduler kini juga menjalankan retry screening/matching setiap menit. Tidak
  menjalankan replay historis atau pemrosesan massal data kerja saat implementasi.

## Migration dan setup

`2026_11_03_000001_complete_application_flows`: kolom catatan/revision organisasi
pada users; marker/pelaku/alasan pelengkapan tenggat pada events; application_id
nullable dan read_at/index pada notification_outbox. Tidak mengubah histori migration.
Tidak ada backfill, seed atau reset data kerja. Rollback membiarkan application_id
nullable agar notifikasi tanpa lamaran tidak dihapus paksa.

Sesudah mengambil branch dan memeriksa database/backup instalasi terkait:

```powershell
php artisan migrate
npm.cmd run build
php artisan schedule:work
```

Scheduler diperlukan untuk retry evaluasi, timeout, reminder dan email. Notifikasi
website tidak menunggu scheduler. SMTP/inbox nyata tetap perlu diuji terpisah.
Pemilik modul: A1 akun/organisasi, A2 event/paket, A4 aktivitas/notifikasi, A3 produsen
seleksi/attendance. Peninjauan dan merge tim tidak diklaim telah dilakukan.

## Demo dan pemeriksaan

1. Admin: coba hapus akun dengan riwayat; harus ditolak tanpa kehilangan dokumen.
   Buka modal status Organizer; is_active dan hasil verifikasi terpisah.
2. Organizer active: ubah kontak biasa; tetap active. Ubah identitas: wajib konfirmasi,
   lalu pending. Admin beri catatan revisi; perbaiki dan ajukan ulang sampai disetujui.
3. Event lama: buka detail, lengkapi kedua tenggat dengan alasan/konfirmasi. Lamaran
   legacy dengan snapshot valid dapat melanjutkan assessment tanpa snapshot ditulis ulang.
4. Admin: buka /admin/packages/create dan /admin/packages/{id}/edit serta modal lama.
5. Kedua role: buka Aktivitas, detail notifikasi dan tandai dibaca. Akun lain harus
   ditolak ketika mengakses PATCH notifikasi langsung.

Tes: IntegratedFlowsTest dan suite existing, MySQL skillmatch_testing terpisah.
Utilitas `flow-concurrency-check.php --env=testing` memakai dua worker independen;
fixture sintetis dipertahankan di database tes, tidak di database kerja.
`flow-ui-review.php --env=testing` merender delapan halaman dengan transaksi fixture
yang di-rollback; `node tools/flow-browser-review.mjs` memeriksa 360/768/1280 piksel,
runtime Alpine serta modal profil/paket dan menyimpan screenshot privat di
storage/app/private/flow-review. Ini pemeriksaan render/layout, bukan alur submit
browser end-to-end, SMTP inbox, Sandbox nyata, atau uji beban.

## Bukti verifikasi lokal

- Migration baru sudah diterapkan pada MySQL database kerja lokal db_skillmatch
  (batch 9) dan database terpisah skillmatch_testing. Status kerja tidak menyisakan
  migration pending. Tidak ada reset, seed atau perubahan nilai tenggat data lama.
- Build Vite berhasil. Tes JavaScript existing: 12/12 lulus.
- flow-concurrency-check: dua koneksi, satu notifikasi dan satu audit pelengkapan
  tenggat; snapshot tetap. Race duplicate key InnoDB yang ditemukan sudah diperbaiki.
- a3-concurrency-check: tujuh skenario quota/conflict/submit/replay dan cancellation
  versus accept/submit/attempt lulus. a4-email-concurrency-check: satu outbox mail,
  satu pengiriman melalui array mailer dan satu worker ignored lulus.
- Delapan render Laravel HTTP 200 dan 24 pemeriksaan Chrome (360/768/1280) lulus,
  termasuk pembukaan modal profil/paket, runtime Alpine dan tanpa overflow horizontal.
  Screenshot profil mobile dan detail event/dashboard desktop juga ditinjau langsung.
- Tes lama yang menganggap layanan belum tersedia diselaraskan dengan kontrak baru;
  assertion database diisolasi ke application/position/attempt supaya fixture dua
  proses yang commit tidak mengubah hasil tes. Assertion SMTP uncertain hanya memeriksa
  lamaran milik tes; antrean sintetis lain boleh diproses oleh command global.
- Alat screenshot opsional memerlukan Chrome Windows dan modul playwright yang sudah
  tersedia lokal; keduanya bukan dependensi runtime aplikasi. Tidak menambah package
  atau mengubah lockfile untuk menjalankan aplikasi.
- Review browser menggunakan hasil render fixture, bukan submit browser ke aplikasi
  hidup. SMTP/inbox, Sandbox nyata dan UAT/review anggota lain belum diuji ulang.
- Branch tetap fix/integrated-application-flows dengan basis 4067a6c. Perubahan belum
  commit/push/PR/merge. Bukti suite PHP akhir dicatat di bawah.


Suite PHP akhir: 307/307 tes, 2.053 assertion lulus pada MySQL khusus tes setelah
fixture konkurensi tersimpan. Pint dan git diff --check lulus. .env, manifest dan
lockfile tidak diubah.
