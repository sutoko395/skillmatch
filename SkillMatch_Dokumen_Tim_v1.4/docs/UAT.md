## Hierarki font screening dan kecocokan - 15 November 2026

Periksa Hasil screening/Kecocokan kandidat pada detail EO: judul, skor, labeltabel
dan keterangan punya hierarki berbeda tanpa memperbesar kartu/padding. Tabel
rinci tetap lengkap, wrapping lokal aman pada390/768/1440.

## Penutupan seleksi di Kelola Pelamar - 15 November 2026

Kelola Pelamar -> Lihat pelamar: Tutup seleksi tertunda ada di bawah daftar jika
masih pending, meski filter kandidat kosong. Submit menyimpan alasan/konfirmasi
dan kembali ke daftar, pending ditolak menurut aturan existing. Pelaksanaan tidak
memiliki form penutupan; pemberitahuan dan Ke Kelola Pelamar muncul hanya ketika
seleksi/assessment masih pending. Tidak ada aksi baru pada terminal. Endpoint
completion tetap menolak pending bila diminta langsung.

## Hierarki font kartu peserta - 15 November 2026

Kartu peserta memiliki hierarki font yang jelas tanpa membesarkan card: nama
lebih tegas daripada posisi/progres, tanggal/jam kecil, status dan tombolringkas.
Verifikasi keterbacaan dan tidak overflow di390/768/1440; posisi aksi tetap.

## Regresi token persiapan absensi otomatis - 15 November 2026

Halaman EO harus memiliki meta csrf-token sesi valid. Pending bootstrap mengirim
POST dengan X-CSRF-TOKEN; jangan mengandalkan mock DOM saja. Token hilang tidak
boleh melempar null/content ke UI atau mengirim request tanpa CSRF; tampil pesan
muat ulang. Error jaringan memakai pesan ramah, tidak retryloop. Validasi server
tetap ditampilkan dan endpoint tetap memakai CSRF/otorisasi existing.

## Persiapan sesi otomatis untuk absensi belum dicatat - 15 November 2026

- Accepted lama tanpa parent/unrecorded: buka detail -> sesi langsung siap tanpa
  checkbox Siapkan. Status tetap Belum dicatat; user menandai hadir sendiri.
- Buka ulang/dua request sama tidak menggandakan sesi atau audit; GET sendiri
  tidak menulis data. Pending ditangani sebelum filter hari supaya tetap terlihat.
- Catatan unrecorded lama tetap di arsip; legacy present/absent tidak direset.
- Foreign owner/role/ids eventlain/nonaccepted/terminal ditolak; invalid snapshot
  rollback dan tampil error tanpa retryloop. Coba lagi hanya atas tindakan user.
- JS mengirim POST CSRF sekali, reload mempertahankan URL; tanpa JS fallback POST.
  Penerimaan baru existing tetap menyiapkan sesi dalam transaksi acceptance.

## Rapatkan aksi dan buang ruang bawah sesi - 15 November 2026

Pada desktop Catatan mendekat ke tanggal dan Edit ke badge; tidak ada ruang kosong
berlebih di bawah aksi tertutup. Buka/tutup editor dan Batal tetap berfungsi tanpa
overlap/overflow pada390/768/1440.

## Kartu lebih padat dan Batal koreksi - 15 November 2026

Kartu/header/sesi/editor lebih pendek; Catatan dan Edit rapat ke baris atas. Ubah
status sementara lalu Batal di kanan Simpan: form tertutup, nilai kembali ke status
tersimpan tanpa request. Batal catatan existing tetap membuang draftnya sendiri.

## Posisi waktu pencatatan dan aksi sesi - 15 November 2026

Sesi yang sudah hadir: Dicatat ada di kanan, Edit absensi di bawah badge Sudah
hadir, Catatan di bawah tanggal. Periksa desktop/tablet/mobile; klik catatan,
batal dan koreksi tetap berfungsi; final tetap tanpa edit.

## Edit absensi ringkas di bawah status - 15 November 2026

Edit absensi tampil kecil tanpa kotak tepat di bawah status, baik desktop maupun
mobile. Klik tetap membuka koreksi, tidak bertumpuk dengan tombol Tandai hadir
atau editor catatan; final tidak memiliki aksi edit.

## Kartu ringkas, Batal catatan dan Edit absensi - 15 November 2026

Periksa kartu peserta tertutup lebih pendek. Buka Catatan, ubah teks lalu Batal:
editor tutup dan draft dibuang, buka lagi menampilkan nilai tersimpan; tidak ada
submit/perubahan DB. Batal berada di kanan Simpan. Edit absensi (pensil, outline
biru) menggantikan titik tiga dan membuka form koreksi existing. Completed tetap
readonly. Periksa desktop/mobile tanpa overflow.

## Penyelarasan visual dengan mockup check-in - 15 November 2026

Bandingkan kartu peserta terhadap mockup yang disetujui: avatar bulat, progres
dengan pemisah, toggle kanan; tanggal/jam/status/aksi sejajar desktop dan Catatan
tepat di bawah tanggal. Klik Catatan membuka editor fullwidth, simpan terpisah
serta readonly final tetap. Menu titik tiga membuka koreksi kehadiran. Search
fullwidth dapat disubmit Enter; Filter peserta menyimpan semua opsi dan rekap.
Periksa tampilan390/768/1440 dan nama panjang tanpa overflow. Tidak menuntut isi
fixture/data/mockup menjadi data nyata pada aplikasi.

## Check-in peserta dan catatan terpisah - 15 November 2026

- Cari peserta -> Lihat sesi -> Tandai hadir: tersimpan langsung, progress dan
  status berubah; catatan sebelumnya dipertahankan. Tutup sesi merapikan kartu.
- Satu/lebih sesi, termasuk sesi berbeda tanggal, menampilkan jam/tanggal benar;
  sebelum mulai tidak ada tombol tulis. Belum dipilih tetap Belum dicatat.
- Tambah catatan membuka editor opsional dan Simpan catatan. Simpan saat unrecorded
  tidak menandai hadir, mengubah lifecycle atau waktu pencatatan absensi. Catatan
  setelah hadir tidak mengubah status/waktu hadir; dapat dihapus. Volunteer dapat
  membacanya di detail lamaran; riwayat terminal memakai komponen existing.
- Ubah kehadiran tetap dapat mencatat Tidak hadir/koreksi sebelum final. Dua halaman
  stale tidak boleh menimpa perubahan. completed/cancelled menolak mutasi catatan.
- Owner/role lain ditolak; POST/PATCH tetap CSRF/revision. Periksa buka-tutup, editor
  tersembunyi, loading dan wrapping di desktop/mobile. Filter/legacy tetap tersedia.

## Rekap sesi inline - 15 November 2026

Di bawah Kehadiran peserta tampil tiga hitungan sesi inline berwarna, tanpa
kartu dan buka-tutup. Nilai sesuai sessionSummary, tetap terbaca pada ponsel.

## Hapus kartu rekap peserta - 15 November 2026

Detail Pelaksanaan tidak menampilkan empat kartu rekap peserta; Rekap sesi,
filter peserta, kartu sesi, form absensi dan tindakan tetap dapat digunakan.

## UAT detail Pelaksanaan EO - 15 November 2026

- Kembali merah kiri atas menuju daftar Pelaksanaan. Jadwal WIB/status, empat
  hitungan peserta serta rekap sesi (buka/tutup) tetap sesuai data nyata.
- Filter nama/posisi/status/tanggal, Reset dan pagination tetap berfungsi.
- Peserta/sesi tampil sebagai kartu responsif dengan ikon; simpan absensi tetap
  POST/PATCH, CSRF dan revision. Catatan readonly serta arsip legacy tetap terbaca.
- Sebelum sesi mulai ada pesan belum dibuka; completed/cancelled tidak memiliki
  form simpan. Completed menampilkan Absensi sudah final tanpa pesan per sesi
  yang berulang. Jadwal lintas tanggal tetap menampilkan tanggal akhir.
- Tutup seleksi dan completion mempertahankan alasan/konfirmasi/zero peserta dan
  aturan backend. Copy lebih ringkas; tidak ada paragraf rekap/petunjuk tabel lama.

## UAT penghapusan keterangan detail pelamar - 15 November 2026

Detail EO tidak menampilkan tiga footer penjelasan screening/matching/assessment
atau tautan Pelaksanaan dan kehadiran. Kartu Keputusan seleksi kosong tidak ada;
form seleksi/alasan/penjelasan batas waktu yang relevan tetap tampil sesuai state.
Komponen yang dipakai volunteer mempertahankan keterangan/link default existing.

## UAT navigasi dan tampilan pelamar EO - 15 November 2026

- Kelola Pelamar -> Lihat Pelamar -> Kembali merah kiri atas menuju Kelola Pelamar.
  Tinjau lamaran -> Kembali merah menuju daftar pelamar event yang sama.
- Tab Semua Pelamar/Siap Ditinjau/Pelaksanaan di daftar tidak ada; status kandidat,
  filter posisi, ready link existing, pagination/urutan skor tetap berfungsi.
- Daftar menampilkan nama/kota/posisi/status/skor/waktu WIB/dokumen dan Tinjau
  outline biru; kosong dan filter tanpa hasil memiliki penjelasan yang sesuai.
- Detail mempertahankan snapshot profil, screening, matching, assessment, jadwal,
  dokumen privat serta form seleksi/alasan/CSRF sesuai state dan hak akses.
- Periksa desktop/mobile: ikon/ukuran font, wrapping, drawer dan tabel scroll lokal.
  Organizer lain/role lain tetap tidak dapat membaca atau memutuskan lamaran.

## UAT tampilan Profil Organisasi EO - 15 November 2026

- Profil existing menampilkan nama/status, deskripsi, alamat, kota, website,
  penanggung jawab, telepon/email dan dokumen dalam kelompok terpisah dengan ikon.
- Edit Profil biru outline membuka dialog existing; Batal/tutup/Escape menutupnya.
  Simpan tetap POST/CSRF, validasi, upload privat dan konfirmasi verifikasi ulang.
- Website kosong dan dokumen kosong menampilkan keterangan. Catatan revisi admin,
  profil baru/pending/inactive serta error form tetap dapat diakses sesuai policy.
- Periksa desktop/ponsel: label kecil, nama lebih tegas, teks panjang membungkus,
  dialog dapat di-scroll dan kartu tidak menyebabkan horizontal overflow.

## UAT penyelarasan daftar EO - 15 November 2026

- Halaman pilih/ubah paket: Kembali merah kiri atas menuju detail kegiatan milik
  organizer; pemilihan paket/pembayaran tetap berjalan sesuai aturan existing.
- Kelola Pelamar: badge Publikasi/Pelaksanaan tidak tampil; metadata/count/aksi ada.
- Pelaksanaan: badge Publikasi/Jadwal tidak tampil; kelompok tetap ada. Cari nama
  dan pilih Semua/Hari ini/Berlangsung/Riwayat; Terapkan/Reset mengikuti gaya
  Kelola Pelamar. Pagination mempertahankan filter, hasil kosong spesifik.
- Filter tidak menampilkan event owner lain, draft, atau event besok. Pencarian
  wildcard dianggap literal; input phase tak dikenal/array/search >255 ditolak.
- Periksa desktop/ponsel tanpa overflow dan tanpa perubahan aturan absensi.

## Batas akhir absensi saat Selesai - 15 November 2026

Catat/koreksi kehadiran setelah sesi dimulai selama nonterminal; selesai acara
saja tidak mengunci. Selesaikan kegiatan baru setelah semua sesi berakhir dan
final serta seleksi/assessment tidak tertunda. Checkbox completion menerangkan
absensi menjadi final. Setelah completed: form simpan/alasan koreksi hilang,
status/jadwal/catatan riwayat tetap terbaca, banner Absensi sudah final terlihat.
PATCH sesi, PATCH parent satu sesi/legacy, dan POST prepare lama ditolak walaupun
payload valid/beralasan atau identik. Status/note/revision/recorded_at/rekap/
riwayat/audit/outbox tidak berubah. Cancelled tetap terkunci, owner/role/pre-start/
revision tetap dibatasi. Simpan vs complete berbagi lock Event. Tidak ada H+1.

## Hub EO berdasarkan publikasi dan hari WIB - 15 November 2026

