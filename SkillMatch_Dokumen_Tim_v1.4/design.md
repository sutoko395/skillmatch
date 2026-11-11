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

Pertahankan hero beranda baseline "Hubungkan Talent Relawan dengan Event Terbaik", identitas indigo, tombol Masuk/Daftar dan footer tim. Gunakan layout/navbar SF agar Inter dan Alpine tetap dimuat sekali. Pengguna yang login melihat CTA menuju tujuan sah sesuai role/status melalui redirect bersama; jangan menyebut halaman pengguna sebagai dashboard analitik. Teks promosi tidak mengklaim engine matching yang belum tersedia sudah berfungsi. Pemberitahuan katalog/matching yang belum tersedia hanya keterangan pendukung, bukan pengganti seluruh beranda.

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
