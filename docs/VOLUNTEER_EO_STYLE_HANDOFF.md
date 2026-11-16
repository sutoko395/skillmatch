# Penyelarasan panel Volunteer dengan EO/Admin

Tanggal: 15 November 2026. Lingkup disetujui pengguna: menyamakan tampilan,
warna, ukuran font, kalimat, tombol, filter dan posisi detail pada sisi volunteer.
Branch: `feature/volunteer-ui-refinement`; dasar `360244b` dari branch admin.
Working tree awal bersih. Tidak commit/push dalam pekerjaan ini.

## Sebelum dan sesudah

Kode existing berasal antara lain dari bc188f6, 55f2b56 dan 4503a04; handoff
profil, aktivitas, katalog, lamaran, asesmen dan riwayat sebelumnya dipertahankan.
Shell memiliki logo huruf S, akun terulang pada header, serta ukuran judul dan
aksi berbeda antarhalaman. Kini logo bunga bersama, sidebar navy 256px, menu
aktif biru, akun di sidebar, logout POST/CSRF di bawah Lihat situs utama, dan
header ponsel/drawer existing tetap berfungsi. Semua menu utama tetap tersedia.

CSS baru `resources/css/volunteer-panel.css`, diimpor melalui app.css dan hanya
berlaku pada `.volunteer-panel`: judul 26px/22px ponsel, section 18px, subjudul
15px, isi 13px, label 12px; kartu putih radius 14px, kontrol 13px, warna/fokus
biru #2563eb. Kartu statistik menggunakan border biru/amber/hijau/cyan berdasarkan
jenis dan kelengkapan nyata. Ukuran kontainer existing dipertahankan.

Detail/edit/tambah/simpan bergaris biru, hapus/batal bergaris merah, kembali
merah solid di kiri atas. Ikon kecil sesuai tindakan. Detail lamaran/riwayat
berada pada bagian aksi kartu; aktivitas juga memberi tautan detail lamaran.
Konfirmasi asesmen memiliki kembali di atas dan Mulai sekarang diikuti Batal.
Katalog panel memakai judul Jelajahi Kegiatan, filter ringkas dan Reset teks
abu-abu hanya saat ada filter aktif. Katalog publik tetap memiliki tampilannya.

Teks memakai Volunteer, Kegiatan, Keahlian, Penyaringan, Asesmen, Draf serta sapaan
Anda. Label pintu masuk navbar menjadi Panel Volunteer. Status lamaran dan label
NotificationOutbox diselaraskan; key outbox, enum, route, data/snapshot dan nama
field tidak diterjemahkan. Label komponen status bersama juga tampil konsisten
pada EO. Toast memakai perilaku admin: sukses/informasi 5 detik, pause hover/fokus,
tutup manual; error dan validasi tetap sampai ditangani. Error per-field tetap.

## Integrasi dan batas lingkup

Tidak ada migration, route, dependensi, perubahan setup atau rumus. Screening,
matching 50/30/20, saran usia, gate asesmen, autosave/revisi/finalisasi, dokumen
privat, penarikan, absensi dan otorisasi tetap. JS assessment hanya mengubah
kalimat konfirmasi/status; permintaan dan handler tidak diubah. Controller
profil hanya mengganti pesan sukses. PRD/kontrak bisnis dan README setup tetap
berlaku; perubahan ini hanya presentasi/label. A3/A4 memakai partial dan komponen
existing. Tidak menghapus data, halaman atau kontrol bisnis.

## Verifikasi

- Uji backend relevan pada MySQL `skillmatch_testing` terpisah, dilindungi guard
  Tests/TestCase.php; DatabaseTransactions, tanpa migrate:fresh/RefreshDatabase.