1. Kegiatan Saya tetap memuat draf/pending/approved/published. Kelola Pelamar
   tidak memuat draf/approved yang belum pernah published; query owner lain tidak
   membocorkan data. Count hanya lamaran submitted.
2. Cari nama, kombinasikan open/closed/history dan pindah halaman. Filter tetap,
   Reset kembali ke semua pernah published; wildcard %, _ dan ! menjadi teks.
   Open pada opens_at persis, bukan deadline persis; closed sejak deadline.
   Jadwal opens_at masa depan dan tenggat belum lengkap masih melalui Semua.
3. Selesai, dibatalkan atau suspended/unpublished setelah published tetap ada
   di Riwayat pelamar dan hasil seleksi masih terbaca. Tanpa published_at tidak
   boleh masuk hanya karena status approved.
4. Bekukan sebelum00.00 WIB dan sesudahnya untuk acara hari berikut pukul12.
   Sebelum00 belum tampil pelaksanaan; tepat00 tampil Hari ini; jam12 menjadi
   Berlangsung. Acara besok00 WIB belum tampil hari ini. Multi-hari yang belum
   berakhir tetap Berlangsung. Bandingkan batasUTC17.00 dengan00.00 WIB.
5. Terminal pernah published tetap Riwayat meski dibatalkan sebelum tanggal
   acara. Lewat ends_at tanpa completion diberi Menunggu penyelesaian dan tidak
   otomatis mengubah lifecycle. Urutan aktif sebelum riwayat, pagination12.
6. Absensi/sesi/completion langsung melalui endpoint tetap tunduk aturan lama;
   daftar hari ini tidak membuka pencatatan sebelum waktu. Role/owner/akun
   pending/inactive tetap dibatasi. Cek390/768/1440px serta empty/filter-empty.

## Notifikasi sementara EO - 14 November 2026

Simpan koreksi: toast centang hijau bertuliskan Perubahan berhasil disimpan
muncul kanan atas tanpa mendorong konten, hilang sekitar5detik, dapat ditutup ?.
Periksa390/768/1440px, pesan panjang membungkus, keyboard/fokus dan reduced motion.
Hover/fokus menunda hilang. Error dan daftar validasi tidak otomatis menghilang;
role lain tetap memakai tampilan notifikasi existing. Toast tidak menutup modal/
drawer (z-index di bawah keduanya). Tanpa JS pesan tetap terlihat.

## Keseragaman detail draf dan published EO - 14 November 2026

Bandingkan kartu Informasi kegiatan, subkartu posisi/jadwal, ringkasan asesmen,
nav dan warna tombol draf/published di390/768/1440px. Published menampilkan Edit
pensil di header informasi dengan label aksesibel khusus judul/deskripsi,
menuju form koreksi teks existing. Tombol Lihat Asesmen/halaman publik/pelamar
outline biru; aksi pembatalan tetap merah. Posisi published tanpa baris aksi
kosong dan tidak mendapat tambah/edit/hapus. Upload cover/paket/jadwal tetap
terkunci, request langsung tetap ditolak. Koreksi teks dan koordinasi mengikuti
aturan existing, tidak ada perubahan status/data melalui perubahan desain ini.

## Label pembatalan draf EO - 14 November 2026

Draf belum diajukan: judul Pembatalan draf, tombol Batalkan draf dan konfirmasi
data tetap tersimpan. Kegiatan pernah diajukan/published: Pembatalan kegiatan,
Batalkan kegiatan dan konfirmasi lamaran terkait. Tidak ada kalimat teknis
ketergantungan layanan. Selesai/dibatalkan tidak menampilkan form seperti
existing. Tidak ada perubahan route/validasi/transisi atau aturan hapus draf.

## Ikon ringkasan asesmen - 14 November 2026

Periksa nama posisi, ikon clipboard, status dengan file/gembok, jumlah soal
berikon daftar dan durasi berikon jam. Draf/belum dibuat/published memakai data
actual; jumlah/durasi hanya tampil bila assessment ada, durasi null berlabel
Belum diatur. Mobile metadata dapat membungkus tanpa overflow. Kelola/Lihat
Asesmen tetap menuju posisi yang benar dan mengikuti editable existing.

## Subkartu posisi EO - 14 November 2026

Periksa setiap posisi memiliki dua subkartu di dalam kartu utama: deskripsi/
skill/persyaratan serta jadwal. Nama/kuota di atas, aksi di bawah. Desktop dua
kolom, mobile bertumpuk tanpa overflow; deskripsi panjang membungkus dan baris
baru tetap. Mode mengikuti kegiatan/khusus, seluruh slot WIB dan persyaratan
wajib/preferensi/dokumen tetap lengkap. Edit/hapus tetap sesuai status/hak akses.

## Pemilih file langsung pada sampul EO - 14 November 2026

Klik Ubah/Tambah gambar di bawah cover: pemilih file terbuka tanpa pindah halaman.
File valid menjadi preview; Simpan menyimpan sampul saja, Batal mengembalikan
preview awal tanpa mengubah database. Uji batal dialog, file invalid/lebih5MB,
error upload, menyimpan/disabled, owner lain dan status terkunci. Judul/jadwal/
status/paket tetap setelah upload. Published tidak mendapat form dan request
langsung ditolak. Mobile kontrol tidak menimpa gambar. Ini menggantikan UAT
sebelumnya yang mengharapkan tombol menuju input di form edit.

## Tombol Edit dan Ubah gambar EO - 14 November 2026

Periksa Edit outline biru sedikit lebih besar dengan pensil. Pada detail editable,
Ubah gambar pada sampul atau Tambah gambar jika kosong menuju input foto di form
edit. Pilih gambar baru, periksa preview, simpan dan periksa sampul berubah.
Coba hapus, file invalid/lebih5MB, owner lain dan published: aturan upload/
editable existing tetap. Cek mobile overlay tidak terpotong. Published tetap
hanya koreksi teks dan koordinasi sesuai aturan, tanpa tombol ganti gambar.

## Keterbacaan kartu Informasi kegiatan EO - 14 November 2026

Periksa bar tiga status tidak tampil pada kartu Ringkasan detail; status daftar
masih tersedia. Ikon pada enam label metadata, judul/label/isi berbeda ukuran,
tanggal di atas jam WIB, rentang mulai-sampai-akhir lengkap termasuk lintas hari.
Periksa deskripsi/lokasi panjang, jadwal kosong, nilai tanggal/jam sama seperti
data existing, 390/768/1440px tanpa overflow. Edit tetap mengikuti hak/status,
catatan admin dan seluruh bagian/form existing masih tersedia.

## Navigasi bagian detail EO - 14 November 2026

Pada detail draf/pending/published, periksa enam tautan anchor mengarah ke bagian
yang benar, highlight biru mengikuti klik dan scroll, serta bagian tidak tertutup
panel sticky. Mobile dapat menggeser deretan tombol; dokumen tidak melebar.
Periksa fokus keyboard, hash URL, drawer dan Kembali merah. Semua konten/form
masih tersedia tanpa memilih tab; tanpa JS tautan anchor tetap bekerja.

## Penempatan akun dan Keluar EO - 14 November 2026

Periksa nama/inisial akun aktif dan Organizer di atas Ringkasan Aktivitas,
terpisah garis/jarak seperti pemisah setelah Profil Organisasi. Keluar berada
setelah Lihat situs utama dengan highlight merah. Bar akun desktop hilang,
mobile tetap menyediakan buka/tutup drawer, Escape dan backdrop. Profil tetap
dapat dibuka melalui menu. Keluar harus POST dengan CSRF, mengakhiri sesi dan
kembali ke login; GET tidak boleh mengeluarkan akun. Cek nama panjang dan
scroll sidebar pada layar pendek. Layout role lain tetap.

## Header dan tombol daftar EO - 14 November 2026

Pada Kegiatan Saya, baris Semua kegiatan dan jumlah tidak ditampilkan lagi.
Tombol Buat Kegiatan lebih besar dan memiliki ikon plus; klik tetap membuka
modal existing. Periksa desktop/mobile dan filter; ketentuan pemeriksaan badge
jumlah hasil pada catatan sebelumnya tidak berlaku untuk tampilan ini.

## Pencarian dan filter moderasi EO - 14 November 2026

Cari sebagian nama, gabungkan dengan setiap status moderasi, pindah halaman,
lalu Reset. Hanya kegiatan pemilik muncul; jumlah dan pagination mengikuti
filter. Hasil kosong berbeda dari belum ada kegiatan. %, _ dan ! menjadi teks
literal. Periksa label, fokus keyboard, 390/768/1440 px dan modal Buat Kegiatan.

## Ikon dan hierarki metadata Kegiatan Saya - 14 November 2026

Periksa judul lebih menonjol dari tanggal/jam/lokasi, ikon kalender/jam/pin,
serta tanggal/jam WIB yang sama dengan data existing. Tanggal/jam bisa membungkus,
lokasi panjang tidak overflow, jadwal kosong berlabel, ikon dekoratif aria-hidden
serta label pembaca layar tersedia. Badge/thumbnail/aksi/modal tetap. Periksa
390/768/1440 px, termasuk judul/lokasi panjang.

## Menu mandiri pelaksanaan EO - 14 November 2026

Periksa sidebar Pelaksanaan & Kehadiran setelah Kelola Pelamar. Daftar hanya
kegiatan aktor, jumlah hanya accepted, jadwal WIB, status aktual, kosong dan
pagination. Kelola pelaksanaan menuju endpoint existing; Kembali ke daftar
pelaksanaan mengarah hub baru. Detail event tanpa kartu navigasi pelaksanaan.
Uji guest/non-EO/pending/nonaktif dan request pemilik asing; pastikan aturan
pencatatan/sesi/completion existing tetap. Periksa 390/768/1440 dan drawer.

## Navigasi atas detail kegiatan - 14 November 2026

Periksa topbar Kegiatan Saya pada detail; judul konten/tab tetap nama kegiatan.
Kembali berupa tombol biru tanpa panah dan menuju daftar. Kartu navigasi atas
hanya menyisakan Pelaksanaan dan kehadiran. Menu Kelola Pelamar dan endpoint
existing tetap tersedia; halaman EO lain tetap memakai judul topbar masing-masing.

## Pemulihan ukuran badge status - 14 November 2026

Periksa badge status daftar kegiatan kembali 11 px/padding 4x8 px/radius 6 px.
Warna hijau/biru/kuning dan label tetap, membungkus tanpa overflow pada mobile.

## Tombol kelola sekunder - 14 November 2026

Periksa Kelola kegiatan putih/border biru tipis/teks biru, hover biru muda, lebih
kecil dari Buat Kegiatan biru solid. Tautan detail, fokus keyboard dan target
mobile tetap. Badge moderasi hijau, publikasi biru, pelaksanaan kuning.

## Koreksi badge publikasi EO - 14 November 2026

Periksa daftar Kegiatan Saya: moderasi hijau, publikasi biru dan pelaksanaan
kuning, teks status tetap lengkap. Tombol Kelola belum berubah pada koreksi ini.

## Badge dan tombol daftar kegiatan - 14 November 2026

Periksa jumlah kegiatan sebagai badge kecil, Kelola kegiatan biru lebih kecil
dari Buat Kegiatan dan tanpa panah. Tiga badge kecil tetap terbaca: Moderasi/
Publikasi hijau, Pelaksanaan kuning, label status tidak berubah. Periksa draf,
published, rejected/cancelled, kosong, pagination, modal buat dan 390/768/1440.
Pastikan tautan kelola tetap menuju detail event yang benar; konteks/status tidak
hilang hanya demi ukuran. Halaman role lain dan detail event tetap.

## Logo EO sama dengan landing - 14 November 2026

Periksa sidebar desktop/drawer mobile memakai simbol empat kelopak biru seperti
landing page menggantikan S. Teks SKILLMATCH/Panel Organisasi dan link Ringkasan
existing tetap. Periksa landing/footer/auth masih menampilkan simbol beserta teks.

## Ukuran judul kelompok tindak lanjut - 14 November 2026

Periksa judul kelompok 16 px, nama kegiatan 14 px, judul utama 18 px. Judul tetap
hitam lembut; badge kecil dan titik warna tetap. Periksa pembungkusan di mobile.

