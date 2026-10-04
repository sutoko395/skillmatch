# Serah-terima implementasi Shared Foundation

Tanggal bukti: 5 Oktober 2026 (WIB). PIC implementasi: SF, sebelum kembali ke A4. Status: **implementasi siap ditinjau; review tim dan gate serah-terima final belum selesai**.

## Basis dan commit

- Branch aktual: `feature/shared-foundation`.
- Dasar: `8fba6038b2775b5b83b617a1355dd50503ab28be`, sama dengan main dan origin/main yang tersedia saat pemeriksaan; tidak ada asumsi develop, fetch/merge/reset.
- `8cff8b4`: SF-01, MySQL dan guard database tes.
- `b6eea83`: SF-02, auth/status/Policy/intended URL dan route modul.
- `94de561`: SF-03/04/06, profil, migration tambahan, audit dan tes.
- `65e2435`: SF-05, layout/komponen/form dan shell sementara.
- Commit SF-07 (seed, README, bukti akhir) dicatat pada Git log setelah dokumen ini dibuat; ambil **tip feature/shared-foundation setelah commit SF-07**, bukan commit tahap antara.
- Perubahan pengguna sebelum pekerjaan: AGENTS.md, `.env.example` (DB_CONNECTION=mysql), paket `SkillMatch_Dokumen_Tim_v1.4/` belum tracked. AGENTS/paket dokumen dipertahankan dan tidak ikut staging otomatis. Perubahan `.env.example` diteruskan sesuai tugas SF.
- Spesifikasi yang dibaca berasal paket lokal versi 1.4.2: PRD, design, kontrak, baseline, SF, brief A1/A4 dan UAT. [Interface SF](SF_INTERFACES.md) merinci bentuk DTO, aturan audit aman, dan aturan transisi tanpa mengubah status bisnis tim.

## Hasil per tahap

| Tahap | Hasil implementasi |
|---|---|
| SF-01 | Default MySQL/InnoDB dan UTC, env contoh kerja/tes, database tes terpisah, migration baseline + tambahan sukses pada database tes yang awalnya kosong. |
| SF-02 | Login bersama semua role, register tanpa admin, email verification/reset, throttle, logout/CSRF, invalidasi sesi akun nonaktif, intended URL terbatas pada hak akses. |
| SF-03 | Profil Volunteer simpan-baca, kota master, skill distinct/empat level, availability WIB → UTC, ProfileEligibilityService dan transaksi atomik. Master referensi tidak dihapus berantai. |
| SF-04 | Profil/kontak Organizer dapat disimpan tanpa upload dan tanpa mengubah status akun/organisasi; pending/inactive tetap dapat melengkapi profil. Nama organisasi terverifikasi menunggu alur verifikasi ulang A1. |
| SF-05 | Navbar pengguna, sidebar admin baseline diperbaiki, komponen bersama, Inter dan Alpine satu kali, shell final tanpa angka palsu. |
| SF-06 | AuditService + audit_logs append-only (model/trigger), payload minimum aman, rollback bersama bisnis, bukti commit dari koneksi independen. |
| SF-07 | Seeder master/demo seluruh role/status, README nyata, database guard dan bukti pengujian. Reviewer anggota lain belum tersedia. |

## Migration, model dan route integrasi

Migration lama tidak diedit. Urutan tambahan:

1. `2026_10_04_000001_add_shared_foundation.php`: cities; skills/categories.is_active; volunteer_profiles.city_id nullable/restrict; volunteer_skills.skill_id FK restrict; availability_slots(user_id,starts_at,ends_at), unique interval identik dan CHECK durasi positif.
2. `2026_10_04_000002_create_audit_logs.php`: audit_logs(actor_id/action/subject_type/subject_id/before/after/reason/created_at), indeks subjek, actor FK restrict, trigger no-update/no-delete.

Model baru City, AvailabilitySlot, AuditLog. Relasi User::availabilitySlots, VolunteerProfile::cityRecord dan relasi baseline dipakai ulang. Tidak membuat event/positions versi kedua atau migration modul A2/A3/A4. Legacy city/availability tetap tersimpan, tidak ditebak. Tidak mengaktifkan otomatis akun lama yang nonaktif.

`routes/web.php` memuat admin.php, volunteer.php, organizer.php, auth.php. Route profil tetap GET/POST `/volunteer/profile` dan `/organizer/profile` dengan nama `.profile.edit/update`. Pending final GET `/organizer/profile/pending`; `/organizer/pending` redirect. `/dashboard` mengarahkan sesuai role/verifikasi; dashboard pengguna lama redirect ke aktivitas.