- Hasil akhir: **111 tes / 1083 asersi lulus**.
- Paket tes: VolunteerCatalog, VolunteerAssessmentInfo, Profile,
  ActivityProfileSummary, A3Application, A3ApplicationDocuments,
  ParticipantAssessment, VolunteerVerificationFlow, SessionAttendance,
  FoundationAccess, NavigationAssets, IntegratedFlows, AdminWorkflowRefinement.
- Build frontend `npm.cmd run build` lulus. Pemeriksaan diff tanpa whitespace error.
- Fixture sintetis merender 19 halaman/varian HTTP 200 dan rollback seluruh data:
  aktivitas, profil, katalog/default/filter, detail kegiatan, daftar lamaran,
  notifikasi, detail draft/submitted/screening_failed/under_review/rejected/
  withdrawn/cancelled/accepted/assessment_pending, konfirmasi/pengerjaan asesmen,
  serta riwayat. Artefak QA hanya pada storage/app/private yang diabaikan Git.
- **57 kombinasi halaman/viewport lulus** (19 halaman/varian x 3 ukuran).
  Browser Edge headless pada 390/768/1440px memeriksa overflow, font, kembali
  merah, sidebar navy, border statistik, menu ponsel/Escape, repeater profil,
  dan navigasi soal; screenshot diperiksa untuk aktivitas/profil/detail mobile.
- Browser memakai HTML Blade yang dirender melalui HTTP kernel dengan fixture;
  tidak mengirim formulir atau melakukan login/simpan end-to-end pada akun kerja.
  Backend tests menguji endpoint sebenarnya pada database tes. Tidak mengklaim
  seluruh UAT, pengiriman email nyata, gateway atau review tim selesai.

## Demonstrasi

Jalankan server Laravel dan frontend sesuai README, login sebagai volunteer lalu
buka `/volunteer/aktivitas`, profil, Jelajahi Kegiatan, Lamaran Saya, detail,
Riwayat Partisipasi dan notifikasi. Konfirmasi/pengerjaan asesmen mengikuti gate
normal lamaran; jangan memodifikasi data kerja untuk melewatinya. Gunakan lebar
ponsel untuk menu drawer dan tombol kembali di kiri atas.

### Penyempurnaan Lamaran Saya — 16 November 2026
Permintaan pengguna: ikon kontekstual dan hierarki font agar kartu lebih ringan. Ditambahkan ikon posisi, lokasi, waktu pembuatan, dan pengiriman; judul 16px, posisi serta nilai metadata 12px, label metadata 11px. Padding dan struktur kartu tetap. Tidak mengubah alur lamaran.
Validasi terbaru: npm run build dan git diff --check lulus. Render Laravel belum dapat dijalankan: PHP tersedia 8.3.31, dependensi memerlukan >=8.4.1 dan path PHP 8.4 sebelumnya tidak tersedia. Pemeriksaan browser otomatis gagal pada Page.enable (timeout); pemeriksaan visual terbaru belum selesai.

Penyempurnaan lanjutan: atas permintaan pengguna, padding vertikal kartu Lamaran Saya menjadi 12px; jarak posisi, lokasi, serta footer diperkecil. Ukuran font dan area klik tombol dipertahankan. Build frontend dan diff --check lulus. Pemeriksaan visual browser belum dilakukan pada perubahan ini.

### Penyelarasan kepadatan semua menu volunteer - 16 November 2026
Lingkup disetujui pengguna: aktivitas, profil, katalog/detail kegiatan, lamaran/detail, riwayat, notifikasi, serta konfirmasi/pengerjaan asesmen. Hirarki judul bagian 16px, subjudul 14px, isi 13px, metadata 11-12px; jarak antarkartu 16px. Padding konten kartu diselaraskan 16px/20px dan 14px/16px di ponsel. Ringkasan riwayat, petunjuk asesmen dan notifikasi dipadatkan; area pilihan soal minimal 44px. Tombol tandai notifikasi mengikuti outline biru, ikon kembali ganda dihapus. Tidak ada perubahan route, schema, status, hak akses, atau alur bisnis.
Validasi: npm run build dan git diff --check lulus. Pengujian Laravel terbaru belum dijalankan karena PHP lokal 8.3.31 tidak memenuhi platform dependensi >=8.4.1. Pemeriksaan browser sedang dicoba; hasilnya dicatat terpisah. Belum commit/push.