## Kartu putih dan titik penanda tindak lanjut - 14 November 2026

Periksa kepala kartu putih dengan titik biru/hijau dekoratif dan judul gelap.
Jumlah tindakan berada di samping judul masing-masing kartu (sesuai collection,
bukan total database); badge gabungan tidak tampil. Tidak ada garis bawah judul
atau baris pertama; garis hanya di antara kegiatan. Periksa kosong dan terisi,
judul membungkus, tombol/fokus dan desktop/mobile. Statistik serta route tetap.

## Kepala kedua kartu kuning penuh - 14 November 2026

Periksa Persiapan dan rekrutmen serta Pelaksanaan: kepala kartu kuning sangat muda dari
tepi kiri ke kanan dan menempel bagian atas, sudut mengikuti kartu, teks keemasan.
Tidak ada jarak putih di sekitar kepala kartu. Isi putih dan tombol biru tetap.
Periksa desktop/mobile, judul membungkus, isi terisi/kosong dan fokus keyboard.

## Baris judul persiapan/pelaksanaan - 14 November 2026

Periksa baris judul Persiapan dan rekrutmen biru sangat muda/teks biru dan
Pelaksanaan kuning sangat muda/teks keemasan. Isi kartu tetap putih; judul panjang
membungkus dan tampilan terisi/kosong tetap terbaca pada desktop/mobile.

## Aksen statistik lebih jelas - 14 November 2026

Periksa border biru/hijau lebih tegas, kuning lebih cerah dan judul Lamaran
Belum Ditinjau kuning keemasan, ikon selaras. Angka hitam, layout serta ungu tetap.

## Dua kartu tindak lanjut dan aksen statistik - 14 November 2026

Periksa judul statistik biru/hijau/ungu dan border ungu lebih jelas. Perlu
Ditindaklanjuti berada di luar dua kartu putih terpisah; kartu persiapan/rekrutmen
dan pelaksanaan tetap muncul dengan keadaan kosong masing-masing. Tombol biru
lebih kecil tanpa panah, target mobile 44 px, tautan tujuan tetap. Periksa kondisi
terisi/kosong/pending/revisi/approved pada 390/768/1440 px dan fokus keyboard.

## Panel tindak lanjut baris ringkas - 14 November 2026

Periksa satu judul Perlu Ditindaklanjuti dan jumlah tindakan yang ditampilkan,
kelompok persiapan/rekrutmen serta pelaksanaan, nama kegiatan terpisah dari
keterangan, dan tombol kanan desktop/bawah mobile. Uji draf, pending moderasi,
revisi, approved belum publikasi, seleksi lewat tenggat dan pelaksanaan. Aksi
menuju detail/pelamar/pelaksanaan milik EO; tidak ada aksi mutasi langsung.
Periksa kosong satu/kedua kelompok, judul panjang, 390/768/1440 px. Identitas
organisasi tidak lagi ditampilkan di panel ini. Query/angka/status backend tetap.

## Warna statistik EO - 14 November 2026

Periksa border Total Kegiatan biru, Kegiatan Aktif hijau, Total Pelamar ungu
beserta ikon ungu, serta Lamaran Belum Ditinjau amber. Latar kartu tetap putih,
angka dan susunan sama. Prioritas Anda belum diubah pada permintaan warna ini.

## Koreksi Ringkasan dan Kelola Pelamar - 14 November 2026

Periksa Ringkasan EO: Pelamar Terbaru, panel Kegiatan Saya dan tombol Buat Kegiatan
tidak tampil; statistik mendahului Prioritas Anda, notifikasi tetap ada. Sidebar
Kegiatan Saya tetap ada dan menu Kelola Pelamar menuju kegiatan milik EO saja.
Periksa jumlah lamaran mengecualikan draf, keadaan tanpa kegiatan, pagination dan
tautan kandidat existing. Parameter organizer_id asing tidak boleh memperluas
hasil; endpoint kandidat kegiatan asing tetap 403. Guest, non-EO, akun nonaktif
dan organisasi pending mengikuti pembatas existing. Periksa tampilan 390/768/1440.
Bukti aktual: [handoff EO](../../docs/ORGANIZER_UI_REFINEMENT_HANDOFF.md).

## Panel EO ringkas - 14 November 2026

Periksa Ringkasan Aktivitas, Kegiatan Saya, detail kegiatan dan halaman pendukung
pada 390/768/1440 px. Sidebar 256 px, header/konten sejajar, drawer dapat dibuka,
ditutup dan ditutup dengan Escape. Tindak lanjut tampil sebelum angka statistik,
termasuk keadaan kosong. Daftar kegiatan memakai thumbnail kecil; judul panjang,
lokasi, status, pagination dan aksi tetap terbaca tanpa overflow.

Moderasi/publikasi/pelaksanaan tetap tiga konteks berbeda dengan label Indonesia;
disetujui tidak berarti dipublikasikan. Semua tautan bagian detail menuju konten
existing yang tetap terbuka. Form koordinasi di bawah asesmen menyimpan melalui
endpoint existing. Periksa modal Buat Kegiatan, unggah cover, form posisi dan
asesmen, pengajuan/publikasi, paket/order, pelamar/kehadiran dan profil pending.
Akses langsung lintas role/pemilik harus tetap ditolak. Periksa pula role lain
karena stylesheet aplikasi dibangun bersama. Hasil nyata dan bagian belum diuji
tercantum pada [handoff EO](../../docs/ORGANIZER_UI_REFINEMENT_HANDOFF.md).

## Usia otomatis dan profil ringkas — 14 November 2026

Ubah tanggal lahir: usia ikut berubah sesuai hari WIB. Periksa ulang tahun,
kabisat, kosong/invalid/masa depan. Usia tidak editable atau tersimpan terpisah.
Periksa form/repeater di desktop/mobile dan POST existing.
Bukti/batas: [handoff usia](../../docs/VOLUNTEER_PROFILE_AGE_HANDOFF.md).

## Panel penarikan ringkas — 14 November 2026

Periksa summary dapat dilipat, peringatan final, alasan opsional dan tombol merah.
POST/CSRF/otorisasi/aturan final tetap. Periksa 1440/768/390 dan pesan error.
Bukti/pemulihan: [handoff panel](../../docs/VOLUNTEER_WITHDRAWAL_REFINE_HANDOFF.md).

## Detail lamaran ringkas — 14 November 2026

Periksa 1440/768/390: banner, timeline, rincian, koordinasi/kehadiran dan jadwal
lebih padat. Dokumen terisi/kosong/purged, aksi desktop kanan/mobile bertumpuk.
Status dan akses tetap. Bukti/batas: [handoff detail ringkas](../../docs/VOLUNTEER_APPLICATION_DETAIL_COMPACT_HANDOFF.md).

## Riwayat Partisipasi ringkas — 14 November 2026

Periksa padding/heading/badge, tautan judul kegiatan dan hasil cancelled/hadir/absent/
unrecorded serta rincian sesi/catatan lama pada 1440/768/390.
Bukti/batas: [handoff riwayat ringkas](../../docs/VOLUNTEER_HISTORY_COMPACT_HANDOFF.md).

## Dashboard ringkas — 14 November 2026

Periksa 1440/768/390: sambutan, statistik, kesiapan profil, skill, ketersediaan,
kegiatan/tindakan/notifikasi tetap tersedia dengan padding lebih padat.
Status Indonesia dan warna gagal sesuai lamaran. Angka lamaran tetap aktual.
Bukti/batas/pemulihan: [handoff dashboard](../../docs/VOLUNTEER_DASHBOARD_COMPACT_HANDOFF.md).

## Sidebar Volunteer ringkas — 14 November 2026

Periksa lebar desktop 256 px dan offset navbar/konten sejajar. Menu/nama/email
tetap terbaca. Pada mobile periksa drawer, overlay dan tutup/buka.
Bukti/batas: [handoff sidebar](../../docs/VOLUNTEER_SIDEBAR_COMPACT_HANDOFF.md).

## Kepadatan kartu Lamaran Saya — 14 November 2026

Periksa zoom 100% pada 1440/768/390: kartu lebih ringkas, badge sesuai teks,
tanggal/tombol tetap terbaca dan tidak overflow. Periksa draft/kosong/pagination.
Bukti/batas: [handoff compact](../../docs/VOLUNTEER_APPLICATIONS_COMPACT_HANDOFF.md).

## Desain panel Admin — 12 November 2026

Periksa tampilan sesuai halaman FigCraft `Electric Blue / 01 Admin`: sidebar
navy/menu aktif abu-abu, header putih, aksen biru, kartu tanpa bayangan, badge
berlabel dan form/modal membulat. Dashboard mempertahankan angka/data asli,
filter periode serta perpindahan seri grafik. Nama posisi harus tampil dari
field `name` existing pada halaman pemeriksaan event.

Periksa filter akun pada layar kecil, geser tabel di dalam pembungkusnya,
drawer dan submenu, modal View/Edit/Delete akun, modal master/paket, dan modal
penolakan event. Jangan mengirim aksi destruktif pada akun/data kerja untuk demo.
Semua form/method/CSRF, batas akses, moderasi, dokumen privat, status pembayaran,
pagination dan snapshot existing tetap berlaku. Tampilan Organizer/Volunteer
harus tetap sama. Bukti terarah dan batas pengujian:
[handoff Admin](../../docs/ADMIN_FIGCRAFT_DESIGN_HANDOFF.md).

## Desain panel Organizer — 12 November 2026

Lingkup disetujui pengguna: tampilan Organizer mengikuti 33 referensi layar pada
halaman FigCraft `Electric Blue / 02 Organizer`. Periksa sidebar navy, menu aktif
biru, header/kartu putih, form dan tombol normal case, ringkasan event/posisi/paket,
serta pembayaran. Pastikan halaman pending/revisi tetap tersedia, state kosong/
error/loading tidak disembunyikan, tanggal WIB dan data asli dipertahankan.
Pada mobile periksa drawer, form, ringkasan satu kolom dan tabel yang dapat digeser.
Modal buat event/profil existing tetap tersedia; tidak mengganti alurnya dengan
halaman statis dari desain. Periksa regresi Admin, Volunteer dan publik.

Bukti tes dan visual terarah: [handoff Organizer](../../docs/ORGANIZER_FIGCRAFT_DESIGN_HANDOFF.md).
Pemeriksaan fixture bukan tanda seluruh UAT bisnis atau pembayaran Sandbox
end-to-end lulus; kriteria bisnis existing tetap berlaku.

## Desain Semua notifikasi Volunteer — 11 November 2026

Dari Aktivitas buka Semua notifikasi. Periksa shell navy/biru, konten, kosong,
read/unread, tanggal WIB, detail/baca dan pagination pada 1440/768/390.
Akses pemilik dan channel tetap dibatasi. Role lain tetap desain existing.
Bukti/batas: [handoff notifikasi](../../docs/VOLUNTEER_NOTIFICATIONS_DESIGN_HANDOFF.md).

## Detail event publik konsisten dengan panel — 11 November 2026

Periksa guest dan akun aktif pada /events/{event}: konten seperti panel, navbar/
footer publik, tautan kembali katalog publik, CTA sesuai role/verifikasi/waktu.
Periksa desktop/mobile dan panel tetap bersidebar. Bukti/batas:
[handoff publik](../../docs/PUBLIC_EVENT_DETAIL_DESIGN_HANDOFF.md).

## Perapian detail event panel — 11 November 2026

Periksa 1440/768/390: header, dua kolom/satu kolom mobile, skill/kuota, jadwal,
persyaratan dan tombol biru. Data tetap dinamis; kuota bukan sisa tempat.
Periksa kondisi buka/tutup/limit/email dan POST lamaran existing. View publik
tetap. Bukti/batas: [handoff detail event](../../docs/VOLUNTEER_EVENT_DETAIL_DESIGN_HANDOFF.md).

## Navigasi detail event panel — 11 November 2026

Klik gambar/judul/Lihat posisi dan persyaratan dari /volunteer/events: menuju
/volunteer/events/{event}, sidebar tetap, kembali ke katalog panel. Periksa
posisi/persyaratan dan alur lamaran existing. Event unpublished harus 404.
Tes akses/link/view lulus; visual/klik browser detail belum diuji.

## Katalog dalam panel Volunteer — 11 November 2026

