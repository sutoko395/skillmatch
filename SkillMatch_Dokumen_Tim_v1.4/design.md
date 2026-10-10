# Design specification SkillMatch

## Editor assessment Organizer — 10 Oktober 2026

Bagian Assessment berada di bawah Posisi dan jadwal sebelum Paket dan pembayaran, menampilkan nama posisi/status/tautan Kelola Assessment atau Lihat Assessment bila terkunci. Editor per posisi memakai navbar/layout SF, beberapa soal dalam satu halaman, tombol tambah/hapus, opsi tetap A–D dan select satu kunci. Durasi 1–120 menit dan hitungan soal maksimal 50 ditampilkan. Simpan Draft mempertahankan isian parsial. Publikasikan Assessment membuka halaman konfirmasi posisi/jumlah/durasi/penguncian, checkbox persetujuan, Konfirmasi Publikasi, serta Simpan draft dan kembali. Published tampil read-only; empty/error/input lama/loading tersedia. Kunci tampil hanya untuk Organizer pemilik pada editor/konfirmasi, tidak pada halaman event publik atau peserta. Editor soal menggunakan Alpine existing satu kali.

## Penyesuaian form jadwal posisi — 10 Oktober 2026

Tampilkan jadwal event berlabel WIB di atas dua radio: Ikuti jadwal event (default posisi baru) dan Atur jadwal tugas khusus. Mode otomatis tidak meminta input waktu. Mode khusus diawali nilai event; event satu hari menampilkan tanggal sebagai keterangan dan input jam, event beberapa hari menampilkan input tanggal/jam. Jadwal existing ditampilkan sebagai jadwal khusus. Tampilkan error server dan pertahankan pilihan/input setelah gagal. Bagian Posisi dan jadwal tetap memiliki tombol pengelolaan posisi sendiri; perubahan ini tidak mencakup editor assessment.

Versi 1.4.2 • 4 Oktober 2026 • Pendamping [PRD tim](PRD_SkillMatch_Tim.md).

## 1. Dasar dan arah

Desain melanjutkan identitas dari `tailwind.config.js`, layout/sidebar dan halaman Blade dalam skillmatch-main.zip: indigo sebagai warna utama, sidebar admin indigo gelap, latar slate terang, kartu putih, radius lembut, ikon SVG garis, serta Inter. Ini spesifikasi berdasarkan sumber kode; tidak mengklaim telah memeriksa hasil render browser ZIP.

Pertahankan komponen yang berfungsi dan rapikan konsistensi. Tujuan: informasi mudah dibaca, satu tindakan utama jelas, konten nyata terlihat, ruang cukup tanpa kartu dekoratif berlebihan. Tidak mengganti stack atau memasukkan template dashboard baru.

Perubahan wajib terhadap ZIP: hanya Admin memakai dashboard analitik. Volunteer/Organizer memakai web utama dengan Ringkasan Aktivitas. Nama halaman saja tidak cukup; sidebar panel pengguna diganti navigasi web utama dan konten berbasis tugas.

## 2. Tokens

### 2.1 Warna sumber ZIP

| Token Tailwind | Hex | Pemakaian |
|---|---|---|
| bg-light | #F8FAFC | Latar halaman |
| bg-brand | #1E1B4B | Sidebar/header brand Admin |
| brand-primary | #4F46E5 | CTA utama, tautan penting, pilihan aktif |
| txt-light-primary | #0F172A | Judul dan teks utama |
| txt-light-secondary | #64748B | Deskripsi pendukung |
| txt-dark-primary | #F8FAFC | Teks utama pada sidebar gelap |
| txt-dark-secondary | #94A3B8 | Teks sekunder pada sidebar gelap |

Tokens pendukung yang diusulkan untuk menyatukan komponen: surface #FFFFFF; border #E2E8F0; hover primary #4338CA; focus indigo-500. Gunakan semantic badge berikut, selalu dengan label teks:

| Keadaan | Background / text | Contoh |
|---|---|---|
| Netral | slate-100 / slate-700 | Draft, belum dicatat |
| Menunggu | amber-50 / amber-800 | Menunggu verifikasi/pembayaran |
| Informasi | indigo-50 / indigo-700 | Assessment tersedia, sedang ditinjau |
| Berhasil | emerald-50 / emerald-800 | Diterima, hadir, pembayaran terverifikasi |
| Gagal/negatif | rose-50 / rose-800 | Ditolak, tidak hadir, pembayaran gagal |
| Dinonaktifkan | slate-100 / slate-600 | Akun/event ditangguhkan |

Kontras teks diperiksa pada implementasi; warna saja tidak membedakan status. Tidak memakai teks abu sangat muda untuk informasi utama.

### 2.2 Tipografi

Inter sebagai satu font sans. `font-sans` mengikuti Tailwind config. Hapus override `font-family: Poppins` pada html karena ZIP memuat Inter. Gunakan fallback system sans; font tidak boleh memblokir halaman jika gagal dimuat.

| Elemen | Target |
|---|---|
| H1 | 24 px mobile / 30 px desktop, semibold, leading rapat tetapi terbaca |
| Judul bagian | 18–20 px, semibold |
| Body/form | 14–16 px, leading 1.5 |
| Tabel | 14 px; jangan memakai 10 px untuk isi penting |
| Label/helper | 12–14 px; error minimal 12 px |
| Angka indikator Admin | 24–30 px, semibold, tabular numerals |

### 2.3 Ukuran dan jarak

Spacing 4/8/12/16/24/32/48 px. Halaman utama max-w-7xl (1280 px), padding horizontal 16 mobile, 24 tablet, 32 desktop. Gap bagian 24–32 px. Form panjang max-w-3xl, detail event max-w-7xl.

Kartu radius 16 px (`rounded-2xl`), border 1 px slate-200, shadow-sm. Input/tombol radius 10–12 px. Hindari shadow berlapis dan blur dekoratif besar. Tinggi kontrol minimal 44 px untuk sentuh; ikon standalone memiliki area klik setara dan aria-label.

## 3. Layout dan navigasi

### 3.1 Publik dan pengguna umum

Navbar atas konsisten: logo SkillMatch, Jelajahi Event, bantuan/informasi seperlunya. Pengunjung melihat Masuk/Daftar; Volunteer/Organizer melihat Aktivitas, menu sesuai role, notifikasi dan menu akun. Tombol admin tidak dicampur ke menu profil Volunteer/Organizer.

Volunteer: Aktivitas, Jelajahi Event, Lamaran Saya, Riwayat, Profil & Skill. Organizer: Aktivitas, Event Saya, Transaksi, Profil Organisasi. Paket dipilih pada event; tidak membuat dashboard paket terpisah. Menu pada mobile menjadi disclosure/drawer dengan tombol berlabel, escape dan fokus kembali.

Detail event Organizer memiliki tab: Ringkasan, Posisi & Persyaratan, Assessment, Pelamar, Pelaksanaan, Paket & Pembayaran. Tab tidak menampilkan angka palsu. Aksi yang belum memenuhi syarat memiliki alasan dan tautan ke langkah perbaikan.

Volunteer/Organizer tidak memakai sidebar gelap panel pada ZIP untuk target final. Konten profil dan komponen kartu lama dipakai ulang dalam shell web utama. Tautan /dashboard lama hanya redirect.

### 3.2 Admin

Login bersama di /login, dashboard /admin/dashboard dengan layout khusus admin. Tidak ada halaman login admin tersendiri. Semua tombol logout memakai POST /logout. Pertahankan sidebar w-72 (288 px), header h-16 (64 px), konten desktop offset kiri 288 px; pada layar di bawah lg sidebar off-canvas dengan overlay. Ikon/nav indigo gelap mengikuti ZIP.

Menu: Dashboard, Pengguna (Organizer/Volunteer), Moderasi Event, Master Data (Skill/Kategori/Kota), Paket, Transaksi, Konten, Pengaturan, Audit. Akun Admin/logout dan Lihat Website berada pada area tersendiri. Jangan menumpuk seluruh form pengelolaan ke dashboard.

