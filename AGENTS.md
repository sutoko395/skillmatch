# AGENTS.md — SkillMatch

Aturan kerja bersama untuk agent yang mengembangkan repository SkillMatch.
Selaras dengan paket dokumen tim revisi 1.4.2, 4 Oktober 2026.
Letakkan file ini di root repository, sejajar dengan composer.json dan artisan.

## 1. Baca sebelum bekerja

- PRD_SkillMatch_Tim.md: kebutuhan dan aturan bisnis.
- docs/KONTRAK_INTEGRASI.md: schema, status, layanan, route, dan kepemilikan.
- design.md: desain dan komponen antarmuka.
- docs/BASELINE_DAN_TOOLS.md: baseline teknologi dan panduan operasional.
- docs/UAT.md: skenario penerimaan.
- docs/anggota/SHARED_FOUNDATION.md dan brief anggota yang sedang ditugaskan.

Gunakan instruksi pengguna terbaru sebagai keputusan pekerjaan. PRD, kontrak,
desain, dan brief harus konsisten. AGENTS.md mengatur cara bekerja; tidak
menggantikan spesifikasi produk. Bila instruksi terbaru mengubah kontrak bersama,
catat perubahan pada dokumen terkait. Jangan diam-diam mengarang status, schema,
rumus, harga, atau kepemilikan baru. Tanyakan hanya konflik yang belum dapat
diselesaikan dari instruksi dan bukti yang tersedia; lanjutkan pekerjaan yang
tidak terblokir. Jika dokumen wajib tidak ditemukan, laporkan nama/path yang hilang.

## 2. Periksa kondisi nyata

Periksa branch, git status, perubahan lokal, manifest/lockfile, migration, route,
model, layanan, layout, dan tes sebelum mengedit. Periksa catatan serah-terima SF.
Kode existing dan hasil anggota lain harus dipakai kembali bila sudah sesuai.
Jangan membuat ulang aplikasi atau menyatakan fitur selesai hanya karena filenya ada.

Pakai Laravel, Blade, Tailwind 3, Alpine.js, Vite, dan PHPUnit sesuai manifest serta
lockfile repository. Target database MySQL/InnoDB. Jangan mengganti framework,
menambah SPA, meng-upgrade major version, atau memperbarui semua dependensi tanpa
kebutuhan tugas. Referensi API eksternal diverifikasi pada dokumentasi resmi ketika
integrasinya dikerjakan. Jangan menambahkan ClamAV atau scanner antivirus.

## 3. Kepemilikan pekerjaan

| Kode | Lingkup | Branch yang disarankan |
|---|---|---|
| SF | MySQL, auth bersama, profil dasar, kota/skill/availability, layout, AuditService, seed, README awal | feature/shared-foundation |
| A1 | Verifikasi organisasi, moderasi event, pengguna, master UI, analitik admin, konten/settings, audit UI, README akhir | feature/a1-foundation |
| A2 | Event, posisi, jadwal posisi, katalog, paket, pembayaran, admin paket/transaksi | feature/a2-events-payment |
| A3 | Lamaran, DocumentStorageService, dokumen privat, seleksi, attendance, riwayat, retensi | feature/a3-applications-attendance |
| A4 | Screening, assessment, matching, NotificationService, ActivityReadService, Ringkasan Aktivitas | feature/a4-assessment-matching |

SF dikerjakan pengguna sebelum kembali ke A4, bukan anggota kelima. A1 melanjutkan
fondasi sesudah serah-terima. Jangan mengulang migration/model/service SF atau
mengambil seluruh tugas anggota lain. Setiap PIC mengerjakan database, backend,
tampilan, otorisasi, pengujian, dan dokumentasi untuk modulnya.

A2 menyediakan struktur event/posisi lebih awal. A3 menyediakan layanan dokumen
untuk A1 dengan otorisasi terpisah per konteks. A4 menyediakan hasil evaluasi dan
layanan notifikasi; setiap PIC memasang pemanggilan audit/notifikasi modulnya.
Integrasi dan perbaikan dibagi menurut pemilik modul.

## 4. Git dan file bersama

Gunakan branch tugas dari branch dasar tim yang benar-benar tersedia dan telah
disepakati; jangan mengasumsikan adanya develop. Jangan bekerja langsung di main,
menimpa perubahan lokal, force-push, reset --hard, atau membersihkan file anggota
lain. Jangan otomatis stash atau membuang perubahan yang tidak dikenal.

Buat commit kecil sesuai tahap jika tugas mengizinkan commit. Push, PR, merge,
dan deployment mengikuti instruksi pengguna; jangan menyatakan tindakan itu
telah dilakukan jika belum terjadi. Merge ke main dan deployment bukan bagian
default implementasi modul.

Koordinasikan perubahan routes/web.php, bootstrap/app.php, config/auth.php,
layout, CSS, komponen, User, dan schema bersama. Utamakan perubahan terarah,
bukan mengganti seluruh file. Gunakan file route modul sesuai kontrak. Pemilik
migration tetap mengikuti tabel tanggung jawab; jangan membuat schema tandingan
untuk mengatasi dependensi yang belum tersedia.

## 5. Auth dan navigasi

Semua role menggunakan GET/POST /login, satu guard session web, dan POST /logout
dengan CSRF. Tidak ada pemilih role di form atau login/guard admin khusus.
Role berasal dari database. Registrasi publik hanya Volunteer/Organizer;
akun admin dibuat melalui prosedur internal/seeder lokal yang terdokumentasi.