Menu Jelajahi Event menuju /volunteer/events; sidebar tetap terlihat. Cari/reset
dan pagination tetap di route panel. Guest ke login, Organizer ditolak; /events
tetap katalog publik. Detail event masih halaman existing. Tes akses/view lulus;
klik dan visual browser belum diperiksa. Lihat handoff navigasi terbaru.

## Navigasi katalog dari panel Volunteer — 11 November 2026

Login Volunteer, buka sidebar (mobile: tombol menu), pilih Jelajahi Event.
Pastikan menuju /events dalam tab yang sama. Lihat Website Utama tetap tersedia.
Pemeriksaan klik browser belum dijalankan; validasi route/build pada
[handoff navigasi](../../docs/VOLUNTEER_EXPLORE_EVENT_HANDOFF.md).

## Pemeriksaan desain Kegiatan dibatalkan — 11 November 2026

Bandingkan cancelled dengan SVG pada 1440/768/390: banner netral, kartu status,
tanggal/alasan, timeline, rincian dan dokumen. Periksa alasan kosong/markup,
attempt cancelled/submitted dan dokumen ready. Tidak ada aksi mulai assessment;
koordinasi peserta tetap tidak tersedia ketika event cancelled.
Bukti/batas: [handoff pembatalan](../../docs/VOLUNTEER_CANCELLED_DESIGN_HANDOFF.md).

## Pemeriksaan desain Lamaran ditarik — 11 November 2026

Bandingkan withdrawn dengan SVG pada 1440/768/390: banner abu-abu, kartu status,
alasan/tanggal, timeline, rincian dan dokumen. Periksa alasan kosong/panjang/
markup, assessment kosong/cancelled/submitted dan event cancelled. Tidak ada
submit/upload/hapus/assessment/tarik ulang; alasan markup harus berupa teks.
Bukti/batas: [handoff ditarik](../../docs/VOLUNTEER_WITHDRAWN_DESIGN_HANDOFF.md).

## Pemeriksaan desain Riwayat Partisipasi — 11 November 2026

Bandingkan Riwayat Partisipasi.svg pada 1440/768/390: shell, badge, judul,
kotak waktu WIB/hasil, catatan lama dan sesi. Periksa kosong, hadir, absent,
unrecorded, sesi campuran, cancelled dan bukan accepted. Catatan lama tetap
terlihat, judul menuju detail dan pagination existing tersedia.
Bukti/batas: [handoff riwayat](../../docs/VOLUNTEER_HISTORY_DESIGN_HANDOFF.md).

## Pemeriksaan penarikan pada detail — 11 November 2026

Bandingkan under_review dengan Penarikan pada detail lamaran.svg pada
1440/768/390: kartu, rincian terbuka, kolom alasan dan tombol merah.
Pastikan panel dapat dilipat, data nyata, dokumen hanya-baca dan tidak overflow.
Penarikan tetap final melalui endpoint existing; uji hanya pada data tes.
Bukti/batas: [handoff penarikan](../../docs/VOLUNTEER_WITHDRAWAL_DESIGN_HANDOFF.md).

## Pemeriksaan desain Lamaran Diterima — 9 November 2026

Bandingkan accepted dengan `Lamaran diterima.svg`: banner hijau, timeline,
assessment terkirim, koordinasi grup/PIC, kehadiran per sesi, jadwal/posisi dan
dokumen. Periksa 1440/768/390, grup kosong, beberapa sesi/status, dokumen dan
catatan. Belum dicatat berbeda dengan hadir/tidak hadir. Tidak ada aksi edit
lamaran/assessment ulang. Akses koordinasi tetap untuk accepted pemilik sah;
event dibatalkan menampilkan pembatalan, bukan banner diterima hijau.
Bukti aktual/batas uji: [handoff diterima](../../docs/VOLUNTEER_ACCEPTED_DESIGN_HANDOFF.md).
## Pemeriksaan Assessment Gagal Simpan — 9 November 2026

Simulasikan koneksi gagal pada preview/test: pesan sesuai `Assessment · gagal
simpan.svg`, jawaban radio tetap dipilih, revision tidak naik, tombol simpan
kembali aktif dan timer tidak direset. Pulihkan transport lalu simpan: respons
sukses memperbarui revision/status tanpa menghilangkan pilihan. Periksa
1440/768/390 dan pastikan footer mobile tidak menutupi navigasi. Jangan memutus
atau memalsukan endpoint produksi untuk demo. Bukti aktual dan batas simulasi:
[handoff gagal simpan](../../docs/VOLUNTEER_ASSESSMENT_SAVE_FAILURE_HANDOFF.md).
## Pemeriksaan desain Kerjakan Assessment — 9 November 2026

Bandingkan attempt dengan `Kerjakan assessment.svg` pada 1440/768/390: sidebar
navy, timer/status, soal/radio, nomor soal/legend, sebelumnya/berikutnya dan
simpan/kirim. Periksa jawaban tetap dipilih saat berpindah, hitungan terjawab,
nomor aktif/terjawab, fokus keyboard, daftar soal mobile, status simpan/gagal,
konfirmasi kirim, expiry dan pemulihan setelah reload. Server tetap menentukan
waktu/status/penilaian dan tidak mengirim kunci jawaban ke peserta.
Bukti aktual (bedakan mock browser dan backend) pada
[handoff pengerjaan](../../docs/VOLUNTEER_ASSESSMENT_WORK_DESIGN_HANDOFF.md).
## Pemeriksaan desain Sebelum Assessment — 9 November 2026

Bandingkan konfirmasi GET assessment dengan `Sebelum assessment.svg`: shell navy,
kartu tengah, jumlah soal/durasi nyata, satu kesempatan, batas selesai WIB,
ketentuan bernomor dan tombol kembali/mulai. Periksa 1440/768/390, teks panjang,
error dan navigasi keyboard. GET/kembali tidak memulai attempt. POST terkonfirmasi
memulai atau memulihkan attempt yang sama; hak akses/tenggat/status tetap dicek
server. Bukti aktual dan batas uji: [handoff konfirmasi](../../docs/VOLUNTEER_ASSESSMENT_CONFIRM_DESIGN_HANDOFF.md).
## Regresi sidebar detail lamaran — 9 November 2026

Buka Lihat Detail untuk draft, submitted, screening_failed, assessment_pending,
under_review, accepted, rejected, withdrawn dan cancelled. Sidebar harus navy
#0F172A, menu Lamaran Saya aktif/ikon avatar biru #2563EB di desktop dan mobile.
Warna shell tidak bergantung pada status atau tersedianya desain konten.
Aturan assessment, keputusan dan dokumen tetap existing. Bukti aktual pada
[handoff koreksi sidebar](../../docs/VOLUNTEER_DETAIL_SIDEBAR_FIX.md).
## Pemeriksaan desain Rejected — 9 November 2026

Bandingkan rejected dengan `Lamaran · Rejected.svg`: banner, timeline keputusan,
panel hasil seleksi dengan waktu WIB dan catatan, screening/assessment terbuka,
jadwal posisi dan dokumen. Periksa 1440/768/390, catatan kosong/panjang/berisi tag,
metadata screening/assessment tidak tersedia dan dokumen kosong/terisi.
Catatan harus escaped; tidak ada aksi draft/penarikan/mulai assessment. Keputusan
historis tidak berubah mengikuti contoh desain. Bukti aktual dan batas uji:
[handoff Rejected](../../docs/VOLUNTEER_REJECTED_DESIGN_HANDOFF.md).
## Pemeriksaan desain Screening Gagal — 9 November 2026

Bandingkan screening_failed dengan `Lamaran · Screening gagal.svg`: banner rose,
penanda gagal, alasan screening terbuka, assessment tidak tersedia, panel status,
jadwal posisi dan dokumen. Periksa 1440/768/390, alasan panjang/beberapa alasan,
hasil belum tersedia dan dokumen kosong/terisi. Rincian tetap dapat dilipat.
Tidak ada hasil/alasan palsu atau aksi upload/hapus/kirim ulang/penarikan; endpoint
assessment tetap menolak status gagal. Bukti aktual dan batas pemeriksaan:
[handoff Screening Gagal](../../docs/VOLUNTEER_SCREENING_FAILED_DESIGN_HANDOFF.md).
## Pemeriksaan desain Submitted — 9 November 2026

Bandingkan status submitted dengan `Lamaran · Submitted.svg`, termasuk banner,
timeline, rincian screening/assessment terbuka, pengumuman/jadwal/posisi, dokumen
kosong/terisi, dan panel penarikan terbuka dengan tombol merah. Periksa viewport
1440/768/390, rincian dapat dilipat, tenggat WIB, hasil screening belum tersedia,
serta assessment tanpa metadata. Tidak boleh menampilkan lolos/angka palsu.
Sesudah submit tidak ada upload/hapus/kirim ulang; unduh tetap terotorisasi.
Penarikan tetap mengikuti izin, status, batas waktu, dan aturan final existing.
Bukti aktual: [handoff Submitted](../../docs/VOLUNTEER_APPLICATION_SUBMITTED_DESIGN_HANDOFF.md).
## Pemeriksaan desain Lengkapi Lamaran — 9 November 2026

Bandingkan detail lamaran draft dengan `Lengkapi lamaran.svg`: banner, kirim,
perkembangan, daftar dokumen, form unggah satu kolom dan panel informasi kanan.
Periksa viewport 1440/768/390, dokumen kosong/terisi, profil belum lengkap,
flash/error, nama berkas, Unduh/Hapus, pilihan CV/pendukung, serta batas unggah
existing. Dokumen tetap privat dan hanya pemilik sah memperoleh akses. Kirim,
upload dan hapus tetap memerlukan pemeriksaan server/verifikasi existing.
Pastikan status setelah submit, assessment, hasil seleksi dan kehadiran tetap
memakai alur existing. Hasil aktual/batas uji pada
[handoff draft](../../docs/VOLUNTEER_APPLICATION_DRAFT_DESIGN_HANDOFF.md).
## Pemeriksaan desain Lamaran Saya — 9 November 2026

Referensi `layout terukur.svg`, route `/volunteer/applications`. Bandingkan
1440 px dengan SVG, lalu periksa 768/390 px: judul/posisi, badge status kanan
atas, lokasi, waktu dibuat/dikirim WIB, dan Lihat Detail. Daftar kosong tetap
informatif; draft tidak memiliki tanggal kirim; pagination menampilkan halaman
selanjutnya. Link detail milik sendiri berfungsi dan akses akun lain ditolak.
Badge mengikuti status existing, bukan contoh statis SVG. Hasil aktual dan batas
uji: [handoff daftar lamaran](../../docs/VOLUNTEER_APPLICATIONS_DESIGN_HANDOFF.md).
## Pemeriksaan desain Profil Volunteer — 9 November 2026

Referensi disetujui: `Profil Volunteer.svg`; route `/volunteer/profile`.
Bandingkan desktop 1440 px dengan SVG dan periksa responsif 768/390 px.
Pastikan menu Profil & Skill aktif, pengingat email kondisional, identitas dan
nilai tersimpan tampil, error/old input dipertahankan, dan tambah/hapus skill
serta interval menghasilkan nama input unik. Periksa label WIB, pembatasan nomor
telepon, batas bio/tanggal lahir, tombol simpan dan keadaan menyimpan. Simpan
hanya data akun sendiri; endpoint lintas role tetap ditolak. Bukti aktual dan
batas pemeriksaan pada [handoff profil](../../docs/VOLUNTEER_PROFILE_DESIGN_HANDOFF.md),
bukan klaim seluruh UAT aplikasi lulus.
## Pemeriksaan desain Ringkasan Aktivitas Volunteer — 9 November 2026

Desain terbaru yang disetujui pengguna: `Ringkasan Aktivitas.svg` dari FigCraft.
Lingkup `/volunteer/aktivitas`, lokal tanpa commit/push. Skenario tambahan:

- Bandingkan desktop 1440 px: sidebar 288 px/navy, navbar 64 px, kartu putih,
  aksen biru, tiga statistik, kolom kelengkapan profil/skill, dan urutan seksi SVG.
- Periksa 1024, 768 dan 390 px: kartu menyusun ulang, teks tidak keluar layar,
  drawer mobile dapat dibuka, ditutup, dan ditutup melalui Escape.