Tidak ada tautan `href="#"` untuk fitur seolah aktif. Pada pembangunan, item belum jadi disembunyikan atau disabled dengan label; final P0 harus semua aktif.

### 3.3 Login bersama — SF

Satu kartu login berisi email, password, ingat saya bila tersedia, lupa password, Masuk, dan tautan daftar Volunteer/Organizer. Tidak ada tab Admin atau pemilih role. Tampilkan error validasi, status pengiriman dan fokus keyboard yang jelas. Admin memakai formulir yang sama lalu masuk panelnya; pengguna umum masuk Aktivitas, dengan langkah verifikasi/profil bila diperlukan. Data role dan redirect ditentukan server sesuai kontrak, bukan kontrol UI.

## 4. Spesifikasi halaman inti

### 4.1 Katalog dan detail event — A2

Katalog: judul "Temukan kegiatan yang sesuai denganmu", pencarian, filter kategori/kota/tanggal, jumlah hasil, grid 1/2/3 kolom sesuai lebar, pagination. Kartu berisi judul, Organizer, kota, jadwal WIB, deadline, posisi tersedia dan tombol Lihat Detail. Poster opsional dengan rasio tetap 16:9; fallback brand rapi jika tidak ada.

Detail: judul dan metadata, deskripsi, daftar posisi beserta skill/jadwal/kuota/persyaratan, panel tindakan. CTA "Lamar posisi" hanya jika eligibility publik terpenuhi; pengunjung diarahkan login lalu kembali ke detail. Event tutup menampilkan alasannya. Kontak privat pelamar tidak pernah muncul di katalog.

### 4.2 Form event — A2, tab assessment A4

Gunakan langkah: Informasi Event → Posisi & Jadwal → Assessment → Paket → Tinjau & Ajukan. Simpan Draft tersedia setiap langkah yang memiliki data valid parsial. Tampilkan progres langkah sebagai teks/stepper pendek, bukan persentase palsu.

Posisi berulang berupa kartu dengan nama, kuota, skill+minimum level, interval tugas, dokumen wajib, dan syarat wajib/preferensi. Form bertingkat panjang dibagi section, bukan satu modal besar. Tinjau menampilkan seluruh syarat publikasi dan tautan perbaikan. Status moderasi, publikasi, dan pelaksanaan ditampilkan dengan label terpisah agar approved tidak disalahartikan sebagai published.

### 4.3 Profil — SF dasar, A1 verifikasi organisasi

Volunteer: Identitas → Kota & Kontak → Skill dan Level → Ketersediaan. Availability menggunakan tanggal dan waktu mulai/akhir berulang, label WIB. Jangan hanya menawarkan Weekend/Weekday karena tidak mencukupi perhitungan jam. Tampilkan pesan migrasi "Lengkapi tanggal dan jam ketersediaan" pada akun lama.

Organizer: identitas/kontak/deskripsi, status verifikasi, dokumen organisasi privat. Simpan perubahan biasa tidak meminta semua berkas ulang. Perubahan yang memerlukan verifikasi ulang menjelaskan dampaknya sebelum konfirmasi. Catatan revisi admin terlihat dekat form.

### 4.4 Lamaran dan upload — A3

Tampilkan event/posisi yang dipilih di bagian atas; ringkasan profil, daftar dokumen wajib, upload dan Tinjau Lamaran. Bantuan upload: "CV: PDF. Dokumen pendukung: PDF/JPG/PNG. Maksimal 5 MB per file dan 5 file per lamaran."

Input file native tetap tersedia; drag-drop opsional, bukan satu-satunya cara. Daftar berkas menampilkan nama, jenis, ukuran, "Dokumen tersimpan", Unduh/Hapus untuk draft. Error spesifik: tipe tidak didukung, melebihi ukuran/jumlah, simpan gagal. Tidak ada spinner "Memindai virus", ikon aman antivirus, atau tahap menunggu scanner.