Default setelah login: Admin /admin/dashboard, Volunteer /volunteer/aktivitas,
Organizer /organizer/aktivitas. Terapkan alur verifikasi email dan pengecualian
profil pending sesuai PRD. Intended URL harus lokal serta sesuai hak akses.
Guest diarahkan ke login; non-admin terautentikasi yang meminta endpoint admin
ditolak. Logout menginvalidasi sesi dan meregenerasi token.

users.is_active terpisah dari organizer_status. Akun nonaktif kehilangan akses
terproteksi, termasuk sesi yang sudah ada. Organizer pending dengan akun aktif
tetap dapat melengkapi profil. Panel/layout admin terpisah; dashboard analitik
hanya admin. Pengguna lain memperoleh Ringkasan Aktivitas.

## 6. Data dan integrasi

- Gunakan schema, nama route, status, dan interface dalam kontrak. Tambahkan
  migration untuk perubahan existing; jangan mengubah histori migration yang
  sudah dipakai tim tanpa keputusan migrasi yang jelas.
- Jangan menjalankan migrate:fresh, RefreshDatabase, atau reset pada database
  kerja/demo yang masih dibutuhkan. Pastikan database tes MySQL benar-benar
  terpisah sebelum menjalankan tes yang mengubah atau menghapus data.
- Role, owner, harga, skor, deadline, dan transisi sensitif ditentukan server.
  Terapkan Policy per objek, validasi, transaksi/locking, dan unique constraint
  sesuai risiko serta kontrak. Tombol tersembunyi bukan pembatasan akses.
- Matching: 50% skill, 30% availability, 20% lokasi; gunakan snapshot dan versi
  aturan. Nilai assessment terpisah. Detail perhitungan mengikuti PRD.
- Approved bukan published. Pembayaran memakai Sandbox; browser redirect bukan
  bukti pembayaran. Callback dan aktivasi entitlement harus idempoten.
- Dokumen privat; validasi format/ukuran dan download terotorisasi. ready tidak
  berarti bebas malware. Jangan menaruh dokumen pelamar di public URL/symlink.
- Attendance dicatat organizer untuk peserta accepted. unrecorded tidak sama
  dengan absent; completion mengikuti semua syarat PRD.
- Simpan waktu sesuai kontrak UTC, tampilkan WIB dengan label yang jelas.
- Jangan menyimpan secret, kredensial nyata, token, atau isi dokumen pribadi
  dalam repository, log, output pengujian, maupun catatan serah-terima.

Jika dependensi anggota lain belum tersedia, kerjakan bagian independen dengan
fixture yang mengikuti kontrak. Catat dependensi yang tertunda. Stub/mock hanya
untuk pengembangan atau tes, bukan bukti integrasi selesai. Jangan membuat bypass
otorisasi, pembayaran, atau screening agar demo tampak berjalan.

## 7. Antarmuka

Ikuti design.md: identitas indigo, kartu putih, Inter, sidebar gelap khusus admin,
dan navbar web utama untuk pengguna. Muat Alpine satu kali. Pakai komponen bersama,
label form, error spesifik, fokus keyboard, dan keadaan loading/kosong/gagal.
Jangan menampilkan statistik palsu, tautan kosong seolah aktif, atau data fixture
sebagai hasil nyata. Shell sementara SF diberi keterangan dan dicatat untuk
dilengkapi pemilik modul; shell bukan bukti fitur penuh selesai.

## 8. Pengujian dan dokumentasi

Jalankan tes yang relevan dengan perubahan dan UAT modul, lalu build frontend.
Periksa akses lintas role/pemilik langsung melalui endpoint. Untuk payment,
seleksi, attempt, dan job, uji pengulangan request/transisi serta konkurensi yang
relevan. Gunakan MySQL khusus tes untuk perilaku transaksi/locking.

Verifikasi perintah tes/build dari manifest dan README yang nyata. Jangan
menebak bahwa alat, database, kredensial, atau layanan tersedia. Jika belum
dapat menjalankan pengujian, tulis "belum diuji" dengan blocker dan langkah
verifikasinya. Mock gateway bukan bukti transaksi Sandbox end-to-end.

Setiap perubahan setup memperbarui README aplikasi: dependensi, konfigurasi,
migration/seeder, akun demo lokal, server/frontend, worker/scheduler bila dipakai,
tes dan troubleshooting. SF membuat README awal; A1 menggabungkan kontribusi
modul untuk README akhir yang dicoba anggota lain. Password demo khusus lokal.

## 9. Laporan akhir dan serah-terima

Laporkan secara ringkas:
1. Fitur yang selesai dan perubahan perilaku.
2. Migration, route, layanan dan komponen yang perlu dipakai anggota lain.
3. Tes/build yang benar-benar dijalankan beserta hasilnya.
4. Dependensi, blocker, dan bagian yang belum diuji/diintegrasikan.
5. Langkah menjalankan dan mendemonstrasikan modul.
6. Branch/commit aktual serta pekerjaan lanjutan pemilik modul.

Selesaikan pekerjaan yang diizinkan sampai hasilnya dapat ditinjau. Jangan
mengklaim semua P0/UAT, review tim, atau serah-terima lulus tanpa bukti. Kesiapan
SF mengikuti gate SHARED_FOUNDATION.md; kesiapan aplikasi penuh mengikuti PRD/UAT.