- Periksa menu akun, tautan profil/lamaran/riwayat/website, assessment dengan
  deadline aktif/kedaluwarsa, jadwal accepted dan notifikasi milik akun sendiri.
- Periksa profil lengkap/belum lengkap, daftar kosong/terisi, pagination dan
  pengingat verifikasi. Data contoh SVG tidak menjadi data aplikasi.

Hasil aktual dan keterbatasan dicatat pada
[handoff desain](../../docs/VOLUNTEER_ACTIVITY_DESIGN_HANDOFF.md);
penambahan ini bukan klaim seluruh UAT aplikasi sudah lulus.
# UAT dan kriteria selesai lintas anggota

## Kehadiran per sesi/hari - keputusan pengguna 3 November 2026

Organizer mencatat setiap sesi/hari dari snapshot jadwal tugas peserta accepted.
Interval melewati tengah malam dipisah menurut tanggal WIB. Completion menunggu
seluruh sesi selesai dan tercatat; partisipasi berhasil jika semua sesi hadir.
Absensi lama tetap berlabel per kegiatan, tidak disalin sebagai hadir setiap hari.
Konversi legacy pada event nonterminal meminta konfirmasi/alasan dan menyimpan arsip.
Halaman, filter, rekap dan route lama dipertahankan dengan kompatibilitas terbatas
untuk satu sesi; beberapa sesi harus dicatat melalui endpoint per sesi.
Schema, aturan, endpoint, demo dan bukti tes: [handoff sesi](../../docs/SESSION_ATTENDANCE_HANDOFF.md).
Ketentuan lama yang membatasi attendance satu catatan per event digantikan keputusan ini.


## Perbaikan alur terintegrasi - 3 November 2026

Keputusan terbaru dan bukti implementasi ada pada [handoff perbaikan alur](../../docs/INTEGRATED_FLOWS_HANDOFF.md).
Penghapusan akun tanpa data terkait tetap tersedia; akun bersejarah dilindungi.
Revisi identitas organisasi memerlukan konfirmasi dan review ulang, penolakan
bercatatan dapat diajukan ulang. Event lama dapat melengkapi tenggat kosong sekali
melalui aksi owner yang diaudit; snapshot existing tidak diubah, hanya metadata
tenggat kosong yang dapat membaca perbaikan eksplisit. Aktivitas dan notifikasi
website memakai layanan nyata serta outbox existing yang dibedakan dari email.
Layout, modal dan fitur existing dipertahankan. Catatan tahap sebelumnya di bawah
bersifat historis bila menyebut layanan tersebut belum tersedia.


Pemeriksaan pelestarian hasil kerja pada setiap perubahan: catat lingkup yang
disetujui, branch/commit dasar dan halaman/alur terdampak. Bandingkan hasil dengan
implementasi sebelum perubahan; pastikan fitur, navigasi dan tampilan di luar
lingkup tetap tersedia, termasuk halaman konsumen komponen bersama. Untuk
perubahan visual, catat pemeriksaan desktop/mobile serta interaksi yang relevan;
build sukses tidak otomatis meluluskan pemeriksaan visual. Penggantian di luar
lingkup harus memperoleh persetujuan terlebih dahulu sesuai AGENTS.md. Status
review, tes dan persetujuan dicatat terpisah, tidak dianggap selesai lewat dokumen ini.

Regresi alur email Volunteer (28 Oktober 2026, UAT-01/02/09/34): daftar Volunteer
mengirim tautan dan menuju verifikasi; akun lama dapat login dan kirim ulang tanpa
mendaftar lagi. Profil tetap dapat diisi. Melamar/submit/upload/hapus dokumen sebelum
verified mengarah ke verifikasi tanpa perubahan domain. Tautan valid membuka akses;
ID/hash palsu, expired, lintas akun dan akun nonaktif ditolak. Intended eksternal
atau lintas role dibuang; POST tidak dijalankan ulang setelah verifikasi.
Uji gagal SMTP lalu retry tanpa kehilangan akun, throttle kirim ulang, serta
Organizer/Admin tetap mengikuti alur existing. Mailer log diuji terpisah dari
pengiriman nyata SMTP/inbox. Bukti otomatis dan batas manual ada di DEVELOPMENT.

Regresi auth seleksi A3: Organizer approved/akun aktif dengan email belum verified
harus dapat melakukan seleksi/pelaksanaan miliknya sesuai alur Organizer existing,
tanpa diarahkan melalui verification.notice ke dashboard. Organizer pending/nonaktif
dan owner lain tetap ditolak. Email_verified_at tidak boleh diisi otomatis untuk
meluluskan seleksi. Bukti 21 tes/156 assertion di A3_HANDOFF aplikasi.

Regresi Terima Pelamar 28 Oktober 2026 (UAT-21/22/34): dari detail kandidat dengan
referer dashboard, sukses selalu menuju detail kandidat dengan accepted dan attendance
unrecorded; kuota penuh menuju detail yang sama dengan alasan jelas dan tanpa attendance.
Klik browser Alpine harus mengirim native POST+CSRF; disabled/loading tidak menahan
pengiriman. Bukti tes/probe browser dan batas verifikasi ada di A3_HANDOFF aplikasi.

## Bukti terarah A3 — 28 Oktober 2026

Status pusat di tabel tetap belum berarti UAT tim lulus. Implementasi dan bukti terbaru
ada di docs/A3_HANDOFF.md aplikasi, branch feature/a3-applications-attendance dari
34ae483. Tes otomatis memetakan UAT-11–16/19/21/22/27/28/31/32/33; build dan browser
terbatas memetakan UAT-34. Dua proses MySQL skillmatch_testing membuktikan kuota satu,
konflik Volunteer lintas event, counter submit, accept idempoten serta cancellation
versus accept/submit/attempt. Recovery restore ledger/checksum diuji dengan fixture,
bukan restore backup operasional penuh. Notifikasi A4 nyata, setup anggota lain,
browser submit end-to-end, p95 dan review tim masih belum diuji.

Tambahan kasus A3: tepat decision_deadline menolak keputusan baru, replay keputusan
final tidak mengubah alasan/audit; accepted withdrawal hanya Organizer sebelum awal;
stale revision attendance ditolak; koreksi completed tetap ditolak walau beralasan; riwayat sumber tidak berubah;
file purged tidak aktif sesudah restore, missing kembali ready hanya checksum sama;
orphan rollback tidak dibersihkan sebelum masa aman 24 jam; dokumen organisasi tidak
terkena retensi lamaran. Cleanup publik harus opt-in sesudah kebijakan disetujui.

## Matching — 27 Oktober 2026

UAT-18/19/32: contoh S=75,A=75,L=100 → 80,00; level wajib/preferensi sama-sama dihitung,
tidak ada bonus level di atas minimum; union interval termasuk overlap/adjacency, batas
setengah terbuka dan offset timezone; kota berbeda=0. Data invalid ditunda, bukan skor
palsu. Hasil immutable dari snapshot, replay tidak menggandakan skor/status/audit; terminal
dan cancelled/completed tidak diaktifkan. Lolos screening memicu matching sesudah commit,
assessment_pending hanya setelah skor tersimpan. EO owner mendapat skor/rincian, Volunteer
hanya status/pesan dan tidak menerima angka. Uji akses endpoint lintas akun dan race dua
koneksi pada MySQL tes terpisah. Bukti unit/rendering bukan bukti UAT transaksi/browser;
status verifikasi aktual pada docs/A4_MATCHING_HANDOFF.md aplikasi.

Bukti 27 Oktober 2026: suite matching/screening/regresi A3/unit terkait lulus 70 tes/
341 assertion pada MySQL skillmatch_testing. Dua proses independen matching ganda dan
screening → matching ganda lulus melalui tools/a4-matching-concurrency-check.php.
Review browser lintas ukuran dan race pembatalan lintas layanan A3 tetap belum diuji;
bukti ini tidak meluluskan seluruh UAT aplikasi.

## Screening bertahap — 11 Oktober 2026

Untuk UAT-17/19/32: uji syarat wajib profil/dokumen/skill/level, jadwal penuh dan kota
hanya jika wajib; preferensi/manual tidak menggagalkan. Hasil/alasan dibaca dari snapshot,
EO mendapat tabel, Volunteer mendapat ringkasan. Legacy/malformed ditunda tanpa hasil
palsu. Replay tidak menggandakan hasil/revision/audit, rollback konsisten, terminal/event
cancelled tidak diaktifkan lagi; akses langsung hanya akun yang berhak. Passed tetap
submitted menunggu matching nyata; tahap screening bukan bukti akses assessment selesai.
Tes unit/rendering/Policy lulus, tes transaksi/endpoint MySQL dan race dua koneksi belum
diuji karena akses skillmatch_testing ditolak (1044). Bukti terbaru di
docs/A4_SCREENING_HANDOFF.md aplikasi; status UAT penuh tetap belum diuji.

## Regresi navigasi dengan cache Blade kosong - 10 Oktober 2026

Pada server pengembangan dengan Vite, kosongkan cache view saja (php artisan view:clear; jangan reset database), lalu klik tautan sekali dari beranda ke katalog/login/register dan antarhalaman panel Admin/Organizer/Volunteer. Klik harus mencapai URL tujuan tanpa full-reload Vite dari storage/framework/views, tanpa net::ERR_ABORTED pada request Document dan tanpa kembali ke halaman asal. Ulangi setelah cache terbentuk. Penulisan cache/runtime tidak memicu reload; perubahan sumber Blade/CSS/JS tetap diperbarui oleh dev server. Build dengan sumber Blade tanpa cache harus berhasil. Bukti Chrome desktop dan tes MySQL terisolasi dicatat di DEVELOPMENT; seluruh tombol submit, browser pengguna dan UAT lintas modul belum otomatis dinyatakan lulus. Pengujian dropdown/font berikut merupakan kasus terpisah.

## Navigasi dan font lambat semua role - 10 Oktober 2026

UAT navigasi/responsive: pada Admin, Organizer dan Volunteer, tunda/blokir stylesheet penyedia font eksternal dan buka halaman baru dengan cache dingin. Layout tidak meminta stylesheet tersebut. Tunda berkas font lokal; dropdown harus tetap membuka dengan klik pertama karena Alpine tidak bergantung pada selesai unduhan font. Uji tautan sidebar menuju halaman nyata, keyboard fokus, tombol drawer mobile lalu satu klik item, serta login/register/beranda dengan fallback font. Pastikan file WOFF2 400/500/600/700 dan lisensi ikut distribusi. Bukti browser terisolasi dan tes rendering ada di DEVELOPMENT; bukan klaim seluruh UAT atau profil browser pengguna telah diuji.

## Regresi pembayaran pending dan tombol kembali - 10 Oktober 2026

Tambahan navigasi: kunjungan detail biasa tidak mengaktifkan sync otomatis; finish URL checkout baru dengan check_payment=1 hanya memicu POST verifikasi, tidak menandai paid. Simulasikan respons gateway terlambat lalu klik menu sekali: request browser dibatalkan dan hasil lama tidak memuat ulang/membatalkan tujuan navigasi. Modifier/new tab/download/anchor tidak dianggap perpindahan halaman saat ini. Periksa navigasi Dashboard/Event Saya/Profil desktop serta item menu mobile setelah drawer dibuka. Timeout server status dibatasi lima detik; timeout tidak menghasilkan paid/entitlement palsu. Bukti dan batas reproduksi browser pengguna dicatat di DEVELOPMENT.

UAT-10/24/25/26/34: selesaikan pembayaran Sandbox, kembali ke halaman order dan periksa satu POST otomatis ber-CSRF. Status harus mengikuti Get Status server, bukan query redirect. Gateway pending tetap menunggu; settlement/capture valid mencatat paid dan aktivasi sekali, lalu halaman diperbarui. Uji Sinkronkan status tanpa JavaScript (button type submit), klik ganda, timeout, sesi berakhir, throttle, nominal/ref/currency palsu serta owner/role/akun nonaktif. Admin tetap dapat sinkron melalui form POST. Kembali ke event menuju detail event yang benar, mempunyai fokus keyboard dan gaya tombol konsisten. Tes MySQL/mock gateway dan tes JS dicatat di DEVELOPMENT; uji browser visual, webhook publik dan konkurensi dua proses tetap terpisah dari verifikasi Get Status Sandbox nyata.