Hasil pemeriksaan browser terbaru: Edge CDP berhenti pada `Page.enable` (timeout), termasuk setelah mencoba port dan profil browser terpisah. Pemeriksaan visual/responsif terbaru belum berhasil; tidak mengklaim lulus berdasarkan build.

### Pemeriksaan lima bagian volunteer - 16 November 2026
Daftar lamaran mendapat petunjuk langkah berikutnya sesuai status. Tombol asesmen baru kini mengikuti publikasi/published_at yang diwajibkan AssessmentService; attempt in_progress tetap dapat dilanjutkan sesuai inspect server. Metadata tenggat memakai WIB secara eksplisit dan layout tiga kolom hanya pada layar besar. Ringkasan kehadiran di detail/riwayat dapat dibuka-tutup dengan details/summary bawaan, tetap memuat status, arsip, catatan panitia dan waktu pencatatan. Istilah kota event menjadi kota kegiatan; informasi usia tetap informasional sesuai keputusan pengguna.
Ditambahkan regresi publikasi pada VolunteerAssessmentInfoTest untuk tombol mulai dan lanjutkan. Perintah tes dicoba, tetapi terhenti sebelum tes dijalankan: PHP CLI 8.3.31, platform dependensi >=8.4.1. PHP XAMPP juga hanya 8.2.12; tidak mengubah dependensi atau melewati platform check.
Build frontend dan diff --check lulus. Browser Edge berhasil setelah dijalankan di luar sandbox: 30 kombinasi halaman/lebar 390, 768, 1440 tanpa overflow, termasuk drawer, repeater profil dan navigasi soal. Screenshot aktivitas desktop dan detail kegiatan mobile diperiksa. Browser memakai fixture HTML hasil render sebelumnya dengan aset CSS terbaru; belum memverifikasi perubahan Blade/status terbaru, sesi baru, atau submit formulir secara end-to-end karena render PHP terblokir. Tidak ada perubahan schema/route/service atau data kerja. Belum commit/push.

### Penyeragaman istilah - 16 November 2026
Atas permintaan pengguna, istilah volunteer digunakan di seluruh teks antarmuka (panel Volunteer, EO, admin, dan halaman publik), pesan profil, deskripsi seed, dokumentasi produk, serta ekspektasi tes terkait. Kapitalisasi mengikuti kalimat/judul. Role, route, dan nama tabel tetap volunteer. Laporan hasil E2E historis dan instruksi AGENTS.md dipertahankan. Build frontend dan diff --check lulus; tes PHP terbaru masih terblokir versi platform. Tidak menjalankan ulang seeder atau mengubah teks dalam data pengguna yang sudah tersimpan. Commit/push tetap ditunda.

### Sapaan dashboard seragam - 16 November 2026
Permintaan pengguna: sapaan mengikuti admin. Volunteer dan EO memakai Selamat Datang Kembali, nama akun berwarna biru, dan tanda seru dalam kartu putih dengan aksen lembut yang sama. Label pembuka tambahan dihapus; deskripsi tetap mengikuti peran. Ukuran judul EO disamakan dengan admin/volunteer (26px desktop, 22px ponsel). Build dan diff --check lulus. Perubahan Blade terbaru belum diperiksa di browser karena render Laravel memerlukan PHP >=8.4.1. Commit/push masih ditunda.

Lingkaran dekoratif kanan atas sapaan Volunteer dan EO disamakan dengan admin: warna #e9efff dan tanpa blur, tetap dekoratif/aria-hidden serta tidak menghalangi klik. Build dan diff --check lulus; belum diperiksa langsung di browser.