Titik kelanjutan dashboard dan penggantian shell:

- A1 (koreksi 5 Oktober 2026 sesuai instruksi pengguna): `admin.dashboard` kembali memakai `AdminDashboardController@index` dan `resources/views/admin/dashboard.blade.php`. Hitungan database dan aksi cepat baseline ditampilkan, dengan keterangan bahwa analitik lengkap belum tersedia. View shell `admin/foundation.blade.php` dihapus. A1 memperluas dashboard dasar ini, bukan menggantinya dengan shell; rincian tugas ada pada brief A1 yang diperbarui. Proteksi SF tetap berlaku.
- A4: `resources/views/shared/activity.blade.php` pada `volunteer.activity.index` dan `organizer.activity.index`; ganti route view dengan controller + ActivityReadService masing-masing role.
- A2: `resources/views/welcome.blade.php` / home, lalu katalog events.index/show. Navbar hanya menautkan fitur yang sudah ada.
- A1/A3: pemberitahuan dokumen belum tersedia pada organizer/profile/edit; preview publik admin dihentikan. OrganizerDocument/schema/berkas lama tetap ada untuk migrasi terkontrol.
- Compatibility layout organizer/volunteer sidebar kini meneruskan layouts.user. Layout app/nav lama tidak lagi memanggil route profil yang tidak terdaftar.
- Controller mati `app/Http/Controllers/Volunteer/ProfileController.php` dihapus setelah pencarian tidak menemukan route pemanggil; namespace sebelumnya gagal PSR-4. Controller profil aktif baseline tetap dikembangkan.

## Bukti yang dijalankan

Lingkungan: Windows PowerShell, PHP 8.4.24, Composer 2.8.11, Node 24.14.1, npm 11.11.0, MySQL 8.0.30, InnoDB. Database kerja `db_skillmatch` tidak di-migrate/reset. Tes hanya pada `skillmatch_testing`; tidak ada migrate:fresh/RefreshDatabase.

| Pemeriksaan | Hasil aktual |
|---|---|
| composer install --no-interaction --prefer-dist | Sukses dari lockfile, tidak meng-upgrade dependensi. Warning PSR-4 awal diselesaikan dengan menghapus controller tidak terhubung. |
| composer check-platform-reqs / composer validate --no-check-publish | Lulus. PDO MySQL tersedia melalui pemeriksaan DB. |
| composer dump-autoload --strict-psr | Lulus, tanpa warning PSR-4 tersisa. |
| npm ci --ignore-scripts --no-audit --no-fund | Sukses, 117 paket. Hanya @tailwindcss/vite dan dependensi khususnya yang dihapus; Tailwind tetap 3.4.19. |
| migrate --env=testing | Seluruh 18 migration baseline + 2 SF sukses; baseline dijalankan saat database masih kosong. |
| db:seed --env=testing, diulang | Sukses; seeder tidak mengubah akun/profil yang sudah ada. |
| php vendor/bin/phpunit | **52 tes, 227 assertion, lulus**. |
| sf-audit-commit-check.php --env=testing | Lulus; koneksi kedua tidak melihat audit sebelum commit, melihat setelah commit, tidak melihat audit rollback. |
| sf-db-check.php --env=testing | mysql/skillmatch_testing, MySQL 8.0.30, default InnoDB, 0 tabel non-InnoDB. |
| Pint --test pada PHP SF | Lulus setelah format; PHP baseline di luar perubahan SF tidak diformat ulang. |
| artisan route:list --except-vendor / view:cache | 46 route terdaftar; kompilasi Blade sukses. |
| npm run build | Sukses setelah npm ci; Vite 8.3.1, CSS sekitar 55.22 kB dan JS 55.09 kB. |
| git diff --check | Lulus pada perubahan SF. |