Saat submit, tombol loading/disabled mencegah klik tidak sengaja; server tetap idempoten. Success mengarah detail lamaran dengan status aktual. Jika evaluasi belum selesai: "Lamaran terkirim, menunggu evaluasi persyaratan." Dokumen bersifat privat; Organizer terkait melihatnya sesudah lamaran dikirim.

### 4.5 Detail lamaran dan kandidat — A3 + hasil A4

Volunteer: status terbaru, timeline tahapan, alasan screening, CTA assessment bila eligible, hasil keputusan dan catatan. Tahapan tidak diberi tanda selesai sebelum data tersedia.

Organizer: tab Semua Pelamar/Siap Ditinjau/Diterima/Ditolak; cari nama/filter posisi/status. Kolom utama nama, posisi, status, Match Score, nilai assessment, waktu submit, aksi. Pada mobile gunakan kartu atau scroll di dalam tabel, bukan overflow seluruh halaman.

Detail kandidat: profil snapshot, alasan screening per syarat, komponen skor S/A/L dan bobot, assessment terpisah, dokumen sebagai metadata+unduh, tombol Terima/Tolak bila under_review. Tampilkan "Skor membantu peninjauan; keputusan ditentukan Organizer" secara singkat. Konfirmasi Terima menyebut sisa kuota; Tolak meminta alasan. Error kuota/bentrok menjelaskan tindakan tanpa kehilangan konteks.

Jangan menampilkan kunci jawaban pada UI peserta. Dokumen pelamar tidak di-embed dalam public iframe; gunakan unduhan attachment dengan otorisasi.

### 4.6 Assessment — A4

Halaman mulai: judul posisi, jumlah soal, durasi, satu percobaan, tombol Mulai. Setelah mulai: timer terlihat, daftar nomor soal, pertanyaan dengan radio group berlabel, Sebelumnya/Berikutnya, status penyimpanan dan Kirim Jawaban.

Auto-save gagal menampilkan "Jawaban belum tersimpan, coba lagi"; jangan menutupi semua layar. Reload mengambil waktu akhir dan jawaban dari server. Saat habis, tampilkan status sedang menyelesaikan lalu hasil tersimpan. Halaman hasil: nilai assessment dan "Menunggu peninjauan Organizer"; tidak menjanjikan diterima berdasarkan skor.

### 4.7 Pelaksanaan/attendance — A3

Di detail event → Pelaksanaan. Header nama/jadwal/lokasi/status event. Rekap ringkas: Peserta diterima, Hadir, Tidak hadir, Belum dicatat. Ini rekap operasional event, bukan dashboard Organizer.

Tabel: Nama, Posisi, Kehadiran, Waktu pencatatan, Catatan, Aksi. Default unrecorded berlabel "Belum dicatat". Organizer menekan Catat, memilih Hadir/Tidak hadir, catatan opsional, Simpan. Edit sesudah event selesai meminta alasan wajib. Volunteer hanya melihat hasil.

Bulk action jika ada: checkbox peserta eligible → "Tandai hadir (n)" → konfirmasi nama/jumlah. Jangan default menandai semua hadir. Completion button "Selesaikan Event" membuka ringkasan dan blocked reasons untuk belum akhir jadwal, seleksi menggantung, atau kehadiran belum dicatat. Waktu pencatatan tidak disebut jam datang.

Riwayat Volunteer: event, posisi, tanggal, status kehadiran; absent diberi "Tidak hadir", cancelled "Event dibatalkan". Sertifikat/performa tidak dijanjikan pada MVP.

### 4.8 Paket dan pembayaran — A2

Paket menampilkan harga/limit posisi/limit lamaran/periode secara sejajar. Nilai demo diberi "Contoh paket"; halaman transaksi diberi "Sandbox — simulasi pembayaran". Tinjau checkout menampilkan event dan snapshot harga final dari server.

Status page menampilkan order ref, nominal, paket, pending/paid/failed/expired, tombol Periksa Status bila sesuai dan tautan kembali ke event. Kembali dari popup tidak otomatis menampilkan sukses. Jika paid tetapi event belum eligible, jelaskan syarat tersisa. Admin melihat snapshot transaksi dan audit, bukan edit status paid.

