# Handoff desain Ringkasan Aktivitas Volunteer

Tanggal: 9 November 2026 (WIB).
Branch: `feature/a3-applications-attendance`.
Commit dasar: `a86f3543503434f2f74870606d4967762c5693e1`.
Tidak membuat commit, push, PR, merge, atau deployment sesuai instruksi pengguna.
Perubahan lokal/staged yang sudah ada sebelum tugas dipertahankan.

## Lingkup yang disetujui dan hasil

Sumber desain: `C:/Users/waely/Downloads/Ringkasan Aktivitas.svg`, FigCraft,
1440 x 1846.47. Hanya `/volunteer/aktivitas`: sidebar navy, header putih,
aksen biru, latar slate, kartu putih, jarak/radius/badge, serta kolom profil/skill.
Existing memakai sidebar oranye dan bagian tindakan tanpa kartu; kini mengikuti
SVG. Konten, layanan dan navigasi existing tetap tersedia. Tampilan halaman
Volunteer lain, Organizer dan Admin tidak diubah oleh stylesheet yang dibatasi
kelas `volunteer-activity`.

Partial Blade di `resources/views/volunteer/activity/`: welcome, stats,
profile-readiness, skills, availability, applications, next-steps.
Dashboard menyusun partial; layout sidebar existing diberi hook CSS serta label
aksesibilitas menu akun/halaman aktif. Notifikasi memakai partial bersama existing.
Stylesheet `resources/css/volunteer-activity.css` diimpor app.css.
Koreksi scoped untuk konflik kelas translate drawer mobile disertakan.

Data contoh dalam SVG tidak disimpan/dipasang sebagai data produksi. Query,
profil, skill, jadwal, hitungan, status, pagination, deadline assessment, accepted,
notifikasi dan verifikasi tetap memakai implementasi existing. Tanggal lamaran
ditampilkan dalam WIB. Tidak ada migration, route, controller, schema, Policy,
atau interface layanan baru. FR-18/kontrak tetap berlaku; design.md dan UAT.md
mencatat desain baru beserta lingkupnya.

## Bukti pemeriksaan

- `npm.cmd run build`: lulus, Vite 8.3.1, final CSS `app-DXwxxUYU.css`.
- PHP 8.4 Herd `artisan view:cache`: lulus.
- PHPUnit IntegratedFlowsTest, FoundationAccessTest, VolunteerVerificationFlowTest:
  40 tes lulus, 333 assertion; MySQL `skillmatch_testing`, guard pemisahan database
  existing, tanpa reset/migrate:fresh/RefreshDatabase.
- `git diff --check`: lulus.
- Render Blade dengan fixture lokal yang tidak disimpan, Chrome headless:
  desktop dibandingkan SVG dan mobile diperiksa. Lebar 1440/1024/768/390 tidak
  overflow; menu akun dan Escape lulus pemeriksaan browser.
- Drawer mobile: pemeriksaan awal menemukan konflik translate; CSS diperbaiki,
  screenshot final `storage/app/volunteer-drawer-open.png` menunjukkan drawer
  terbuka dengan overlay. Pemeriksaan otomatis posisi drawer masih melaporkan
  gagal meskipun screenshot final terbuka; hasil otomatis tersebut tidak diklaim
  lulus. Perlu konfirmasi interaksi drawer pada browser pengguna.
- Artefak visual/fixture hanya di `storage/app/` yang diabaikan Git. Fixture
  bukan hasil integrasi atau data pengguna sungguhan; tidak menambah endpoint.

## Batas dan demonstrasi

Login sebagai Volunteer lalu buka `/volunteer/aktivitas` melalui server Laravel
existing. Vite existing boleh tetap berjalan; bila memakai build pastikan aplikasi
menggunakan manifest build, bukan hot file dari dev server yang sudah mati.
Tidak mengubah setup atau dependensi, sehingga README setup tetap berlaku.
Coba profil lengkap/kosong, assessment aktif/kedaluwarsa, accepted, pagination,
notifikasi dan verifikasi dengan data milik akun sendiri. Feature test menutup
sebagian regresi endpoint; tidak mengklaim seluruh UAT, review tim, atau semua
varian data sudah diperiksa secara visual. Browser login end-to-end dengan akun
kerja belum diuji pada tugas ini. Tidak ada dependensi integrasi baru.

Lanjutan PIC A4: review tampilan dan seluruh varian data nyata; konfirmasi drawer
mobile pada browser pengguna. Perubahan lokal siap ditinjau, belum diserahkan
melalui commit atau diintegrasikan ke branch lain.