Tes: Auth/* meliputi login, verification signed/hash, reset password, confirmation/update password dan register. FoundationAccessTest meliputi role/status/default/intended eksternal/lintas role, sesi nonaktif, registrasi admin, logout, rate limit, CSRF aktif. ProfileTest meliputi round trip WIB/UTC/eligibility, data legacy, distinct/invalid interval/master nonaktif, dan kontak Organizer. AuditTest/FoundationIntegrityTest meliputi redaksi, append-only SQL, rollback atomik, seed berulang, proteksi referensi/master dan Policy target pengguna.

Kegagalan awal yang sudah diselesaikan: manifest Vite belum dibuat saat tes render pertama; build sandbox EPERM diselesaikan dengan menjalankan build yang diizinkan di luar sandbox. Tidak dihitung sebagai hasil lulus sebelum diperbaiki.

## Gate yang masih memerlukan review

Verifikasi koreksi dashboard admin (5 Oktober 2026): `FoundationAccessTest` lulus **22 tes / 124 assertion**, termasuk view dashboard baseline, lima hitungan sesuai database, aksi cepat, label analitik belum tersedia dan proteksi lintas role. `npm run build`, `view:cache`, `route:list --path=admin/dashboard`, Pint pada file yang diubah dan `git diff --check` berhasil. Percobaan tes dalam sandbox ditolak koneksi; pemeriksaan MySQL dan tes ulang di luar sandbox berhasil pada `skillmatch_testing`. Angka 52/227 di atas adalah bukti suite SF sebelumnya, bukan klaim menjalankan ulang seluruh suite pada koreksi ini. Uji browser visual tetap belum dilakukan.

- **Reviewer anggota lain: belum ditetapkan; tanggal review: belum ada.** Jalankan README dari checkout tip SF pada MySQL kosong dan database tes terpisah, lalu catat hasil di bagian ini. UAT-35/review tim belum lulus.
- Uji browser visual/keyboard 360/768/1280 dan screenshot: **belum diuji**, browser automation tidak tersedia pada sesi ini. Build dan render HTTP/Blade sudah diuji; keduanya bukan pengganti pengujian visual.
- SMTP nyata: **belum diuji**; notifikasi auth diuji dengan fake/array mailer. Reviewer dapat memakai SMTP uji atau tautan log lokal.
- Migration/import pada salinan data kerja nyata serta backup/restore: **belum diuji**. Fixture legacy diperiksa, tetapi belum menjadi bukti migrasi semua data pengguna.
- Audit dependensi keamanan Composer/npm: **belum dijalankan**; install/validate/build bukan audit advisori.
- Tidak melakukan push, PR, merge, deployment, atau mengklaim aplikasi penuh/P0/UAT lulus.

## Batas dan pekerjaan pemilik berikutnya

| PIC | Pekerjaan berikutnya / blocker |
|---|---|
| A1 | Review SF dan README; verifikasi organisasi/dokumen privat/revisi/nama identitas; suspend/reactivate beralasan + audit; master CRUD/nonaktifkan termasuk kota; moderasi event, analitik, konten/settings/audit UI, README final. Admin baseline masih perlu integrasi audit/notifikasi/transaksi; relasi moderasi `positions.skills` lama masih harus diganti sesuai kontrak A2. |
| A2 | Event/posisi/jadwal/snapshot, approved bukan published, katalog, paket dan Midtrans Sandbox; aturan FK histori event sesuai kepemilikan schema. |
| A3 | DocumentStorageService privat, migrasi berkas organisasi bersama A1, aplikasi/seleksi/attendance/riwayat/retensi/restore. **Berkas public lama tetap blocker penggunaan nyata** meski SF sudah menghentikan upload/preview publik. |
| A4 | Screening/assessment/matching/notifikasi/ActivityReadService dan mengganti shell aktivitas. Pakai DTO ProfileEligibilityService, snapshot kontrak, audit transaksi dan layout SF; belum dikerjakan di scope ini. |

## Demo/review singkat

1. Ikuti README, migrate/seed pada MySQL kosong, jalankan server + build.
2. Login admin dari `/login`, periksa dashboard dasar: angka sesuai database, aksi cepat, keterangan analitik belum lengkap dan logout POST. Login Volunteer lalu coba `/admin/dashboard` langsung: 403.
3. Edit profil Volunteer A: pilih kota, skill/level dan interval WIB; simpan lalu buka ulang. Coba skill ganda/akhir lebih awal: validasi menolak tanpa perubahan parsial.
4. Login Organizer pending/inactive aktif: profil tetap dapat disimpan tanpa dokumen/status akun berubah. Login akun suspended: ditolak. Gunakan akun unverified untuk alur email.
5. Jalankan PHPUnit dan skrip bukti audit hanya setelah sf-db-check memastikan database tes terpisah. Catat versi, commit, screenshot dan reviewer; jangan mengubah tabel UAT penuh menjadi lulus hanya berdasarkan SF.
