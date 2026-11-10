# Handoff desain Profil Volunteer

Tanggal: 9 November 2026 (WIB).
Branch: `feature/a3-applications-attendance`.
Commit dasar: `a86f3543503434f2f74870606d4967762c5693e1`.
Sesuai instruksi: lokal, tanpa commit/push/PR/merge/deployment.
Perubahan lokal sebelumnya, termasuk desain Ringkasan Aktivitas, dipertahankan.

## Hasil dan integrasi

Referensi: `C:/Users/waely/Downloads/Profil Volunteer.svg` (1440 x 1828).
Halaman `/volunteer/profile` mengikuti sidebar navy, menu aktif biru, navbar
putih, latar slate, kartu, ukuran input, tipografi dan tombol dalam SVG.
Sebelumnya shell oranye dan tombol shared uppercase; kini scoped navy/biru dan
label tombol normal pada halaman profil. Tidak mengubah tampilan role lain.

`resources/views/volunteer/profile/edit.blade.php` menyusun partial baru
identity, skills, availability, actions. skill-row dan slot-row existing tetap
utuh. CSS khusus `resources/css/volunteer-profile.css` diimpor app.css.
Aturan shell di volunteer-activity.css dibagikan melalui selector scoped untuk
aktivitas/profil; sidebar existing menambah aria-current untuk route profil.

Satu form POST existing dipertahankan: CSRF, old input, error per-field,
phone-field, data-repeater, nama input, nilai tersimpan, legacy hints,
validasi tanggal/bio/skill, interval WIB, saving state dan pemeriksaan server.
Tidak ada migration, route, model, controller, service atau aturan bisnis baru.
Design/UAT diperbarui; PRD/kontrak masih berlaku. Setup/dependensi tidak berubah,
sehingga README setup tetap berlaku. Contoh data SVG tidak disimpan di aplikasi.

## Bukti validasi

- `npm.cmd run build`: lulus, Vite 8.3.1.
- PHP 8.4 Herd `artisan view:cache`: lulus.
- PHPUnit ProfileTest + PhoneValidationTest + FoundationAccessTest:
  **31 tes, 330 assertion lulus**, MySQL skillmatch_testing dengan guard pemisahan
  database existing, tanpa reset/migrate:fresh/RefreshDatabase.
- `git diff --check`: lulus.
- Chrome headless dengan fixture model yang tidak disimpan: desktop dibandingkan
  SVG; viewport aktual 1440/768/390 px tidak overflow. Screenshot mobile pertama
  memakai batas ukuran jendela browser; diganti pemeriksaan CDP dengan viewport
  aktual dan screenshot utuh (profile-390-final.png).
- Browser DOM: tambah dua baris lalu hapus/tambah ulang skill dan interval lulus,
  nama input unik, indikator menyimpan dan disabled submit lulus. Tidak mengirim
  form ke database kerja. Artefak preview/QA hanya di storage/app yang diabaikan Git.

## Demonstrasi dan batas

Gunakan server Laravel/Vite existing, login Volunteer, buka `/volunteer/profile`.
Coba ubah identitas, tambah/hapus skill dan interval WIB, lalu Simpan Profil.
Nilai tersimpan kembali dan error validasi diuji oleh feature tests pada database
khusus tes. Gunakan akun belum terverifikasi untuk melihat banner sesuai SVG.

Belum menjalankan login/simpan end-to-end melalui browser dengan akun kerja;
belum melakukan review tim atau seluruh UAT. Varian profil terisi/error/legacy
tercakup sebagian feature tests, bukan seluruhnya diperiksa visual. Lanjutan PIC
SF/A4: tinjau desain dan varian data nyata. Tidak ada dependensi integrasi baru.
