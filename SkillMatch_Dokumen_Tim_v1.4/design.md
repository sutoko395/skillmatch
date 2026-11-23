## Hierarki font screening dan kecocokan - 15 November 2026

Dua kartu evaluasi detail pelamar EO memakai judul16px/600, isi12px, header tabel
11px/600, label baris12px/500 dan keterangan screening11px. Skor26px/600 dengan
pembagi13px serta Match Score11px. Hanya tipografi dalam wrapper review EO; padding,
grid, lebar kartu dan komponen role lain tidak diubah.

## Penutupan seleksi di Kelola Pelamar - 15 November 2026

Tutup seleksi tertunda dipindah dari detail Pelaksanaan ke daftar pelamar kegiatan
di Kelola Pelamar (#tutup-seleksi), tampil bila ada pending dan eventnonterminal.
Form alasan/konfirmasi/tombol merah existing dipertahankan. Pelaksanaan hanya
menampilkan Selesaikan kegiatan; jika ada pending seleksi/assessment, tampil
pemberitahuan ringkas dan Ke Kelola Pelamar ke anchor form. Tanpa pending,
pemberitahuan hilang. Countpending tidak mengikuti filter/pagination kandidat.

## Hierarki font kartu peserta - 15 November 2026

Font kartu peserta dibuat lebih ringan tanpa memperbesar dimensi/padding: nama16px,
posisi12px, progres13px, tanggal/jam13px, status/tombol/Catatan12px, Edit11px.
Bobot status500 dan ikonpendukung14-16px. Nama tetap paling menonjol; grid, ukuran
klik tombol, posisi Catatan/Edit/Dicatat dan seluruh fungsi dipertahankan.

## Persiapan sesi otomatis untuk absensi belum dicatat - 15 November 2026

Detail pelaksanaan tidak menampilkan checkbox dan tombol persiapan untuk accepted
dengan parent absensi kosong/unrecorded. Saat perlu bootstrap tampil Memuat sesi
peserta... sebentar, POST otomatis CSRF, reload ke kartu sesi normal. Tidak ada
ubah state lewat GET. Kegagalan tampil pesan/Coba lagi, bukan reload tanpa batas.
Tanpa JS tersedia Tampilkan sesi peserta tanpa checkbox. Legacy present/absent
mempertahankan konversi eksplisit existing; terminal tetap readonly. Sesi baru
tetap Belum dicatat dan menggunakan Lihat sesi/Tandai hadir/Catatan existing.

## Rapatkan aksi dan buang ruang bawah sesi - 15 November 2026

Baris aksi sesi kini tanpa rowgap desktop; kolom kanan mencakup dua baris agar
tombol tinggi tidak mendorong Catatan/Edit ke bawah. Padding sesi8px atas/6px
bawah, Edit tanpa padding vertikal. Mobile rowgap2px. Editor/form tetap.

## Kartu lebih padat dan Batal koreksi - 15 November 2026

Jarak baris sesi diperkecil menjadi2px desktop, padding sesi12px dan header10px;
avatar40px/nama17px. Catatan dan Edit lebih dekat ke tanggal/status. Editor padding
12px, jarak field8px/select36px/textarea60px. Form koreksi memiliki Batal outline
merah kanan Simpan; membuang pilihan sementara dan menutup tanpa submit.

## Posisi waktu pencatatan dan aksi sesi - 15 November 2026

Dicatat dipindahkan dari bawah status ke kolom kanan sesi. Edit absensi tetap
sejajar kiri tepat di bawah badge status, Catatan tepat di bawah tanggal. Tata
letak tablet menjaga kolom kanan; mobile menaruh waktu Dicatat rata kanan.

## Edit absensi ringkas di bawah status - 15 November 2026

Edit absensi dipindahkan ke bawah status kehadiran, menjadi tautan kecil12px
berikon pensil13px berwarna slate tanpa kotak. Hover biru/underline. Tata letak
mobile menjaga posisi edit di bawah status dan tombol Tandai hadir terpisah.
Form koreksi dan Catatan di bawah tanggal tetap. Menggantikan tombol boxed.

## Kartu ringkas, Batal catatan dan Edit absensi - 15 November 2026

Header kartu peserta dipendekkan: padding desktop14x20px, avatar48px (sebelumnya
64px), mobile44px. Di kanan Simpan catatan ada Batal outline merah: typebutton,
menutup editor dan mengembalikan nilai catatan tersimpan tanpa request. Saat busy
kedua tombol disabled. Menu titik tiga diganti Edit absensi outline biru dengan
ikon pensil, tetap membuka form koreksi existing. Posisi Catatan di bawah tanggal
serta aturan backend tetap. Menggantikan pilihan menu titik tiga sebelumnya.

## Penyelarasan visual dengan mockup check-in - 15 November 2026

Permintaan terbaru pengguna menjadikan gambar mockup sebelumnya sebagai acuan
susunan detail check-in. Header kartu memakai avatar bulat64px, nama19px/posisi15px
polos, progres16px dengan garis pemisah dan toggle kanan. Desktop sesi empat kolom:
tanggal (d M Y Indonesia), jam dengan pemisah vertikal, status/waktu dicatat dan
Tandai hadir. Catatan berikon dokumen tepat di bawah tanggal, bukan satu baris tools
di bawah seluruh sesi. Editor dibuka fullwidth di bawah baris melalui Alpine;
label terlihat Catatan, aria-label menjelaskan tambah/edit/lihat. Koreksi tetap
tersedia melalui menu titik tiga. Mobile ditumpuk dengan Catatan tetap di bawah
tanggal. Pencarian nama fullwidth dengan ikon; filter dan rekap tetap dalam Filter
peserta, terbuka otomatis ketika filter aktif. Jumlah peserta pada halaman tampil
di bawah kartu. Backend/izin/aturan waktu/final dan simpan catatan terpisah tetap.

## Check-in peserta dan catatan terpisah - 15 November 2026

Pengguna menyetujui mockup alur meja absensi dan penjelasan Tambah catatan.
Kartu peserta default tertutup, berisi nama/posisi, x dari y sesi hadir dan Lihat
sesi outline biru; tombol berubah Tutup sesi saat terbuka. Berlaku untuk satu atau
banyak sesi. Baris sesi ringkas tanggal/jam/status/waktu dicatat + Tandai hadir
biru jika sesi dibuka dan belum present. Sudah hadir memakai badge hijau.
+ Tambah catatan membuka textarea opsional + Simpan catatan; jika terisi berubah
Lihat/Edit catatan. Ada keterangan singkat Catatan ini dapat dilihat volunteer.
Ubah kehadiran menyimpan kontrol Hadir/Tidak hadir dalam disclosure terpisah.
Catatan terminal readonly tetap dapat dibuka. Filter/pagination/legacy/completion
existing tetap. Sukses membuka kembali kartu terkait; error membuka kartu dan
mempertahankan draft catatan pada sesi asal. Loading tombol mencegah klik ganda.

## Rekap sesi inline - 15 November 2026

Rekap sesi detail Pelaksanaan menjadi dl inline di bawah Kehadiran peserta:
Hadir hijau, Tidak hadir merah, Belum dicatat kuning dengan titik berwarna dan
angka tebal12px. Tanpa kartu/buka-tutup/teks jumlah catatan; wrap di mobile.
Hitungan tetap seluruh sesi event, bukan peserta atau hasil filter.

## Hapus kartu rekap peserta - 15 November 2026

Pengguna meminta menghapus empat kartu rekap peserta (Peserta diterima/Hadir/
Tidak hadir/Belum dicatat) di detail Pelaksanaan. Blok tersebut beserta CSS dan
partial ikon khusus yang tidak dipakai dihapus. Rekap sesi, filter, kartu peserta
dan form absensi/tindakan tetap tersedia. Menggantikan desain empat kartu di bawah.

## Detail Pelaksanaan dan Kehadiran EO - 15 November 2026

Pengguna menyetujui perapian detail pelaksanaan mengikuti halaman EO sebelumnya
dan penghapusan penjelasan yang tidak diperlukan. Kembali merah kiri atas,
header24px dengan nama kegiatan17px/status ringkas dan jadwal berikon12px WIB.
Rekap peserta empat kartu putih berbatas biru/hijau/merah/kuning dan angka28px.
Rekap seluruh sesi dipindah ke details Rekap sesi, data hitungan tetap tersedia.
Filter nama/posisi/status/hari tugas mengikuti gaya filter EO dengan Reset.

Tabel lebar diganti kartu peserta: inisial/nama16px, posisi/timestamp12px, status,
subcard sesi berikon tanggal/jam, select kehadiran dan catatan opsional. Simpan
kehadiran sesi outline biru36px. Dua kolom sesi pada desktop, satu pada mobile.
Tindakan tutup seleksi dan completion menjadi dua kartu berdampingan desktop,
bertumpuk mobile. Copy rekap panjang, pengantar tentang waktu pencatatan dan
petunjuk geser tabel dihapus; copy konversi/penutupan/completion diringkas.
Peringatan final/cancelled, sesi belum mulai, alasan konversi, arsip legacy,
konfirmasi penutupan/penguncian absensi dan zero peserta tetap tersedia. Pesan
final cukup sekali di atas, catatan sesi readonly tetap. Tidak mengubah backend.

## Penyederhanaan keterangan detail pelamar - 15 November 2026

Pengguna meminta menghapus empat bagian pada screenshot. Detail pelamar EO tidak
lagi menampilkan tautan Pelaksanaan dan kehadiran pada kartu keputusan; kartu
kosong tanpa alasan/form/pesan seleksi tidak dirender. Form terima/tolak/undur,
alasan keputusan dan pesan batas seleksi tetap tersedia bila relevan. Footer
waktu evaluasi screening, keterangan snapshot matching dan paragraf pemisahan
nilai assessment pada detail EO dihapus. Skor/rincian/waktu kirim tetap ada.
Props default komponen bersama tetap true agar tampilan role lain tetap berlaku.

## Daftar dan detail pelamar EO - 15 November 2026

Persetujuan pengguna: dari Lihat Pelamar, Kembali menuju hub Kelola Pelamar;
detail lamaran kembali ke daftar kandidat event. Keduanya Kembali merah kiri
atas tanpa panah mengikuti detail kegiatan. Navigasi Semua Pelamar/Siap Ditinjau/
Pelaksanaan pada daftar dihapus. Status kandidat Siap Ditinjau tetap informasi
seleksi; query ready existing tetap didukung untuk tautan notifikasi/aktivitas.

Daftar memakai row putih: inisial kandidat, nama16px, lokasi/posisi berikon12px,
status ringkas, Match Score18px, waktu kirim WIB dan jumlah dokumen12px; Tinjau
lamaran outline biru tanpa panah. Filter posisi memakai gaya form EO dengan
Terapkan/Reset, count nyata dan empty state membedakan hasil filter. Detail
memakai nama20px, status/tanggal ringkas, screening/matching berdampingan desktop,
assessment terpisah, subcard profil/posisi berikon dan dokumen beraksi outline
biru. Keputusan seleksi diletakkan setelah informasi; Terima biru, Tolak/undur
outline merah. Tabel rinci tetap tersedia dengan scroll lokal pada mobile.
Komponen screening/matching/selection bersama dan perilaku role lain tidak diubah;
flash sukses memakai toast layout EO existing tanpa duplikat blok inline.

## Profil Organisasi EO - 15 November 2026

Pengguna menyetujui perapian Profil Organisasi mengikuti halaman EO sebelumnya.
Tombol Edit Profil memakai outline biru, teks biru dan ikon pensil36px. Informasi
dibagi tiga kartu putih: identitas/status, kontak penanggung jawab, dan dokumen
verifikasi. Identitas menampilkan simbol gedung biru, nama20px, status12px; tentang
organisasi berada dalam subcard slate. Label12px dan isi14px dengan ikon lokasi,
kota, website, penanggung jawab, telepon dan email. Detail ditata tiga kolom pada
desktop dan satu kolom mobile; teks panjang dapat membungkus. Lihat KTP outline
biru dengan ikon mata. Dialog edit mempertahankan form/konfirmasi verifikasi ulang,
memakai gaya EO, Batal outline merah serta area scroll terbatas tinggi viewport.
Catatan admin, seluruh data, empty state, upload dan preview privat dipertahankan.
Halaman pendaftaran profil baru dan pending existing tetap tersedia.

## Penyelarasan daftar EO dan Ubah Paket - 15 November 2026

Tombol Kembali merah berada di kiri atas halaman pilih/ubah paket, sebelum judul,
memakai komponen gaya dan tujuan detail kegiatan existing. Daftar Kelola Pelamar
menghapus badge Publikasi/Pelaksanaan; daftar Pelaksanaan menghapus badge
Publikasi/Jadwal. Metadata tanggal/jam/lokasi serta jumlah peserta tetap.
Pelaksanaan memakai form filter bersama seperti Kelola Pelamar: pencarian nama,
Tahap pelaksanaan (Semua/Hari ini/Berlangsung/Riwayat), Terapkan, Reset dan empty
state khusus hasil filter. Tidak mengubah aturan visibilitas atau absensi.

## Absensi terkunci setelah kegiatan Selesai - 15 November 2026

Pengguna menyetujui pencatatan absensi berhenti saat kegiatan ditandai Selesai.
Pada detail pelaksanaan completed tampil pesan Absensi sudah final dan tidak
dapat diubah; form simpan sesi/legacy dan alasan koreksi tidak dirender. Riwayat
status/waktu/catatan tetap terlihat. Checkbox completion yang sudah ada kini
menjelaskan absensi dikunci setelah selesai. Kegiatan cancelled tetap terkunci.
Tidak mendesain ulang halaman/tabel/filter atau menambah alur persetujuan baru.

## Kelola Pelamar dan pelaksanaan mengikuti tahap kerja - 15 November 2026

Persetujuan pengguna: Kelola Pelamar hanya kegiatan pernah published, gaya row
seperti Kegiatan Saya dengan thumbnail, ikon tanggal/jam/lokasi, count lamaran,
tombol outline biru tanpa panah. Badge publikasi/pelaksanaan dihapus sesuai
permintaan terbaru pengguna.
Form pencarian nama + Status rekrutmen (Semua/Terbuka/Ditutup/Riwayat), Terapkan,
Reset serta hasil kosong terpisah. Terbuka memakai waktu pendaftaran, ditutup
ketika deadline lewat; belum dibuka tetap melalui Semua. Riwayat tetap tersedia
untuk kegiatan selesai/dibatalkan/publikasi dihentikan.

Pelaksanaan & Kehadiran tampil sejak00.00 WIB pada tanggal acara, dikelompokkan
Hari ini/Berlangsung/Riwayat dalam kartu row dengan gaya sama. Badge Publikasi/Jadwal dihapus; kelompok tampilan tetap terlihat. Lewat jam
akhir tetapi belum completed diberi Menunggu penyelesaian. Kelompok tidak
mengubah state. Header menjelaskan jam muncul dan aturan kehadiran. Empty state
menjelaskan kegiatan published akan muncul pada hari pelaksanaan. Status draft
hanya berada di Kegiatan Saya; bukan hub pelamar/pelaksanaan.

## Toast notifikasi panel EO - 14 November 2026

Pengguna meminta notifikasi lebih modern dan sementara. Layout EO kini memakai
organizer-flash tersendiri; layout role lain tetap memakai flash existing.
Toast putih ringkas kanan atas, radius14px/shadow lembut, ikon hijau untuk
success, biru status/info, merah error; judul13px, pesan12px dan tombol tutup.
Success/status otomatis hilang5detik dengan transisi; hover/fokus menghentikan
waktu lalu mengulang saat keluar. Error dan daftar validasi tetap sampai ditutup/
dikoreksi agar pesan penting terbaca. Timer dibersihkan saat komponen dihancurkan.
Mobile top80px/width viewport minus40px, desktop top24px/right32px/max380px.
Koreksi teks memakai pesan Perubahan berhasil disimpan. Tanpa JS pesan tetap
terlihat. Tidak mengubah backend koreksi atau pesan modul lain.

## Batal pada koreksi teks kegiatan - 14 November 2026

Pengguna meminta Batal di sebelah Simpan Koreksi. Tautan outline merah di kanan
Simpan Koreksi kembali ke detail kegiatan #ringkasan tanpa submit/perubahan data.
Baris aksi flex-wrap agar tetap rapi di mobile. Form PATCH/koreksi existing tetap.

## Konsistensi detail kegiatan published - 14 November 2026

Pengguna meminta gaya detail published sama dengan draf: kartu/subkartu, ikon,
font, navigasi dan penempatan tetap bersama. Seluruh secondary link detail
(selain aksi merah) memakai gaya Edit outline biru minimum36px/padding6x12px.
Koreksi judul/deskripsi published di header Informasi kegiatan berlabel Edit
berpensil, aria-label/title menjelaskan cakupan koreksi. Tautan halaman publik
berikon external, kelola pelamar berikon users tanpa panah. Posisi read-only tidak
menyisakan baris aksi kosong. Semua syarat editable/published dan route tetap;
tidak membuka tambah/edit/hapus posisi, paket atau cover pada published.

## Border Kelola Asesmen biru - 14 November 2026

Permintaan pengguna: garis kotak Kelola Asesmen juga biru. Border kini #3b82f6,
hover #2563eb, selaras teks biru existing. Ukuran/latar/perilaku tetap.

## Label pembatalan sesuai fase kegiatan - 14 November 2026

Pengguna menyetujui label Batalkan draf untuk status draft yang belum pernah
diajukan/dipublikasikan, Batalkan kegiatan sesudahnya. Judul form mengikuti
Pembatalan draf/kegiatan, konfirmasi draf menjelaskan data tetap tersimpan.
Kalimat teknis tentang dependensi layanan lamaran/asesmen dihapus dari UI.
Pembatalan berbeda dari hapus draf; route, validasi, akses dan transisi tetap.

## Teks tautan Kelola Asesmen biru - 14 November 2026

Atas permintaan pengguna, teks tombol Kelola Asesmen pada detail menjadi biru
#1d4ed8, hover #1e40af; gaya sama saat label Lihat Asesmen. Ukuran/border/latar,
tujuan dan kondisi tombol tetap.

## Label Draf dan ikon pensil asesmen - 14 November 2026

Atas permintaan pengguna, status Draf asesmen pada ringkasan asesmen detail
menjadi Draf saja. Ikon status belum published diganti pensil agar berbeda
dari ikon clipboard utama. Published tetap gembok; belum dibuat tetap labelnya.
Tidak mengubah status tersimpan atau aturan asesmen.

## Ikon dan hierarki ringkasan asesmen posisi - 14 November 2026

Pengguna meminta kartu ringkasan asesmen lebih mudah dibaca. Nama posisi15px
semibold didampingi ikon clipboard biru dalam kotak38px. Status12px dengan ikon
file untuk draf/belum dibuat atau gembok untuk published/terkunci. Jumlah soal
berikon daftar dan durasi berikon jam pada baris metadata12px, gap16px dan bisa
membungkus. Semua ikon dekoratif; label/jumlah/durasi/kondisi serta tautan
Kelola/Lihat Asesmen tetap. Jarak status5px dan metadata10px memisahkan hierarki.

## Tombol Kelola Asesmen EO - 14 November 2026

Sesuai tiga screenshot pengguna: Kembali ke kegiatan menjadi tombol merah solid,
Hapus soal outline/teks merah, Tambah soal berikon plus16px dengan outline/teks
biru. Gunakan gaya tombol EO existing; pertahankan klik add/remove, batas50,
busy/disabled, kondisi editable dan tujuan anchor kembali ke asesmen kegiatan.

## Tombol posisi dengan ikon dan outline - 14 November 2026

Pengguna meminta Tambah posisi berikon plus, Edit posisi berikon pensil, teks
serta border keduanya biru. Keduanya memakai gaya Edit existing minimum36px,
padding6x12px/font13px dan ikon16px. Hapus posisi menjadi tombol outline merah,
teks merah, latar putih dan hover merah muda, ukuran setara Edit. Aksi bawah
sejajar, tautan/DELETE/CSRF/konfirmasi/kondisi editable tetap.

## Subkartu Informasi kegiatan bertumpuk - 14 November 2026

Pengguna meminta pola dua subkartu juga pada Informasi kegiatan, selalu atas/
bawah. Kelompok deskripsi/lokasi/pelaksanaan menjadi subkartu pertama; Jadwal
rekrutmen kedua, tetap di kartu utama. Latar slate50, border slate200, radius10,
padding16px sama seperti subkartu posisi. Ikon/font, Edit dan data tetap.

## Dua subkartu di dalam posisi - 14 November 2026

Pengguna meminta deskripsi dan jadwal dipisah menjadi dua kartu di dalam kartu
posisi utama (sesuai screenshot posisi). Nama/kuota tetap di atas. Subkartu kiri
Deskripsi & persyaratan berisi deskripsi, skill/level dan persyaratan tambahan;
kanan Jadwal posisi berisi mode jadwal dan seluruh slot WIB. Latar slate50,
border slate200, radius10px, padding16px. Desktop dua kolom, mobile bertumpuk.
Edit/hapus tetap di bawah dan mengikuti editable existing. Nilai/aturan tetap.

## Rekomendasi dimensi sampul detail EO - 14 November 2026

Keterangan di bawah Ubah/Tambah gambar ditambah rekomendasi 1600 x 900 px
(16:9), sama seperti form pembuatan. Format/ukuran berkas di baris pertama,
saran dimensi di baris kedua. Dimensi merupakan rekomendasi, bukan validasi baru.

## Keterangan format di bawah Ubah gambar - 14 November 2026

Permintaan pengguna: keterangan JPG/PNG/WebP maksimal5MB dikelompokkan tepat
di bawah tombol Ubah/Tambah gambar, rata kanan mengikuti tombol. Simpan/Batal
terpisah dari kelompok itu. Upload dan validasi tetap.

## Pilih gambar langsung dari detail - 14 November 2026

Pengguna meminta Ubah gambar langsung membuka pemilih file, tombol tidak menimpa
cover. Form inline detail memakai eventCoverUpload existing, tombol di bawah
sampul. Pilihan baru menjadi preview; Simpan/Batal muncul setelah ada file valid.
Simpan memakai POST multipart dengan method PATCH ke events.cover.update,
Batal mengembalikan preview tersimpan. Status menyimpan/disabled, pesan validasi
client/server, format JPG/PNG/WebP maks5MB. Tanpa JS ada tautan form edit.
Aturan editable tetap; published/terkunci tidak mendapat form. Ini menggantikan
overlay/link Ubah gambar ke form edit yang disetujui sebelumnya.

## Edit dengan pensil dan akses penggantian sampul - 14 November 2026

Pengguna meminta Edit sedikit lebih besar, ikon pensil, dan gambar dapat diganti.
Tombol kini minimum36px/padding6x12px/font13px, pensil16px, outline/teks biru.
Pada sampul detail yang editable tersedia Ubah gambar kanan bawah; tanpa sampul
tersedia Tambah gambar. Tautan menuju form edit existing di #cover_image dengan
offset mobile/desktop. Form sudah multipart dan memakai event-cover-upload
(preview, ganti/hapus, validasi JPG/PNG/WebP maks5MB). Aturan editable existing
tetap, termasuk larangan mengganti sampul setelah publikasi. Tidak mengubah
alur koreksi judul/deskripsi published.

## Informasi kegiatan EO tanpa bar status - 14 November 2026

Disetujui pengguna: hapus bar Moderasi/Publikasi/Pelaksanaan pada kartu Informasi
kegiatan. Status di daftar/menu lain dan aturan akses tetap. Metadata kartu
memakai ikon dokumen, pin, jam, kalender, clipboard dan centang (dekoratif),
label12px abu slate, isi14px, judul18px, subjudul Jadwal rekrutmen14px. Jarak
antar informasi24px; deskripsi line-height1.8. Rentang waktu mulai/akhir tetap,
tanggal dan jam dipisah baris; semua jam WIB. Kondisi belum diisi tetap eksplisit.
Edit kecil outline biru, catatan admin dan form/bagian lain tetap tersedia.

## Ukuran tombol Edit diringkas - 14 November 2026

Permintaan pengguna: kotak Edit terlalu besar. Tombol Informasi kegiatan kini
min-height32px, padding4x10px, font12px, radius6px, align-self center agar tidak
meregang mengikuti baris grid. Warna biru serta tujuan/aturan edit tetap.

## Tombol Edit informasi kegiatan - 14 November 2026

Pengguna meminta tombol Edit konfigurasi kegiatan pada kartu Informasi kegiatan
berlabel Edit saja, teks biru #1d4ed8, border biru #3b82f6 dan latar putih.
Hover biru muda, fokus keyboard biru; aksesibilitas memakai label Edit informasi
kegiatan. Posisi/tujuan/aturan editable tetap; tombol lain tidak berubah.

## Sebaran navigasi detail EO - 14 November 2026

Permintaan pengguna: rata kanan kiri. Tombol navigasi bagian kini flex-grow1
sehingga mengisi lebar panel sampai kedua sisi; teks tetap terpusat pada tiap
tombol. Layar sempit tetap horizontal scroll, tanpa memotong label. Anchor dan
highlight existing tetap.

## Navigasi bagian detail kegiatan EO - 14 November 2026

Pengguna meminta deretan Ringkasan/Posisi & jadwal/Asesmen/Koordinasi peserta/
Paket & pembayaran/Tindakan diperjelas sebagai navigasi. Kini panel putih
border tipis radius12 dengan tombol radius8, warna biru muda/teks biru pada
bagian aktif. Label, urutan dan anchor existing tetap; semua bagian masih
terlihat. Panel sticky16px desktop/80px mobile, horizontal scroll pada layar
sempit; fokus keyboard jelas. Anchor punya offset100px desktop/164px mobile.
Alpine memberi aria-current=location berdasarkan klik, hash dan scroll; klik
mempertahankan pilihan sesaat agar bagian pendek dekat akhir tidak langsung
mengalihkan highlight. Tautan anchor tetap berfungsi tanpa JavaScript.

## Tombol Kembali merah - 14 November 2026

Atas permintaan pengguna, Kembali pada detail kegiatan EO memakai latar dan
border merah #dc2626, teks putih, hover #b91c1c dan fokus merah. Ukuran,
posisi dan tujuan tautan tetap. Ketentuan warna biru sebelumnya digantikan.

## Ringkas area cover detail EO - 14 November 2026

Pengguna meminta kalimat tepat di atas/bawah gambar dihapus. Deskripsi header
Persetujuan admin, pembayaran dan publikasi merupakan tahap yang berbeda serta
lima langkah konfigurasi di bawah cover dihapus pada detail kegiatan. Judul
kegiatan, Kembali, cover dan navigasi bagian tetap. Ini menggantikan ketentuan
stepper detail sebelumnya; aturan moderasi/pembayaran/publikasi tetap terpisah.

## Identitas dan logout di sidebar EO - 14 November 2026

Pengguna meminta akun/nama dan role dipindah di atas Ringkasan Aktivitas,
dengan jarak serta garis pemisah seperti setelah Profil Organisasi. Tombol
Keluar pindah tepat di bawah Lihat situs utama, latar merah transparan dan
teks/ikon merah muda yang terbaca pada sidebar navy. Bar akun desktop dihapus,
konten berjarak atas32px. Mobile menyisakan bar tombol navigasi dan judul agar
drawer tetap dapat diakses. Nama dinamis dari akun aktif, bukan hardcode cahyo.
Dropdown lama tidak diperlukan; Profil Organisasi tetap tersedia di sidebar.
Tidak ada footer di layout existing; bagian yang dimaksud diterapkan pada bar
akun atas sesuai penjelasan saat pengerjaan. Logout tetap POST dengan CSRF.

## Header daftar dihapus dan tombol tambah diperbesar - 14 November 2026

Pengguna meminta seluruh baris Semua kegiatan beserta badge jumlah dihapus.
Daftar kini langsung menampilkan baris kegiatan setelah filter. Buat Kegiatan
memakai font 16 px, padding 12x24 px serta ikon plus dekoratif 20 px di kiri.
Modal, pencarian/filter dan isi daftar tetap. Ini menggantikan ketentuan judul/
badge jumlah sebelumnya pada halaman Kegiatan Saya.

## Pencarian dan filter moderasi EO - 14 November 2026

Disetujui pengguna: form GET di atas Semua kegiatan dengan pencarian nama,
status moderasi (Semua, Draf, Menunggu moderasi, Disetujui, Ditolak), Terapkan,
dan Reset saat aktif. Desktop satu baris, mobile bertumpuk, label terlihat.
Jumlah mengikuti hasil; pagination menyimpan filter. Hasil kosong punya pesan
tersendiri. Filter tidak mencampur publikasi/pelaksanaan.

## Header daftar kegiatan dipersingkat - 14 November 2026

Atas permintaan pengguna, kalimat penjelasan di bawah Semua kegiatan dihapus.
Judul, badge jumlah, informasi kegiatan, status dan aksi tetap.

## Hierarki informasi dan ikon daftar kegiatan EO - 14 November 2026

Permintaan pengguna: teks daftar kegiatan terasa bertumpuk. Judul kini 17 px
(desktop)/16 px (mobile), metadata tanggal/jam/lokasi 12 px dengan warna sekunder.
Tanggal dan jam dipisahkan menjadi dua kelompok berikon kalender/jam pada satu
baris yang bisa membungkus; lokasi berikon pin di baris berikutnya. Ikon 15 px,
jarak metadata 6 px, jarak ke judul 8 px. Badge status tetap 11 px dan warna yang
sudah disetujui; thumbnail, tombol outline dan modal tetap. Waktu tetap WIB.

## Menu Pelaksanaan & Kehadiran EO - 14 November 2026

Pengguna menyetujui menu sidebar tersendiri setelah Kelola Pelamar. Menu membuka
daftar kegiatan milik EO: judul, jadwal WIB, jumlah peserta accepted, publikasi/
pelaksanaan dan Kelola pelaksanaan (outline biru kecil). Kegiatan kosong dan
pagination tersedia. Menu aktif mencakup daftar/detail pelaksanaan. Detail
pelaksanaan kembali ke daftar ini. Kartu navigasi Pelaksanaan dan kehadiran di
atas detail event dihapus; konfigurasi dan isi detail lainnya tetap.

## Header dan navigasi atas detail kegiatan - 14 November 2026

Permintaan pengguna: header atas detail selalu Kegiatan Saya; nama kegiatan tetap
menjadi judul konten/tab browser. Layout EO mendukung section topbar-title opsional,
dengan fallback judul existing pada halaman lain. Semua kegiatan berubah menjadi
tombol biru Kembali tanpa panah menuju daftar existing. Hanya tautan Pelamar di
kartu navigasi atas dihapus karena sudah ada menu Kelola Pelamar. Pelaksanaan dan
kehadiran di kartu tersebut tetap sementara penempatan barunya dibahas.

## Ukuran badge daftar kegiatan dikembalikan - 14 November 2026

Sesuai permintaan pengguna, badge status Kegiatan Saya kembali ke ukuran dasar
organizer-status: 11 px, padding 4x8 px, radius 6 px. Override ukuran 10 px dihapus.
Moderasi hijau, Publikasi biru, Pelaksanaan kuning serta tombol outline tetap.

## Kelola kegiatan memakai outline biru - 14 November 2026

Pengguna menyetujui alternatif: Kelola kegiatan berlatar putih, border biru tipis
#93C5FD dan teks biru #1D4ED8; hover biru sangat muda. Ukuran kecil tanpa panah
serta target mobile tetap. Buat Kegiatan tetap biru solid sebagai aksi utama.
Perubahan hanya daftar Kegiatan Saya; tombol di halaman lain tetap.

## Koreksi warna publikasi pada daftar EO - 14 November 2026

Pengguna mengoreksi warna: Publikasi biru muda/teks biru (#EFF6FF/#1D4ED8),
Moderasi tetap hijau, Pelaksanaan kuning. Ukuran dan label status tetap. Tombol
Kelola kegiatan masih biru solid; alternatif outline sedang dibahas, belum diterapkan.

## Badge dan aksi Kegiatan Saya - 14 November 2026

Atas permintaan pengguna, jumlah kegiatan memakai badge kecil yang sama dengan
jumlah tindakan. Kelola kegiatan memakai tombol biru kecil tanpa panah (12 px,
36 px desktop, target mobile 44 px), lebih kecil dari Buat Kegiatan existing.
Badge daftar kegiatan 10 px/padding 3x6 px: konteks Moderasi dan Publikasi hijau,
Pelaksanaan kuning, dengan teks status/konteks lengkap tetap tampil. Ini warna
kategori presentasi daftar ini, bukan perubahan arti/status: Draf tidak menjadi
Disetujui, Belum dipublikasikan tetap berbeda dari Dipublikasikan. Halaman detail
dan komponen status bersama tetap memakai warna existing.

## Logo sidebar EO mengikuti landing page - 14 November 2026

Sesuai permintaan pengguna, kotak huruf S di sidebar EO diganti simbol biru empat
kelopak dari public-brand landing page, berukuran 36 px. Komponen public-brand
mendukung markOnly opsional (default false); landing/auth/footer tetap menampilkan
simbol dan teks existing. Sidebar memakai simbol saja di samping SKILLMATCH dan
Panel Organisasi existing. Route/ukuran sidebar tetap, tanpa aset/dependensi baru.

## Hierarki judul kelompok tindak lanjut - 14 November 2026

Permintaan terbaru: Persiapan dan rekrutmen serta Pelaksanaan menjadi 16 px,
lebih besar dari nama kegiatan (14 px) dan lebih kecil dari Perlu Ditindaklanjuti
(18 px). Warna tetap slate-900/hitam lembut (#0F172A), bobot 600; titik biru/hijau
serta badge jumlah tetap. Hanya ukuran judul kelompok yang berubah.

## Kartu tindak lanjut putih dengan penanda titik - 14 November 2026

Pengguna menyetujui arah baru: kedua kartu putih, judul gelap dengan titik biru
untuk Persiapan dan rekrutmen serta titik hijau untuk Pelaksanaan. Jumlah tindakan
yang ditampilkan kini di samping judul tiap kartu, menggantikan badge gabungan.
Blok warna kepala dan garis di bawah judul dihapus; pemisah hanya antarkegiatan.
Tombol biru kecil, dua kartu, judul luar, warna statistik dan alur tetap. Keadaan
kosong tiap kartu tetap eksplisit. Keputusan ini menggantikan kepala kuning penuh
pada catatan sebelumnya.

## Kepala kartu tindak lanjut kuning menyatu - 14 November 2026

Klarifikasi pengguna: warna mengisi seluruh kepala kartu hingga sisi kiri/kanan
serta atas, mengikuti sudut kartu; kedua judul memakai kuning sangat muda #FFFEF7 dengan
teks keemasan #A16207. Menggantikan strip judul inset biru/kuning sebelumnya.
Isi kartu tetap putih. Padding kepala sejajar isi pada desktop/mobile; judul,
angka statistik, tombol biru, data dan alur tetap.

## Baris judul tindak lanjut berwarna lembut - 14 November 2026

Atas permintaan pengguna, baris Persiapan dan rekrutmen memakai latar biru sangat
muda #EFF6FF dan teks biru #1D4ED8; Pelaksanaan memakai kuning sangat muda #FEFCE8
dan teks kuning keemasan #A16207. Aksen hanya pada baris judul di dalam kartu,
dengan padding 10/12 px dan radius 7 px. Isi kartu putih, tombol serta data tetap.

## Aksen statistik lebih tegas - 14 November 2026

Permintaan terbaru: border Total Kegiatan biru #60A5FA, Kegiatan Aktif hijau
#34D399, Lamaran Belum Ditinjau kuning cerah #FACC15. Judul lamaran memakai
kuning keemasan #A16207 agar tetap terbaca pada putih; ikon kuning #CA8A04 dengan
latar #FEF9C3. Border/ikon ungu dan susunan halaman yang disetujui tetap.

## Statistik dan dua kartu tindak lanjut EO - 14 November 2026

Permintaan terbaru: border Total Pelamar ungu lebih jelas (#C084FC), judul Total
Kegiatan biru tua, Kegiatan Aktif hijau tua, Total Pelamar ungu tua, selaras judul
amber pada Lamaran Belum Ditinjau. Angka tetap hitam. Perlu Ditindaklanjuti kini
judul di luar kartu seperti Notifikasi terbaru; dua kartu putih terpisah dan
bertumpuk untuk Persiapan dan rekrutmen serta Pelaksanaan. Tiap kartu memiliki
keadaan kosong. Tombol biru solid, tanpa panah, font 12 px/tinggi 36 px desktop;
mobile mempertahankan target 44 px. Data, kondisi dan route tujuan tetap.

## Panel Perlu Ditindaklanjuti - 14 November 2026

Pengguna menyetujui usulan daftar baris ringkas. Panel memakai satu judul Perlu
Ditindaklanjuti, badge jumlah tindakan, kelompok Persiapan dan rekrutmen serta
Pelaksanaan. Nama kegiatan gelap, keterangan singkat di bawah dan tombol aksi
kanan; mobile tombol turun di bawah. Identitas organisasi/kontak/status verifikasi
dan judul Prioritas Anda dihapus dari panel. Kelompok kosong tidak ditampilkan;
panel kosong menampilkan Belum ada tindak lanjut kegiatan. Tombol mengarah ke
detail, pelamar atau pelaksanaan existing sesuai tindak lanjut. Warna statistik
yang telah disetujui tetap. Bukti: [handoff EO](../docs/ORGANIZER_UI_REFINEMENT_HANDOFF.md).

## Warna border statistik EO - 14 November 2026

Sesuai gambar pengguna: border Total Kegiatan biru muda, Kegiatan Aktif hijau
muda, Total Pelamar ungu muda dengan ikon/latar ikon ungu. Border amber Lamaran
Belum Ditinjau tetap. Hanya aksen warna; layout, angka dan panel Prioritas Anda
tetap. Panel Prioritas Anda masih dibahas, belum disetujui desain penggantinya.

## Koreksi Ringkasan dan menu pelamar - 14 November 2026

Keputusan pengguna terbaru menggantikan susunan Ringkasan EO pada bagian di bawah:
hapus panel Pelamar Terbaru, panel Kegiatan Saya dan tombol Buat Kegiatan dari
Ringkasan Aktivitas. Urutan menjadi sambutan, empat statistik existing, Prioritas
Anda/tindak lanjut, lalu notifikasi. Buat Kegiatan tetap tersedia di Kegiatan Saya.
Sidebar menambah Kelola Pelamar di antara Kegiatan Saya dan Profil Organisasi.
Menu membuka daftar kegiatan milik EO, jumlah lamaran terkirim dan tautan Lihat
pelamar ke halaman kandidat per kegiatan existing. Tidak mendesain ulang halaman
lain dalam koreksi ini. Bukti: [handoff EO](../docs/ORGANIZER_UI_REFINEMENT_HANDOFF.md).

## Panel EO ringkas - 14 November 2026

Pengguna menyetujui perapian tampilan EO dari brainstorming pada branch baru.
Sidebar 256 px dengan offset header/konten selaras; Ringkasan Aktivitas menempatkan
tindak lanjut sebelum statistik; Kegiatan Saya menjadi daftar baris dengan
thumbnail kecil. Detail kegiatan memiliki tautan bagian Ringkasan, Posisi/jadwal,
Asesmen, Koordinasi, Paket/pembayaran dan Tindakan. Form koordinasi dipindahkan
setelah konfigurasi posisi/asesmen, tetap terbuka dan dapat disimpan.

Label status moderasi, publikasi dan pelaksanaan memakai bahasa Indonesia dan
konteks terpisah. Status lamaran memakai komponen application-status existing.
Kegiatan/draf/asesmen diselaraskan pada label Organizer terkait. Warna navy/biru,
Inter, data dinamis, route dan alur existing tetap. CSS hanya dalam organizer-panel;
perubahan ini menggantikan proporsi/layout EO FigCraft sebelumnya dalam lingkup
persetujuan terbaru. Bukti dan batas: [handoff EO](../docs/ORGANIZER_UI_REFINEMENT_HANDOFF.md).

## Profil ringkas dan usia — 14 November 2026

Permintaan pengguna: rapikan form dan tambah Usia pada Identitas dan Kontak.
Usia readonly otomatis dari tanggal lahir, tanpa data usia terpisah. Tiga kolom
tanggal lahir/usia/gender desktop, bertumpuk mobile. Form/skill lebih padat.
Bukti: [handoff usia](../docs/VOLUNTEER_PROFILE_AGE_HANDOFF.md).

## Panel penarikan ringkas — 14 November 2026

Disetujui pengguna: satu kartu tanpa lapisan di dalam, judul Tarik lamaran,
peringatan final rose, alasan opsional lebih pendek dan tombol merah.
Bukti/pemulihan: [handoff panel](../docs/VOLUNTEER_WITHDRAWAL_REFINE_HANDOFF.md).

## Detail lamaran ringkas — 14 November 2026

Permintaan pengguna: kartu/banner/timeline lebih padat selaras halaman lain.
CSS scoped seluruh detail; dokumen desktop aksi kanan, mobile bertumpuk.
Informasi, rincian terbuka dan alur existing dipertahankan.
Bukti/batas: [handoff detail ringkas](../docs/VOLUNTEER_APPLICATION_DETAIL_COMPACT_HANDOFF.md).

## Riwayat Partisipasi ringkas — 14 November 2026

Pengguna menyetujui kartu/heading lebih padat, badge sesuai teks, deskripsi
singkat. Sesuai koreksi pengguna, akses detail cukup melalui judul biru. Penjelasan Belum dicatat hanya pada hasil
terkait. Data dan rincian sesi existing tetap.
Bukti: [handoff riwayat ringkas](../docs/VOLUNTEER_HISTORY_COMPACT_HANDOFF.md).

## Dashboard ringkas — 14 November 2026

Pengguna menyetujui perapian ukuran/jarak kartu dashboard dengan backup desain
sebelumnya. Sambutan/statistik lebih padat, status memakai label Indonesia dan
warna komponen lamaran. Statistik Lamaran Terkirim sesuai query existing.
Bukti/pemulihan: [handoff dashboard](../docs/VOLUNTEER_DASHBOARD_COMPACT_HANDOFF.md).

## Sidebar Volunteer ringkas — 14 November 2026

Disetujui pengguna: lebar 256 px, padding identitas/menu lebih padat dan offset
navbar/konten disesuaikan. Warna, teks, ikon dan navigasi dipertahankan.
Bukti/batas: [handoff sidebar](../docs/VOLUNTEER_SIDEBAR_COMPACT_HANDOFF.md).

## Kepadatan Lamaran Saya — 14 November 2026

Atas permintaan pengguna, kartu lebih ringkas: padding/jarak dan judul dikurangi,
badge mengikuti teks, tombol lebih sempit dengan target 44 px tetap. Sidebar
dan warna dipertahankan. Tidak memakai zoom halaman.
Bukti: [handoff compact](../docs/VOLUNTEER_APPLICATIONS_COMPACT_HANDOFF.md).

## Panel Admin mengikuti FigCraft — 12 November 2026

Sesuai permintaan lanjutan pengguna, tampilan Admin mengikuti 17 frame pada
`Electric Blue / 01 Admin` di `skillmatch-electric-blue-system.figcraft`.
Struktur halaman, navigasi bertingkat dan modal existing dipertahankan: sidebar
navy #0F172A, menu aktif abu-abu transparan, header/kartu putih tanpa bayangan,
aksen #2563EB dan tindakan destruktif utama berwarna peach #FB923C.
Filter akun tersusun vertikal pada mobile. Badge tetap memiliki label status.

Grafik memakai data/pemilihan seri existing dengan garis biru lurus, titik penuh,
grid horizontal dan tanpa legend yang mengulang judul seri. Angka contoh desain
tidak disalin ke runtime. Nama posisi pada verifikasi membaca `EventPosition.name`
yang sudah ada, menggantikan binding view `position_name` yang menghasilkan kosong.
CSS terbatas `admin-panel`; backend, route, field form dan role lain tetap.
PRD/kontrak/brief dan README setup tetap berlaku. Bukti/batas pemeriksaan:
[handoff Admin](../docs/ADMIN_FIGCRAFT_DESIGN_HANDOFF.md).

## Panel Organizer mengikuti FigCraft — 12 November 2026

Keputusan pengguna: ubah hanya tampilan role Organizer berdasarkan halaman
`Electric Blue / 02 Organizer` pada `skillmatch-electric-blue-system.figcraft`,
di branch baru. Sidebar navy #0F172A, menu aktif/tombol biru #2563EB, Inter,
header putih, latar #F8FAFC, kartu putih tanpa bayangan dan kontrol membulat.
Ringkasan status/jadwal, posisi dan paket pada detail event ditata berkelompok;
detail pembayaran dua kolom pada desktop dan satu kolom pada mobile.
Keputusan ini mengatasi pedoman visual Organizer lama dalam dokumen ini.

Komponen bersama tetap dipakai dengan CSS terbatas pada `organizer-panel`.
Field, route, method/CSRF, Alpine, aturan/validasi, status dan data dinamis tetap.
Data contoh di FigCraft tidak dipasang ke aplikasi. Tampilan role lain tetap.
PRD/kontrak dan README setup tetap berlaku. Bukti/batas pemeriksaan:
[handoff Organizer](../docs/ORGANIZER_FIGCRAFT_DESIGN_HANDOFF.md).

## Notifikasi Volunteer — 11 November 2026

Permintaan pengguna: halaman Semua notifikasi mengikuti panel navy/biru.
Kartu putih, kembali ke Aktivitas, keadaan kosong berikon, penanda read/unread,
tanggal WIB dan aksi detail/baca. Role lain dan ringkasan dashboard tetap.
Bukti/batas: [handoff notifikasi](../docs/VOLUNTEER_NOTIFICATIONS_DESIGN_HANDOFF.md).

## Detail event publik disamakan dengan panel — 11 November 2026

Sesuai pengguna, /events/{event} memakai konten detail yang sama dengan panel
Volunteer: kartu dua kolom, ringkasan, badge, persyaratan dan tombol biru.
Navbar/footer publik tetap. Partial bersama events/partials/detail memakai
catalogRoute sesuai konteks. Guest/otorisasi/alur lamaran existing dipertahankan.
Bukti: [handoff publik](../docs/PUBLIC_EVENT_DETAIL_DESIGN_HANDOFF.md).

## Perapian detail event Volunteer — 11 November 2026

Disetujui pengguna: layout dua kolom, deskripsi/posisi kiri, ringkasan jadwal
dan lokasi kanan. Skill/kuota berupa badge, persyaratan terpisah, tombol biru
normal case. Mobile satu kolom. View publik dan aturan lamaran dipertahankan.
Bukti/batas: [handoff detail event](../docs/VOLUNTEER_EVENT_DETAIL_DESIGN_HANDOFF.md).

## Detail event dalam panel Volunteer — 11 November 2026

Sesuai persetujuan lanjutan pengguna, gambar/judul/CTA katalog panel menuju
volunteer.events.show dengan sidebar dan tautan kembali ke katalog panel.
Isi posisi/persyaratan serta alur melamar existing dipakai ulang.

## Koreksi Jelajahi Event dalam panel — 11 November 2026

Sesuai pengguna, menu menuju /volunteer/events dengan sidebar Volunteer, filter,
kartu event dan pagination. Katalog /events publik tetap. Detail event masih
menggunakan events.show existing. Lingkup hanya halaman jelajah dalam panel.

## Navigasi Jelajahi Event Volunteer — 11 November 2026

Persetujuan pengguna: menu Jelajahi Event di sidebar langsung menuju events.index
(/events), sebelum Lihat Website Utama. Gaya menu existing dipertahankan.
Bukti/batas: [handoff navigasi](../docs/VOLUNTEER_EXPLORE_EVENT_HANDOFF.md).

## Kegiatan dibatalkan — desain SVG, 11 November 2026

Mengikuti Kegiatan dibatalkan.svg (1440 × 1568): banner abu-abu, kartu putih,
status/tanggal/alasan, jadwal posisi dan rincian terbuka. Pembatalan tetap
menghentikan proses; data, dokumen dan otorisasi existing dipertahankan.
Bukti/batas: [handoff pembatalan](../docs/VOLUNTEER_CANCELLED_DESIGN_HANDOFF.md).

## Lamaran ditarik — desain SVG, 11 November 2026

Mengikuti Lamaran ditarik.svg (1440 × 1568): banner netral, kartu putih,
status/waktu/alasan kanan, rincian screening/assessment dan waktu event terbuka.
Withdrawn tetap final, dokumen hanya-baca dan alasan di-escape. Data nyata.
Bukti/batas: [handoff ditarik](../docs/VOLUNTEER_WITHDRAWN_DESIGN_HANDOFF.md).

## Riwayat Partisipasi — desain SVG, 11 November 2026

Mengikuti Riwayat Partisipasi.svg (1440 × 1000): sidebar navy/menu biru,
kartu putih, tautan judul biru, badge kanan, kotak waktu/hasil dan catatan amber.
Partial kartu terpisah; data, status dan rincian sesi existing dipertahankan.
Hasil sukses hanya dari attendance aktual, cancelled bukan sukses.
Bukti/batas: [handoff riwayat](../docs/VOLUNTEER_HISTORY_DESIGN_HANDOFF.md).

## Detail lamaran: penarikan — 11 November 2026

Desain disetujui melalui Penarikan pada detail lamaran.svg. Status under_review
memakai kartu detail putih, rincian dan panel penarikan terbuka, alasan opsional
dan tombol merah. Data serta aturan penarikan existing dipertahankan.
Bukti dan batas: [handoff penarikan](../docs/VOLUNTEER_WITHDRAWAL_DESIGN_HANDOFF.md).

## Lamaran Diterima — desain FigCraft, 9 November 2026

Referensi `Lamaran diterima.svg` (1440 x 1662), detail Volunteer accepted pada
event tidak dibatalkan. Lokal tanpa commit/push. Shell navy, banner emerald,
timeline penerimaan dengan rincian terbuka, dokumen, koordinasi peserta, kehadiran
per sesi, jadwal dan informasi posisi mengikuti SVG. Kehadiran dipisah sebagai
partial, memakai rekap/sesi existing. Mobile mempertahankan koordinasi/kehadiran
lebih awal sesuai urutan operasional existing.

Status, tanggal WIB, screening/assessment, grup/PIC dan absensi berasal dari data
nyata. Koordinasi tetap melalui layanan terotorisasi; tidak memasang grup/PIC
contoh SVG. Tanpa data grup/kehadiran menampilkan keadaan kosong existing.
Event dibatalkan tidak memakai banner penerimaan hijau. Tidak mengubah Policy,
route, schema, transisi atau layanan. PRD/kontrak tetap berlaku. Bukti/batas:
[handoff diterima](../docs/VOLUNTEER_ACCEPTED_DESIGN_HANDOFF.md).
## Assessment Gagal Simpan — desain FigCraft, 9 November 2026

Referensi `Assessment · gagal simpan.svg` (1440 x 1000) memakai layout attempt
yang sudah diterapkan, dengan pesan kegagalan pada baris status di bawah timer/
hitungan jawaban. Pesan existing tetap kondisional sesuai hasil request; tidak
dijadikan error statis atau banner baru. Pilihan lokal/revision/queue dan retry
menggunakan implementasi assessment-attempt.js existing tanpa perubahan.

Perbaikan responsif: pada layar kecil timer/footer tidak sticky agar pesan
panjang dan footer dua tombol tidak menutupi navigasi sebelumnya/berikutnya.
Selector scoped diperkuat agar mengalahkan utility sticky. Desktop tetap sama.
Lokal tanpa commit/push. PRD/kontrak tetap berlaku. Bukti/batas pemeriksaan:
[handoff gagal simpan](../docs/VOLUNTEER_ASSESSMENT_SAVE_FAILURE_HANDOFF.md).
## Kerjakan Assessment — desain FigCraft, 9 November 2026

Referensi `Kerjakan assessment.svg` (1440 x 1000). Halaman attempt Volunteer
memakai shell navy, header, kartu timer/status penyimpanan, soal kiri, navigasi
nomor kanan, tombol sebelumnya/berikutnya dan footer simpan/kirim sesuai SVG.
Status terjawab/aktif/fokus tetap terlihat. Mobile memakai tombol daftar soal
yang dapat dibuka/tutup dan footer tombol penuh. Timer tidak sticky pada mobile
kecil agar tidak menutupi soal; desktop mempertahankan sticky existing.

Bagian status, navigasi, panel soal dan footer dipisah menjadi partial Blade.
Semua x-data/x-ref/event/nama input, radio terpilih, revision, timer, autosave,
konfirmasi kirim dan fallback tanpa JavaScript dipertahankan. JavaScript/service
assessment tidak berubah; tidak ada soal/jawaban/timer contoh SVG dipasang ke data
aplikasi. PRD/kontrak tetap berlaku. Lokal tanpa commit/push. Bukti/batas uji:
[handoff pengerjaan](../docs/VOLUNTEER_ASSESSMENT_WORK_DESIGN_HANDOFF.md).
## Sebelum Assessment — desain FigCraft, 9 November 2026

Referensi `Sebelum assessment.svg` (1440 x 1000), halaman konfirmasi GET assessment
sebelum attempt dimulai. Lokal tanpa commit/push. Shell navy, konten tengah
maksimal 768 px, kartu putih, tiga ringkasan (jumlah soal, durasi, satu kesempatan),
perkiraan batas akhir WIB, ketentuan bernomor, garis pemisah dan tombol Kembali/
Mulai mengikuti SVG. Mobile menyusun ringkasan dan tombol vertikal.

Summary, rules dan actions terpisah menjadi partial. Jumlah soal/durasi dan
expires_at tetap dari AssessmentService/Controller existing, bukan contoh SVG.
POST start, CSRF dan confirmed=1 tetap; GET tidak memulai attempt. Ketentuan satu
kali, durasi/tenggat terpendek, waktu tetap berjalan, otorisasi dan server timer
tidak berubah. Halaman pengerjaan assessment belum didesain ulang pada tahap ini.
PRD/kontrak tetap berlaku. Bukti/batas: [handoff konfirmasi](../docs/VOLUNTEER_ASSESSMENT_CONFIRM_DESIGN_HANDOFF.md).
## Koreksi shell detail semua status — 9 November 2026

Koreksi pengguna: sidebar masih oranye ketika membuka Lihat Detail untuk status
yang belum memiliki desain konten, misalnya assessment_pending. Sidebar/navbar
navy/biru kini mengikuti kelas dasar volunteer-application-detail pada seluruh
status detail lamaran, tanpa bergantung pada kelas desain konten per status.
Layout/isi halaman untuk status lain tetap existing; koreksi ini hanya shell.
Aktivitas, profil, daftar lamaran dan role lain mempertahankan tampilannya.
Bukti: [handoff koreksi sidebar](../docs/VOLUNTEER_DETAIL_SIDEBAR_FIX.md).
## Detail Rejected — desain FigCraft, 9 November 2026

Referensi `Lamaran · Rejected.svg` (1440 x 1568) untuk detail lamaran Volunteer
berstatus rejected, lokal tanpa commit/push. Shell navy/biru, banner rose,
timeline dengan keputusan Belum terpilih, panel Hasil seleksi rose berisi waktu
WIB/catatan, jadwal posisi, informasi posisi dan dokumen mengikuti SVG.
Rincian screening, assessment dan Waktu event terbuka default, dapat dilipat.
Catatan organizer tetap di-escape sebagai teks; contoh SVG tidak menjadi data.

Komponen detail existing dan CSS scoped yang sudah disetujui dipakai ulang.
Keputusan, tanggal, catatan, hasil screening/assessment dan dokumen tetap dari
data nyata, tanpa perubahan schema/route/Policy/service/transisi. Tidak memberi
aksi kirim ulang, upload/hapus, penarikan atau mulai assessment pada status ini.
PRD/kontrak tetap berlaku. Bukti dan batas uji:
[handoff Rejected](../docs/VOLUNTEER_REJECTED_DESIGN_HANDOFF.md).
## Detail Screening Gagal — desain FigCraft, 9 November 2026

Referensi `Lamaran · Screening gagal.svg` (1440 x 1759) berlaku khusus status
screening_failed pada detail Volunteer. Lokal tanpa commit/push. Shell navy,
banner rose, indikator timeline gagal, alasan hasil screening terbuka, panel
status kanan, jadwal posisi, informasi posisi dan dokumen mengikuti SVG.
Rincian assessment serta Waktu event terbuka; assessment tetap tidak tersedia.
Komponen existing dipakai ulang dan alasan berasal dari checks_json, bukan SVG.

CSS dasar detail dibagikan dengan Draft/Submitted dan CSS rose khusus dipisah.
Status/alasan, endpoint, Policy, gate screening, formula, snapshot dan privasi
file tidak berubah. Tidak ada aksi upload/hapus/kirim ulang/penarikan untuk status
terminal ini. PRD/kontrak tetap berlaku. Bukti dan batas uji:
[handoff Screening Gagal](../docs/VOLUNTEER_SCREENING_FAILED_DESIGN_HANDOFF.md).
## Detail Lamaran Submitted — desain FigCraft, 9 November 2026

Referensi `Lamaran · Submitted.svg` (1440 x 1946), diterapkan pada detail
Volunteer ketika status submitted. Lokal tanpa commit/push. Shell navy/biru dan
layout dua kolom memakai komponen detail existing. Banner menunggu evaluasi atau
pencocokan mengikuti hasil screening nyata. Rincian screening, assessment,
Waktu event dan penarikan terbuka secara default, tetap dapat dilipat. Tombol
penarikan merah; dokumen setelah submit tetap hanya-baca. Angka assessment,
tenggat dan hasil screening tidak diambil dari contoh SVG.

CSS spesifik Submitted terpisah; bagian dasar detail dibagikan dengan Draft.
Status lain tidak didesain ulang. Tidak mengubah rumus, transisi, gate, Policy,
route, schema, service, privasi dokumen atau sifat final penarikan. PRD/kontrak
tetap berlaku. Bukti dan batas pemeriksaan:
[handoff Submitted](../docs/VOLUNTEER_APPLICATION_SUBMITTED_DESIGN_HANDOFF.md).
## Lengkapi Lamaran — desain FigCraft, 9 November 2026

Referensi pengguna `Lengkapi lamaran.svg` (1440 x 1808) diterapkan khusus detail
lamaran berstatus draft, `/volunteer/applications/{application}`. Lokal tanpa
commit/push. Shell navy/biru, tombol kembali, header, banner draft, dua kolom
perkembangan/dokumen dan pengumuman/jadwal/informasi posisi mengikuti SVG.
Dokumen tersusun vertikal dengan aksi Unduh/Hapus di bawah identitas; form unggah
satu kolom, ikon jadwal outline dan waktu event terbuka saat draft. Native file
picker/tanggal tetap mengikuti browser. Mobile menyusun panel ke satu kolom.

Header dan banner dipisah sebagai partial; partial perkembangan, dokumen,
informasi serta komponen existing dipakai ulang. Flash sukses hanya mengikuti
hasil operasi nyata. Pengingat profil tidak lengkap, dokumen kosong, error,
status non-draft, assessment, penarikan, koordinasi dan attendance dipertahankan.
CSS dibatasi `volunteer-application-draft`; status lain tidak didesain ulang.
Tidak ada perubahan aturan unggah, Policy, privasi dokumen, transisi, schema,
route atau service. PRD/kontrak tetap berlaku. Bukti dan batas validasi:
[handoff draft](../docs/VOLUNTEER_APPLICATION_DRAFT_DESIGN_HANDOFF.md).
## Lamaran Saya — desain FigCraft, 9 November 2026

Pengguna menyetujui `layout terukur.svg` (1440 x 1000) untuk daftar lamaran
Volunteer di `/volunteer/applications`, lokal tanpa commit/push. Sidebar navy
288 px dan navbar putih 64 px mengikuti halaman Volunteer yang sudah disetujui.
Menu Lamaran Saya aktif biru. Kartu putih berborder/radius 16 px memuat judul dan
posisi di kiri, status di kanan atas, label lokasi, garis pemisah, waktu dibuat/
dikirim dalam WIB serta tombol Lihat Detail biru. Mobile menyusun status dan
footer ke bawah tanpa mengubah alur. Kartu terpisah di partial list-card; komponen
status existing, sumber data akun, empty state, pagination dan otorisasi tetap.
Desain detail lamaran dan halaman lain tidak termasuk perubahan ini. PRD,
kontrak, schema dan status bisnis tetap berlaku. Bukti dan batas validasi:
[handoff daftar lamaran](../docs/VOLUNTEER_APPLICATIONS_DESIGN_HANDOFF.md).
## Profil Volunteer — desain FigCraft, 9 November 2026

Instruksi pengguna melanjutkan desain Volunteer dengan `Profil Volunteer.svg`
(1440 x 1828). Lingkup `/volunteer/profile`, lokal tanpa commit/push. Gunakan
sidebar navy #0F172A, menu Profil & Skill aktif biru #2563EB, header putih 64 px,
latar #F8FAFC, konten berjarak 32 px dari sidebar pada desktop, kartu putih,
input 44 px, judul 30 px, serta tombol berlabel normal sesuai SVG. Bagian
identitas, skill, ketersediaan, dan aksi simpan dipisahkan menjadi partial Blade;
komponen field/phone, baris skill/interval, CSRF, old input, error dan validasi
existing dipakai ulang. Form tetap satu POST ke route existing. Pengingat
verifikasi hanya muncul untuk akun yang belum terverifikasi; data contoh dalam
SVG bukan data aplikasi. Tanggal dan waktu memakai WIB sesuai kontrak existing.

Style shell biru dipakai bersama Ringkasan Aktivitas; halaman Volunteer lain,
Organizer dan Admin mempertahankan tampilannya. Ketentuan sidebar pengguna lama
digantikan desain pengguna untuk kedua halaman yang sudah disetujui ini.
Tidak ada perubahan schema, status bisnis, Policy atau interface layanan;
PRD/kontrak tetap berlaku. Bukti dan batas validasi ada pada
[handoff profil](../docs/VOLUNTEER_PROFILE_DESIGN_HANDOFF.md).
## Ringkasan Aktivitas Volunteer — desain FigCraft, 9 November 2026

Keputusan pengguna: implementasikan `Ringkasan Aktivitas.svg` pada halaman
`/volunteer/aktivitas`, hanya sisi Volunteer, lokal tanpa commit/push/publikasi.
Desain yang disetujui memakai sidebar navy #0F172A selebar 288 px, header putih
64 px, aksen biru #2563EB, latar #F8FAFC, kartu putih dengan border/radius lembut,
serta Inter. Pada desktop konten mulai x=320 px; jarak kartu 24 px, tiga kartu
statistik berjarak 16 px, kolom profil dan skill mengikuti rasio pada SVG.
Ketentuan lama tentang penggantian sidebar pengguna tidak berlaku pada halaman
ini sesuai desain terbaru pengguna. Halaman Volunteer lainnya, Organizer dan
Admin mempertahankan tampilan existing.

Bagian sambutan, statistik, kelengkapan profil, skill, ketersediaan, kegiatan,
dan langkah berikutnya dipisahkan menjadi partial Blade di
`resources/views/volunteer/activity/`. Sidebar/navbar existing dipakai ulang;
style baru dibatasi kelas `volunteer-activity`. Nama, hitungan, status, daftar,
tenggat, dan notifikasi tetap berasal dari data/layanan existing, bukan isi
contoh SVG. Tanggal lamaran ditampilkan dalam WIB; tidak ada perubahan schema,
route, Policy, transisi, atau interface layanan. FR-18 dan kontrak data tetap
berlaku. Bukti dan batas validasi: [handoff desain](../docs/VOLUNTEER_ACTIVITY_DESIGN_HANDOFF.md).
# Design specification SkillMatch

## Kehadiran per sesi/hari - keputusan pengguna 3 November 2026

Organizer mencatat setiap sesi/hari dari snapshot jadwal tugas peserta accepted.
Interval melewati tengah malam dipisah menurut tanggal WIB. Completion menunggu
seluruh sesi selesai dan tercatat; partisipasi berhasil jika semua sesi hadir.
Absensi lama tetap berlabel per kegiatan, tidak disalin sebagai hadir setiap hari.
Konversi legacy pada event nonterminal meminta konfirmasi/alasan dan menyimpan arsip.
Halaman, filter, rekap dan route lama dipertahankan dengan kompatibilitas terbatas
untuk satu sesi; beberapa sesi harus dicatat melalui endpoint per sesi.
Schema, aturan, endpoint, demo dan bukti tes: [handoff sesi](../docs/SESSION_ATTENDANCE_HANDOFF.md).
Ketentuan lama yang membatasi attendance satu catatan per event digantikan keputusan ini.


## Perbaikan alur terintegrasi - 3 November 2026

Keputusan terbaru dan bukti implementasi ada pada [handoff perbaikan alur](../docs/INTEGRATED_FLOWS_HANDOFF.md).
Penghapusan akun tanpa data terkait tetap tersedia; akun bersejarah dilindungi.
Revisi identitas organisasi memerlukan konfirmasi dan review ulang, penolakan
bercatatan dapat diajukan ulang. Event lama dapat melengkapi tenggat kosong sekali
melalui aksi owner yang diaudit; snapshot existing tidak diubah, hanya metadata
tenggat kosong yang dapat membaca perbaikan eksplisit. Aktivitas dan notifikasi
website memakai layanan nyata serta outbox existing yang dibedakan dari email.
Layout, modal dan fitur existing dipertahankan. Catatan tahap sebelumnya di bawah
bersifat historis bila menyebut layanan tersebut belum tersedia.


## Persetujuan perubahan tampilan — 28 Oktober 2026

Pertahankan tampilan existing yang sudah dibangun, termasuk layout/sidebar/navbar,
konten berguna, komponen, warna, tipografi dan perilaku interaksi. Spesifikasi lama
bukan izin untuk menghapusnya atau mengganti dengan template/shell. Perubahan
visual mengikuti lingkup permintaan pengguna; redesign atau perubahan di luar
lingkup itu harus diajukan dengan alasan dan perbandingan sebelum-sesudah untuk
persetujuan terlebih dahulu. Persetujuan yang sudah jelas tidak diminta ulang.
Periksa halaman lain yang memakai komponen bersama. Catat hasil review visual
secara terpisah dari build; build sukses tidak membuktikan tampilan tetap sesuai.

## Verifikasi email Volunteer — 28 Oktober 2026

Halaman verifikasi memakai auth-layout existing, teks Indonesia, alamat email
escaped, tombol Kirim ulang dengan keadaan mengirim, status berhasil/gagal, tautan
melengkapi profil dan logout POST. Sidebar Volunteer yang belum verified memberi
pengingat dan tautan verifikasi; detail event menawarkan "Verifikasi email untuk
melamar" sebelum menampilkan form lamaran. Akun aktif tidak disebut nonaktif hanya
karena email belum verified. Status pengiriman tidak berarti email sudah verified
atau sudah diterima di inbox; pengembangan dengan mailer log dijelaskan di README.

Menu Riwayat Partisipasi Volunteer memakai ikon jam outline 24×24 dengan ukuran
h-5 w-5 dan stroke 2 seperti menu existing; state aktif/hover mengikuti menu lain,
dengan aria-current pada halaman riwayat (penyesuaian 28 Oktober 2026).

## Seleksi/pelaksanaan A3 — 28 Oktober 2026

Daftar Pelamar menambah tautan Semua Pelamar/Siap Ditinjau/Pelaksanaan; filter posisi
mempertahankan tab. Detail menampilkan keputusan dan alasan, hasil A4 terpisah, serta
profil dari snapshot. Legacy tanpa field menyebut tidak tercatat, bukan mengambil
profil terkini sebagai bukti historis. Terima/Tolak hanya ditawarkan ketika masih
under_review dan periode keputusan terbuka; server tetap memeriksa ulang.

Pelaksanaan memakai page-header, section-card, flash dan tombol existing; rekap nyata
accepted/present/absent/unrecorded, filter nama/posisi/status, tabel gulir dalam region
berlabel/fokus keyboard, waktu pencatatan WIB, catatan dan alasan koreksi completed.
Penutupan pending/penyelesaian meminta konfirmasi; nol peserta dikonfirmasi tersendiri.
Riwayat Volunteer menampilkan event/posisi/waktu dan hasil sumber. File purged/missing
tidak ditampilkan sebagai ready atau tautan unduh aktif.

Layout sidebar Organizer/Volunteer terbaru existing dipertahankan sesuai keputusan
pelestarian UI, tanpa mengganti shell global di lingkup A3. Rekonsiliasi target navbar
global pada bagian 3 tetap koordinasi SF/A1; perubahan ini tidak membatalkan desain
yang sudah dipakai. Bukti responsive dan batas interaksi browser ada di A3_HANDOFF.

## Matching pelamar — 27 Oktober 2026

EO mendapat kolom Match Score pada daftar pelamar dan kartu Kecocokan kandidat pada
detail existing: total /100, tabel komponen/skor/bobot/poin, jumlah skill cocok, cakupan
jam serta kota sama/berbeda. Tampilan skor dua desimal; nilai assessment terpisah dan
keputusan akhir tetap EO. Skor belum tersedia ditandai kosong/tertunda, bukan nol palsu.
Volunteer tidak mendapat kartu angka matching; badge Menunggu assessment dan pesan
“Pencocokan selesai. Lamaranmu siap lanjut ke assessment.” sesudah status assessment_pending.
Tombol start assessment belum ditambahkan oleh tahap matching. Layout, sidebar, navigasi,
dashboard dan komponen screening yang sudah ada dipertahankan; tambahan hanya di atas.

Komponen dan tampilan yang sudah dipakai modul lain harus diperiksa pada kode,
branch, dan handoff terkini sebelum diubah. Rancangan awal atau contoh baseline
tidak membatalkan tampilan baru yang telah disepakati. Perubahan desain dilakukan
terarah pada komponen terkait, menjaga route dan perilaku yang berfungsi, lalu
memperbarui spesifikasi ini bila keputusan visual berubah.

## Font lokal dan klik navigasi semua role - 10 Oktober 2026

Partial layouts/fonts memuat Inter WOFF2 lokal dari public/fonts/inter, bobot 400/500/600/700, memakai font-display swap dan preload bobot normal. Deklarasi @font-face berada pada partial bersama dengan URL asset Laravel; tidak memakai stylesheet eksternal yang menunda inisialisasi Alpine. Auth-layout juga memakai partial ini. Font-sans/fallback system dan identitas visual tetap berlaku. Menu/dropdown harus merespons satu klik setelah JavaScript aplikasi tersedia, termasuk saat file font belum selesai diunduh. Pada mobile, tombol membuka drawer dan item menu membawa pengguna ke halaman dengan satu klik. Lisensi OFL.txt disertakan; role/route/Policy dan komponen sidebar existing dipertahankan.

## Status pembayaran dan navigasi kembali - 10 Oktober 2026

Halaman status Organizer memeriksa order pending dengan checkout sekali melalui server saat kembali dari checkout baru dengan check_payment=1; kunjungan biasa tidak memanggil gateway otomatis. Tampilkan pesan memeriksa, menunggu, gagal dan retry manual tanpa mengklaim sukses dari redirect. Klik menu tetap navigasi native sekali, membatalkan request browser dan mencegah reload hasil pembayaran yang terlambat. Disable hanya tombol Sinkronkan status selama request dan sediakan pesan aria-live. Tombol berupa submit POST agar bekerja tanpa JavaScript. Kembali ke event adalah tautan berbentuk tombol sekunder dengan ikon panah, tinggi/padding sejajar, fokus keyboard dan tujuan detail event milik order. Pakai komponen/layout existing; status paid dan publikasi tetap dua hal berbeda. Admin tetap sinkron manual. Mode Sandbox tetap dijelaskan pada konteks pembayaran.

## Kota pada form event - perbaikan 10 Oktober 2026

Pakai select Kota dari master aktif, label terkait id city_id, placeholder Pilih Kota, pilihan old input dan error spesifik. Draft lama tanpa city_id menampilkan teks kota lama sebagai petunjuk agar Organizer memilih sendiri. Pertahankan gaya form existing; layout Organizer memakai flash bersama untuk menampilkan pesan kegagalan pengajuan dan sukses simpan.

Popup Buat Event di Event Saya dan halaman buat/edit memakai komponen event-city-field yang sama. Popup terbuka kembali setelah validasi gagal dengan old input; jika master kota aktif kosong, tampilkan petunjuk menghubungi Admin. Pesan kota menggunakan bahasa Indonesia.

## Hasil screening — 11 Oktober 2026

Tambahkan kartu Hasil screening pada detail lamaran existing. Volunteer melihat ringkasan
lolos/gagal, seluruh alasan syarat wajib gagal dan pesan “Lamaranmu belum bisa dilanjutkan
ke tahap selanjutnya.” EO melihat tabel syarat/hasil/keterangan; manual ditandai ditinjau
manual, preferensi tidak diwajibkan. Teks di-escape; tabel responsif melalui overflow.
Hasil memakai snapshot saat submit; pending tidak menampilkan hasil palsu. Passed yang
masih submitted diberi label Lolos screening — menunggu pencocokan sampai matching siap.
Tidak ada tombol assessment/seleksi/bypass baru pada tahap screening ini.

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

Detail publik mempertahankan poster dan kartu existing. Ringkasan pelaksanaan
dalam satu hari memakai satu tanggal dengan rentang jam WIB. Pada bagian posisi, tampilkan satu kali
deskripsi yang persis mengulang paragraf Tentang kegiatan dan tonjolkan tombol
masuk atau verifikasi saat lamaran bisa dimulai.

Katalog: judul "Temukan kegiatan yang sesuai denganmu", pencarian, filter kategori/kota/tanggal, jumlah hasil, grid 1/2/3 kolom sesuai lebar, pagination. Kartu berisi judul, Organizer, kota, jadwal WIB, deadline, posisi tersedia dan tombol Lihat Detail. Poster opsional dengan rasio tetap 16:9; fallback brand rapi jika tidak ada.

Detail: judul dan metadata, deskripsi, daftar posisi beserta skill/jadwal/kuota/persyaratan, panel tindakan. CTA "Lamar posisi" hanya jika eligibility publik terpenuhi; pengunjung diarahkan login lalu kembali ke detail. Event tutup menampilkan alasannya. Kontak privat pelamar tidak pernah muncul di katalog.

### 4.2 Form event — A2, tab assessment A4

Gunakan langkah: Pilih Paket → Informasi Event → Posisi & Jadwal → Assessment → Tinjau & Ajukan. Form popup dan halaman buat/edit sama-sama menempatkan paket di awal, dengan harga/limit per event dari server. Tampilkan warning dekat input jadwal jika durasi pendaftaran melebihi paket; simpan diblokir sampai paket/durasi sesuai. Pilihan paket dan tanggal menggunakan old input sesudah gagal. Simpan draft memerlukan paket dan informasi/jadwal valid, sementara posisi/assessment bisa dilengkapi berikutnya. Tampilkan progres langkah sebagai teks/stepper pendek, bukan persentase palsu.

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

Tabel mempertahankan Nama/Posisi dan rekap peserta; tiap sesi menampilkan tanggal/jam WIB, Kehadiran, Waktu pencatatan, Catatan dan Aksi. Filter tanggal/status berlaku untuk sesi. Default unrecorded berlabel "Belum dicatat". Organizer menekan Catat, memilih Hadir/Tidak hadir, catatan opsional, Simpan. Edit sesudah event selesai meminta alasan wajib. Volunteer hanya melihat hasil.

Bulk action jika ada: checkbox peserta eligible → "Tandai hadir (n)" → konfirmasi nama/jumlah. Jangan default menandai semua hadir. Completion button "Selesaikan Event" membuka ringkasan dan blocked reasons untuk belum akhir jadwal, seleksi menggantung, atau kehadiran belum dicatat. Waktu pencatatan tidak disebut jam datang.

Riwayat Volunteer: event, posisi, tanggal, status kehadiran; absent diberi "Tidak hadir", cancelled "Event dibatalkan". Sertifikat/performa tidak dijanjikan pada MVP.

### 4.8 Paket dan pembayaran — A2

Paket menampilkan harga/limit posisi/limit lamaran/periode secara sejajar. Nilai demo diberi "Contoh paket"; halaman transaksi diberi "Sandbox — simulasi pembayaran". Tinjau checkout menampilkan event dan snapshot harga final dari server.

Status page menampilkan order ref, nominal, paket, pending/paid/failed/expired, tombol Periksa Status bila sesuai dan tautan kembali ke event. Kembali dari popup tidak otomatis menampilkan sukses. Jika paid tetapi event belum eligible, jelaskan syarat tersisa. Admin melihat snapshot transaksi dan audit, bukan edit status paid.

### 4.9 Ringkasan Aktivitas — A4

Volunteer: sambutan singkat; prioritas tindakan (profil belum lengkap, assessment); lamaran terbaru; jadwal accepted; notifikasi. Organizer: event sendiri; moderasi/revisi/payment tertunda; kandidat perlu review; tugas attendance. Hindari empat kartu angka kosong seperti organizer dashboard ZIP. Bila belum ada data, tampilkan CTA konkret "Buat event pertama" atau "Jelajahi event".

### 4.10 Admin analitik — A1

Di bawah grafik Admin gunakan tiga kartu putih dengan grid responsif seperti
kartu dashboard. Lamaran mengikuti periode yang dipilih dengan tanggal WIB
yang terlihat. Pelaksanaan dan kehadiran menampilkan rekap terkini dengan
label yang memperjelas cakupan masing-masing.

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
3. Satukan font Inter lokal melalui layouts/fonts dan CSS global di app.css; hapus override Poppins/duplikasi style. Jangan menambahkan stylesheet CDN font yang memblokir pemuatan runtime.
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

Pertahankan hero beranda baseline "Hubungkan Talent Volunteer dengan Event Terbaik", identitas indigo, tombol Masuk/Daftar dan footer tim. Gunakan layout/navbar SF agar Inter dan Alpine tetap dimuat sekali. Pengguna yang login melihat CTA menuju tujuan sah sesuai role/status melalui redirect bersama; jangan menyebut halaman pengguna sebagai dashboard analitik. Teks promosi tidak mengklaim engine matching yang belum tersedia sudah berfungsi. Pemberitahuan katalog/matching yang belum tersedia hanya keterangan pendukung, bukan pengganti seluruh beranda.

A2 mengembangkan beranda ini dan menghubungkan katalog setelah route/data publik memenuhi kontrak. A1 mempertahankan kartu hitungan database dan aksi cepat admin, kemudian menambah analitik periode, tren dan state. Tidak mengembalikan sidebar pengguna, login terpisah, statistik dummy atau tautan kosong dari baseline.

## Pemulihan ringkasan pengguna - 5 Oktober 2026

Aktivitas Volunteer/Organizer memakai kembali sambutan, kartu putih beraksen indigo dan aksi cepat profil dari baseline dalam navbar SF. Tampilkan informasi profil aktual dan interval WIB, bukan dashboard analitik platform. Status akun dan organisasi ditampilkan terpisah. Bagian kegiatan yang belum terintegrasi memakai keterangan; tidak membuat kartu angka nol atau tombol seolah berfungsi.

## Implementasi antarmuka A2 - 5 Oktober 2026

Beranda baseline dipertahankan dengan CTA Jelajahi Event. Katalog memakai navbar publik, filter judul/kategori/kota/tanggal WIB, jumlah hasil nyata, pagination dan kartu 1/2/3 kolom. Detail menampilkan jadwal/skill/kuota/syarat; pengajuan lamaran diberi keadaan belum tersedia sampai route A3 ada. Login Volunteer dapat kembali ke detail yang masih eligible melalui events.join.

Event Saya merupakan daftar operasional, bukan dashboard baru. Pilih Paket -> Informasi Event -> Posisi/Jadwal -> Assessment -> Tinjau/Ajukan ditampilkan sebagai urutan teks. Popup/halaman form memakai event-package-selector, event-package-warning dan Alpine eventPackageForm; durasi WIB dihitung independen timezone browser. Skill/jadwal/syarat berulang memakai Alpine yang sudah dimuat SF; form server tetap memvalidasi seluruh data. Assessment Organizer tersedia sesuai catatan 10 Oktober; fitur peserta A4 tetap mengikuti batas implementasinya. Status moderasi/publikasi/pelaksanaan ditampilkan terpisah.

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

## Koordinasi peserta - 3 November 2026

Tambahkan section Koordinasi Peserta pada detail event organizer dengan tiga field opsional: Tautan grup, Nama PIC, Kontak PIC; tombol Simpan koordinasi, error revisi dan validasi field. Pertahankan seluruh section existing. Detail lamaran accepted menampilkan tombol Gabung grup peserta (tab baru), PIC dan kontak, atau placeholder organizer belum membagikan tautan. Bukan konten publik; withdrawn/cancelled tidak menerima section. Tidak ada input titik kumpul, waktu kedatangan atau arahan. Teks menyebut informasi tersebut disampaikan melalui grup. Completed/cancelled menampilkan riwayat read-only untuk owner. Gunakan layout/komponen/Alpine existing.

## Perbaikan input kontak dan batas form - 3 November 2026

Pertahankan layout/modal existing, gunakan x-phone-field pada profil Volunteer dan buat/edit Organizer dengan type tel, keyboard/autocomplete tel, 8-15 digit dan + opsional. Tampilkan contoh/error Indonesia; tolak ketikan/paste huruf tanpa mengubahnya diam-diam menjadi nomor lain. Constraint native dan validasi server tetap berlaku tanpa Alpine. Field Kontak PIC tetap teks karena mendukung email/nama pengguna. Tambahkan max/step=1 existing pada input paket dan max tanggal hari ini pada tanggal lahir; tidak mengubah desain atau aturan bisnis lainnya.

## Foto sampul event — keputusan 5 November 2026

Organizer menambah/mengganti/menghapus foto opsional pada create/edit draft yang masih editable. JPG/PNG/WebP maksimal 5 MB, rekomendasi 1600 × 900; kartu konsisten 16:9 dengan fallback ilustrasi. Kolom nullable events.cover_image_path; input cover_image dan remove_cover_image, path ditentukan server. GET /events/{event}/cover (events.cover) mengikuti visibility event atau pemilik/admin aktif. Tidak mengubah moderasi/publikasi/paket/pembayaran maupun dokumen privat. Detail implementasi, UAT dan bukti tes tersedia pada docs/DEVELOPMENT.md#foto-sampul-event di root repository.

## Electric Blue Publik dan Auth — keputusan 6 November 2026

Permintaan pengguna mengizinkan penerapan FigCraft Electric Blue / 06 Publik dan Auth pada landing, katalog, login dan registrasi. Blue #2563EB, Navy #0F172A, Peach #FB923C, Off-white #F8FAFC; judul editorial Georgia dan body Inter. Tampilan operasional tiga role tetap. Data, filter, pagination, upload foto, validasi, satu login/guard web dan seluruh aturan bisnis existing dipertahankan. UAT: periksa kelima tampilan desktop/mobile, menu, pencarian/filter/reset, kartu foto, role registrasi, error dan toggle password. Bukti implementasi dan tes ada pada docs/DEVELOPMENT.md#electric-blue-publik-auth di root repository; belum menyatakan seluruh UAT tim lulus.

## Koreksi kartu status — 14 November 2026

Disetujui pengguna: kartu Status lamaran screening_failed menjadi putih; banner
dan badge rose dipertahankan. Status lain tetap. Perubahan terarah pada partial
information; tanpa alur/route/schema baru. Build, view:cache, diff --check lulus.
Visual browser belum diperiksa ulang. Tanpa commit/push.

## Penyesuaian jarak tahap kedua — 14 November 2026

Pengguna menyetujui uji perapian jarak dengan opsi pemulihan. Salinan CSS sebelum
tahap ini: storage/app/application-detail-before-spacing-20261114/volunteer-application-compact.css.
Pemulihan hanya menyalin CSS itu kembali; tidak membatalkan panel penarikan atau
perapian tahap sebelumnya. Jarak bagian 20 ke 16 px, judul/header lebih rapat,
antartahap timeline 24 ke 16 px, koordinasi/PIC dipadatkan. Teks utama dan tombol
44 px tetap. Build, fixture accepted/readonly/cancelled dan diff --check lulus.
CDP 1440/768/390 tidak overflow; desktop diperiksa visual. Tanpa PHPUnit ulang
karena hanya CSS jarak. Klik browser akun nyata belum diuji. Tanpa commit/push.

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

## Katalog panel lebih ringkas — 14 November 2026

Pengguna menyetujui heading ringkas serta kartu putih utuh berborder untuk setiap
event. Wrapper ve-catalog dan CSS volunteer-catalog.css scoped, diimpor app.css.
Heading Jelajahi Event, filter padat, font sans, kartu padding16, jarak20, grid
3/2/1 kolom. Komponen event-card/route/filter/pagination/data tetap; publik tidak
diubah. Build/view:cache/diff --check lulus. VolunteerCatalogTest 2 tes/27 assertion
lulus pada MySQL khusus tes. Fixture kosong/terisi tanpa simpan CDP1440/768/390
tidak overflow; desktop diperiksa visual. Klik akun nyata belum diuji.
Tanpa commit/push, perubahan lokal lain dipertahankan.

Koreksi pengguna: heading promosi serif Ada kegiatan. Ada tempat untukmu.
dipertahankan bersama eyebrow dan deskripsi asli. Ukuran responsif 30–44 px,
jarak judul12 px; kartu/filter ringkas tetap. Scoped panel saja. Build dan
view:cache lulus; visual browser ukuran heading baru belum diperiksa ulang.


## Detail event panel ringkas - 14 November 2026

Padding kartu 20px/mobile 16px, judul 28px/mobile 26px, jarak 14-20px.
Tanggal pelaksanaan fleksibel dalam satu blok. Khusus panel Volunteer.
Bukti: docs/VOLUNTEER_EVENT_DETAIL_DESIGN_HANDOFF.md pada root repository.


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


### Admin: jumlah data dan identitas EO transaksi ? 15 November 2026
Pilihan per_page 10/50/100 dipindahkan ke atas tabel Organizer dan Volunteer; form GET, search/status dan pagination tetap. Footer hanya muncul bila ada halaman lanjutan. Tabel transaksi menampilkan kolom EO/Organisasi (organization_name, fallback nama akun) dan email akun; eager load event.organizer.organizerProfile menghindari N+1. Label Referensi menjadi Nomor transaksi; nilai tetap SM- + UUID yang dibuat PaymentService::createOrder, bukan nomor EO. Pencarian existing tetap berdasarkan order_ref, tanpa perubahan payment/route/status. Empty colspan mengikuti lima kolom.
44 tes/298 asersi (A2PaymentTest, FoundationAccessTest) dan build lulus; migration tidak diperlukan. PRD/kontrak bisnis tetap berlaku.


### Detail transaksi admin ? 15 November 2026
Halaman admin memakai partial admin/orders/detail sendiri agar tampilan pembayaran EO existing tetap. Ringkasan kegiatan/status/nominal, kartu informasi transaksi dan identitas EO dua kolom responsif, serta bagian pembayaran/aktivasi dengan waktu WIB. Label 12 px, isi 14 px, nominal 24 px; nomor transaksi tetap utuh dengan break-all. Status diterjemahkan, Sandbox tetap diberi label, peringatan follow-up/refund dan kegiatan belum terbit tetap tersedia dengan teks ringkas. Sinkronisasi POST/CSRF memakai route/service existing dan guard busy, tanpa perubahan status manual.
OrderController::show eager-load event.organizer.organizerProfile. Tidak ada migration/route/schema/aturan payment baru. 22 tes A2PaymentTest/139 asersi dan build lulus. Fixture detail HTTP 200 dengan rollback MySQL testing. Tampilan shared orders/detail tidak diedit.


### Navigasi situs utama per role ? 15 November 2026
Permintaan pengguna: EO/Volunteer mengikuti Admin dengan satu pintu masuk panel. public-navigation dan fallback layouts/navigation menampilkan Panel Organizer atau Panel Volunteer menuju activity.index masing-masing, serta Keluar POST/CSRF. Tautan langsung Aktivitas, Profil, Lamaran Saya, Event Saya dan Verifikasi email di navbar dihapus; menu internal panel dan route/guard/verifikasi email existing tetap. Admin tetap Panel Admin; navigasi umum dan guest tetap. NavigationAssetsTest + VolunteerCatalogTest: 6 tes/87 asersi lulus, build dan diff --check lulus. Pemeriksaan visual khusus navbar terbaru belum dilakukan. Tidak ada perubahan schema, business rule atau setup.


## Admin: lima penyesuaian operasional ? 15 November 2026
Lingkup disetujui pengguna pada feature/admin-ui-refinement (dasar 9e2e241, working tree awal bersih):
1. Transaksi: pencarian literal nomor transaksi, judul kegiatan, akun/email EO dan nama organisasi; OR dikelompokkan di dalam filter status. date_from/date_to memakai tanggal pembuatan transaksi dalam WIB, awal inklusif dan batas hari berikutnya eksklusif dalam UTC; rentang terbalik/format invalid ditolak. Pagination mempertahankan query.
2. Detail verifikasi: identitas kegiatan, jadwal/rekrutmen, posisi/tugas/saran usia, paket snapshot dan EO dikelompokkan. Badge mengikuti status nyata; tombol keputusan hanya untuk pending (server tetap menolak replay 409). Daftar verifikasi default pending, filter approved/rejected/all menampilkan kembali pengajuan lama. Status all mencakup pengajuan pending/approved/rejected, bukan semua draf. Route existing tetap.
3. Catatan keputusan terakhir: detail kegiatan menampilkan verification_note + verified_at WIB, termasuk saat resubmit. Modal detail organisasi menampilkan organizer_verification_note dan waktu review terakhir dari audit organizer.reviewed; bila tidak ada audit, waktu dinyatakan belum tercatat. Resubmit profil organisasi sekarang mempertahankan catatan terakhir, bukan menghapusnya; halaman pending membedakannya sebagai catatan sebelumnya. Admin review berikutnya tetap mengganti/menghapus catatan sesuai perilaku existing. Ini bukan arsip lengkap semua siklus penolakan; catatan lama yang sudah terhapus sebelum perubahan ini tidak direkonstruksi.
4. Paket: banner dan helper status pada modal/form menjelaskan nonaktif hanya menutup pilihan paket baru; snapshot/hak pembelian lama tetap berlaku. Tidak ada revoke/refund atau perubahan payment.
5. Dashboard: angka antrean organisasi mengarah ke status=pending, antrean kegiatan menuju daftar verifikasi default pending. Kartu grafik, data dan handler existing dipertahankan.
Tidak ada migration, status/schema baru atau dependensi. PRD/kontrak diperbarui untuk retensi catatan terakhir saat pengajuan ulang dan filter baca; A1 tetap pemilik review, A2 tetap pemilik payment/paket. Data catatan tetap di field existing, tidak disalin ke audit/log/notifikasi. Tampilan EO/Volunteer di luar label catatan profil pending tidak diubah.


Dashboard admin: label antrean diperjelas menjadi Organisasi menunggu verifikasi dan Kegiatan menunggu persetujuan. Tiga teks Lihat tren di kartu total dihapus sesuai permintaan; data, klik seri grafik, dan tautan antrean tetap. Perubahan presentasi saja.

Dashboard admin: kartu Manajemen Transaksi dan Aksi Cepat Admin dihapus sesuai screenshot pengguna. Menu sidebar, kartu total transaksi, grafik serta antrean pemeriksaan tetap. Ekspektasi FoundationAccessTest diselaraskan untuk memastikan kedua bagian tidak tampil.

Kartu statistik admin (dashboard, Organizer, Volunteer, verifikasi kegiatan) disamakan dengan EO: bidang putih dengan border berwarna, label/ikon senada, angka navy. Biru total/master, hijau aktif/disetujui, merah nonaktif/ditolak, amber antrean, cyan transaksi, violet kategori. Ukuran dan data/tautan/handler grafik tetap.

Reset filter admin disamakan dengan EO di Organizer, Volunteer, verifikasi kegiatan dan transaksi: teks Reset tanpa ikon/box, abu-abu dan hover biru, hanya tampil bila filter aktif. Reset menuju URL daftar awal (verifikasi kembali pending); per_page non-default pada daftar akun juga dapat direset. Tidak ada perubahan route/query/controller.


### Konsistensi akhir Admin dan uji alur ? 15 November 2026
Permintaan pengguna mencakup istilah, modal dan detail verifikasi ponsel. Teks UI admin memakai Kegiatan, Volunteer, Keahlian; status transaksi ditampilkan Menunggu pembayaran/Lunas/Gagal/Kedaluwarsa/Dibatalkan/Perlu pemeriksaan. Enum, route, field, Alpine camelCase dan data tetap. Modal menempatkan submit sebelum Batal, tombol simpan outline biru, pembatalan/hapus outline merah, dan tutup Escape; dismiss icon diberi nama aksesibel. Nonaktifkan volunteer merah, Aktifkan biru. Pintasan ponsel menuju #verification-decision dan #verification-note memudahkan menemukan keputusan/catatan tanpa mengubah urutan informasi desktop.
84 tes backend/624 asersi lulus: AdminWorkflowRefinement, FoundationAccess, FoundationIntegrity, IntegratedFlows, A2Event, A2Payment, DefaultPackage, OrganizerKtpUpload. Mencakup akses lintas role, verifikasi/revisi/resubmit organisasi, moderasi dan publikasi, validasi paket/snapshot, callback pembayaran terverifikasi/replay/idempotensi, serta filter transaksi. Ini tes otomatis MySQL testing terpisah; gateway pembayaran memakai fixture/mocking existing, bukan transaksi Sandbox nyata. Tidak ada perubahan keputusan bisnis pada langkah konsistensi ini.
Build dan diff --check lulus. Fixture 13 halaman HTTP 200 dengan rollback; review browser termasuk modal/ponsel dicatat setelah selesai. Tidak ada commit/push; branch feature/admin-ui-refinement.


## Panel Volunteer konsisten dengan EO/Admin - 15 November 2026
Permintaan pengguna menyamakan sidebar navy/logo bersama, warna biru, kartu putih,
border statistik berwarna, hierarki font 26/22-18-15-13-12px, tombol detail/edit
outline biru, kembali solid merah dan hapus/batal outline merah. Posisi kembali
kiri atas, detail pada bagian aksi kartu. Katalog panel memiliki header/filter
ringkas; halaman publik tetap terpisah. Istilah Volunteer/Kegiatan/Keahlian/
Penyaringan/Asesmen/Draf dan sapaan Anda digunakan konsisten, termasuk label
notifikasi dan Panel Volunteer pada navbar. Tidak mengubah aturan bisnis, route,
status atau snapshot. Detail implementasi, bukti dan batas pengujian:
[handoff volunteer](../docs/VOLUNTEER_EO_STYLE_HANDOFF.md).

### Penyelarasan panel volunteer (16 November 2026)
Atas arahan pengguna, seluruh menu volunteer memakai kepadatan EO/admin: judul halaman 26px (22px di ponsel), judul bagian 16px, subjudul 14px, isi 13px, metadata 11-12px, jarak antarkartu 16px, serta tombol outline biru dan kembali merah. Padding kartu dan ringkasan dipadatkan tanpa mengubah aturan bisnis atau area pilihan soal minimal 44px. Detail hasil pemeriksaan ada di docs/VOLUNTEER_EO_STYLE_HANDOFF.md pada root repository.

### Header landing page - 16 November 2026
Atas permintaan pengguna, header gelap beranda tetap terlihat saat scroll menggunakan position:sticky/top:0, lapisan di atas konten dan bayangan lembut. Tautan Cara kerja/Untuk Organizer menutup menu ponsel; scroll-margin menjaga judul bagian tidak tertutup header. Menu ponsel dapat discroll jika lebih tinggi dari layar. Halaman publik lain tetap memakai posisi header semula. Branch feature/landing-page-refinement, dasar 8c0f1c3. Build frontend dan diff --check lulus; pemeriksaan browser terbaru belum dilakukan. Tidak mengubah route, data, atau aturan bisnis.

### Kartu ajakan landing page - 16 November 2026
Sesuai referensi gambar pengguna, CTA akhir menjadi kartu biru membulat dalam container berlatar paper: teks rata kiri (label, judul sans-serif, deskripsi), tombol akun navy dan tombol outline Jelajahi kegiatan di kanan. Palet biru/navy/putih tetap. Tablet/ponsel menumpuk konten dan tombol responsif. Tautan akun tetap mengikuti status login; tautan kedua memakai katalog existing. Header sticky dipadatkan ke 76px desktop/64px ponsel. Build frontend dan diff --check lulus; belum ada pemeriksaan browser untuk kartu CTA terbaru. Tidak ada migration atau perubahan kontrak bisnis.

Pembaruan warna CTA sesuai arahan terbaru: latar oranye #fb923c, judul navy, label biru tua #1e3a8a, deskripsi gelap #1e293b dan tombol sekunder teks/garis navy. Tombol utama navy tetap memakai teks putih untuk kontras. Build dan diff --check lulus.

### Cara kerja landing page - 16 November 2026
Arahan pengguna terbaru: tetap tiga langkah seperti semula, disusun vertikal di kanan dengan kartu transparan berbingkai putih lembut dan sudut membulat. Pengantar di kiri mengikuti referensi dengan judul sans-serif, deskripsi, dan tautan katalog. Palet navy/putih/oranye dipertahankan; layar kecil menumpuk dua kolom. Build dan diff --check lulus; visual browser terbaru belum diperiksa.

### Palet publik revisi - 16 November 2026
Mengikuti referensi terbaru pengguna, warna primer publik menjadi ungu kebiruan #7047ff, aksen oranye menjadi hijau muda #c8f45b, dan paper #f7f6f2. Warna hover, label CTA, tombol, tautan dan dua ilustrasi SVG hero ikut diselaraskan. Navy dan teks gelap tetap. Palet panel admin/EO/volunteer serta autentikasi tidak diubah; perubahan dibatasi shell publik. Build frontend dan diff --check lulus, visual browser terbaru belum diperiksa.

Koreksi lingkup putih sesuai klarifikasi pengguna: hanya pita pencarian kegiatan yang memakai latar #fff selebar viewport; isi form tetap mengikuti container. Katalog kegiatan dan area paper di bawah tetap krem #f7f6f2. Form dipisahkan dari container katalog; route/filter tetap. Build dan diff --check lulus.

### Kepadatan landing dan warna kulit - 16 November 2026
Atas permintaan pengguna, ukuran visual landing diperkecil sekitar 20% melalui CSS terisolasi electric-landing: judul, tombol, ilustrasi, jarak, kartu dan padding. Area tombol ponsel tetap minimal 44px. Tidak memakai zoom/transform skala halaman. Warna wajah dan lengan kedua karakter SVG menjadi warna kulit #e8b28e, sementara pot/aksen hijau tetap. File baru resources/css/landing-density.css diimpor oleh app.css; halaman publik lain/panel tidak terkena aturan kepadatan. Build dan diff --check lulus. Visual browser terbaru belum diperiksa.

FAQ sesuai revisi pengguna terbaru: latar krem, judul gelap di tengah kolom kiri, satu kotak ungu kebiruan membulat di kanan berisi seluruh accordion pertanyaan. Pertanyaan/jawaban putih, ikon + hijau muda, pemisah tipis transparan; tinggi baris tetap ringkas. Build dan diff --check lulus; belum verifikasi visual browser terbaru.

FAQ: ditambahkan label PERTANYAAN UMUM dan deskripsi singkat, judul kembali rata kiri sesuai persetujuan pengguna. Kotak biru hanya untuk kolom pertanyaan. Jarak bawah FAQ dan atas CTA masing-masing 24px (total 48px), menggantikan jarak gabungan 136px desktop. Build dan diff --check lulus; belum diperiksa visual browser terbaru.

Paket landing mengikuti referensi terbaru pengguna: section gelap #1c1c22 selebar halaman, judul sans-serif putih di kiri/deskripsi abu di kanan, tiga kartu transparan membulat dengan nomor hijau muda. Nama/harga/kapasitas tetap memakai data paket aktif dan komponen paket existing; styling dibatasi landing. Grid tetap satu kolom di ponsel. Build dan diff --check lulus; visual browser terbaru belum diperiksa.

Cara kerja direvisi sesuai referensi terbaru: section putih, pengantar rata tengah, baris Cukup mau terlibat berwarna primer, deskripsi abu, tiga langkah berjajar dalam satu panel krem membulat dengan pemisah vertikal. Di ponsel tiga langkah ditumpuk dengan pemisah horizontal. Isi dan tautan katalog dipertahankan. Build dan diff --check lulus, visual browser terbaru belum diperiksa.

Kartu peran mengikuti referensi terbaru: Volunteer putih dengan judul sans-serif gelap, ikon centang primer dan tombol outline; Organizer ungu kebiruan dengan teks putih, centang serta tombol hijau muda. Sudut membulat dan jarak konten konsisten. Isi tiga langkah serta route pendaftaran menurut role tetap, tidak menambahkan harga atau fitur dari referensi. Build dan diff --check lulus; visual browser terbaru belum diperiksa.

Ikon panah diseragamkan atas permintaan pengguna: seluruh karakter panah Unicode/entity pada view menjadi komponen SVG arrow-icon (right/left/down/up-right), mengikuti currentColor, ukuran 16px, dekoratif aria-hidden. Mencakup landing, katalog/detail, navbar publik, autentikasi, serta tautan tersisa di dashboard admin/detail volunteer. Route dan label tetap. Build dan diff --check lulus; render Blade/browser terbaru belum diverifikasi.

### Aksen aprikot - 16 November 2026
Atas permintaan pengguna, aksen hijau muda publik diganti aprikot #ffb86b untuk mendampingi primer ungu kebiruan #7047ff. Tombol, CTA, ikon, nomor dan ilustrasi memakai token yang sama; hover #f5a451/#ffd4a3. Kulit karakter tetap #e8b28e. Build dan diff --check lulus; belum diperiksa visual browser terbaru.

Palet akhir sesuai permintaan kembali ke warna awal: biru #2563eb, oranye #fb923c, navy tetap #0f172a. Hover/aksen/ilustrasi diselaraskan kembali; kulit karakter alami dan seluruh tata letak terbaru tetap dipertahankan. Area krem dan pita putih tetap sesuai keputusan sebelumnya. Build dan diff --check lulus.

Revisi palet terakhir pengguna: kembali ke ungu kebiruan #7047ff dan hijau muda #c8f45b, termasuk hover, tombol, aksen dan ilustrasi; kulit alami serta tata letak tetap. Build dan diff --check lulus.

Header landing: menu desktop dipusatkan dalam ruang setelah logo, gap 18px dan lebar container maksimal 1120px. Buat akun disorot tombol hijau muda; drawer ponsel tetap. Pot sudah hijau muda; badan/corong/gagang megafon kini hijau muda juga. Kulit karakter tetap alami. Build dan diff --check lulus; visual browser terbaru belum diperiksa.

Katalog/detail publik: header navy ringkas dan sticky disamakan dengan landing. Tombol daftar mobile/desktop memiliki aturan visibility eksplisit agar tidak ganda. Ditambahkan tombol Kembali merah di katalog menuju beranda; detail menuju katalog. File public-refinement.css mengurangi ukuran font/kontrol/kartu/jarak sekitar 20% hanya pada halaman publik interior. Build dan diff --check lulus. Pemeriksaan live browser katalog 390/1440px lulus tanpa overflow: header 57/62px, CTA desktop satu dan mobile tertutup sesuai drawer. Screenshot desktop diperiksa; pengujian pertama sebelum aset Vite selesai dimuat gagal lalu ulang setelah aset siap lulus. Detail dengan data event belum diperiksa visual karena katalog kerja kosong; tidak membuat fixture di database kerja.

Revisi navigasi publik sesuai persetujuan pengguna: katalog tidak lagi memuat tombol kembali; detail event memakai tautan kecil Kembali ke kegiatan dengan ikon panah SVG, teks abu gelap dan tanpa kotak. Tombol kembali dalam panel volunteer tetap mengikuti style panel. Build dan diff --check lulus; visual detail terbaru belum diperiksa.

Palet publik diperhalus sesuai keluhan tampilan HP: biru netral #3566a8 (hover #294f83), hijau sage #c4d8bd (hover #aec8a5), dengan turunan biru pucat untuk teks pendukung dan hover. Ilustrasi diselaraskan, kulit tetap alami. Browser live pada lebar 390/1440px memastikan token baru termuat, tidak ada overflow, header 57/62px, CTA tidak ganda. Screenshot halaman penuh mobile diperiksa. Build dan diff --check lulus; perbedaan kalibrasi layar perangkat fisik tidak diuji.

Arahan palet terbaru: biru publik disamakan dengan panel #2563eb/hover #1d4ed8. Aksen sage diganti aprikot pucat #f6d2aa/hover #edbd86 agar lebih lembut; teks di aksen tetap navy. Ilustrasi dan semua turunan warna ikut diselaraskan; kulit tetap alami. Build dan diff --check lulus.
## Notifikasi terbaru pada Ringkasan Aktivitas Volunteer

Daftar memakai satu panel membulat dengan pemisah tipis dan mengikuti lebar
panel Ringkasan Aktivitas di atasnya. Ikon berukuran sama
sejajar dengan judul. Isi menghubungkan notifikasi dengan kegiatan dan posisi
yang dapat diakses Volunteer, memberi arahan sesuai jenis notifikasi, dan
menampilkan waktu WIB serta indikator belum dibaca. Untuk asesmen yang masih
tersedia, tampilkan tenggat dan tautan tindakan. Setelah kesempatan berakhir,
arahkan ke detail lamaran untuk melihat status terbaru.

Gunakan panel dan baris yang sama untuk Admin dan Organizer, termasuk Organizer
yang menunggu verifikasi, serta di halaman Semua notifikasi ketiga peran.
Organizer melihat konteks lamaran, kegiatan atau transaksi yang dimilikinya dan
tindakan yang sesuai. Admin dapat membuka antrean verifikasi organisasi dari
notifikasi pengajuan. Status baca dan waktu WIB berada dalam satu kelompok.