### 4.9 Ringkasan Aktivitas — A4

Volunteer: sambutan singkat; prioritas tindakan (profil belum lengkap, assessment); lamaran terbaru; jadwal accepted; notifikasi. Organizer: event sendiri; moderasi/revisi/payment tertunda; kandidat perlu review; tugas attendance. Hindari empat kartu angka kosong seperti organizer dashboard ZIP. Bila belum ada data, tampilkan CTA konkret "Buat event pertama" atau "Jelajahi event".

### 4.10 Admin analitik — A1

Baris filter periode dan waktu hitung data; kartu kondisi saat ini terpisah dari angka periode; grafik tren sederhana dan tabel alternatif; antrean moderasi/organisasi/transaksi. Semua angka berasal query. Nilai payment berlabel "Nilai transaksi sandbox". Error query ditampilkan sebagai gagal memuat dengan Coba Lagi, bukan nol.

## 5. Komponen bersama dan PIC

Nama komponen berikut target baru/penyelarasan, bukan klaim telah ada semuanya.

| Komponen | Fungsi | Pemilik |
|---|---|---|
| layouts/public, layouts/user | Navbar web utama; shell pengguna | SF |
| admin/layouts/sidebar | Shell Admin yang diperbaiki dari ZIP | SF |
| components/page-header, section-card | Judul, deskripsi, satu CTA utama; kontainer | SF |
| components/button, form-field, status-badge | Variant konsisten, label/error/helper | SF |
| components/empty-state, flash, confirm-dialog | Feedback dan konfirmasi yang reusable | SF |
| components/event-card | Katalog dan metadata event | A2 |
| components/file-list | Metadata upload/unduh, tanpa path internal | A3 |
| components/match-breakdown, assessment-timer | Rincian hasil/timer presentasional | A4 |
| components/notification-menu | Notifikasi/read | A4 |

Komponen Breeze yang tersedia (input-label, text-input, input-error, modal, primary/secondary/danger-button) dipakai ulang atau dibungkus; jangan menciptakan tiga sistem tombol berbeda.

## 6. State dan interaksi wajib

Setiap halaman data: loading bila request asinkron, empty dengan CTA relevan, error dengan tindakan, success setelah server commit. Form: label bukan placeholder saja, required jelas, error dekat field dan ringkasan bila panjang. Simpan mempertahankan input saat gagal. Aksi destructive pakai konfirmasi dan alasan bila ditentukan PRD.

Alpine hanya untuk state UI: tab lokal, dropdown, modal, disclosure, timer visual dan autosave trigger. Status lamaran, nilai, harga, eligibility, kuota dan deadline tetap server. Untuk tab yang menjadi halaman terpisah gunakan route/URL agar bisa dibookmark.

Animasi pendek 150–200 ms; hormati prefers-reduced-motion. Tidak ada hover scale pada semua kartu/form yang mengganggu baca. Dropdown/modal menutup dengan Escape, memiliki fokus awal, focus trap bila modal dan fokus kembali ke pemicu.

## 7. Responsivitas dan aksesibilitas

Uji 360, 768, 1280 px. Tidak ada horizontal scroll halaman keseluruhan. Filter bertumpuk pada ponsel, dua kolom form hanya pada desktop, tabel dapat scroll dalam region berlabel. Tombol utama terlihat dan tidak tertutup header. Kontras teks/status diperiksa; ikon dekoratif aria-hidden, ikon aksi aria-label. Radio assessment dikelompokkan dengan fieldset/legend.

Hapus `body:not(.ready){display:none}` dari layout ZIP. Gunakan x-cloak hanya pada elemen Alpine yang memang perlu disembunyikan sementara; halaman tetap dapat dibaca jika JavaScript gagal. Loading overlay tidak menutup permanen konten.

## 8. Integrasi CSS/JS dan langkah perbaikan

