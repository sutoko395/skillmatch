# Brief A1 — melanjutkan fondasi dan fitur admin

Versi 1.4.2 • PIC: teman pembuat ZIP. Baca [PRD](../../PRD_SkillMatch_Tim.md), [kontrak](../KONTRAK_INTEGRASI.md), [design](../../design.md), [UAT](../UAT.md), dan [shared foundation](SHARED_FOUNDATION.md).

## Pekerjaan lama tetap dihitung

Auth, role, profil Volunteer/Organizer, upload organisasi, master/admin pengguna, dashboard dasar dan kerangka verifikasi event dari ZIP merupakan kontribusi A1. Status awal berasal audit statis; keberadaan kode bukan bukti runtime lulus.

Fondasi kini dikerjakan kamu melalui SF sebelum kembali ke A4. A1 tidak perlu membuat ulang MySQL, login, profil dasar, layout atau AuditService. Ambil commit SF yang disepakati dan baca catatan serah-terimanya.

## Sisa tugas A1

| Urutan | Deliverable | Kriteria selesai |
|---|---|---|
| 1 | Review serah-terima SF | Jalankan README awal, pahami interface, catat blocker; lanjutkan pada branch A1 dari fondasi yang telah diintegrasikan. |
| 2 | Verifikasi organisasi | Review dokumen privat, approve/reject dengan catatan, revisi dan verifikasi ulang terkontrol. Edit kontak biasa tidak meminta ulang semua berkas. Pakai DocumentStorageService A3; tidak membuat layanan duplikat. |
| 3 | Moderasi event | Daftar/detail, relasi positions.positionSkills.skill benar, approve/reject/revisi; publikasi melalui service A2 dan tidak menyamakan approved dengan published. |
| 4 | Pengguna | Pencarian/filter/pagination, suspend/reactivate dengan alasan dan audit. Middleware SF menegakkan status di semua request. Tutup hard delete yang merusak histori. |
| 5 | Master data | CRUD/nonaktifkan kota, skill dan kategori menggunakan schema/seed SF; data referensi lama tetap terbaca. |
| 6 | Lanjutkan dashboard dasar menjadi analitik admin | Pertahankan ringkasan database dan aksi cepat baseline pada `/admin/dashboard`. Tambahkan indikator PRD, filter periode, tren, timestamp dan empty/error state secara bertahap; jangan mengganti fitur yang sudah bekerja dengan shell atau angka hardcoded. |
| 7 | Konten dan pengaturan | Allowlist teks/settings; bukan secret atau eksekusi kode. |
| 8 | Halaman audit | Baca/filter/pagination log dari AuditService SF; tanpa edit/hapus histori melalui UI. |
| 9 | Konsistensi dan README akhir | Rapikan UI admin, uji modul sendiri, gabungkan dokumentasi A2/A3/A4 dan bukti UAT-35. |

## Kepemilikan setelah serah-terima

### Penyesuaian 5 Oktober 2026 — dashboard baseline dipertahankan

Keputusan pengguna: dashboard admin lama yang berfungsi tetap ditampilkan. Route `admin.dashboard` kembali memakai `AdminDashboardController@index` dan `resources/views/admin/dashboard.blade.php`. SF mempertahankan hitungan Volunteer, Organizer, skill, kategori dan event pending dari database, aksi cepat serta proteksi auth/akun aktif/role/verified/Policy. Hitungan ini adalah kondisi saat halaman dimuat, termasuk akun/master nonaktif; belum merupakan analitik periode lengkap.

A1 melanjutkan controller/view tersebut, bukan membuat ulang dashboard. Pisahkan angka kondisi saat ini dari metrik periode; tambahkan filter 7/30 hari/rentang WIB, waktu hitung, tren dan tabel alternatif sesuai PRD. Metrik lamaran/payment/publikasi menunggu data serta kontrak A2/A3; jangan mengisi nol buatan ketika modul atau query belum tersedia. Uji perubahan angka berdasarkan fixture database, kondisi kosong/gagal dan akses non-admin. Keterangan fitur belum tersedia hanya untuk bagian yang belum diimplementasikan; hapus keterangan ketika bagian itu benar-benar selesai dan teruji.

Halaman Aktivitas Volunteer/Organizer tetap milik A4. Koreksi dashboard admin ini tidak memindahkan tugas A4 ke A1 dan tidak membatalkan proteksi SF.

A1 melanjutkan pemeliharaan fondasi bersama dan fitur admin. Perubahan kontrak profil/auth/audit atau komponen perlu koordinasi pemakai A2–A4. Tidak mengganti schema/model yang sudah diserahkan dengan versi sendiri. Guard `web`, GET/POST `/login`, dan POST `/logout` dipakai semua role. Panel `/admin/*` tetap memakai pemeriksaan role admin dan layout khusus.

## Serah-terima lintas anggota

- A2 menerima layout admin dan kontrak moderasi; UI paket/transaksi tetap A2.
- A3 menyediakan layanan file privat bagi dokumen organisasi; A1 menangani konteks/Policy/moderasi organisasi dan migrasi berkas publik lama bersama A3.
- A4 menyediakan NotificationService; A1 memasang pemanggilan notifikasi untuk aksi admin miliknya.
- ProfileEligibilityService dan AuditService berasal SF; tiap anggota menggunakannya tanpa menghitung/mencatat ulang dengan aturan berbeda.

## Uji dan demo

A1 bertanggung jawab pada UAT-05/06/07 bagian moderasi, 30 bagian analitik, 32/34 yang relevan, serta integrasi 33/35. Periksa regresi UAT-01–04 dari SF ketika mengubah fondasi. Admin tidak otomatis berhak mengunduh semua CV.

Demo: login bersama sebagai admin, review organizer, revisi/approve event, pengguna dinonaktifkan kehilangan akses, master yang digunakan tidak dihapus, analitik sesuai data dan audit terbaca. Tidak ada demo dua pintu login.

## Batas tugas

Tidak mengambil engine A4, halaman aktivitas A4, payment A2, upload pelamar atau attendance A3. Integrasi menjadi tanggung jawab tiap pemilik modul; kontribusi lama tetap dihitung.

## Koordinasi beranda - 5 Oktober 2026

Beranda publik (`home` / `welcome.blade.php`) dipulihkan oleh SF dan menjadi pengembangan lanjut A2, bukan pekerjaan pembangunan ulang A1. A1 menjaga konsistensi layout bersama dan dapat mengintegrasikan konten terkelola setelah modul konten tersedia. Dashboard admin tetap dilanjutkan sesuai penyesuaian di atas; aktivitas pengguna tetap A4.

## Pelestarian UI auth bersama - 5 Oktober 2026

Sesuai permintaan pengguna, tampilan login/register telah dirapikan pada branch A2 memakai auth-layout dan auth-password-field. Backend/guard/route dan pengecualian verifikasi SF tetap dipakai. A1 menggunakan tampilan ini saat melanjutkan akun/admin; jangan membuat login khusus admin. Registrasi publik masih terbatas Volunteer/Organizer. Halaman reset/verifikasi belum didesain ulang oleh perubahan ini. Bukti tes/browser tersedia di A2_HANDOFF.