## Paket dahulu dan warning durasi - 10 Oktober 2026

UAT-07/10/23/34: popup Event Saya dan halaman buat/edit mewajibkan paket lebih dahulu. Uji tepat 7/30/60 hari berhasil dan lebih satu menit ditolak; ubah paket/durasi mengubah warning langsung dan tombol simpan. Pastikan batas dari master/snapshot, timezone browser tidak mengubah WIB, pilihan/tanggal dipertahankan setelah validasi gagal. Endpoint menolak package_id hilang/tidak ada/nonaktif, snapshot/harga palsu, rentang terlalu panjang dan penurunan kuota posisi yang tidak mencukupi; event/audit rollback bersama. Create berbayar tetap draft/unpublished tanpa order/entitlement; bayar hanya setelah approved. Edit ID paket sama tidak mengikuti perubahan master; draft legacy tanpa paket harus memilih. Published/transaksi dan owner lain tetap ditolak. Bukti tes/build dicatat di DEVELOPMENT; mock payment dan tes state JavaScript tidak meluluskan Sandbox end-to-end atau review browser.

## Regresi kota event - 10 Oktober 2026

Uji kedua jalur pembuatan (popup Event Saya dan halaman Buat Event): keduanya mengirim city_id, menyembunyikan kota nonaktif, dan menyimpan relasi master. Kegagalan validasi membuka kembali popup dan mempertahankan kota yang dipilih; tidak membuat event parsial.

Untuk UAT-06/07/09/19: simpan dan baca ulang city_id/relasi cityRecord, ubah kota melalui master aktif, abaikan teks kota palsu dari request, tolak ID hilang/tidak ada/nonaktif tanpa mengubah event/audit. Draft lama tanpa city_id menampilkan petunjuk koreksi; pengajuan gagal dengan pesan validasi yang terlihat, bukan HTTP 500. Assessment published tetap menjadi syarat pengajuan. Bukti tes terarah dicatat di docs/DEVELOPMENT.md; seluruh UAT/manual lintas modul tidak otomatis lulus.

## Assessment Organizer — tambahan 10 Oktober 2026

Uji draft tanpa soal/durasi dan draft parsial berhasil tetapi tidak membuka readiness; publikasi 1/50 soal, 1/120 menit valid; 0/51 soal, durasi 0/121 dan opsi selain tepat A–D ditolak. Soal/opsi kosong atau tanpa satu kunci ditolak. Preview tidak menulis database, confirmed publish mengunci set, edit setelah publish ditolak dan replay tidak menggandakan audit. Uji stale revision, dua tab, rollback, akses Organizer lain/Volunteer/Admin/guest/nonaktif dan event frozen melalui endpoint. Readiness nyata membuka pengajuan/publikasi hanya jika syarat A2 lainnya terpenuhi; snapshot assessment_version tetap immutable. Posisi draft assessment dapat dihapus; published tidak. UI diuji 360/768/1280, keyboard, error/old input/loading dan konfirmasi. Tes validasi tidak berarti UAT timer/attempt/screening/matching selesai; bukti tiap tahap dicatat terpisah.

## Tambahan UAT jadwal posisi — 10 Oktober 2026

Untuk UAT-07/10/19/32/34: pastikan posisi baru default mengikuti event dengan interval dari server; interval request palsu diabaikan pada mode ini. Mode khusus harus berdurasi positif, dalam rentang event dan setelah deadline. Uji event satu hari (input jam) dan beberapa hari (input tanggal/jam), kegagalan simpan mempertahankan pilihan, serta endpoint lintas pemilik/role. Perubahan event editable menyelaraskan hanya posisi mengikuti event; bila jadwal khusus tidak valid, seluruh transaksi rollback. Migration mempertahankan interval existing sebagai khusus. Published tetap terkunci dan snapshot submitted tidak berubah. Bukti otomatis dan visual harus dicatat terpisah; penambahan ini tidak berarti UAT sudah lulus.

Versi 1.4.2 • ID di bawah khusus paket ini; menggantikan daftar penerimaan lama bila ada perbedaan scanner/DB. P0 wajib lulus. Kolom hasil awal semuanya **Belum diuji** karena paket ini hanya spesifikasi.

Siapkan fixture pada kontrak integrasi. Bukti tiap kasus: tanggal, environment/DB, langkah atau nama tes, hasil aktual, screenshot/log aman, commit, PIC dan penguji anggota lain. Tidak menaruh secret atau CV asli pada bukti.