1. Pertahankan Tailwind 3 + PostCSS dan tokens existing; tidak mengaktifkan plugin Tailwind 4 yang tidak terpakai.
2. Import Alpine hanya dari resources/js/app.js; hapus CDN Alpine pada tiga layout.
3. Satukan font Inter dan CSS global di app.css; hapus override Poppins/duplikasi style.
4. SF membuat shell user baru dan mengadaptasi profil lama; admin sidebar dipertahankan.
5. A2/A3/A4 memakai komponen bersama dan menghubungkan seluruh menu ke route kontrak.
6. Semua PIC menyertakan screenshot desktop/mobile modulnya saat PR. A1 meninjau konsistensi, bukan mengerjakan ulang seluruh UI.

## 9. Checklist penerimaan desain

- Warna/typography sesuai tokens; tidak ada statistik dummy atau tautan # pada P0.
- Dashboard hanya Admin; pengguna berada pada web utama dengan aktivitas.
- Satu primary CTA per section utama; status teks jelas dan berbeda per dimensi.
- Upload tanpa label/antrean antivirus, download privat.
- Timer/paid/score/attendance mencerminkan status server.
- Unrecorded bukan absent; waktu catat bukan waktu datang.
- Empty/error/validation/success dan keyboard diuji pada semua alur inti.
- Tidak ada secret, storage_key, stack trace atau istilah teknis yang tidak membantu pengguna dalam UI.

## Penyesuaian 5 Oktober 2026 - beranda dan dashboard dasar

Pertahankan hero beranda baseline "Hubungkan Talent Relawan dengan Event Terbaik", identitas indigo, tombol Masuk/Daftar dan footer tim. Gunakan layout/navbar SF agar Inter dan Alpine tetap dimuat sekali. Pengguna yang login melihat CTA menuju tujuan sah sesuai role/status melalui redirect bersama; jangan menyebut halaman pengguna sebagai dashboard analitik. Teks promosi tidak mengklaim engine matching yang belum tersedia sudah berfungsi. Pemberitahuan katalog/matching yang belum tersedia hanya keterangan pendukung, bukan pengganti seluruh beranda.

A2 mengembangkan beranda ini dan menghubungkan katalog setelah route/data publik memenuhi kontrak. A1 mempertahankan kartu hitungan database dan aksi cepat admin, kemudian menambah analitik periode, tren dan state. Tidak mengembalikan sidebar pengguna, login terpisah, statistik dummy atau tautan kosong dari baseline.

## Pemulihan ringkasan pengguna - 5 Oktober 2026

Aktivitas Volunteer/Organizer memakai kembali sambutan, kartu putih beraksen indigo dan aksi cepat profil dari baseline dalam navbar SF. Tampilkan informasi profil aktual dan interval WIB, bukan dashboard analitik platform. Status akun dan organisasi ditampilkan terpisah. Bagian kegiatan yang belum terintegrasi memakai keterangan; tidak membuat kartu angka nol atau tombol seolah berfungsi.

## Implementasi antarmuka A2 - 5 Oktober 2026

Beranda baseline dipertahankan dengan CTA Jelajahi Event. Katalog memakai navbar publik, filter judul/kategori/kota/tanggal WIB, jumlah hasil nyata, pagination dan kartu 1/2/3 kolom. Detail menampilkan jadwal/skill/kuota/syarat; pengajuan lamaran diberi keadaan belum tersedia sampai route A3 ada. Login Volunteer dapat kembali ke detail yang masih eligible melalui events.join.

Event Saya merupakan daftar operasional, bukan dashboard baru. Informasi Event -> Posisi/Jadwal -> Assessment -> Paket -> Tinjau/Ajukan ditampilkan sebagai urutan teks. Skill/jadwal/syarat berulang memakai Alpine yang sudah dimuat SF; form server tetap memvalidasi seluruh data. Form assessment belum tersedia dan tidak dianggap selesai. Status moderasi/publikasi/pelaksanaan ditampilkan terpisah.

