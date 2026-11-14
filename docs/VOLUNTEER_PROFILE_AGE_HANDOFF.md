# Profil ringkas dan usia otomatis

14 November 2026. feature/volunteer-design, dasar 8c37f57. Pengguna meminta
perapian profil dan kolom usia di Identitas dan Kontak. Lokal tanpa commit/push;
perubahan lain dipertahankan.

identity.blade.php menempatkan Tanggal lahir/Usia/Jenis kelamin dalam tiga kolom
desktop, bertumpuk mobile. Usia readonly tanpa name, dihitung dari birth_date
melalui Alpine existing dan tanggal hari ini WIB dari server. Berubah saat input
tanggal lahir berubah; tanggal kosong/tidak valid/masa depan tidak diberi usia.
Usia tidak dikirim/disimpan sebagai kolom baru. Tanpa JavaScript tampil petunjuk;
tanggal lahir tetap dapat diisi dan disimpan. Tidak menambah validasi usia minimum.
Old input, error, POST, CSRF dan validasi profil server tetap.

CSS volunteer-profile scoped merapatkan heading/padding/jarak form dan baris
skill; tombol hapus skill sejajar desktop. Mobile tetap ditumpuk. Input tetap
44 px. Tidak ada route, schema, migration/controller/service baru. PRD/kontrak
dan README setup tetap berlaku; design/UAT diperbarui.

ProfileTest + PhoneValidationTest: 9 tes/171 assertion lulus pada MySQL khusus
tes dengan guard existing. Build, view:cache, diff --check lulus. CDP usia tujuh
kasus lulus: normal, sebelum/pada ulang tahun, masa depan, kosong, tanggal tidak
valid, kabisat. Preview fixture tanpa simpan 1440/768/390 tidak overflow; desktop
diperiksa visual. Script/screenshot di storage/app diabaikan Git. Penyimpanan
profil/klik repeater browser akun nyata belum diuji. PIC SF/A4 tetap memakai
birth_date sebagai sumber, usia hanya informasi tampilan.

## Aksen tombol hapus — 14 November 2026

Disetujui pengguna: Hapus skill/Hapus interval memakai teks rose, border rose
lembut, latar putih; hover rose-50 dan fokus keyboard rose. Selector hanya
.vp-profile button[data-remove], sehingga tombol tambah/simpan tetap. Build
dan diff --check lulus; visual browser belum diperiksa ulang. Tanpa commit/push.

## Tombol tambah — 14 November 2026

Sesuai pengguna, Tambah skill dan Tambah interval memakai ikon plus aria-hidden,
outline biru, hover biru muda serta fokus biru. Hook data-add/handler existing
tetap. Hapus tetap rose, simpan tetap biru solid. Build/view:cache/diff --check
lulus; interaksi browser belum diuji ulang. Tanpa commit/push.
