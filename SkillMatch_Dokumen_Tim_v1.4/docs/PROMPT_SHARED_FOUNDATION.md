# Prompt implementasi shared foundation

Versi 1.4.2 • Salin teks dalam blok berikut ke agent yang memiliki akses repository aplikasi dan dokumen paket ini. Ini prompt implementasi; kode belum diubah oleh revisi dokumen.

```text
Saya akan mengerjakan shared foundation (SF) SkillMatch sebelum kembali ke tugas A4. Implementasikan SF saja sampai siap diserahterimakan agar A1 dapat melanjutkan fitur admin.

Baca PRD_SkillMatch_Tim.md versi 1.4.2, design.md, docs/KONTRAK_INTEGRASI.md, docs/BASELINE_DAN_TOOLS.md, docs/anggota/SHARED_FOUNDATION.md, docs/anggota/A1_Akun_Admin.md, brief A4 dan docs/UAT.md. Periksa AGENTS.md yang berlaku, manifest/lockfile, migration, route, model, middleware, layout dan tes existing sebelum mengubah kode. Gunakan project teman saya sebagai baseline dan pertahankan kontribusi yang sudah ada.

Periksa Git, branch dasar tim dan perubahan lokal. Buat atau gunakan feature/shared-foundation dari dasar yang benar, tanpa menimpa perubahan lokal dan tanpa mengerjakan langsung di main. Jangan menebak branch remote, membuat ulang project, atau mereset data kerja.

Kerjakan SF-01 sampai SF-07 dengan commit kecil: MySQL/InnoDB dan database test terpisah; auth bersama; profil kota/skill/availability yang benar-benar dapat disimpan/dibaca; aturan status organisasi; layout dan komponen bersama; AuditService; seeder, README awal dan bukti serah-terima.

Login semua role tetap GET/POST /login dengan satu guard web dan POST /logout. Tidak ada login/guard admin terpisah atau pemilih role di form. Admin tetap memiliki /admin/dashboard, layout khusus, middleware akun aktif/role admin dan Policy. Validasi intended URL lokal dan hak akses; ikuti pengecualian verifikasi email serta organizer pending pada PRD. Registrasi publik tidak dapat membuat admin. Akun nonaktif ditolak termasuk sesi yang masih aktif.

Pakai schema dan service pada kontrak. SF menyediakan profil dasar/availability, ProfileEligibilityService, AuditService, layout dan seed. Jangan mengambil event/payment A2, DocumentStorageService/lamaran/attendance A3, atau engine/notifikasi/aktivitas lengkap A4. Tidak menambahkan ClamAV. Pertahankan beranda baseline (hero, CTA auth, footer) dalam layout SF untuk dilanjutkan A2, serta dashboard dasar admin dengan hitungan database/aksi cepat untuk dilanjutkan A1. Jangan mengganti halaman yang sudah berfungsi dengan shell karena fitur lanjutannya belum lengkap. Gunakan shell hanya untuk bagian yang benar-benar belum tersedia, termasuk aktivitas A4 pada route final, dan catat pemiliknya. Beri keterangan analitik/katalog/matching yang belum tersedia tanpa angka palsu atau klaim fitur lengkap. Keputusan ini mengikuti koreksi pengguna 5 Oktober 2026.

Pertahankan Laravel, Blade, Tailwind 3, Alpine dan Vite sesuai lockfile. Gunakan migration tambahan dan jelaskan transisi data lama. Jangan menjalankan migrate:fresh atau tes destruktif pada database kerja. Verifikasi gate SF menggunakan database test MySQL, tes akses lintas role/status/intended URL, simpan-baca profil, audit commit/rollback dan build frontend. Jika alat atau layanan belum tersedia, sebutkan pengujian yang belum berjalan dan blocker; jangan klaim lulus.

Tulis README aplikasi sesuai kode yang benar-benar dapat dijalankan: prasyarat, database kosong, env, dependensi, migration/seeder, frontend/server, email auth, akun demo dan tes. Catat konfigurasi modul lanjutan sebagai belum tersedia bila belum diimplementasikan.

Akhiri dengan catatan serah-terima SF: commit dasar/hasil, migration, route/model/service, komponen bersama, bukti tes/build, reviewer yang masih diperlukan, blocker dan sisa tugas A1/A2/A3/A4. Jangan menandai review tim, merge, push atau deployment sebagai selesai jika belum dilakukan. Jangan mengerjakan A1 lanjutan atau A4 inti dalam scope SF ini.
```
