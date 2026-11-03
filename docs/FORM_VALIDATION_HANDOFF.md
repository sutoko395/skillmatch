# Validasi form telepon dan pemeriksaan sejenis - 3 November 2026

Branch: `fix/integrated-application-flows`, basis `4067a6c`. Melanjutkan perubahan
integrasi, kehadiran dan koordinasi yang belum commit. Tidak stash/reset atau
menghapus hasil kerja sebelumnya. Riwayat profil terakhir pada `674054a`.

## Temuan dan perbaikan

1. Profil Volunteer menerima nomor berhuruf karena VolunteerProfileRequest hanya
   memakai required/string/max:30; profil Organizer (buat/edit) memiliki kelemahan
   yang sama di controller. Keduanya sekarang memakai `App\Rules\PhoneNumber`:
   nilai string 8–15 digit ASCII, boleh satu `+` di depan, tanpa spasi/tanda baca
   lain. Contoh `081234567890` dan `+6281234567890`. Ini validasi format, bukan
   bukti kepemilikan nomor atau keberhasilan menghubungi nomor tersebut.
2. Panggilan langsung VolunteerProfileService.save bisa melewati FormRequest.
   Service kini memvalidasi nomor sebelum menulis data/transaksi profil. Perubahan
   salah ditolak tanpa menimpa nama, kota, skill atau jadwal pengguna.
3. ProfileEligibilityService hanya memeriksa nomor tidak kosong. Nilai lama
   berhuruf karenanya dianggap lengkap. Kini aturan nomor sama dipakai pada
   eligibility; field phone masuk missing_fields sampai pengguna memperbaikinya.
   Tidak melakukan backfill/menghapus data lama atau mengubah snapshot lamaran.
   Pembuatan draft baru mengikuti gate eligibility existing; histori tetap ada.
4. Ketiga input telepon memakai komponen `x-phone-field` yang meneruskan
   komponen form-field/text-input existing: type tel, inputmode/autocomplete tel,
   pattern, batas panjang, contoh dan pesan Bahasa Indonesia. Alpine existing
   menolak ketikan/paste berhuruf, tanpa menghapus karakter secara diam-diam.
   Pattern native memeriksa panjang/posisi + dan server tetap menolak request
   langsung atau nilai autofill yang tidak valid. Tidak menggunakan type number,
   sehingga nol awal dan awalan internasional tetap string.
5. Form tambah/edit paket admin (modal dan halaman mandiri) tidak menyertakan
   batas maksimum existing di server. Tambahkan max/step=1 sesuai PackageService:
   harga 0–1.000.000.000, posisi 1–1.000, lamaran 1–1.000.000, hari 1–365.
   Server sebelumnya sudah menolak nilai salah; perubahan ini menyamakan UX.
6. Tanggal lahir di browser belum memiliki max meskipun server sudah menolak
   tanggal masa depan. Max kini memakai tanggal hari ini dengan timezone aplikasi
   yang sama dengan aturan server existing. Tidak menambahkan batas umur baru.

Kontak PIC sengaja tetap teks: kontraknya memperbolehkan nomor, email atau nama
pengguna, sehingga huruf bukan bug pada field tersebut. Website Organizer sudah
memakai url:http,https dan tipe URL. Email, enum gender/level, referensi kota/skill,
interval waktu dan angka paket yang diperiksa sudah memiliki validasi server;
tidak mengklaim audit seluruh kemungkinan form aplikasi selesai.

## Menjalankan dan demonstrasi

Tidak ada migration, route, dependency atau konfigurasi baru. Gunakan server
aplikasi existing. Buka Profil & Skill Volunteer atau Profil Organisasi, lalu
coba mengetik/paste huruf pada nomor dan simpan. Nomor valid harus menyimpan nol
awal atau awalan +. Request langsung dengan huruf mendapat error field phone.
Periksa form paket admin dan tanggal lahir untuk batas native sebelum submit.

Validasi berjalan tanpa JavaScript melalui constraint HTML dan validasi server;
filter ketikan/paste memerlukan Alpine existing. Layout, modal, otorisasi,
peninjauan organisasi dan seluruh fitur existing tetap dipertahankan.

## Verifikasi

- Preflight: MySQL 8.0.30/InnoDB, database tes `skillmatch_testing` terpisah
  dari database kerja. Tes menggunakan transaksi rollback, tanpa reset/seeder.
- Tes fokus PhoneValidationTest, ProfileTest, OrganizerKtpUploadTest,
  IntegratedFlowsTest, FoundationIntegrityTest: 27/27, 315 assertion lulus.
- Seluruh suite PHP: 326/326, 2.339 assertion lulus. Run pertama 325/326 akibat
  collision email Faker dengan fixture tersimpan di database tes; tes email
  terkait diuji ulang sendiri dan lulus, lalu seluruh suite diulang dan lulus.
  Tidak mengubah UserFactory atau membersihkan database untuk menyembunyikan ini.
- `npm run build` lulus setelah pengulangan di luar sandbox (spawn EPERM pada
  percobaan awal). Pint PHP terkait dan `git diff --check` lulus.
- `php tools/phone-ui-review.php --env=testing`: lima render HTTP 200,
  fixture sintetis seluruhnya rollback. `node tools/phone-browser-review.mjs`:
  enam kombinasi profil/lebar 360/1280 lulus untuk ketikan huruf, paste huruf,
  validasi native nilai script/autofill, nomor valid dan tanpa overflow. Modal
  tambah paket dan halaman mandiri juga lulus untuk max/step angka. Tidak ada
  error runtime browser. Screenshot modal organizer mobile diperiksa langsung.
- Artefak review privat di `storage/app/private/phone-review`. Pemeriksaan ini
  memakai HTML dari kernel aplikasi dengan data sintetis; tidak melakukan submit
  browser ke data kerja atau mengirim email/pesan eksternal. Endpoint create/edit
  dan rollback diuji melalui tes HTTP PHP di database khusus tes.
- Tidak mengubah `.env`, manifest/lockfile atau data kerja. Tidak ada migration,
  route atau dependency baru. Tidak commit/push/merge. Payment Sandbox dan layanan
  eksternal tidak diuji ulang. Pemeriksaan sejenis terarah pada form profil,
  koordinasi dan batas numerik paket, bukan klaim seluruh bug aplikasi ditemukan.