| ID | Skenario | Hasil yang harus dibuktikan | PIC | Status |
|---|---|---|---|---|
| UAT-01 | Registrasi, verifikasi, login/reset/logout | Registrasi role umum saja; email unik; email belum verified tidak melamar/publish; reset token berlaku sesuai konfigurasi. | SF; integrasi A2/A3 | Belum diuji |
| UAT-02 | Login bersama semua role dan intended URL | Satu /login, guard web, tanpa pemilih role; redirect sesuai role/status; intended eksternal/tidak berizin dibuang; non-admin ditolak 403 dari /admin/*; semua keluar melalui POST /logout dengan sesi tidak berlaku lagi. | SF | Belum diuji |
| UAT-03 | Akun dinonaktifkan saat masih memiliki sesi | Request berikutnya terproteksi ditolak; pending organisasi masih bisa melengkapi profil jika akun aktif. | SF middleware; A1 UI | Belum diuji |
| UAT-04 | Profil lama hanya Weekend, skill duplikat, kota tak dikenal | Butuh interval nyata; skill duplikat ditolak; city mapping tidak menebak; form membantu koreksi. | SF | Belum diuji |
| UAT-05 | Upload organisasi dan edit profil | Berkas privat bisa ditinjau Admin; perubahan kontak biasa tidak wajib upload semua atau reset tanpa alasan. | A1/A3 | Belum diuji |
| UAT-06 | Master direferensikan dinonaktifkan | Riwayat tetap dapat dibaca, tidak dapat dipilih untuk konfigurasi baru; hard delete berbahaya ditutup. | A1 | Belum diuji |
| UAT-07 | Event draft/revisi/approve | Data tanggal/kuota/assessment valid; catatan revisi tersimpan; detail relasi posisi bekerja. | A2/A1/A4 | Belum diuji |
| UAT-08 | Event approved belum memiliki entitlement | Tidak publik; free aktivasi sekali setelah approval; paid butuh verifikasi pembayaran. | A2 | Belum diuji |
| UAT-09 | Katalog dan manipulasi ID event privat | Filter/pagination benar; draft/unpublished/suspended tidak terbuka publik lewat URL langsung. | A2 | Belum diuji |
| UAT-10 | Ubah aturan event published | Perubahan substantive ditolak; teks ringan diaudit; snapshot pelamar tidak berubah. | A2 | Belum diuji |
| UAT-11 | Submit dua kali, dua posisi satu event, atau terlambat | Satu lamaran per Volunteer/event; submission idempoten; deadline timestamp server; quota paket tidak terlampaui. | A3/A2 | Belum diuji |
| UAT-12 | File >5 MB, file keenam, ekstensi/MIME salah | Ditolak server dan tidak ready; CV non-PDF ditolak; pesan jelas; tidak butuh scanner. | A3 | Belum diuji |
| UAT-13 | Organizer A/B mengakses dokumen draft/submitted | Draft privat; A hanya melihat submitted miliknya; B ditolak meski tahu ID/path. | A3 | Belum diuji |
| UAT-14 | Nama file HTML/traversal dan URL publik langsung | Teks ter-escape, internal name acak, attachment authorized; folder privat tidak dapat diakses publik. | A3 | Belum diuji |
| UAT-15 | File simpan sukses tetapi metadata gagal atau file hilang | Cleanup/kompensasi; tidak ada ready palsu; missing/purged tidak menghasilkan unduhan sukses palsu. | A3 | Belum diuji |
| UAT-16 | Withdraw/suspend lalu download ulang | Hak Organizer berakhir; file tidak dipindah ke akun lain; audit download tersedia. | A3/A1 | Belum diuji |
| UAT-17 | Syarat wajib gagal vs preferensi tak cocok | Screening gagal dengan alasan hanya untuk wajib; assessment tidak bisa dimulai; tidak parsing klaim keaslian CV. | A4 | Belum diuji |
| UAT-18 | S=75,A=75,L=100; interval overlap; level kurang | 80,00 pada contoh; interval tidak double count; level minimum konsisten; nilai 0–100. | A4 | Belum diuji |
| UAT-19 | Edit profil setelah submit | Snapshot dan skor historis tetap; tidak membaca profil terbaru untuk skor lama. | A4/A3 | Belum diuji |
| UAT-20 | Refresh/start ganda, timeout browser ditutup, submit paralel assessment | Satu attempt, timer server tetap; finalisasi sekali; jawaban tersimpan; kunci tidak muncul di response peserta. | A4 | Belum diuji |
| UAT-21 | Urutan kandidat dan keputusan seleksi | Match lalu assessment lalu submit/id; hanya under_review bisa accepted; skor tinggi tidak otomatis diterima. | A3/A4 | Belum diuji |
| UAT-22 | Dua accept berebut satu kuota; volunteer accepted pada jadwal overlap | Satu berhasil; konflik jadwal ditolak; diuji dua proses dengan satu database MySQL uji dengan koneksi independen. | A3 | Belum diuji |
| UAT-23 | Browser mengklaim sukses payment | Order tetap tidak paid tanpa verifikasi server; event belum eligible tidak publik. | A2 | Belum diuji |
| UAT-24 | Webhook ganda/tidak berurutan/nominal salah | Tidak ada aktivasi ganda; paid_at tetap; mismatch ditolak/ditandai; pending lama tidak menurunkan paid. | A2 | Belum diuji |
| UAT-25 | Admin mengubah harga paket setelah order dibuat | Snapshot nominal/manfaat order lama tetap; admin tidak bebas edit paid; entitlement lama konsisten. | A2 | Belum diuji |
| UAT-26 | Sandbox checkout dan status server nyata | Minimal satu transaksi sandbox terverifikasi; log referensi aman; mock terpisah dari bukti E2E. | A2 | Belum diuji |
| UAT-27 | Attendance oleh Volunteer/Organizer lain; peserta belum diterima | Ditolak server; hanya accepted milik event dapat dicatat; unrecorded tidak absent. | A3 | Belum diuji |
| UAT-28 | Completion terlalu dini/ada pending, kunci setelah selesai | Diblokir bila prasyarat belum terpenuhi; present berhasil, absent bukan berhasil; koreksi setelah selesai ditolak. | A3 | Belum diuji |
| UAT-29 | Aktivitas akun A meminta data akun B | Hanya data akun aktif; tidak ada grafik/dashboard pengguna; route lama redirect aman. | A4/A1 | Belum diuji |
| UAT-30 | Retry notifikasi dan filter dashboard | Satu notifikasi per transisi/penerima; indikator periode memakai timestamp benar; gagal query bukan 0. | A4/A1 | Belum diuji |
| UAT-31 | Event cancelled ketika ada assessment/lamaran/payment | Lamaran/attempt aktif ditutup; callback terlambat tidak publish; riwayat menampilkan dibatalkan. | A2/A3/A4 | Belum diuji |
| UAT-32 | Input role/harga/score/owner palsu, CSRF, akses log | Field sensitif server; request ilegal ditolak; log tidak membocorkan secret/berkas. | Semua | Belum diuji |
| UAT-33 | Backup MySQL+private file, retensi dan restore | Data/berkas konsisten; purge ledger diterapkan; file dihapus tidak kembali aktif; instruksi reproducible. | A1/A3 | Belum diuji |
| UAT-34 | Desktop/mobile, keyboard, build, font/Alpine | 360/768/1280 px berfungsi; no body blank; Alpine sekali; npm build dan PHPUnit/Pint sesuai scope. | Semua | Belum diuji |

| UAT-35 | Anggota lain menjalankan aplikasi dari README pada lingkungan baru | Setup MySQL, .env, migration/seeder, frontend, server, worker/scheduler dan login demo berhasil; database test terpisah; semua langkah terdokumentasi. | A1 + semua | Belum diuji |

## Rekap analitik kegiatan Admin

- Pilih bulan dan verifikasi bahwa batas periode rekap lamaran mengikuti WIB.
  Draf tidak dihitung. Status seleksi pada rekap mengikuti status terkini
  dari lamaran yang dikirim dalam periode itu.
- Cocokkan kegiatan berlangsung, menunggu penyelesaian dan selesai dengan
  jadwal serta status kegiatan. Cocokkan kehadiran berdasarkan rekap peserta
  diterima pada kegiatan yang sudah dimulai dan tidak dibatalkan.
- Periksa tiga kartu di desktop dan seluler. Pastikan akses langsung oleh
  Organizer atau Volunteer ditolak.

## Perapian detail kegiatan publik

Pada detail publik, pastikan daftar posisi tetap tampil setelah informasi
kegiatan, deskripsi identik tidak diulang dan deskripsi unik tetap tampil.
Periksa jadwal satu hari dan lintas hari dalam WIB. Cocokkan tombol melamar
dengan status pendaftaran dan alur login atau verifikasi yang tersedia.

## Uji kinerja

Catat p95 endpoint katalog, aktivitas dan kandidat pada fixture 100 event/1.000 lamaran/20 pengguna. Target <=2 detik di lingkungan yang dicatat, tidak termasuk gateway. Jika belum diuji tulis Belum diuji, bukan mengklaim lulus.

## Format checklist PR per anggota

- Kebutuhan FR dan UAT yang ditangani.
- Migration tambahan dan dependensi terhadap PR anggota lain.
- Policy/validasi dan alur gagal yang diuji.
- Screenshot desktop/mobile untuk UI yang berubah.
- Langkah demo, fixture dan hasil tes aktual.
- Known limitation yang masih memblokir P0.

Selesaikan satu alur end-to-end sebelum memperluas P1. PRD/kontrak/design diperbarui bersama jika aturan berubah; tidak menerima kondisi "masing-masing modul jalan" ketika integrasi gagal.

## Gerbang SF sebelum integrasi penuh

Gunakan SF-01–07 dan delapan kriteria pada [brief SF](anggota/SHARED_FOUNDATION.md). Reviewer anggota lain mencatat commit, lingkungan, hasil dan blocker. UAT-01–04 diperiksa sejauh fondasi tersedia; langkah melamar/publikasi diverifikasi lagi saat integrasi A2/A3. UAT-35 penuh baru lulus setelah modul, worker/scheduler dan README final tersedia. Seluruh skenario tetap berstatus Belum diuji pada revisi dokumen ini.

## Regresi pelestarian baseline - 5 Oktober 2026

Tambahan pemeriksaan UAT-02/09/30/34, bukan pengganti skenario P0:

- Guest membuka `/`: hero beranda dan identitas tim tampil, Masuk/Daftar menuju route auth bersama; tidak ada angka event palsu atau link katalog yang belum terpasang.
- Admin/Volunteer/Organizer membuka beranda setelah login: CTA menggunakan tujuan sesuai role/status/verifikasi, tanpa login khusus admin atau dashboard analitik pengguna.
- Admin membuka `/admin/dashboard`: hitungan sesuai database dan aksi cepat tetap tampil; non-admin tetap 403. Analitik periode yang belum tersedia tidak diklaim selesai.
- A2 menghubungkan beranda ke katalog setelah aturan publikasi/otorisasi terpenuhi; A1 memperluas dashboard tanpa menghilangkan fungsi dasar.

Hasil browser desktop/mobile/keyboard dan review anggota lain masih perlu dicatat; pemulihan beranda tidak otomatis meluluskan UAT-09/30/34/35 penuh. Bukti tes implementasi ada pada handoff aplikasi.

## Bukti parsial A2 - 5 Oktober 2026

Lihat [A2_HANDOFF](../../docs/A2_HANDOFF.md) untuk environment, commit dan hasil aktual. Checklist penerimaan lintas anggota di atas tidak otomatis berubah menjadi lulus.

| Cakupan | Bukti implementasi saat ini | Yang masih diperlukan |
|---|---|---|
| UAT-07/08/10 | A2EventTest: konfigurasi, moderasi baseline, Free sekali, snapshot, blokir assessment belum tersedia, paid belum entitled tidak publik | Validator assessment nyata dan review moderasi A1 |
| UAT-09/34 | Render beranda/katalog/detail, filter dan endpoint visibilitas, Blade/build, intended login lokal | Browser visual/keyboard/mobile |
| UAT-11/22/31 | Snapshot kontrak, reserve batas paket transaksional dan race dua proses; cancellation mock serta blokir tanpa layanan | Submit/seleksi/cancellation dan attempt nyata A3/A4 |
| UAT-23/24/25/26 | A2PaymentTest: fake signature/status, nominal/IDR/ref, retry, status terlambat, refund/challenge, audit rollback; race order/reconcile/publish | Satu transaksi Sandbox end-to-end dan callback HTTPS |
| Notifikasi | Stable key, replay ledger dan rollback dengan collaborator tes | Outbox/worker/delivery A4 |

Uji konkurensi A2 tidak menggantikan uji kuota acceptance/bentrok jadwal A3 atau pengukuran performa keseluruhan. Review tim dan UAT penuh tetap belum selesai.

## Regresi beranda informatif (UAT-09/34) - 5 Oktober 2026

- Guest memahami tujuan platform melalui hero/pengenalan; panduan kedua peran dan FAQ membedakan fitur tersedia dari yang masih dikembangkan.
- Pencarian beranda menuju katalog menggunakan parameter search; tautan detail, auth, CTA akun dan anchor footer mengarah tujuan yang tersedia.
- Event draft, belum published, suspended, terminal, berakhir, pemilik nonaktif, pendaftaran belum dibuka/tutup atau kapasitas paket habis tidak masuk pratinjau. Maksimal enam event, urut starts_at/ID; detail waktu berlabel WIB.
- Tidak ada event eligible: tampil pesan informatif dan CTA katalog; tidak ada statistik nol/testimoni/fixture promosi.
- Periksa lebar 360/768/1280, fokus dan aktivasi FAQ melalui keyboard, label pencarian dan kontras. Screenshot statis tidak menggantikan seluruh uji keyboard atau UAT lintas modul.

Bukti aktual pemeriksaan dicatat dalam A2_HANDOFF. Checklist UAT keseluruhan belum ditandai lulus.

## Regresi UI masuk/daftar - 5 Oktober 2026

Login dan registrasi diperbarui secara visual. Bukti: 40 tes auth/FoundationAccess lulus (183 assertion), build/Blade sukses, Chrome viewport 360/1280 tanpa overflow. Toggle password terbukti berganti password -> text -> password; login tidak memiliki input role, registrasi hanya Volunteer/Organizer. Periksa juga error server, restore old input, checkbox ingat saya, lupa kata sandi, fokus/radio keyboard dan submit loading pada UAT manual. Pemeriksaan ini tidak meluluskan SMTP atau seluruh audit aksesibilitas.

## Regresi paket awal dan kartu

DefaultPackageTest memeriksa nilai awal sesuai keputusan, seeding ulang setelah rename/edit/nonaktif tanpa reset/duplikasi, perubahan admin tampil pada kartu aktif dan tidak mengubah snapshot order/event lama. Bersama A2EventTest, HomeTest dan RegistrationTest: 24 tes / 183 assertion lulus pada MySQL tes. Pemilihan paket tetap mengikuti limit dan Policy existing; fitur lanjutan yang belum terintegrasi tidak dinyatakan lulus.

## UAT tambahan - koordinasi peserta

- Owner memperbarui koordinasi pada event published tanpa mengubah jadwal, snapshot, paket atau keputusan seleksi. Semua field kosong tetap dapat melanjutkan alur existing.
- Hanya accepted pemilik lamaran melihat grup/PIC; under_review/rejected/withdrawn/cancelled, user lain, Admin dan guest tidak mendapat nilai privat. Pembatalan event mencabut akses; accepted baru melihat data terkini. Periksa HTML dan endpoint langsung, bukan hanya tombol.
- Tolak URL non-HTTPS, user/password URL, input terlalu panjang dan revision stale. Replay identik tidak menggandakan notifikasi/audit. Pengosongan menarik tautan.
- Pembaruan hanya memberi notifikasi accepted; URL/kontak tidak ada pada payload/audit. Kegagalan notifikasi membatalkan transaksi. Completed/cancelled menolak edit.
- Review desktop/mobile dan submit native dengan CSRF; tidak ada input arahan/titik kumpul/waktu kedatangan. Gabung grup membuka tab baru. Integrasi keanggotaan grup tetap manual eksternal.

## Regresi validasi profil dan form sejenis

- Nomor berhuruf/campuran, terlalu pendek/panjang, spasi/pemisah, notasi ilmiah, + ganda dan array ditolak melalui endpoint profil Volunteer/Organizer; tidak ada simpan parsial atau perubahan status organisasi.
- Nomor lokal dengan nol awal dan internasional dengan + tersimpan sebagai string. Filter browser menolak ketik/paste huruf; nilai script/autofill salah tetap gagal native validation dan server. Periksa tanpa JS juga.
- Profil lama bernomor invalid tidak dianggap lengkap sampai diperbaiki; data lama dan snapshot tidak ditulis ulang. Panggilan langsung service juga ditolak bila phone invalid.
- Modal/halaman paket memakai max/step existing server; tanggal lahir tidak bisa memilih masa depan. Kontak PIC tetap menerima email/nama pengguna. Tidak mengubah aturan umur, harga atau limit paket.

## Foto sampul event — keputusan 5 November 2026

Organizer menambah/mengganti/menghapus foto opsional pada create/edit draft yang masih editable. JPG/PNG/WebP maksimal 5 MB, rekomendasi 1600 × 900; kartu konsisten 16:9 dengan fallback ilustrasi. Kolom nullable events.cover_image_path; input cover_image dan remove_cover_image, path ditentukan server. GET /events/{event}/cover (events.cover) mengikuti visibility event atau pemilik/admin aktif. Tidak mengubah moderasi/publikasi/paket/pembayaran maupun dokumen privat. Detail implementasi, UAT dan bukti tes tersedia pada docs/DEVELOPMENT.md#foto-sampul-event di root repository.

## Electric Blue Publik dan Auth — keputusan 6 November 2026

Permintaan pengguna mengizinkan penerapan FigCraft Electric Blue / 06 Publik dan Auth pada landing, katalog, login dan registrasi. Blue #2563EB, Navy #0F172A, Peach #FB923C, Off-white #F8FAFC; judul editorial Georgia dan body Inter. Tampilan operasional tiga role tetap. Data, filter, pagination, upload foto, validasi, satu login/guard web dan seluruh aturan bisnis existing dipertahankan. UAT: periksa kelima tampilan desktop/mobile, menu, pencarian/filter/reset, kartu foto, role registrasi, error dan toggle password. Bukti implementasi dan tes ada pada docs/DEVELOPMENT.md#electric-blue-publik-auth di root repository; belum menyatakan seluruh UAT tim lulus.


Detail event panel (14 November): periksa 1440/768/390, tanggal panjang
dan kartu posisi ringkas tanpa overflow; kondisi CTA existing tetap.
Preview fixture lulus audit lebar; browser akun nyata belum diuji ulang.


### Penyempurnaan EO 15 November 2026: lima perbaikan
- Basis feature/organizer-ui-refinement @367a7de; mempertahankan perubahan lokal dan ukuran kartu.
- Penutupan seleksi dinonaktifkan sebelum tenggat pendaftaran, dengan waktu tersedia WIB.
- Penyelesaian menampilkan jadwal, seleksi/asesmen, dan jumlah peserta yang sesi/absensinya belum final; tombol terkunci sampai siap. Konfirmasi nol hanya untuk nol peserta. Validasi server tetap berlaku.
- Fase tampilan mengikuti waktu melalui Event::executionPhase(), tanpa menulis status atau menyelesaikan otomatis.
- Pencarian peserta 350 ms, hasil HTML server diperbarui tanpa mengganti input/filter; permintaan lama dibatalkan, paginasi mempertahankan pencarian. Tanpa JS tetap GET/Enter. Pencarian nama memakai LIKE literal.
- Istilah antarmuka EO: kegiatan, asesmen, penyaringan, pelamar, skor kecocokan/keahlian. Identifier integrasi dan default komponen role lain tetap.
- Tidak ada migration, dependensi, atau route baru. Pemeriksaan visual browser terbaru belum dilakukan.


## Usia yang disarankan per posisi ? keputusan pengguna 15 November 2026
Posisi memiliki suggested_min_age dan suggested_max_age nullable unsigned tiny integer (0?255 sebagai batas penyimpanan), masing-masing opsional. Jika keduanya diisi, maksimum harus >= minimum. Kosong berarti tidak ada saran usia pada sisi tersebut; posisi existing tetap null. EO mengatur saat konfigurasi posisi mengikuti otorisasi dan kunci publikasi existing. Form serta detail posisi EO/volunteer menampilkan usia pada tanggal mulai kegiatan sebagai informasi, bukan syarat wajib. Volunteer di luar rentang tetap dapat melamar; screening, assessment, matching 50/30/20 dan snapshot evaluasi tidak memakai kolom ini. Profil volunteer tetap menyimpan birth_date, tidak menambah kolom umur profil.
Migration tambahan: database/migrations/2026_11_15_160000_add_suggested_age_to_event_positions.php. Tidak ada route baru. UI berbagi components/position-suggested-age.blade.php. A2 pemilik kolom dan konfigurasi; A3/A4 tidak perlu menambahkan pembatasan umur.
UAT: kosong/satu sisi/rentang valid tersimpan dan tampil, rentang terbalik/negatif/non-integer ditolak, kosongkan kedua nilai menghapus saran, pemilik lain ditolak, posisi published tetap terkunci, snapshot evaluasi identik setelah hanya usia diubah.


## Usia pelamar pada detail EO ? 15 November 2026
ApplicationService::submit menambah profile.birth_date (YYYY-MM-DD nullable) ke snapshot lamaran baru. Ini data pribadi untuk owner terotorisasi, bukan bagian audit/notifikasi atau rumus evaluasi. Detail lamaran EO menghitung tahun penuh pada tanggal starts_at dalam WIB dan menampilkan usia bersama saran usia posisi. Tidak ada eliminasi otomatis atau perubahan bobot/score.
Lamaran lama tanpa key birth_date memakai tanggal lahir profil saat ini dan menampilkan sumbernya secara eksplisit; snapshot lama tidak ditulis ulang. Snapshot dengan tanggal kosong/tidak valid atau tanggal lahir setelah tanggal kegiatan menampilkan Belum tersedia. Tidak ada migration atau route baru untuk usia pelamar.
UAT: ulang tahun tepat hari kegiatan, sehari sesudahnya, starts_at UTC yang masuk tanggal WIB berikutnya, data lama/missing/invalid, perubahan profil setelah submit tidak mengubah usia dari snapshot, owner lain ditolak. Alur terintegrasi diuji dari fixture pembuatan event/posisi, asesmen terbit, ajukan moderasi, admin approve/publikasi paket gratis, submit lamaran di luar saran usia, penyaringan/pencocokan/asesmen, accepted, absensi sesi, completed, riwayat dan final lock. Pada DatabaseTransactions, afterCommit tertunda sampai rollback; evaluasi nyata dipanggil eksplisit dalam tes, bukan bukti worker/queue produksi.


## Penyelarasan tampilan Admin dengan EO ? 15 November 2026
Persetujuan pengguna: ubah sisi admin dengan gaya EO untuk ditinjau. Dasar feature/organizer-ui-refinement @4503a04, working tree awal bersih. Existing Admin FigCraft (d984b27) sudah memiliki dashboard, tabel, grafik, modal, filter dan endpoint; semua dipertahankan. Ini penyempurnaan tampilan existing, bukan penggantian modul.
Sidebar navy 256 px, brand bunga yang sama, akun di atas navigasi, menu utama aktif biru, logout POST/CSRF di bawah situs utama. Submenu akun/master tetap ada. Header desktop dipindahkan ke identitas sidebar; header mobile/drawer dan fokus keyboard tetap.
Hierarki judul 26/22 px, section 18 px, tabel 13 px/header 11 px, kartu putih border halus, kontrol ringkas. Tindakan edit/tambah/detail outline biru; kembali solid merah dan hapus/tolak/batal outline merah; ikon sesuai. Label View/Delete menjadi Detail/Hapus. Warna status aktif/hijau, pending/amber, gagal/merah diperjelas. Grafik mempertahankan data/controller/click handler dan seri Chart.js.
Admin-flash terpisah: toast sukses/informasi 5 detik, pause hover/focus, tutup manual; error tetap sampai ditutup dan error validasi tidak hilang otomatis. Ringkasan flash lama per halaman digantikan satu outlet layout; error per-field tetap. CSS hanya .admin-panel; EO/Volunteer dan komponen default bersama tidak diubah.
Tidak ada migration, perubahan route, controller/service/model/Policy atau aturan bisnis. PRD dan kontrak tetap berlaku; ini perubahan desain/A1 saja. Tidak ada dependency baru. Tidak ada commit/push untuk langkah ini.


## Admin: lima penyesuaian operasional ? 15 November 2026
Lingkup disetujui pengguna pada feature/admin-ui-refinement (dasar 9e2e241, working tree awal bersih):
1. Transaksi: pencarian literal nomor transaksi, judul kegiatan, akun/email EO dan nama organisasi; OR dikelompokkan di dalam filter status. date_from/date_to memakai tanggal pembuatan transaksi dalam WIB, awal inklusif dan batas hari berikutnya eksklusif dalam UTC; rentang terbalik/format invalid ditolak. Pagination mempertahankan query.
2. Detail verifikasi: identitas kegiatan, jadwal/rekrutmen, posisi/tugas/saran usia, paket snapshot dan EO dikelompokkan. Badge mengikuti status nyata; tombol keputusan hanya untuk pending (server tetap menolak replay 409). Daftar verifikasi default pending, filter approved/rejected/all menampilkan kembali pengajuan lama. Status all mencakup pengajuan pending/approved/rejected, bukan semua draf. Route existing tetap.
3. Catatan keputusan terakhir: detail kegiatan menampilkan verification_note + verified_at WIB, termasuk saat resubmit. Modal detail organisasi menampilkan organizer_verification_note dan waktu review terakhir dari audit organizer.reviewed; bila tidak ada audit, waktu dinyatakan belum tercatat. Resubmit profil organisasi sekarang mempertahankan catatan terakhir, bukan menghapusnya; halaman pending membedakannya sebagai catatan sebelumnya. Admin review berikutnya tetap mengganti/menghapus catatan sesuai perilaku existing. Ini bukan arsip lengkap semua siklus penolakan; catatan lama yang sudah terhapus sebelum perubahan ini tidak direkonstruksi.
4. Paket: banner dan helper status pada modal/form menjelaskan nonaktif hanya menutup pilihan paket baru; snapshot/hak pembelian lama tetap berlaku. Tidak ada revoke/refund atau perubahan payment.
5. Dashboard: angka antrean organisasi mengarah ke status=pending, antrean kegiatan menuju daftar verifikasi default pending. Kartu grafik, data dan handler existing dipertahankan.
Tidak ada migration, status/schema baru atau dependensi. PRD/kontrak diperbarui untuk retensi catatan terakhir saat pengajuan ulang dan filter baca; A1 tetap pemilik review, A2 tetap pemilik payment/paket. Data catatan tetap di field existing, tidak disalin ke audit/log/notifikasi. Tampilan EO/Volunteer di luar label catatan profil pending tidak diubah.


### Konsistensi akhir Admin dan uji alur ? 15 November 2026
Permintaan pengguna mencakup istilah, modal dan detail verifikasi ponsel. Teks UI admin memakai Kegiatan, Volunteer, Keahlian; status transaksi ditampilkan Menunggu pembayaran/Lunas/Gagal/Kedaluwarsa/Dibatalkan/Perlu pemeriksaan. Enum, route, field, Alpine camelCase dan data tetap. Modal menempatkan submit sebelum Batal, tombol simpan outline biru, pembatalan/hapus outline merah, dan tutup Escape; dismiss icon diberi nama aksesibel. Nonaktifkan volunteer merah, Aktifkan biru. Pintasan ponsel menuju #verification-decision dan #verification-note memudahkan menemukan keputusan/catatan tanpa mengubah urutan informasi desktop.
84 tes backend/624 asersi lulus: AdminWorkflowRefinement, FoundationAccess, FoundationIntegrity, IntegratedFlows, A2Event, A2Payment, DefaultPackage, OrganizerKtpUpload. Mencakup akses lintas role, verifikasi/revisi/resubmit organisasi, moderasi dan publikasi, validasi paket/snapshot, callback pembayaran terverifikasi/replay/idempotensi, serta filter transaksi. Ini tes otomatis MySQL testing terpisah; gateway pembayaran memakai fixture/mocking existing, bukan transaksi Sandbox nyata. Tidak ada perubahan keputusan bisnis pada langkah konsistensi ini.
Build dan diff --check lulus. Fixture 13 halaman HTTP 200 dengan rollback; review browser termasuk modal/ponsel dicatat setelah selesai. Tidak ada commit/push; branch feature/admin-ui-refinement.


## Konsistensi panel Volunteer - 15 November 2026
- Login volunteer, buka aktivitas/profil/katalog/lamaran/riwayat/notifikasi dan
  detail seluruh status. Sidebar navy, menu aktif biru dan logo sama EO/Admin.
- Judul desktop 26px/mobile 22px, isi lebih kecil; kartu tidak overflow pada
  390/768/1440px. Kartu statistik berborder sesuai data/status, angka tetap nyata.
- Kembali merah solid di kiri atas; detail/edit/simpan/tambah outline biru;
  hapus/batal outline merah. Konfirmasi asesmen: Mulai sekarang lalu Batal.
- Coba filter katalog dan Reset (hanya saat filter aktif); kembali ke katalog
  awal. Pilih posisi/lamaran tetap mengikuti gate yang ada.
- Profil: tambah/hapus keahlian dan interval, validasi dan simpan tetap. Asesmen:
  konfirmasi, navigasi, autosave, submit/deadline/finalisasi tetap sesuai kontrak.
- Label memakai Volunteer/Kegiatan/Keahlian/Penyaringan/Asesmen/Draf dan Anda.
  Periksa toast sukses singkat dan error tetap dapat dibaca/dikoreksi.
Bukti otomatis/visual dan bagian browser end-to-end yang belum diuji dicatat di
[handoff](../../docs/VOLUNTEER_EO_STYLE_HANDOFF.md); daftar ini bukan klaim semua UAT lulus.

### Regresi volunteer - 16 November 2026
Periksa petunjuk langkah pada daftar lamaran sesuai status; asesmen baru pada kegiatan suspended tidak menawarkan tombol mulai; attempt berjalan tetap menawarkan lanjutkan sesuai AssessmentService. Tenggat ditampilkan WIB. Di riwayat/detail kehadiran, buka/tutup Rincian sesi dan periksa status, catatan panitia, arsip, serta waktu pencatatan. Cek 390/768/1440px tanpa scroll horizontal. Browser dengan fixture HTML lama/aset terbaru lulus 30 kombinasi; Blade terbaru dan endpoint regresi belum diuji karena PHP lokal belum memenuhi >=8.4.1. Rincian bukti dan batas pemeriksaan ada di handoff volunteer.
## Notifikasi terbaru pada Ringkasan Aktivitas Volunteer

- Periksa nama kegiatan dan posisi, waktu WIB, dan tujuan tindakan pada
  notifikasi lamaran diterima dan asesmen. Asesmen yang telah berakhir kembali
  mengarah ke detail lamaran.
- Tandai notifikasi telah dibaca, lalu kembali dari Semua notifikasi ke
  Ringkasan Aktivitas. Indikator dan tombol baca hilang. Akun Volunteer lain
  hanya melihat notifikasinya sendiri.
- Pada desktop dan seluler, pastikan tepi panel sejajar dengan Langkah
  berikutnya, ikon sejajar dengan judul, dan nama kegiatan atau posisi yang
  panjang dapat membungkus.

Periksa Notifikasi terbaru dan Semua notifikasi untuk ketiga peran. Organizer
membuka detail objek miliknya, Organizer pending dapat membuka profil, dan
Admin membuka antrean verifikasi. Akun lain tidak mendapat konteks atau tautan
ke detail objek yang tidak dimilikinya. Penandaan baca dan paginasi tetap tersedia.