Paket/order dan UI admin paket/transaksi memakai komponen SF serta layout admin existing. Checkout selalu berlabel Sandbox, tidak ada tombol paid manual atau statistik fixture. Event dibatalkan/pembayaran review menampilkan status sebenarnya. Build/render Blade sudah diperiksa; review visual 360/768/1280 dan keyboard masih perlu dilakukan.

## Beranda publik lengkap - 5 Oktober 2026

Beranda memakai hero indigo gelap dengan tipografi putih/indigo terang, ilustrasi komunitas SVG lokal, pencarian dalam permukaan putih, pengenalan produk, tiga prinsip skill/waktu/lokasi, pratinjau event, panduan Volunteer/Organizer, FAQ native details/summary, CTA dan footer tim. Identitas serta judul hero baseline diteruskan. Tidak ada gambar eksternal, testimoni, logo mitra atau angka promosi buatan.

Grid beradaptasi dari satu kolom menjadi dua/tiga kolom. CTA memiliki target sentuh minimal 44px, heading/label/landmark semantik, ilustrasi berjudul, fokus keyboard dari CSS bersama, dan FAQ bekerja tanpa JavaScript. Inter, navbar, Vite dan Alpine tetap memakai layout SF. Komponen `event-card` A2 dipakai oleh beranda dan katalog untuk kategori, Organizer, posisi, kota, jadwal/deadline WIB dan tautan detail.

Event kosong tampil sebagai keadaan informatif, bukan kartu demo. Pratinjau terbatas enam event publik yang pendaftarannya sedang terbuka dan batas lamaran paket belum habis. FAQ serta panduan memberi keterangan fitur lanjutan yang belum tersedia; CTA hanya menuju katalog, auth, redirect akun atau anchor section yang benar-benar ada. Katalog tetap menyediakan hasil/filter/pagination lengkap.

## Tampilan masuk dan registrasi - 5 Oktober 2026

Login/register memakai komponen auth-layout khusus dengan logo SkillMatch, tautan beranda, panel indigo editorial pada desktop, form putih beradius 24px, Inter dan CTA penuh. Pada mobile panel editorial disembunyikan agar form mudah dijangkau. Registrasi menampilkan kartu radio Volunteer/Organizer berlabel; login tetap hanya email/password tanpa pemilih role. State lama role/nama/email dipertahankan setelah validasi; kata sandi tidak diisi kembali.

Komponen auth-password-field memiliki toggle Lihat/Sembunyikan berbasis Alpine existing, aria-pressed/label dan fallback input password jika JavaScript tidak tersedia. Form memakai label/error spesifik, autocomplete, fokus keyboard dan status submit. Tidak ada akun admin publik, login khusus admin, dependency tambahan atau perubahan guard/redirect/backend. Halaman reset/verifikasi tetap memakai guest-layout existing.

## Navigasi paket dan transaksi admin

Kelola Paket dan Transaksi Sandbox berada pada sidebar kiri admin, mengikuti ikon/style menu existing. Penanda aktif memakai admin.packages.* dan admin.orders.* serta aria-current. Sidebar mobile memuat menu yang sama; tautan ganda di atas konten dihapus.

## Nama menu transaksi

Permintaan pengguna: label menu, judul halaman dan title browser admin menggunakan Transaksi. Mode gateway tetap Sandbox; keterangan simulasi tetap berada pada konteks pembayaran/detail, bukan menjadi nama menu. Paket Free/Standard/Premium memerlukan konfigurasi harga dan batas manfaat yang disepakati sebelum diaktifkan.

## Kartu paket berdasarkan keputusan pengguna

Beranda menampilkan kartu Free/Standard/Premium dari paket aktif database: judul, harga besar (Gratis untuk 0), per event, batas posisi/lamaran/hari dan CTA Mulai sebagai Organizer. Tampilan kartu putih berborder/radius mengikuti gambar pengguna dan identitas indigo. Status fitur screening/assessment/seleksi/dokumen/attendance yang belum tersedia tertulis Dalam pengembangan. Admin memiliki Edit paket dan badge status; Organizer memilih dari kartu yang sama. Grid beradaptasi 1/2/3 kolom, tanpa label rekomendasi palsu.
