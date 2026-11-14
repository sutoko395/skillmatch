# Lamaran Saya lebih ringkas

14 November 2026. Branch feature/volunteer-design, dasar 8c37f57.
Pengguna menyetujui perapian ukuran setelah memastikan zoom browser 100%.
Lokal tanpa commit/push. Perubahan lokal lainnya dipertahankan.

Hanya resources/css/volunteer-applications.css: heading 32→28 px, judul kartu
20→18 px, padding 24→18/20 px, jarak lokasi/footer dipadatkan, badge mengikuti
teks (sebelumnya minimum 196 px), tombol minimum 156→120 px, jarak kartu 16 px.
Target sentuh tombol tetap 44 px; mobile tombol penuh. Sidebar, warna, komponen,
data/status, route dan alur existing tidak diubah. Tidak ada CSS zoom/transform
untuk mengecilkan halaman. Ini perubahan kepadatan kartu, bukan skala perangkat.
PRD/kontrak/README setup tetap berlaku; design/UAT diperbarui.

Build Vite 8.3.1 lulus, git diff --check lulus. Render fixture tanpa simpan:
kosong, draft dan pagination lulus. Chrome CDP 1440/768/390 tidak overflow;
screenshot desktop/mobile diperiksa. Artefak di storage/app diabaikan Git.
Tidak menjalankan ulang PHPUnit karena hanya ukuran CSS, tanpa perubahan alur.
Browser akun nyata dan variasi skala Windows belum diuji.

Demo: buka Lamaran Saya pada zoom 100%, bandingkan kepadatan kartu/status/tombol.
Jika seluruh panel masih terasa besar, evaluasi skala perangkat dan ukuran shell
secara terpisah; tahap ini mempertahankan sidebar antarhalaman. PIC SF/A3 memakai
kelas list existing. Tanpa dependensi/migration/route baru.
