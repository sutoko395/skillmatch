## Hierarki font screening dan kecocokan - 15 November 2026

Branch feature/organizer-ui-refinement dasar367a7de. User meminta hierarki font
kartu screening/matching tanpa mengubah ukuran card. Hanya CSS scoped wrapper
review EO berubah: judul16, isi12, header11, keteranganscreening11, skor26/pembagi13/
label11. Padding/grid/lebar/komponen bersama/rolelain serta perubahan lokal tetap.
Build/diff lulus. FixtureQA empat baris screening sintetis dirender HTTP200 dengan
rollback; browser4keadaan390/768/1440 lulus12kombinasi, overflow/Alpine/drawer.
Screenshot desktop ditinjau. Tidak menambah tes untukfont; backend tidakdiubah.
Design/UAT diperbarui, dokumen bisnis/kontrak/setup masih berlaku. Tidak commit.

# Pindah penutupan seleksi ke Kelola Pelamar - 15 November 2026

Branch feature/organizer-ui-refinement dasar367a7de. Pengguna menyetujui pemindahan
Tutup seleksi tertunda dari Pelaksanaan ke daftar pelamar. Seluruh perubahan lokal
sebelumnya dipertahankan. Form POST route existing/alasan/CSRF/confirmed dipindah
utuh ke applications/index #tutup-seleksi, hanya pending>0/eventnonterminal.
ApplicationController menghitung pending seluruh event memakai SelectionService::PENDING,
tidak dipengaruhi filter/pagination. ExecutionController hasPendingSelection sama
dengan guardcompletion (pendingstatus atau attemptin_progress); viewcompletion
menampilkan pemberitahuan/link Kelola Pelamar, bukan formreject. Dua-kolomtindakan
pelaksanaan dihapus pembungkusnya karena hanya completion tersisa. Service/route/
audit/notifikasi/guarddeadline/idempotence/owner existing tetap, no migration.

25 tes/203 assertions OrganizerApplicantOverviewTest+A3ExecutionTest+
OrganizerExecutionOverviewTest lulus MySQL khusustes rollback. Regresi memeriksa
lokasiform, count lintasfilter, tautanpelaksanaan, submitredirect ke daftar, pending
hilang setelahclose, serta terminal tanpaaksi; serviceflow dan hakaksesexisting
ikut lulus. Build/view:cache/diff lulus. Render daftar terisi/kosong/filtered dan
detail kandidat HTTP200 rollback; browser390/768/1440 diperiksa. Pelaksanaan
editable/completed juga dirender HTTP200; noticepending dicakup HTTP featuretests,
tidak di-submit melalui browser. Design/UAT/briefA3 diselaraskan; PRD/kontrak/README
masih berlaku karena aturan bisnis/endpoint/setup tetap. Tidak commit/push/merge.

## Hierarki font kartu peserta - 15 November 2026

Branch feature/organizer-ui-refinement dasar367a7de; user meminta mainkan ukuran
font tanpa menambah card. Hanya CSS scoped kartu peserta diubah: nama16, posisi12,
progres13, tanggal/jam13, status/tombol/Catatan12, Edit11; ikon disesuaikan. Tidak
mengubah padding/grid/dimensi card/tombol atau backend/form. Perubahan lokal lain
tetap. Build/diff lulus; render editable/completed HTTP200 rollback; browser6
kombinasi390/768/1440 lulus toggle/note/cancel/edit/final/overflow. Screenshot
desktop ditinjau. Design/UAT diperbarui, dokumen bisnis/setup masih berlaku.
Tidak menambah tes unit untuk ukuran font; tidak commit/push/merge.

# Perbaiki token CSRF bootstrap absensi - 15 November 2026

Branch feature/organizer-ui-refinement dasar367a7de. Pengguna melaporkan Cannot
read properties of null (reading content) pada proses otomatis. Akar masalah:
JS baru membaca meta csrf-token, sedangkan layout sidebar EO belum menyediakannya.
Ini kesalahan integrasi pada implementasi bootstrap sebelumnya; unit mock token
ada dan QA statis hanya peserta sudah siap sehingga tidak menangkapnya.

Layout EO mendapat meta csrf-token dari csrf_token() sesi existing. JS memakai
optional access/guard; token kosong tidak mengirim request, menampilkan pemulihan
ramah pengguna. Error jaringan tidak membocorkan exceptionteknis, validation pesan
server tetap. CSRF tidak dinonaktifkan. Tidak mengubah DB/route/status/perilaku
absensi, layout role lain atau perubahan lokal. PRD/kontrak/design/brief/setup
existing tetap berlaku; UAT diberi regresi DOMmeta.

13 tes SessionAttendanceTest/169 assertions lulus (termasuk render aktual meta
CSRF saat pending),5 tes Node lulus (missingtoken/no unsafePOST, networkmessage,
oncePOST/CSRF/busy/retry), build/view:cache/diff lulus. Browser390/768/1440 pada
editable/completed memeriksa kehadiran token DOM tanpa mencetaknilainya serta
regresiUI. Submit/reload otomatis pada browser loginlive belum diujiend-to-end;
serverendpoint dan transportJS dicakup tes. Tidak commit/push/merge.

# Langsung tampil sesi untuk legacy belum dicatat - 15 November 2026

Branch feature/organizer-ui-refinement dasar367a7de; pengguna meminta melewati
layar checkbox/persiapan unrecorded pada screenshot. Existing serviceprepare/
prepareApplication/prepareLocked dan tes legacy ditinjau. Perubahan lokal lain
dipertahankan. Nonterminal accepted missing/unrecorded kini otomatis POST CSRF
batch100 dari Alpine lalu reload, tanpa menulis pada GET. Pending IDs sebelum
filter, tampil Memuat sesi sebentar, error+retry eksplisit. Tanpa JS fallbackPOST.
Legacy yang sudah present/absent dan terminal dilindungi, tidak direset otomatis;
konfirmasi/reason konversi recorded existing tetap. Scope screenshot unrecorded
terpenuhi; tidak membuat bukti hadir setiap sesi dari status lama per kegiatan.

Tambahan POST organizer.attendance-sessions.initialize / initializeSessions /
initializeUnrecorded; satu transaksi Event/App berurutid/Attendance, idempotent.
Hanya accepted ownerevent, archived unrecorded note/meta sebelum prepareLocked;
existing sesi/recorded dilewati. Invalid snapshot rollback, tidak menebakjadwal.
No migration/dependensi/status/notification baru. resources/js/attendance-session-setup
terdaftar pada Alpine existing tunggal. UI/form seleksi/record/note/final tetap.

33 tes/307 assertions SessionAttendanceTest+A3ExecutionTest+OrganizerExecutionOverviewTest
lulus MySQL khusus skillmatch_testing rollback. Baru: GET readonly, pendingfilter,
missing/unrecorded init, archive, recordedpreserved, replay/audit, role/owner/id
mismatch/duplicate/size/nonaccepted, terminal dan invalidsnapshot rollback.3 tes
Node frontend lulus: sekaliPOST CSRF+busy, reload hanyasukses, failuretanpaloop,
explicitretry dan nopending tanpa request. Build/view:cache/diff lulus. Browser
editable/completed390/768/1440 lulus6 kombinasi toggle/cancel/edit/final/overflow.
HTTP bootstrap endpoint diuji via feature tests; POST otomatis+cookie/reload pada
browser live belum diuji end-to-end (static fixtures tidak menjalankan bootstrap
karena sesi sudah disiapkan). Tidak mengklaim semuaUAT/reviewtim lulus.

PRD/kontrak/design/UAT/briefA3/handoff sesi diperbarui; READMEsetup tetap berlaku.
Demonstrasi: acceptedlegacyunrecorded -> Kelola pelaksanaan -> sesi otomatis ->
Lihat sesi -> Tandai hadir; ulanghalaman tidak menggandakan sesi. Data kerja tidak
direset/dimigrasikan lewat CLI. Tidak commit/push/merge.

## Rapatkan aksi dan buang ruang bawah sesi - 15 November 2026

Branch feature/organizer-ui-refinement, dasar367a7de. Pengguna meminta tautan
lebih rapat dan membuang ruang bawah. Akar layout: kolom tombol kanan menentukan
tinggi baris pertama grid. Kolom kanan kini span dua baris sehingga tidak mendorong
Catatan/Edit. Rowgap0 desktop, padding sesi8/6px, Edit padding0; mobile gap2px.
Hanya CSS scoped berubah; form/backend/perubahan lokal tetap. Build/diff lulus,
render editable/completed HTTP200 rollback; browser6 kombinasi390/768/1440 lulus.
Probe desktop mengukur Catatan3px dari tanggal, Edit1.5px dari badge, sisa bawah6px.
Toggle/editor/Batal/koreksi/final tetap lulus. Design/UAT diperbarui; dokumen bisnis
dan setup tetap berlaku. Tidak menambah tes unit/commit/push/merge.

## Kartu lebih padat dan Batal koreksi - 15 November 2026

Branch feature/organizer-ui-refinement, dasar367a7de; scope terbaru: rapatkan
Catatan/Edit, tambah Batal koreksi, pendekkan semua kartu. CSS scoped mengecilkan
padding header/sesi/form dan rowgap2px (mobile6px). Koreksi Batal typebutton
memakai status server @js, mengembalikan select dan tutup; disabled saatbusy.
Note Batal tetap. Route/backend/CSRF/revision/final guards dan perubahan lokal lain
tetap. Design/UAT diperbarui; PRD/kontrak/brief/README masih berlaku. Build,
view:cache dan diff lulus. Render editable/completed dua sesi HTTP200 rollback;
browser390/768/1440 memeriksa6 kombinasi buka-tutup, Batal note/koreksi reset,
no overflow serta final tanpa mutasi. Tidak menambah tes unit; tidak commit.

## Posisi waktu pencatatan dan aksi sesi - 15 November 2026

Branch feature/organizer-ui-refinement, dasar367a7de. Scope pengguna: Dicatat di
kanan, Edit tepat di bawah status, Catatan di bawah tanggal. Timestamp dipindah
ke actions kanan; status/edit/note tetap pada grid existing. Tablet diselaraskan
agar timestamp kanan tidak mengambil kolom jam. Backend/form/route dan perubahan
lokal lain dipertahankan. Design/UAT diperbarui; PRD/kontrak/brief/README berlaku.
Build/view:cache/diff lulus. Fixture dua sesi (satu hadir, satu belum dicatat) serta
completed HTTP200 rollback; browser390/768/1440 lulus6 kombinasi toggle/editor/
batal/koreksi/final/overflow. Screenshot desktop ditinjau. Tidak menambah tes
untuk perubahan layout; tidak commit/push/merge.

## Edit absensi ringkas di bawah status - 15 November 2026

Branch feature/organizer-ui-refinement, dasar367a7de; pengguna meminta penempatan
dan ukuran edit lebih ringan. Edit absensi kini tautan12px/pensil13px di bawah
status, tanpa border/backgroundbutton. Grid desktop/tablet/mobile diselaraskan
agar tidak bertumpuk dengan status, Tandai hadir atau editor. Form/route/backend
serta perubahan lokal lain dipertahankan. Design/UAT diperbarui, PRD/kontrak/brief/
README tetap berlaku. Build/view:cache/diff lulus; browser editable/completed di
390/768/1440 diperiksa. Tidak menambah tes untuk style; tidak commit/push/merge.

## Kartu ringkas, Batal catatan dan Edit absensi - 15 November 2026

Branch feature/organizer-ui-refinement, dasar367a7de; scope disetujui kartu lebih
pendek, Batal kanan Simpan catatan, pengganti titik tiga. Padding header diperkecil,
avatar64->48px desktop/44mobile. Batal typebutton menggunakan nilai note server
yang di-escape melalui @js, reset textarea lalu menutup editor; disabled saatbusy.
Edit absensi outline biru/pensil menggantikan menu titik tiga. Form/route/service/
CSRF/revision/final guards tidak berubah; perubahan lokal lain tetap.
Design/UAT diperbarui; PRD/kontrak/brief/README tetap berlaku (hanya UI). Build dan
view:cache lulus; browser diperiksa untuk Batal/reset dan koreksi. Tidak menambah
tes unit untuk perubahan UI ini. Tidak commit/push/merge.

## Penyelarasan visual dengan mockup check-in - 15 November 2026

Branch feature/organizer-ui-refinement, dasar367a7de. Pengguna menolak jarak/susunan
implementasi awal dan meminta mengikuti gambar mockup buatan sebelumnya, khususnya
Catatan di bawah tanggal. Gambar lokal asli dilihat sebelum mengedit. Scope hanya
view show/session-forms, CSS scoped EO serta assertion tanggal pada tes existing.
Perubahan lokal lain dipertahankan; backend note-only/record tetap.

Header peserta: avatar bulat64px, nama19px, posisi polos15px, progres16px/garis
pemisah dan toggle kanan. Sesi empat kolom tanggal/jam/status/aksi; Catatan berikon
tepat di bawah tanggal; editor Alpine fullwidth di bawah row. Menu titik tiga
mempertahankan koreksi (tanpa label tools panjang). Searchfullwidth berikon,
filter/rekap dipertahankan dalam details Filter peserta, count halaman di bawah.
Mobile ditumpuk. Tidak mengganti sidebar/branding atau menghapus legacy, form
completion, endpoint, CSRF/revision/owner/time/final guard. Tidak ada migration,
route/layanan baru atau dependensi. PRD/kontrak/brief/README tetap berlaku karena
aturan bisnis/interface/setup tidak berubah. Design/UAT diselaraskan.

31 tes/269 assertions SessionAttendanceTest+A3ExecutionTest+OrganizerExecutionOverviewTest
lulus; format tanggal assertion diselaraskan dengan tanggal Indonesia baru.
Build/view:cache/diff --check lulus. Render editable/completed dua sesi HTTP200
rollback MySQL test; browser390/768/1440 lulus6 kombinasi, toggle kartu, Catatan
tertutup/terbuka, bbox membuktikan link di bawah tanggal dan editor >=90% lebar,
menu koreksi berfungsi, no overflow serta completed tanpa formmutasi. Screenshot
desktop/mobile ditinjau terhadap referensi. Submit browser tidak diulang (endpoint
tercakup feature tests). Tidak mengklaim kesamaan pixel untuk semua viewport atau
seluruh UAT/review tim. Tidak commit/push/merge.

# Kartu sesi buka-tutup, check-in dan catatan - 15 November 2026

Branch feature/organizer-ui-refinement, dasar367a7de. Pengguna menyetujui mockup
kartu tertutup/terbuka, cari nama -> lihat sesi -> Tandai hadir; + Tambah catatan
membuka editor tersendiri. Perubahan lokal sebelumnya dipertahankan. Existing
session-forms/show/service/route/test ditinjau; UI lama selalu menampilkan form
select/note/save setiap sesi. Kini progress x/y hadir dan toggle, baris sesi
ringkas, tombol satu klik serta details note dan koreksi terpisah. Satu/many sesi
sama. Form legacyprepare/conversion/record tetap, seluruhfilter dan completion ada.

A3 menambah PATCH organizer.attendance-sessions.note + updateNote service dengan
lock Event/Application/Attendance/Session, policy owner, accepted, published_at,
waktu mulai dan final guards. Note-only mempertahankan status/recorded_at/by dan
lifecycle; revision sesi/parent naik, no-op identik idempoten. Audit metadata
revision tanpa note; tidak mengirim notifikasi baru. No migration/dependensi.
Record existing tetap dan tombol hadir mengirim note current; stale revision
mencegah overwrite. Redirect membuka kartu terkait; error draftnote mempertahankan
sesi asal. Catatan terlihat volunteer existing; readonly ketika final. Tidak
menambah bulk-save/auto-absent atau menandai sesi mendatang sebagai hadir.

Validasi A3ExecutionTest + SessionAttendanceTest + OrganizerExecutionOverviewTest
lulus31 tes/269 assertions pada MySQL khusus skillmatch_testing rollback. Kasus
baru note-only unrecorded/present, timestamp/status/lifecycle immutable, no-op,
revision stale lintas record/note, hapusnote, audit/outbox, aksesrole/owner, waktu,
validasi dan terminal (termasuk payload identik). Awal tes memperbandingkan instance
Carbon dengan assertSame; diganti rawattributes. Cek volunteer semula lewat
riwayat sebelum terminal (kosong sesuai aturan); diganti detail lamaran. Lalu lulus.
Build/view:cache/diff --check lulus. QA one-session dan two-session, editable dan
completed HTTP200 rollback; browser390/768/1440 lulus6 kombinasi per fixture,
buka-tutup, noteeditor tersembunyi/terbuka, directformstatus, separate noteform,
no overflow/drawer/Alpine dan final tanpa formmutasi. Screenshot desktop/mobile
one-session ditinjau. Submit nyata via browser tidak diuji; feature tests endpoint
lulus. Dua koneksi konkurensi nyata tidak diuji ulang; lock+revision digunakan.

PRD/kontrak/design/UAT/briefA3/handoff sesi diselaraskan. README tetap berlaku karena
setup tidak berubah. Demonstrasi: Kelola pelaksanaan -> cari peserta -> Lihat sesi
-> Tandai hadir -> Lihat/Edit catatan -> Simpan catatan; periksa detail relawan.
Tidak commit/push/merge atau mengklaim review tim/seluruh UAT lulus.

## Rekap sesi inline - 15 November 2026

Branch feature/organizer-ui-refinement, dasar367a7de. Pengguna menyetujui usulan
rekap inline di bawah judul Kehadiran peserta. Details/kartu Rekap sesi dan jumlah
catatan dihapus, diganti dl inline bertitik hijau/merah/kuning dan angka tebal.
CSS khusus details yang tidak dipakai dihapus. Hitungan sessionSummary global
tetap; filter/form/backend dan perubahan lokal lainnya dipertahankan.
Design/UAT diperbarui; PRD/kontrak/brief/README tetap berlaku. Build/view:cache
dan diff lulus; render editable/completed HTTP200 dengan rollback. Browser
390/768/1440 lulus6 kombinasi overflow/Alpine/drawer. Perubahan tampilan ringan
tidak menambah tes. Tidak commit.

## Hapus kartu rekap peserta - 15 November 2026

Branch feature/organizer-ui-refinement, dasar367a7de; pengguna meminta menghapus
empat kartu pada screenshot. Blok rekap peserta di show, CSS khusus dan partial
summary-icon yang baru ditambahkan pada tahap sebelumnya dihapus setelah pencarian
referensi memastikan tidak dipakai halaman lain. Perubahan lokal lainnya tetap.
Query summary/controller, Rekap sesi, filter, peserta, absensi dan completion
tidak diubah. Design/UAT diselaraskan; PRD/kontrak/brief/README tetap berlaku.
Perubahan hanya tampilan; tidak menambah tes. Build/view:cache/diff diperiksa.
Tidak commit/push/merge. Pemeriksaan screenshot tidak diulang untuk penghapusan ini.

# Perapian detail Pelaksanaan EO - 15 November 2026

Branch feature/organizer-ui-refinement, dasar HEAD367a7de; perubahan lokal menu,
profil, pelamar dan lock absensi dipertahankan. Pengguna menyetujui desain detail
pelaksanaan dan pemangkasan penjelasan. Existing view367a7de/1648250 ditinjau;
baseline HTML tersimpan lokal. Sebelum: tabel3 kolom, rekap/teks panjang, tautan
kembali polos. Sesudah: Kembali merah kiri atas; header/jadwal berikon, empat
kartu rekap peserta, details rekap sesi, filter gaya EO/Reset, kartu peserta dan
subcard sesi; dua kartu tindakan ringkas. Data/filter/form/akses tetap.

Show/session-forms dan partial summary-icon lokal dipakai ulang; CSS scoped EO.
Tidak ada controller/service/route/schema/migration/dependensi baru. Seluruh
POST/PATCH/CSRF/revision, syarat waktu/owner/accepted, legacyprepare reason dan
confirmed, arsip/notefinal, completion confirmed/confirm_zero tetap. Penjelasan
panjang rekap/pengantar waktu/petunjuk tabel dihapus, copy konversi/seleksi/final
lebih singkat; risiko penutupan/absensi final dan sesi belum dibuka tetap jelas.
Design/UAT diperbarui; PRD/kontrak/briefA3/README tetap berlaku karena aturan
bisnis, interface, setup dan kepemilikan tidak berubah.

Validasi A3ExecutionTest + SessionAttendanceTest + OrganizerExecutionOverviewTest
lulus29 tes/232 assertions pada MySQL skillmatch_testing dengan rollback. Build
Vite, view:cache dan diff --check lulus. QA accept -> event selesai secara jadwal
-> render absensi editable -> record -> complete -> render readonly melalui
service existing dengan fixture rollback. Kedua keadaan HTTP200; browser
390/768/1440 lulus6 kombinasi, overflow/Alpine/drawer dan completed tanpa mutation
form. Screenshot editable desktop/mobile ditinjau; submit melalui browser tidak
diulang, endpoint dicakup tes existing. Tidak mengklaim seluruh UAT/review tim.
Helper QA awal gagal otorisasi karena fixture owner sudah diubah inactive untuk
QA profil; urutan diperbaiki sebelum mutasi fixture profil, lalu lulus.
Demonstrasi: Pelaksanaan -> Kelola pelaksanaan -> buka Rekap sesi/filter peserta
-> simpan per sesi -> periksa konfirmasi selesai. Tidak commit/push/merge.

# Hapus bagian yang ditunjuk pada detail pelamar - 15 November 2026

Persetujuan pengguna atas empat screenshot: hapus tautan Pelaksanaan dan kehadiran
beserta kartu keputusan bila kosong, footer screening waktu evaluasi, footer
matching snapshot dan paragraf pemisahan nilai assessment. Dasar tetap367a7de,
branch feature/organizer-ui-refinement; seluruh perubahan lokal dipertahankan.
Form keputusan dan alasan existing tidak dihapus. application-selection menambah
showExecutionLink defaulttrue, matching showEvaluationInfo defaulttrue; hanya
pemanggilan detail EO memakai false. Screening memakai prop existing false.
View EO merender keputusan jika ada alasan, under_review (aksi/pesan deadline),
atau accepted sebelum mulai (form undur). Role/halaman pemakai lain tidak berubah.
Tidak ada backend/route/schema/migration baru. PRD/kontrak/brief tetap berlaku
karena aturan bisnis tidak berubah; design/UAT diperbarui.

21 tes/154 assertions OrganizerApplicantOverviewTest+A3ExecutionTest lulus pada
MySQL khusus tes dengan rollback; mencakup teks/kartu kosong hilang serta alur
seleksi existing. npm build/view:cache/diff --check lulus. Perubahan ini belum
diperiksa ulang melalui screenshot browser; QA visual halaman pada tahap
sebelumnya tersedia. Tidak ada commit/push/merge.

# Daftar dan detail pelamar mengikuti gaya EO - 15 November 2026

Branch feature/organizer-ui-refinement; dasar HEAD367a7de. Lingkup disetujui:
Kembali dari daftar ke Kelola Pelamar, Kembali detail bergaya merah kiri atas,
hapus tab Semua Pelamar/Siap Ditinjau/Pelaksanaan dan rapikan tampilan seperti
halaman EO sebelumnya. Riwayat view367a7de/6798ed4 ditinjau; baseline HTML
tersimpan lokal untuk QA. Perubahan lokal profil/menu/absensi dipertahankan.

Daftar index kini row responsif dengan inisial/nama/lokasi/posisi/status, skor,
waktu WIB, jumlah dokumen dan tombol tinjau outline biru tanpa panah. Semua data
kolom lama tetap tersedia. Filter posisi memakai Terapkan/Reset gaya EO; count
paginator nyata. Navigasi tab dihapus, query tab=ready tetap didukung untuk link
existing (notifikasi/aktivitas), status Siap Ditinjau tidak dihapus. Kembali ke
organizer.applications.overview. Detail show Kembali ke daftar event yang sama,
ringkasan nama/status, evaluasi dua kolom desktop, subcard profil/posisi berikon,
dokumen outline biru, dan keputusan di bawah informasi. Tolak/undur outline
merah lewat CSS scoped, komponen selection/screening/matching role lain tetap.
Flash inline duplikat dihapus karena layout sudah merender toast EO existing.
Tidak ada controller/service/route/schema/migration/dependensi baru.

Validasi akhir OrganizerApplicantOverviewTest + A3ExecutionTest lulus21 tes/150
assertions: navigasi, tabs hilang, owner boundary, ready filter, urutan skor dan
transisi seleksi existing. Tambahan tes awal keliru memakai substring route detail
sehingga cocok dengan route form aplikasi; diperbaiki menjadi exact href.
Pemeriksaan lebih luas A3ApplicationTest menghasilkan34/35 lulus (207 assertions),
1 gagal pada test_volunteer_can_create_draft_application_for_published_event yang
mencari CTA publik lama Pilih posisi dan mulai lamaran. View publik/controller
lamaran tidak diubah tugas ini; kegagalan di luar halaman yang dirapikan dicatat,
bukan dianggap lulus atau diatasi dengan menghapus assertion. Perlu pemilik modul
menyelaraskan tes CTA katalog terhadap implementasi publik yang disepakati.

Build Vite, view:cache dan diff --check lulus. Fixture daftar terisi/kosong/filtered
serta detail under_review dengan assessment dirender HTTP200 dan rollback pada
MySQL khusus skillmatch_testing. Browser390/768/1440 lulus12 kombinasi, overflow,
Alpine dan drawer; screenshot desktop/list/detail serta detail mobile ditinjau.
Form seleksi dipertahankan dan diuji server; submit/download dokumen sungguhan
melalui browser belum diuji. Design/UAT diperbarui; PRD/kontrak/briefA3/README
masih berlaku sebab aturan bisnis, interface, setup dan kepemilikan tetap.
Demonstrasi: Kelola Pelamar -> Lihat pelamar -> Kembali; buka lagi -> Tinjau
lamaran -> lihat evaluasi/dokumen/keputusan -> Kembali. Tidak commit/push/merge.

# Profil Organisasi mengikuti gaya EO - 15 November 2026

Branch feature/organizer-ui-refinement, dasar HEAD367a7de. Lingkup disetujui:
merapikan Profil Organisasi dengan gaya halaman EO sebelumnya dan Edit teks/border
biru. Existing pada edit.blade.php (riwayat1648250) berupa header/statuscard dan
satu daftar data panjang; form modal, upload serta preview dokumen dipertahankan.
Perubahan lokal menu/absensi sebelumnya tidak ditimpa. Baseline HTML disimpan
lokal untuk QA sebelum mengedit.

Tampilan existing profil kini tiga kartu: identitas/status/tentang/alamat/website,
kontak penanggung jawab, dan dokumen verifikasi. Nama20px, section17px, isi14px,
label12px; ikon sesuai dan subcard slate seperti detail kegiatan. Grid tiga kolom
menjadi satu di mobile; teks panjang membungkus. Edit Profil berikon pensil dan
Lihat KTP berikon mata memakai outline biru36px existing. Batal modal outline
merah; dialog EO punya area scroll sesuai viewport serta Escape/tombol tutup.
Data/status/catatan admin, form create/error, CSRF/payload, konfirmasi verifikasi
ulang serta preview route dokumen privat tetap tersedia. Tidak ada controller,
route, model, schema, migration, layanan atau dependensi baru.

Bukti: ProfileTest, OrganizerKtpUploadTest, PhoneValidationTest, IntegratedFlowsTest
lulus23 tes/291 assertions pada MySQL khusus skillmatch_testing dengan rollback.
Build Vite dan view:cache lulus. QA profil aktif, revisi dan baru dirender HTTP200,
rollback fixture; browser390/768/1440 memeriksa9 kombinasi, drawer/Alpine/overflow
serta dialog Edit pada profil aktif. Screenshot desktop/mobile/dialog ditinjau.
Form simpan/upload dan hak akses dicakup tes existing; tidak mengklaim submit
upload dokumen sungguhan melalui browser. Tidak mengklaim seluruh UAT/review tim.
Design dan UAT diperbarui; PRD/kontrak/brief SF/README tetap berlaku karena aturan
bisnis, interface, setup dan kepemilikan tidak berubah. Demonstrasi: Profil
Organisasi -> Edit Profil -> scroll form -> Batal; data existing tetap terlihat.
Tidak ada commit/push/merge.

# Penyelarasan Ubah Paket dan daftar EO - 15 November 2026

Branch feature/organizer-ui-refinement, dasar HEAD367a7de. Perubahan lokal menu
serta penguncian absensi sebelumnya dipertahankan. Lingkup disetujui: Kembali
merah kiri atas halaman pilih/ubah paket ke detail event; hapus badge Publikasi/
Pelaksanaan di Kelola Pelamar dan Publikasi/Jadwal di daftar Pelaksanaan; tambah
filter dengan komponen gaya sama seperti Kelola Pelamar. Metadata tanggal/jam/
lokasi, jumlah peserta, aksi dan kelompok tetap tersedia.

ExecutionController menerima search literal max255 + phase today/ongoing/history,
memfilter sebelum paginate12 dan mempertahankan query pagination. Kondisi sama
dengan kelompok existing; batas owner/published/tanggal WIB serta absensi tetap.
Tidak ada migration, route, schema, dependensi atau CSS bersama baru. Dokumen
design/kontrak/UAT diperbarui. PRD/brief/README tetap berlaku karena tidak mengubah
aturan bisnis, kepemilikan modul atau setup. Tidak ada commit/push/merge.

Validasi: OrganizerEventMenuVisibilityTest, OrganizerApplicantOverviewTest,
OrganizerExecutionOverviewTest dan A2EventTest lulus27 tes/227 assertions pada
MySQL khusus skillmatch_testing dengan rollback. Kasus filter meliputi tiap
kelompok, batas pemilik/tanggal/publikasi, pagination, wildcard literal, validasi,
empty/reset dan tidak mengubah state. npm build, view:cache dan diff --check lulus.
Fixture7 keadaan dirender HTTP200; browser390/768/1440 memeriksa21 kombinasi
halaman/viewport, overflow, Alpine dan drawer; seluruh probe lulus, tetapi helper
berakhir exit1 karena timeout Browser.close setelah pemeriksaan selesai.
Screenshot desktop Pelaksanaan dan
Paket ditinjau. Render memakai fixture sintetis, bukan data/demo pengguna; tidak
menguji pembayaran Sandbox end-to-end atau seluruh UAT. Demonstrasi: buka Ubah
Paket untuk Kembali; Kelola Pelamar tanpa badge; Pelaksanaan pilih tahap/cari nama,
Terapkan lalu Reset atau pindah halaman.

# Absensi final ketika kegiatan ditandai Selesai

15 November 2026; feature/organizer-ui-refinement / dasar HEAD367a7de. Perubahan
menu sebelumnya masih uncommitted dan dipertahankan. Pengguna menyetujui absensi
berhenti saat Selesai, menggantikan izin koreksi completed dengan alasan.
Riwayat kode A3 utama f89fd78 dipertahankan selain lingkup penguncian ini.

Selesai: AttendanceSessionService::recordLocked menolak completed/cancelled setelah
lock sebelum no-op/revision/write; EventExecutionService::record memberi guard
sama sebelum cabang parent/legacy. reason tidak dapat melewati guard. Prepare
terminal juga ditolak sebelum return sesi existing. Hapus cabang koreksi completed
serta audit reason post_completion_correction untuk operasi baru; histori lama
beserta correction_reason tidak dihapus. Lock Event existing sama dengan complete,
sehingga guard membaca state yang sudah dikunci, bukan hanya model stale/UI.
Prasyarat completion, awal event/sesi, peserta accepted, owner/role, revision dan
aturan cancelled tetap. Tidak ada H+1/migration/route/status/dependensi baru.

UI: banner Absensi sudah final, form simpan/koreksi sesi maupun legacy dihilangkan
pada completed; status/timestamps/catatan tersimpan tetap bisa dibaca. Checkbox
completion existing menjelaskan absensi dikunci setelah selesai. Tidak menambah
alur persetujuan baru atau mendesain ulang halaman. PRD/kontrak/design/UAT/brief
A3 diselaraskan termasuk mengganti aturan normatif koreksi completed lama.
Handoff A3/sesi diberi catatan keputusan terbaru tanpa menghapus bukti historis.
README tetap berlaku karena setup/perintah/dependensi tidak berubah.

Bukti: A3ExecutionTest + SessionAttendanceTest + OrganizerExecutionOverviewTest +
OrganizerEventMenuVisibilityTest lulus32 tes/270 assertions. Pengujian awal gagal
pada perbandingan fixture legacy yang belum di-refresh (default DB belum ada pada
model in-memory); diperbaiki menjadi snapshot fresh DB. Setelah menambahkan
read-only note, SessionAttendanceTest lulus9 tes/93 assertions. Kasus mencakup
absensi normal, setelah jadwal lewat sebelum completion, prasyarat sesi final,
blocked completed dengan/tanpa alasan/payload sama melalui parent/sesi/legacy,
prepare ditolak, status/note/revision/recorded_at/rekap/history/audit/outbox tetap,
form simpan hilang, catatan final masih terlihat, owner/role/pre-start/revision/
cancelled existing. DB MySQL khusus skillmatch_testing dengan transaction rollback,
bukan DB kerja; tidak ada reset/migrate. Build/view:cache/diff --check lulus.

QA fixture menempuh accept -> record sesi -> complete melalui service existing,
render detail completed HTTP200 lalu rollback. Browser390/768/1440 lulus, banner
ada, tidak ada form mutasi di main (form GET filter tetap), tombol simpan tidak
ada, drawer/overflow tetap. Probe awal sempat menghitung POST logout sidebar;
dibatasi ke main sesuai lingkup absensi lalu lulus. Screenshot desktop ditinjau.
Konkurensi record-vs-complete nyata dengan dua koneksi tidak diuji ulang; lock
Event existing tetap dipakai. Tidak mengklaim seluruh UAT/review tim lulus.

Demo: catat semua sesi setelah mulai, tunggu semua jadwal berakhir, pastikan
seleksi/asesmen final, centang konfirmasi lalu Selesaikan kegiatan. Setelahnya
kehadiran/read-only note tetap ada dan semua update ditolak, termasuk request
lama dengan alasan. Tidak membuka kembali kegiatan/data historis. Tanpa commit,
push, merge atau deployment. Tidak ada blocker/dependensi baru.

# Visibilitas menu berdasarkan publikasi dan hari pelaksanaan WIB

15 November 2026; branch feature/organizer-ui-refinement, dasar HEAD367a7de
(Mengubah Tampilan EO). Working tree awal bersih; perubahan UI sebelumnya sudah
ada pada commit tersebut dan dipertahankan. Pengguna menyetujui pembagian menu
serta pencarian/filter Kelola Pelamar setelah brainstorming.

Selesai: ApplicationController::overview hanya published_at terisi termasuk
riwayat. Filter GET search literal/trim max255 + recruitment open/closed/history,
AND/owner-scoped, validasi, reset, pesan hasil kosong dan pagination12 menyimpan
parameter. Open mengikuti opens<=now<deadline dan nonterminal published; closed
sejak deadline pada published nonterminal; history terminal atau publikasi tidak
aktif. Sebelum masa pendaftaran/tenggat lama belum lengkap tetap lewat Semua.
Hasil seleksi/lamaran submitted tetap dapat dibuka; count submitted existing.
Kegiatan Saya tetap seluruh status termasuk draft.

ExecutionController::index hanya pernah published dan starts_at sebelum esok
00.00 WIB dalam UTC, dengan pengecualian terminal tetap riwayat meski batal dini.
Aktif mendahului riwayat pada pagination12; groups isi halaman Hari ini sebelum
jam mulai, Berlangsung dalam interval jadwal, Riwayat terminal/akhir lewat/
publikasi dihentikan. Jadwal hanya label turunan, tidak update lifecycle.
Lewat jadwal belum completed mendapat Menunggu penyelesaian. Count accepted
existing. View memakai thumbnail/metadata ikon/badge/button seperti Kegiatan Saya.
Filter Kelola Pelamar memakai gaya form yang sama. Semua query pemilik dari aktor.

Tidak ada migration/route/layanan mutasi/status tersimpan baru. Endpoint detail
existing tetap bisa diakses pemilik lewat URL, daftar ini bukan pembatasan hak
baru; aturan waktu/peserta/role/owner/session attendance/completion tetap di server.
Kemunculan pada awal hari tidak mengizinkan absensi sebelum waktunya. PRD,
kontrak, design, UAT dan brief A2/A3 diselaraskan. README tidak berubah karena
setup/dependensi/perintah tetap. Tidak ada commit/push/merge/review tim.

Bukti: suite OrganizerEventMenuVisibilityTest + kedua overview + A3ExecutionTest
+ SessionAttendanceTest lulus35 tes/264 assertions sebelum penajaman urutan/
label; final suite kedua overview + visibility lulus9 tes/77 assertions.
Kasus: published/nonpublished, riwayatcancelled/suspended/completed, count,
akses guest/role/pending/inactive/foreign owner, kombinasi search/filter,
wildcard literal, pagination, validasi, batasUTC16:59:59->17:00:00 (WIB00),
acara12siang muncul00, multi-hari, hari besok tersembunyi, akhir lewat belum
completed tetap unchanged. Kehadiran/sesi service regression lulus; tidak ada
DB kerja/reset/migrate dijalankan, memakai guard MySQL skillmatch_testing serta
transaction rollback. Build/view:cache/diff --check lulus.

Renderer5 keadaan HTTP200 dengan fixture sintetis dan rollback; Edge15 kombinasi
populated/empty/filtered pelamar dan populated/empty pelaksanaan x390/768/1440
lulus (overflow, Alpine, sidebar/mobile drawer). Screenshot pelamar desktop serta
pelaksanaan desktop/mobile diperiksa. Submit filter live di browser belum diuji,
HTTP filter/pagination diverifikasi feature test. Tidak mengklaim seluruh UAT
atau deployment/review tim selesai. Tidak ada blocker/dependensi baru.

Demo: refresh Kelola Pelamar untuk published/history, cari/pilih fase dan
Terapkan/Reset. Pelaksanaan terlihat mulai tanggal WIB (refresh halaman), label
Hari ini sebelum jam acara dan Berlangsung saat masuk jam. Buka Kelola pelaksanaan;
absensi tetap ditolak sebelum aturan waktunya. Riwayat tidak menghapus data.

# Notifikasi toast ringkas dan otomatis hilang

14 November 2026; feature/organizer-ui-refinement / dasar HEAD31d1fcf.
Pengguna meminta bentuk notifikasi lebih modern dan muncul beberapa detik.
Layout EO mengganti x-flash dengan x-organizer-flash baru, shared flash role lain
tetap. Toast kanan atas putih radius14/shadow, ikon status berwarna, judul13px,
pesan12px, close ?. Success/status5detik; hover/fokus pause/reset, lifecycle
membersihkan timer, transisi menghormati reduced-motion. Error persistent sampai
ditutup dan validasi tetap inline agar pesan penting tidak hilang. Tanpa JS
notice tetap terlihat. EventController correctText memakai Perubahan berhasil
disimpan. Tidak ada schema/route/layanan/dependensi baru. Design/UAT selaras;
PRD/kontrak/README tetap karena notifikasi presentasi dan copy saja.

A2EventTest + ActivityProfileSummaryTest19 tes/148 assertions lulus. Build,
view:cache/diff --check lulus. QA renderer4 keadaan HTTP200 pada MySQL khusus
tes/transaksi rollback; fixture toast success pada draf. Browser390/768/1440
lulus: toast terlihat/role status/lebar sesuai, otomatis hilang setelah5.4detik,
manual close setelah dibuka ulang; regresi nav/drawer/preview cancel cover.
Screenshot toast desktop/mobile ditinjau. Hover/fokus pause dan toast error
belum diuji durasi khusus di browser; dukungan di kode. Tidak mengklaim UAT/
review tim penuh. Demo: published > Edit > Simpan Koreksi, amati toast5detik atau
klik ?. Tidak ada commit/push/merge.

# Tombol Batal di koreksi teks

14 November 2026; feature/organizer-ui-refinement / dasar HEAD31d1fcf.
Pengguna meminta Batal di sebelah Simpan Koreksi. events/text menambah tautan
outline merah di kanan tombol simpan, menuju events.show#ringkasan tanpa submit.
Wrapper aksi flex-wrap/gap12px. Form/validasi/route mutasi tidak berubah.
Design selaras; PRD/kontrak/UAT/README tetap karena navigasi presentasi.
Build/view:cache/diff --check lulus. Tidak menambah/mengulang tes untuk tautan
ini; visual browser belum diperiksa ulang. Tidak ada migration/route/layanan/
dependensi baru, commit/push/merge. Demo: published > Edit > Batal.

# Samakan gaya detail published dengan draf

14 November 2026; feature/organizer-ui-refinement / dasar HEAD31d1fcf.
Pengguna meminta style/button/posisi detail published sama dengan draf. View
bersama sudah membawa subkartu/font/ikon/nav. Sisa secondary links events/show
kini gaya Edit biru minimum36px termasuk Lihat Asesmen/halaman publik/pelamar/
paket/transaksi; aksi merah dikecualikan. Koreksi teks published menjadi Edit
pensil di lokasi header Informasi kegiatan, aria-label/title menyatakan hanya
judul/deskripsi. Lihat halaman publik berikon external dan kelola pelamar users,
panah lama dihapus. Wrapper aksi posisi hanya dirender saat editable agar
published tidak menyisakan ruang aksi kosong. Form/route/validasi/otorisasi
serta larangan edit struktur setelah published tetap; tidak membuka kontrol
posisi/paket/cover. Design/UAT diperbarui; PRD/kontrak/README tetap berlaku.
Tidak ada migration/route/layanan/dependensi baru.

A2EventTest + OrganizerAssessmentTest + EventCoverUploadTest lulus26 tes/
229 assertions, termasuk pembatasan edit/upload published dan foreign owner.
Build/view:cache/diff --check lulus. Renderer4 keadaan HTTP200 pada MySQL khusus
tes dengan transaksi fixture rollback. Browser draf/published x390/768/1440
lulus6 kombinasi nav/highlight/offset/overflow/drawer dan preview/batal cover
pada draf. Screenshot published desktop ditinjau. Mutasi live published melalui
browser belum diuji ulang; tidak mengklaim UAT/review tim penuh. Demo: bandingkan
detail draf dan published, klik Edit published menuju koreksi teks saja.
Tanpa commit/push/merge.

# Border Kelola Asesmen biru

14 November 2026; feature/organizer-ui-refinement / dasar HEAD31d1fcf.
Pengguna meminta garis kotak Kelola Asesmen biru. CSS organizer-assessment-link
kini border #3b82f6 dan hover #2563eb. Teks biru/ukuran/latar/tautan tetap.
Design diperbarui; PRD/kontrak/UAT/README tetap karena warna border saja.
Build/diff --check lulus. Tidak menambah/mengulang tes; browser belum diperiksa
ulang. Tanpa migration/route/layanan/dependensi baru, commit/push/merge.

# Bedakan Batalkan draf dan Batalkan kegiatan

14 November 2026; feature/organizer-ui-refinement / dasar HEAD31d1fcf.
Pengguna menyetujui usulan label pembatalan berdasarkan fase dan penghapusan
kalimat teknis layanan. Events/show memakai isUnsubmittedDraft untuk status
draft tanpa submitted_at/published_at: judul Pembatalan draf, tombol Batalkan
draf, konfirmasi data tetap tersimpan. Fase lain tetap Pembatalan kegiatan/
Batalkan kegiatan serta konfirmasi proses lamaran. Hapus paragraf dependensi
layanan dari UI; backend integrasi tetap. Route/validasi/hak/transisi dan syarat
hapus draf tidak diubah. Design/UAT diselaraskan; PRD/kontrak/README tetap karena
label/konfirmasi presentasi saja. Tanpa migration/route/layanan/dependensi baru.

A2EventTest17 tes/118 assertions lulus; build/view:cache/diff --check lulus.
Tidak menambah tes copy; visual dan dialog browser belum diperiksa ulang.
Tidak mengklaim UAT/review tim penuh. Demo detail draf belum diajukan lalu
bandingkan dengan detail pending/published. Tidak menjalankan pembatalan data
kerja. Tanpa commit/push/merge.

# Teks Kelola Asesmen biru

14 November 2026; feature/organizer-ui-refinement / dasar HEAD31d1fcf.
Pengguna meminta teks Kelola Asesmen biru. Tautan assessments.edit pada detail
memakai organizer-assessment-link; CSS scoped biru #1d4ed8/hover #1e40af,
termasuk saat label Lihat Asesmen. Border/latar/ukuran/tujuan/kondisi tetap.
Design diperbarui; PRD/kontrak/UAT/README tetap karena warna teks saja.
Build/diff --check lulus. Tidak menambah/mengulang tes; visual browser belum
diperiksa ulang. Tanpa migration/route/layanan/dependensi baru, commit/push/merge.

# Status ringkasan asesmen: Draf dan pensil

14 November 2026; feature/organizer-ui-refinement / dasar HEAD31d1fcf.
Pengguna meminta Draf asesmen cukup Draf dan ikon berbeda dari ikon utama.
Events/show mengganti label dan path SVG status non-published menjadi pensil;
published tetap gembok, label belum dibuat tetap. Data/aturan/tautan tetap.
Design diselaraskan; PRD/kontrak/UAT/README tetap karena copy/ikon saja.
Build/view:cache/diff --check lulus. Tidak menambah/mengulang tes; visual browser
belum diperiksa ulang. Tidak ada migration/route/layanan/dependensi baru atau
commit/push/merge. Demo: detail > Asesmen yang masih draf.

# Hierarki dan ikon kartu ringkasan asesmen

14 November 2026; feature/organizer-ui-refinement / dasar HEAD31d1fcf.
Pengguna meminta ikon/hierarki agar ringkasan posisi/draf/jumlah soal/durasi
lebih nyaman dibaca. Events/show + CSS EO memberi clipboard biru dalam tile38px,
nama15px semibold, status12px file/gembok, metadata12px daftar/jam dengan gap
serta wrap. Semua ikon dekoratif. Status/nilai/fallback/belum dibuat dan link
Kelola/Lihat Asesmen tetap, tidak mengubah data/aturan. Design/UAT diselaraskan;
PRD/kontrak/README tetap karena presentasi saja. Tidak ada schema/route/layanan/
dependensi baru.

OrganizerAssessmentTest3 tes/36 assertions lulus; build/view:cache/diff --check
lulus. Renderer awal mendapat Windows Access denied saat rename compiled view;
helper QA diisolasi ke direktori compiled sendiri, render4 keadaan HTTP200
setelahnya pada MySQL khusus tes/transaksi rollback. Browser draf390/768/1440
memeriksa overflow/nav/drawer/cover; fixture draf belum punya assessment sehingga
metadata jumlah/durasi tidak tampil pada probe tersebut. Screenshot/fixture
jumlah2 soal5menit belum diperiksa khusus. Tidak mengklaim UAT/review tim penuh.
Demo: detail > Asesmen. Tanpa commit/push/merge.

# Tombol Kelola Asesmen

14 November 2026; feature/organizer-ui-refinement / dasar HEAD31d1fcf.
Pengguna meminta tiga perubahan screenshot: Kembali berkotak merah, Hapus soal
teks/border merah, Tambah soal plus teks/border biru. assessments/edit memakai
gaya EO existing organizer-detail-back, organizer-position-delete dan
organizer-info-edit; SVG plus dekoratif16px. Tambah soal native button mengganti
wrapper secondary tanpa mengubah x-ref/addQuestion/disabled50/busy; Hapus tetap
removeQuestion dan editable. Tujuan kembali ke #assessment tetap.
Design selaras; PRD/kontrak/UAT/README tetap karena gaya tombol saja. Tidak ada
migration, route, layanan, dependensi atau CSS global baru.

OrganizerAssessmentTest3 tes/36 assertions lulus. Build/view:cache/diff --check
lulus. Tidak menambah tes gaya; visual/interaksi browser belum diperiksa ulang.
Tidak mengklaim UAT/review tim penuh. Demo: detail editable > Kelola Asesmen,
tambah/hapus soal atau kembali. Tanpa commit/push/merge.

# Tombol Tambah/Edit/Hapus posisi

14 November 2026; feature/organizer-ui-refinement / dasar HEAD31d1fcf.
Disetujui: Tambah posisi plus dengan teks/outline biru; Edit posisi pensil dan
biru; Hapus posisi berkotak teks merah. Events/show memakai organizer-info-edit
existing untuk dua tautan dan CSS baru organizer-position-delete merah. Ukuran
minimum36px/padding6x12px/font13px, ikon16px; baris aksi align-center gap12px.
DELETE POST/CSRF/konfirmasi, tujuan tautan dan kondisi editable tetap.
Design diperbarui; PRD/kontrak/UAT/README tetap karena tampilan tombol saja.
Tidak ada migration, route, layanan atau dependensi baru.

Build/view:cache/diff --check lulus. Renderer empat keadaan HTTP200 pada MySQL
khusus tes dengan transaksi fixture rollback. Browser draf390/768/1440 memeriksa
layout/overflow/nav/drawer dan regresi preview/batal cover. Tes unit tidak
ditambah/diulang untuk ikon/warna tombol; mutasi hapus tidak dijalankan di browser.
Tidak mengklaim UAT/review tim penuh. Demo: detail editable > Posisi dan jadwal.
Tanpa commit/push/merge.

# Informasi kegiatan memakai dua subkartu atas-bawah

14 November 2026; feature/organizer-ui-refinement / dasar HEAD31d1fcf.
Pengguna meminta pola subkartu juga pada Informasi kegiatan, tersusun atas-bawah.
Events/show memberi class organizer-info-subcard pada dl metadata dan div Jadwal
rekrutmen. CSS scoped padding16/border slate200/latar slate50/radius10. Kartu utama,
ikon/font/Edit/data/aturan tetap. Design diselaraskan; PRD/kontrak/UAT/README
fungsi tetap, layout kedua kelompok kini dibingkai terpisah. Tidak ada migration,
route, layanan atau dependensi baru.

Build/view:cache/diff --check lulus. Renderer empat keadaan HTTP200 pada MySQL
khusus tes dengan transaksi rollback. Tes unit tidak ditambah/diulang untuk
class/padding ini. Browser draf390/768/1440 memeriksa layout/overflow/nav/drawer
serta regresi preview/batal cover. Probe awal berjalan sebelum render terbaru
selesai sehingga CSS lama tidak ditemukan; dijalankan ulang setelah render.
Tidak mengklaim UAT/review tim penuh. Demo: detail > Ringkasan. Tanpa commit,
push atau merge.

# Dua subkartu deskripsi dan jadwal posisi

14 November 2026; feature/organizer-ui-refinement / dasar HEAD31d1fcf.
Sesuai screenshot, pengguna meminta deskripsi/jadwal menjadi dua kartu kecil
di dalam kartu posisi utama. Events/show mengelompokkan deskripsi/skill/level/
persyaratan di kiri, mode dan seluruh slot jadwal WIB di kanan. Nama/kuota tetap
di atas, Edit/hapus tetap di bawah. CSS scoped slate50/border200/radius10/padding16,
dua kolom desktop dan satu kolom mobile existing. Data/transisi/hak/tautan/form
mutasi tetap. Design/UAT diselaraskan; PRD/kontrak/README tetap karena layout.
Tidak ada migration, route, layanan atau dependensi baru.

A2EventTest17 tes/118 assertions lulus. Build/view:cache/diff --check lulus.
Renderer empat keadaan HTTP200 di MySQL khusus tes dengan transaksi rollback.
Browser draf390/768/1440 lulus overflow/nav/drawer serta regresi cover preview/
batal; screenshot desktop ditinjau. Deskripsi posisi sangat panjang/multislot
belum diprobe khusus di browser. Tidak mengklaim UAT/review tim penuh. Demo:
detail kegiatan > Posisi & jadwal. Tanpa commit/push/merge.

# Rekomendasi ukuran gambar pada detail EO

14 November 2026; feature/organizer-ui-refinement / dasar HEAD31d1fcf.
Pengguna meminta informasi ukuran seperti form buat kegiatan. Helper di bawah
Ubah/Tambah gambar kini menampilkan format/maks5MB dan baris kedua Disarankan
1600 x 900 px (16:9), sama dengan event-cover-upload existing. Tidak menambah
validasi dimensi. Design diperbarui; PRD/kontrak/UAT/README tetap karena copy
presentasi saja. Build/view:cache/diff --check lulus. Tidak menambah/mengulang
tes; visual browser belum diperiksa ulang. Tidak ada schema/route/layanan atau
dependensi baru, commit/push/merge. Demo: detail kegiatan editable.

# Posisi bantuan format sampul

14 November 2026; feature/organizer-ui-refinement / dasar HEAD31d1fcf.
Pengguna meminta teks JPG/PNG/WebP maksimal5MB tepat di bawah tombol edit gambar.
Events/show mengelompokkan tombol dan helper dalam organizer-cover-picker, rata
kanan, controls align-start; Simpan/Batal terpisah. Input/aria-describedby dan
perilaku upload tetap. Design diperbarui; PRD/kontrak/UAT/README tetap karena
perubahan posisi saja. Build/view:cache/diff --check lulus. Tidak menambah/
mengulang tes; visual browser belum diperiksa ulang. Tidak ada schema/route/
layanan/dependensi baru, commit/push/merge. Demo: detail kegiatan editable.

# Ubah gambar langsung dengan kontrol di bawah sampul

14 November 2026; feature/organizer-ui-refinement / dasar HEAD31d1fcf.
Pengguna meminta klik Ubah gambar langsung pilih file dan tombol tidak menimpa
cover. Form inline events/show kini tombol di bawah gambar, hidden file input,
preview, Simpan/Batal dan pesan validasi/loading. eventCoverUpload existing
memiliki cancel untuk revoke objectURL, pulihkan preview awal dan kosongkan input.
Tidak memindah ke halaman edit; fallback noscript tetap tersedia.

Tambahan A2 PATCH events/{event}/cover (organizer.events.cover.update), controller
updateCover dan EventService::updateCover. Hanya file mengubah path/revision,
field lain diabaikan. JPG/PNG/WebP5MB, Policy update dan editable existing,
lock User/Event dan validasi ulang, audit event.cover_updated, cleanup file gagal
serta hapus cover lama afterCommit. Published/orders/entitlement/lifecycle tetap
terkunci. Tidak ada migration/dependensi baru. Design/kontrak/UAT diperbarui;
PRD/README tetap karena aturan bisnis/storage/setup existing tidak berubah.

Validasi: A2EventTest + EventCoverUploadTest23 tes/193 assertions lulus; termasuk
endpoint direct, field lain tetap, invalid file, foreign owner/role dan status
terkunci. Frontend4 tes lulus termasuk cancel mengembalikan saved preview.
Build/view:cache/diff --check lulus. Renderer4 keadaan HTTP200 di MySQL khusus
tes dengan transaksi rollback. Browser draf390/768/1440 lulus: tombol memanggil
input.click, pilihan File melalui DataTransfer menghasilkan blob preview,
kontrol berada di bawah gambar, tombol simpan siap dan batal reset; juga anchor,
drawer, overflow. Dialog OS dan submit file live via browser belum diuji;
backend upload diverifikasi lewat feature test. Penghapusan old file afterCommit
mengikuti pattern existing, test transaksional tidak mengeksekusi outer commit.
Tidak mengklaim UAT/review tim penuh. Demo: detail editable > Ubah/Tambah gambar
> pilih > Simpan atau Batal. Tanpa commit/push/merge.

# Edit sedikit lebih besar, pensil dan akses Ubah gambar

14 November 2026; feature/organizer-ui-refinement / dasar HEAD31d1fcf.
Permintaan pengguna: Edit dibesarkan sedikit, pensil, gambar bisa diubah.
Existing form sudah multipart, event-cover-upload dan EventService mendukung
ganti/hapus sampul saat editable. Dipakai kembali tanpa backend/schema baru.
Events/show kini mengevaluasi editable sebelum cover; overlay Ubah gambar pada
cover editable atau Tambah gambar saat kosong, menuju events.edit#cover_image.
Edit berikon pensil16px, minimum36px/padding6x12px/font13px, outline biru. CSS
memberi offset input dan overlay. Syarat editable/larangan ganti published,
koreksi teks, CSRF/otorisasi/validasi format5MB tetap. Design/UAT diselaraskan;
PRD/kontrak/README tetap karena alur upload/setup existing dipakai kembali.

A2EventTest + EventCoverUploadTest lulus21 tes/157 assertions, termasuk ganti/
hapus sampul, isolasi owner dan blokir setelah publikasi. Frontend cover upload
3 tes lulus (invalid, hapus, oversized). Build/view:cache/diff --check lulus.
Renderer empat keadaan HTTP200 di MySQL tes terpisah, transaksi rollback.
Browser draf390/768/1440 memeriksa layout/nav/drawer. Fixture browser tanpa
cover; overlay Ubah gambar dan submit file live belum dicek melalui browser.
Tidak mengklaim UAT penuh/review tim. Demo: detail editable > Ubah/Tambah gambar
> pilih foto > simpan form. Tidak ada migration/route/layanan/dependensi baru,
commit/push/merge.

# Rapikan Informasi kegiatan dan hapus bar status

14 November 2026; feature/organizer-ui-refinement / dasar HEAD31d1fcf.
Lingkup screenshot disetujui: hapus bar Moderasi/Publikasi/Pelaksanaan pada kartu
Informasi kegiatan, beri ikon dan hierarki font informasi lain pada kartu itu.
Events/show menghapus dl status saja. Metadata memakai ikon dekoratif dokumen,
pin, jam, kalender, clipboard, centang. CSS scoped: judul18px, subjudul14px,
label12px, isi14px, jarak24px. Tanggal dan jam WIB dipisah dua baris; rentang
mulai/akhir lengkap serta fallback Belum diisi. Catatan admin, Edit, bagian/form
lain, kondisi akses dan sumber nilai tetap. Status pada daftar/menu lain tetap.
Design/UAT selaras; PRD/kontrak/README tetap karena hanya presentasi. Tidak ada
migration, route, layanan atau dependensi baru.

A2EventTest lulus17 tes/118 assertions. Build/view:cache/diff --check lulus.
Renderer daftar/draf/pending/published HTTP200 menggunakan fixture sintetis di
MySQL khusus tes, transaksi rollback. Browser draf390/768/1440 lulus termasuk
anchor/highlight, overflow, drawer; screenshot desktop/mobile diperiksa.
Deskripsi/lokasi sangat panjang dan jadwal kosong belum diprobe di browser.
Tidak mengklaim UAT/review tim penuh. Demo: detail kegiatan > Informasi kegiatan.
Tanpa commit/push/merge.

# Kecilkan kotak Edit informasi kegiatan

14 November 2026; feature/organizer-ui-refinement / dasar HEAD31d1fcf.
Pengguna meminta kotak Edit lebih kecil. CSS organizer-info-edit kini tinggi
minimum32px, padding4x10px, font12px, radius6px dan align-self center. Sebelumnya
mewarisi minimum44px/padding9x14px/font13px. Warna/tautan/kondisi edit tetap.
Design diperbarui; PRD/kontrak/UAT/README tetap karena ukuran presentasi saja.
Build dan diff --check lulus. Tidak menambah/mengulang tes; visual browser belum
diperiksa ulang. Tanpa schema/route/layanan/dependensi baru, commit/push/merge.
Demo: tombol Edit pada kartu Informasi kegiatan yang masih editable.

# Label dan warna Edit pada Informasi kegiatan

14 November 2026; feature/organizer-ui-refinement / dasar HEAD31d1fcf.
Permintaan screenshot: Edit konfigurasi kegiatan cukup Edit, teks/border biru.
Tautan events.edit di events/show kini berlabel Edit dan class organizer-info-edit;
CSS scoped teks #1d4ed8/border #3b82f6, latar putih, hover/fokus biru. Aria-label
Edit informasi kegiatan. Link, posisi, kondisi editable dan tombol lain tetap.
Design diselaraskan; PRD/kontrak/UAT/README tetap karena presentasi saja.
Build, view:cache, diff --check lulus. Tidak menambah/mengulang tes untuk label/
warna; visual browser belum diperiksa ulang. Tidak ada migration/route/layanan/
dependensi baru, commit/push/merge. Demo: detail kegiatan yang masih editable.

# Navigasi bagian memenuhi lebar panel

14 November 2026; feature/organizer-ui-refinement / dasar HEAD31d1fcf.
Pengguna meminta rata kanan kiri. CSS organizer-section-nav a diubah dari
flex-shrink0 ke flex1 0 auto agar sisa lebar terbagi ke semua tombol. Teks tetap
center, layar sempit tetap scroll horizontal. Tidak mengubah Blade/anchor,
highlight atau alur. Design diperbarui; PRD/kontrak/UAT/README tetap berlaku.
Build dan diff --check lulus; render empat keadaan HTTP200 memakai transaksi
fixture sintetis MySQL tes terpisah lalu rollback. Browser draf390/768/1440
memeriksa anchor/highlight/offset/overflow/drawer. Tidak menambah atau mengulang
unit test untuk CSS ini. Tanpa migration/route/layanan/dependensi baru, commit,
push atau merge. Demo: buka detail kegiatan pada desktop.

# Navigasi bagian detail kegiatan diperjelas

14 November 2026; feature/organizer-ui-refinement, dasar HEAD31d1fcf.
Pengguna meminta tampilan menu anchor lebih proper. Sebelumnya deretan teks
bergaris bawah panel. Kini nav enam bagian menjadi panel putih border/radius12,
tombol44px, bagian aktif biru muda dan teks biru, hover/fokus keyboard. Sticky
16px desktop/80px mobile, deretan horizontal bisa digeser di layar sempit.
Isi/form/anchor tetap terbuka; tidak membuat tab yang menyembunyikan konten.
Alpine existing memantau klik/hash/scroll dengan aria-current=location. Klik
menahan highlight500ms untuk menghindari bagian pendek dekat bawah langsung
berubah; kembali ke atas selalu dihitung ulang. Tanpa JS anchor tetap bekerja.
Hanya events/show.blade.php dan CSS EO; tidak ada schema/route/layanan/dependensi
baru. Design/UAT diselaraskan; PRD/kontrak/README tetap berlaku.

A2EventTest lulus17 tes/118 assertions. Build, view:cache dan diff --check lulus.
Renderer empat keadaan HTTP200 pada MySQL tes terpisah dengan fixture rollback.
Edge12 kombinasi daftar/draf/pending/published x390/768/1440 lulus, termasuk
enam tujuan anchor, highlight klik, kembali ke atas, offset sticky/overflow,
drawer dan modal daftar. Probe awal menemukan highlight Paket berpindah ke
Tindakan dekat bawah; ditangani dengan prioritas pilihan klik. Pengetatan reset
highlight saat scrollY0 diverifikasi ulang pada draf di tiga viewport.
Screenshot desktop detail ditinjau. Fixture tanpa cover; cover aktual tidak
diuji ulang di browser. Tidak mengklaim UAT/review tim penuh. Demo: buka detail,
klik bagian/scroll halaman, geser nav di mobile. Tanpa commit/push/merge.

# Warna tombol Kembali detail kegiatan

14 November 2026; feature/organizer-ui-refinement, dasar HEAD31d1fcf.
Pengguna meminta Kembali menjadi merah. Tambah class organizer-detail-back pada
link existing di events/show; CSS scoped merah #dc2626, teks putih, hover
#b91c1c serta focus-visible. Ukuran/posisi/tujuan tautan dan tombol lain tetap.
Design diselaraskan; PRD/kontrak/UAT/README tetap berlaku karena hanya warna.
Build dan diff --check lulus. Tidak menambah/menjalankan tes untuk perubahan
warna ini; visual browser belum diperiksa ulang. Tanpa migration/route/layanan
baru, dependensi/blocker baru, commit/push/merge. Demo: detail kegiatan > Kembali.

# Hapus teks di sekitar gambar detail kegiatan

14 November 2026, feature/organizer-ui-refinement / dasar HEAD31d1fcf.
Sesuai screenshot pengguna: hapus deskripsi persetujuan/pembayaran/publikasi
tepat di atas cover serta lima langkah Pilih paket sampai Tinjau dan ajukan
tepat di bawahnya, di events/show.blade.php. Judul, Kembali, cover, nav bagian,
form dan aturan akses/transisi tetap. Design diselaraskan. PRD/kontrak/UAT/
README tetap berlaku untuk fungsi; stepper presentasi pada catatan historis
tidak lagi tampil. Tidak ada migration, route, layanan atau dependensi baru.
Perubahan terbatas presentasi, tidak menambah tes. Build, view:cache dan
diff --check lulus. Browser/visual belum diperiksa ulang untuk penghapusan ini.
Tidak ada commit/push/merge.

# Akun dan tombol Keluar pindah ke sidebar EO

14 November 2026; feature/organizer-ui-refinement, dasar HEAD31d1fcf.
Lingkup disetujui: hapus bar lama, pindah nama/role di atas Ringkasan Aktivitas
dengan garis/jarak seperti setelah Profil, pindah Keluar di bawah tautan situs
dengan highlight merah. Kode existing tidak memiliki footer; bagian akun berada
di topbar. Interpretasi tersebut dijelaskan kepada pengguna saat pengerjaan.
Layout sidebar dan CSS saja: akun dinamis, avatar inisial multibyte aman,
role Organizer; dropdown dihapus karena profil sudah ada di navigasi. Bar desktop
dihapus dan padding konten32px; bar mobile tetap untuk drawer. Logout memakai
route POST/CSRF existing, bukan tautan GET. Tidak ada schema/route/layanan baru.
Design/UAT diselaraskan; PRD/kontrak/README tetap karena auth/setup tidak berubah.

FoundationAccessTest + ActivityProfileSummaryTest lulus24 tes/189 assertions,
termasuk invalidasi sesi, POST/CSRF dan GET logout405 existing. view:cache,
build, diff --check lulus. Fixture renderer MySQL terpisah dengan transaksi
rollback, dua keadaan HTTP200. Browser enam kombinasi daftar/empty x
390/768/1440 lulus termasuk pemeriksaan lokasi akun, POST/token, urutan Keluar,
bar desktop tersembunyi, drawer dan modal. Screenshot desktop/drawer mobile
diperiksa. Logout live dengan klik browser belum diuji; UAT penuh/review tim
belum dilakukan. Demo di semua halaman EO, buka drawer di mobile untuk akun
serta Keluar. Tidak ada dependensi baru/commit/push/merge.

# Hapus header daftar dan perbesar Buat Kegiatan

14 November 2026; feature/organizer-ui-refinement / dasar HEAD 31d1fcf.
Disetujui lewat screenshot: hapus baris Semua kegiatan beserta jumlahnya,
perbesar tombol Buat Kegiatan dan beri ikon +. Sebelum: header dengan jumlah,
tombol font14/padding10x20. Kini header dihapus hanya pada events/index, tombol
font16/padding12x24 dengan SVG plus dekoratif20. Modal tetap dibuka oleh Alpine
existing. Filter/query/pagination, route/schema/layanan tetap. Design/UAT
selaras; PRD/kontrak/README tidak berubah karena hanya presentasi.

Build, view:cache dan diff --check lulus. Render dua keadaan daftar HTTP200
pada MySQL khusus tes dengan fixture transaksi rollback. Browser Edge enam
kombinasi populated/empty x 390/768/1440 lulus, termasuk modal/sidebar/overflow;
screenshot desktop ditinjau. Tidak menambah atau menjalankan ulang unit test
untuk perubahan presentasi ini; tidak mengklaim UAT penuh. Demo: buka Kegiatan
Saya dan klik + Buat Kegiatan. Tidak ada dependensi/blocker baru, commit/push/merge.

# Pencarian nama dan filter moderasi Kegiatan Saya

14 November 2026, feature/organizer-ui-refinement, dasar HEAD 31d1fcf.
Pengguna menyetujui pencarian dan filter moderasi saja. Sebelumnya: daftar
owner-scoped terbaru, pagination 12 tanpa filter. EventController::index,
events/index.blade.php dan CSS EO kini memberi form GET, empat status moderasi,
reset, pesan hasil kosong, jumlah terfilter dan parameter pagination. Query
pemilik selalu dari aktor; LIKE literal memakai bindings. Tidak ada migration,
route baru atau perubahan layanan mutasi. Design/kontrak/UAT diselaraskan;
PRD/brief/README tetap berlaku karena aturan bisnis dan setup tetap.

Validasi: OrganizerEventFilterTest + A2EventTest pada MySQL skillmatch_testing
terpisah lulus 20 tes/147 assertions. view:cache, npm run build, diff --check
lulus. Renderer dua keadaan HTTP 200 memakai fixture sintetis lalu rollback.
Edge enam kombinasi populated/empty x 390/768/1440 lulus termasuk overflow,
sidebar dan modal. Screenshot desktop/mobile diperiksa. Submit filter dan
pagination diuji HTTP pada feature test; submit ke server live melalui browser
belum diuji. Tidak mengklaim UAT/review tim penuh.

Demo: Kegiatan Saya > masukkan nama dan/atau pilih Status moderasi > Terapkan;
pindah halaman jika ada, Reset mengembalikan daftar. Tidak ada dependensi baru
atau blocker. Tidak ada commit/push/merge. Catatan terbaru ini melengkapi histori.

# Hapus penjelasan di bawah Semua kegiatan

14 November 2026, feature/organizer-ui-refinement / HEAD 31d1fcf. Kalimat
Moderasi, publikasi, dan pelaksanaan ditampilkan terpisah dihapus dari header
daftar sesuai permintaan pengguna. Hanya satu paragraf Blade; data, badge dan
alur tetap. Design diselaraskan; UAT/PRD/kontrak/README setup tetap berlaku.
Tidak menambah tes untuk penghapusan teks presentasi. Belum commit/push/merge.

# Hierarki ukuran dan ikon metadata Kegiatan Saya

14 November 2026, feature/organizer-ui-refinement / HEAD 31d1fcf. Pengguna meminta
teks kegiatan tidak menumpuk: judul 17 px desktop/16 px mobile, metadata 12 px
warna sekunder. Tanggal dan jam dipisah dengan ikon kalender/jam (tetap satu
baris bila cukup); pin lokasi pada baris berikutnya. Ikon inline SVG dekoratif
15 px, aria-hidden, disertai label sr-only. Lokasi/judul tetap membungkus tanpa
menghilangkan data. Waktu tetap dikonversi Asia/Jakarta dan dilabel WIB; jadwal
kosong tetap eksplisit. Badge 11 px serta warna/tombol outline/thumbnail tetap.

View organizer/events/index dan CSS scoped organizer-panel berubah. Tanpa route,
backend/query, migration, aset baru atau dependency. Design/UAT diselaraskan;
PRD/kontrak/README tetap berlaku. Salinan sebelum perubahan: organizer-event-list-
review/before-metadata-icons.blade.php (privat ignored). Build/view:cache/diff
--check lulus. A2EventTest: 17 tes / 118 assertion lulus pada MySQL khusus tes.
Render daftar terisi/kosong HTTP 200 dengan fixture sintetis rollback. Edge
6 kombinasi 390/768/1440 lulus tanpa overflow, drawer/Alpine aktif, modal Buat
Kegiatan dapat dibuka tanpa submit. Screenshot desktop/mobile diperiksa langsung.
Judul/lokasi panjang dan data jadwal kosong belum diprobe khusus di browser;
markup/CSS mempertahankan pembungkusan dan fallback jadwal. UAT akun nyata/review
tim belum dilakukan. Belum commit/push/merge.

# Menu mandiri Pelaksanaan & Kehadiran

14 November 2026, feature/organizer-ui-refinement / HEAD 31d1fcf. Pengguna
menyetujui menu baru setelah Kelola Pelamar serta penghapusan kartu pelaksanaan
di atas detail kegiatan. Implementasi menambah GET /organizer/execution dengan
nama organizer.execution.index di routes/a3.php, ExecutionController::index,
view organizer/execution/index dan link sidebar aktif untuk execution.*.

Daftar hanya event.organizer_id milik aktor, Gate create Event dan middleware
A3 existing; parameter request tidak memperluas pemilik. Menampilkan judul,
jadwal WIB, count accepted (sama kondisi dengan rekap existing), status, pagination
12 dan empty state. Semua status kegiatan milik EO tersedia untuk ditinjau;
link Kelola pelaksanaan menuju show existing tanpa bypass aturan pencatatan.
Kembali pada show menuju hub baru agar alur menu mandiri konsisten. Seleksi,
attendance/sesi, completion, Policy, schema/migration dan dependensi tetap.

Kartu navigasi atas detail dihapus, isi konfigurasi/koordinasi/asesmen/paket/
tindakan existing dipertahankan. Kontrak/desain/UAT diselaraskan; README setup
serta PRD bisnis tetap berlaku. OrganizerExecutionOverviewTest menambah verifikasi
pemilik, count accepted, batas role/pending/nonaktif, kosong dan penghapusan kartu.
Verifikasi: build/view:cache/diff --check lulus. OrganizerExecutionOverviewTest,
A3ExecutionTest, SessionAttendanceTest dan A2EventTest: 46 tes / 326 assertion
lulus pada MySQL khusus tes. Review visual menemukan fallback header lama
menampilkan literal @yield pada halaman tanpa topbar-title; directive inline
dipisah per baris. Assertion regresi ditambah pada overview dan ringkasan;
OrganizerExecutionOverviewTest + ActivityProfileSummaryTest diulang sesudah fix:
5 tes / 50 assertion lulus. Tidak mengubah judul konten/detail yang disengaja.

Empat keadaan dirender ulang HTTP 200 dengan fixture sintetis rollback. Edge
12 kombinasi 390/768/1440 lulus (konten tanpa directive mentah, tidak overflow,
Alpine/drawer aktif). Screenshot overview desktop dan detail tanpa kartu di
mobile diperiksa langsung; overview desktop diperiksa lagi setelah fix header.
Artefak privat ignored: organizer-execution-menu-review. UAT akun nyata/submit
browser dan review tim belum dilakukan. Demo: login EO -> Pelaksanaan & Kehadiran
-> Kelola pelaksanaan -> Kembali ke daftar pelaksanaan.
Belum commit/push/merge, review pengguna/UAT tim belum diklaim.

# Penyesuaian awal header detail kegiatan

14 November 2026, feature/organizer-ui-refinement / HEAD 31d1fcf. Pengguna meminta
header atas detail Kegiatan Saya, tombol Kembali biru tanpa panah menggantikan
Semua kegiatan, dan penghapusan Pelamar dari kartu navigasi atas. Detail mendefinisikan
section topbar-title opsional; layout EO memakainya bila tersedia dan tetap fallback
ke title pada halaman lain. Judul konten/tab browser tetap nama event. Kembali
menuju organizer.events.index existing. Hanya tautan Pelamar pada kartu atas dihapus;
menu Kelola Pelamar, route dan tautan modul existing tetap. Pelaksanaan dan kehadiran
masih pada kartu atas sambil brainstorming, belum didesain ulang.

View show dan satu binding judul layout EO berubah, tanpa CSS/route/backend/schema/
dependensi baru. Design/UAT diselaraskan, PRD/kontrak/README tetap berlaku. Salinan
show sebelumnya di organizer-refinement-review/before-detail-top-navigation.blade.php
(privat ignored). Build/view:cache/diff --check lulus. A2EventTest dan
ActivityProfileSummaryTest: 19 tes / 147 assertion lulus pada MySQL khusus tes.
Empat render HTTP 200 (daftar/draf/pending/published) memakai fixture rollback;
Edge 12 kombinasi 390/768/1440 lulus tanpa overflow dan drawer aktif. Modal buat
pada daftar tetap dapat dibuka. Screenshot detail draf desktop diperiksa langsung.
Artefak privat ignored: organizer-detail-top-review. Submit akun nyata dan review/
UAT tim belum dilakukan. Belum commit/push/merge.

# Ukuran badge status kembali ke ukuran semula

14 November 2026, feature/organizer-ui-refinement / HEAD 31d1fcf. Pengguna meminta
ukuran status dikembalikan. Override organizer-event-badges/organizer-status
10 px/padding 3x6 px/radius 5 px dihapus sehingga memakai dasar existing 11 px,
4x8 px/radius 6 px. Warna hijau/biru/kuning, label dan tombol outline tetap.
Hanya CSS; tanpa route/backend/migration/dependensi/tes baru. Design/UAT diperbarui;
PRD/kontrak/README tetap berlaku. Build/diff --check lulus; fixture HTML existing
dengan CSS terbaru diperiksa Edge 6 kombinasi 390/768/1440, tanpa overflow; modal
Buat Kegiatan aktif. Screenshot mobile diperiksa langsung, tidak menulis database.
Belum commit/push/merge.

# Kelola kegiatan menjadi tombol outline biru

14 November 2026, feature/organizer-ui-refinement / HEAD 31d1fcf. Pengguna
menyetujui usulan putih/border biru/teks biru untuk Kelola kegiatan. CSS scoped
organizer-event-manage memakai putih, border #93C5FD, teks #1D4ED8 dan hover
#EFF6FF/border #60A5FA. Ukuran kecil, tanpa panah dan target mobile tetap;
Buat Kegiatan solid biru serta tombol halaman lain tetap. Tidak ada perubahan
view/route/backend/data/schema/dependensi pada tahap ini. Design/UAT diselaraskan;
PRD/kontrak/README tetap berlaku. Build/diff --check lulus; render terisi/kosong
HTTP 200 memakai MySQL tes dan fixture rollback. Edge 6 kombinasi 390/768/1440
lulus, termasuk modal Buat Kegiatan tanpa submit; screenshot desktop diperiksa.
Verifikasi ini turut memeriksa koreksi publikasi biru tahap sebelumnya. Tanpa tes
backend baru, belum commit/push/merge; UAT/review pengguna/tim belum.

# Koreksi badge publikasi menjadi biru

14 November 2026, feature/organizer-ui-refinement / HEAD 31d1fcf. Pengguna
mengoreksi instruksi: Publikasi pada Kegiatan Saya biru (#EFF6FF/#1D4ED8), bukan
hijau. View index mengganti class konteks publikasi, CSS scoped menambah varian
biru. Moderasi/pelaksanaan tetap hijau/kuning. Status, ukuran, route/backend tetap.
Tombol Kelola masih solid karena pengguna sedang meminta ide alternatifnya;
usulan putih/border biru belum disetujui. Design/UAT diperbarui; PRD/kontrak/README
tetap berlaku. Build/diff --check lulus; browser/screenshot belum diperiksa ulang
untuk koreksi satu badge ini. Tanpa migration/dependensi/tes backend baru, belum commit/push.

# Badge jumlah, tombol biru kecil dan warna konteks Kegiatan Saya

14 November 2026, feature/organizer-ui-refinement / HEAD 31d1fcf. Sesuai gambar
pengguna: badge jumlah kegiatan memakai organizer-action-count existing; tombol
Kelola kegiatan memakai organizer-action-button biru kecil tanpa panah, ukuran
lebih kecil daripada Buat Kegiatan. Target desktop 36 px, mobile 44 px. Badge
status dipadatkan ke 10 px/padding 3x6 px, konteks Moderasi/Publikasi hijau dan
Pelaksanaan kuning, hanya pada daftar ini. Teks konteks dan nilai status tetap
lengkap: warna kategori bukan perubahan status atau syarat publikasi. Detail
serta komponen status global tidak diubah. CSS scoped organizer-panel/daftar.

View organizer/events/index dan CSS berubah, tanpa route, backend, migration,
dependensi atau aturan baru. Semua action, CSRF, handler modal dan pagination
tetap. Design/UAT diselaraskan; PRD/kontrak/README setup tetap berlaku. Salinan
sebelumnya: organizer-refinement-review/before-event-list-badges.blade.php (privat
ignored). Build/view:cache/diff --check lulus. A2EventTest dan EventCoverUploadTest:
21 tes / 157 assertion lulus pada MySQL khusus tes. Render terisi/kosong HTTP 200
dengan fixture sintetis rollback. Edge 390/768/1440: 6 kombinasi lulus, modal
Buat Kegiatan dibuka pada ketiga ukuran tanpa submit. Screenshot daftar desktop
serta mobile diperiksa langsung. Artefak privat ignored: organizer-event-list-review.
Submit browser akun nyata/UAT tim belum dilakukan.
Masih lokal, belum commit/push/merge, UAT/review pengguna/tim belum.

# Simbol sidebar EO memakai logo landing page

14 November 2026, feature/organizer-ui-refinement / HEAD 31d1fcf. Permintaan pengguna:
ganti S di samping SkillMatch dengan logo landing. Sidebar kini memakai simbol
biru empat kelopak dari x-public-brand existing, tanpa kotak S. Public-brand
diberi prop markOnly opsional/default false; EO memakainya true. Default landing/
footer/auth mempertahankan simbol dan teks existing. CSS scoped EO menetapkan
simbol 36 px dan biru #2563EB. Tidak menggambar ulang/menduplikasi SVG, menambah
aset/dependensi, atau mengubah route/backend. Design/UAT diperbarui; PRD/kontrak/
README setup tetap berlaku. Build/view:cache/diff --check lulus. FoundationAccess
serta ActivityProfileSummaryTest: 24 tes / 188 assertion lulus pada MySQL tes
terpisah (termasuk render home/EO). Fixture EO dirender HTTP 200 dengan rollback;
Edge 390/768/1440 tanpa overflow dan drawer mobile aktif, screenshot desktop
logo diperiksa langsung. Tidak mengklaim perbandingan screenshot landing/auth/
footer; default markup logo ditinjau melalui diff dan render tes existing.
Belum commit/push/merge, review pengguna/tim belum.

# Judul kelompok tindak lanjut menjadi 16 px

14 November 2026, feature/organizer-ui-refinement / HEAD 31d1fcf. Judul Persiapan
dan rekrutmen serta Pelaksanaan menjadi 16 px sesuai permintaan, di antara nama
kegiatan 14 px dan judul utama 18 px. Warna hitam lembut #0F172A/bobot 600 tetap;
titik, badge, alur/data/route/backend tidak berubah. Hanya CSS scoped, design/UAT
diselaraskan; PRD/kontrak/README tetap berlaku. Tidak memerlukan migration,
dependensi atau tes backend ulang. Build/diff --check lulus; fixture HTML dengan
CSS build terbaru diperiksa Edge 390/768/1440 tanpa overflow. Screenshot mobile
diperiksa langsung. Tidak menulis database. Masih lokal, belum commit/push/merge.

# Kartu putih dengan penanda titik dan jumlah per kelompok

14 November 2026, feature/organizer-ui-refinement / HEAD 31d1fcf. Pengguna
menyetujui usulan kartu putih dengan titik biru (persiapan/rekrutmen), titik
hijau (pelaksanaan), judul gelap dan jumlah tindakan di samping judul setiap
kartu. Badge gabungan, kepala kuning dan garis di bawah judul dihapus; hanya
baris kegiatan kedua dan berikutnya memakai pemisah. Tombol biru kecil tetap.

View activity/organizer dan CSS scoped organizer-panel berubah. Jumlah berasal
collection existing yang dibatasi query existing; bukan total seluruh data.
Tidak mengubah query, aturan, route, backend, migration atau dependensi. Design/
UAT diselaraskan; PRD/kontrak/README setup tetap berlaku. Salinan sebelum perubahan:
organizer-action-rows-review/before-white-cards.blade.php (privat ignored).
Verifikasi: build, view:cache dan diff --check lulus; ActivityProfileSummaryTest
serta IntegratedFlowsTest 11 tes / 107 assertion lulus pada MySQL khusus tes.
Lima keadaan render HTTP 200 memakai fixture sintetis yang di-rollback. Edge
390/768/1440: 15 kombinasi lulus, tanpa overflow, Alpine/drawer aktif. Screenshot
terisi desktop dan mobile diperiksa langsung. Submit akun nyata/UAT tim belum
dilakukan. Artefak privat ignored di organizer-action-rows-review.
Masih lokal, belum commit/push/merge. Review pengguna/UAT tim belum diklaim selesai.

# Kuning kepala kartu dimudakan lagi

14 November 2026, branch feature/organizer-ui-refinement / HEAD 31d1fcf.
Permintaan lanjutan: latar kepala kedua kartu dimudakan dari #FEFCE8 ke #FFFEF7,
pemisah bawah dilunakkan ke #FEF9C3. Teks keemasan/layout/data tetap. CSS scoped;
design/UAT diselaraskan. Tidak ada perubahan backend/route/migration/dependensi.
PRD/kontrak/README tetap berlaku; tidak mengulang tes backend untuk warna saja.
Build dan diff --check lulus; screenshot/browser belum diperiksa ulang pada
penyesuaian dua nilai warna ini. Masih lokal, belum commit/push/merge.

# Kedua kepala kartu kuning penuh hingga tepi

14 November 2026, feature/organizer-ui-refinement / HEAD 31d1fcf. Pengguna
mengklarifikasi kepala kartu harus menyatu dari tepi kiri/kanan hingga atas,
lalu menyetujui kuning untuk kedua kelompok. CSS organizer-action-group-title
memakai margin sesuai padding kartu (20 px desktop, 16 px mobile), padding
kepala sejajar konten, sudut atas 11 px mengikuti border luar 12 px, latar
#FEFCE8 dan teks #A16207. Strip biru/inset sebelumnya diganti sesuai lingkup
persetujuan. Isi putih, tombol, data, route dan backend tetap; tanpa migration,
dependensi atau tes backend baru. Design/UAT diselaraskan, PRD/kontrak/README
setup tetap berlaku. Build dan diff --check lulus; fixture HTML existing dengan
CSS build terbaru diperiksa Edge 390/768/1440 tanpa overflow. Screenshot desktop
dan mobile diperiksa langsung, tanpa menulis database. Masih lokal, belum commit/push/merge.

# Baris judul persiapan dan pelaksanaan memakai warna lembut

14 November 2026, feature/organizer-ui-refinement / HEAD 31d1fcf. Persetujuan
pengguna: judul Persiapan dan rekrutmen berlatar biru sangat muda (#EFF6FF) dengan
teks biru (#1D4ED8), Pelaksanaan kuning muda (#FEFCE8) dengan teks keemasan
(#A16207). Padding baris 10/12 px, radius 7 px, isi kartu tetap putih. Hanya CSS
scoped organizer-panel; layout/alur/data/route/backend tetap. Design/UAT diperbarui;
PRD/kontrak/README setup tetap berlaku. Tidak memerlukan migration atau tes backend
ulang untuk perubahan warna ini. Build/diff --check lulus; fixture HTML existing
dengan CSS build terbaru diperiksa Edge 390/768/1440 tanpa overflow. Screenshot
desktop diperiksa langsung; tidak menulis database. Masih lokal, belum commit/push/merge.

# Border biru/hijau dan kuning diperjelas

14 November 2026, feature/organizer-ui-refinement / HEAD 31d1fcf. Permintaan terbaru
hanya warna statistik: border biru #60A5FA, hijau #34D399, kuning cerah #FACC15.
Judul Lamaran Belum Ditinjau kuning keemasan #A16207; ikon #CA8A04 dengan latar
#FEF9C3. Perubahan CSS scoped organizer-panel; layout, angka, ungu, route/schema/
backend/dependensi tetap. Design/UAT diselaraskan; PRD/kontrak/README tetap berlaku.
Build dan diff --check lulus; fixture HTML dashboard existing dimuat dengan CSS
build terbaru, Edge 390/768/1440 lulus dan screenshot desktop diperiksa langsung.
Tidak render ulang/menulis database pada tahap warna ini.
Tidak menambah/mengulang tes backend untuk perubahan warna ini. Masih lokal,
belum commit/push/merge.

# Dua kartu tindak lanjut dan aksen statistik diperjelas

14 November 2026, feature/organizer-ui-refinement / HEAD 31d1fcf. Pengguna meminta
border ungu lebih jelas, judul statistik berwarna sesuai aksen (biru/hijau/ungu),
judul Perlu Ditindaklanjuti di luar kartu, dua kartu terpisah untuk persiapan/
rekrutmen serta pelaksanaan, dan tombol biru lebih kecil tanpa panah. Diterapkan
pada activity/organizer dan CSS scoped organizer-panel. Kartu ditumpuk agar daftar
pelaksanaan yang lebih panjang tetap terbaca; masing-masing punya keadaan kosong.
Tombol 36 px desktop/font 12 px, target mobile 44 px. Route, handler, kondisi,
angka, query dan backend tetap; tanpa migration atau dependensi baru. PRD/kontrak/
README setup tetap berlaku; design/UAT diselaraskan. Salinan sebelum tahap ini:
organizer-action-rows-review/before-split-cards.blade.php (artefak privat ignored).
Verifikasi tahap ini: build, view:cache dan diff --check lulus. ActivityProfileSummary
serta IntegratedFlowsTest: 11 tes / 107 assertion lulus pada MySQL tes terpisah.
Lima keadaan render HTTP 200 dengan fixture sintetis di-rollback; Edge 15 kombinasi
390/768/1440 lulus tanpa overflow, Alpine/drawer aktif. Screenshot terisi desktop
serta mobile diperiksa langsung. Percobaan browser awal sempat gagal karena waktu
animasi drawer; probe diperbaiki menunggu posisi aktual, lalu seluruhnya lulus.
Tidak mengubah kode drawer untuk mengatasi timing QA. UAT akun nyata dan browser
lain belum dilakukan.
Masih lokal, belum commit/push/merge; review pengguna dan UAT tim belum diklaim.

# Panel tindak lanjut menjadi daftar baris

14 November 2026, feature/organizer-ui-refinement / HEAD 31d1fcf, masih lokal.
Pengguna menyetujui usulan daftar baris ringkas: satu judul Perlu Ditindaklanjuti,
badge n tindakan, kelompok Persiapan dan rekrutmen serta Pelaksanaan. Nama gelap,
keterangan singkat dan tombol kanan; mobile tombol turun. Identitas organisasi,
kontak, status verifikasi dan judul Prioritas Anda dihapus dari panel sesuai
usulan yang disetujui. Keadaan kosong eksplisit dan kelompok kosong disembunyikan.

View activity/organizer dan CSS scoped organizer-panel berubah. Query/kondisi
pemilihan event existing tidak diubah. Button membaca kondisi existing: draf atau
revisi/pelengkapan/publikasi menuju detail; pending memakai Lihat status; keputusan
lewat tenggat menuju pelaksanaan; kandidat siap ditinjau menuju pelamar. Semua
hanya GET menuju endpoint existing yang memiliki otorisasi, tanpa transisi baru.
Dua tugas berbeda dari event sama tetap bisa tampil dalam dua kelompok; badge
menghitung tindakan yang ditampilkan, bukan event unik atau total seluruh data.
Tidak ada migration/route/dependensi baru; PRD/kontrak/README setup tetap berlaku.
Design dan UAT diselaraskan. Assertion ringkasan mengikuti judul baru dan memeriksa
profil milik aktor dari view data, karena identitas tidak lagi tampil di panel.

Verifikasi: ActivityProfileSummaryTest dan IntegratedFlowsTest **11 tes / 107
assertion lulus** pada MySQL khusus skillmatch_testing. Build Vite, view:cache dan
diff --check lulus. Lima keadaan terisi/kosong/pending/approved/rejected dirender
HTTP 200 dengan fixture sintetis di-rollback. Edge headless **15 kombinasi** pada
390/768/1440 lulus: konten terbaca, tanpa overflow, Alpine dan drawer mobile.
Screenshot terisi desktop dan mobile diperiksa langsung. Akses/aksi mutasi nyata
tidak diuji ulang melalui browser; aturan dan endpoint backend tetap existing.

Salinan panel sebelumnya: storage/app/private/organizer-sidebar-adjustments/
before-action-rows.blade.php. Artefak baru: organizer-action-rows-review (privat,
ignored, fixture sintetis pada database tes dengan rollback). Review/UAT tim dan
submit dengan akun nyata belum dilakukan; warna statistik serta perapian sidebar
sebelumnya dipertahankan. Demo: login EO -> Ringkasan Aktivitas -> Perlu
Ditindaklanjuti, lalu klik Lanjutkan/Lihat status/Kelola pada kegiatan terkait.

# Penyesuaian warna statistik sesuai gambar pengguna

14 November 2026, branch tetap feature/organizer-ui-refinement / HEAD 31d1fcf.
Pengguna meminta border Total Kegiatan biru, Kegiatan Aktif hijau, Total Pelamar
ungu dan ikon Total Pelamar ungu. Border memakai intensitas muda setara amber
existing; latar putih, ukuran, data dan urutan tetap. Hook semantik ditambahkan
pada tiga kartu dashboard dengan CSS scoped organizer-panel. Tidak ada perubahan
backend, route, migration atau dependensi. PRD/kontrak/README tetap berlaku.
Gambar kedua (Prioritas Anda) hanya diminta brainstorming; belum diubah.
Build, view:cache dan diff --check lulus; render HTTP 200 dan Edge pada
390/768/1440 lulus. Screenshot desktop diperiksa langsung. Tidak menambah atau
mengulang tes backend untuk perubahan warna ini.

# Koreksi sesuai permintaan lanjutan pengguna

14 November 2026 (WIB), tetap di `feature/organizer-ui-refinement`, HEAD `31d1fcf`.
Pengguna secara eksplisit meminta empat penyesuaian setelah meninjau tahap awal.
Keputusan ini menggantikan susunan dashboard dalam catatan tahap pertama di bawah.

1. Sidebar menambah **Kelola Pelamar**. GET `/organizer/applications`, nama
   `organizer.applications.overview`, menampilkan kegiatan milik aktor dan jumlah
   lamaran terkirim, lalu menuju halaman pelamar per kegiatan existing.
   Implementasi terarah pada ApplicationController::overview dan view
   organizer/applications/overview; pagination 12 dan keadaan kosong tersedia.
   Gate Event/create dan middleware Organizer existing tetap berlaku; query
   selalu memakai organizer_id dari aktor, bukan request. Tidak ada migration.
2. Panel **Pelamar Terbaru** dan **Kegiatan Saya** di Ringkasan dihapus sesuai
   persetujuan terbaru. Menu Kegiatan Saya dan daftar kandidat per kegiatan tetap.
3. **Prioritas Anda / Tindak lanjut kegiatan** dipindah ke bawah empat statistik.
4. Tombol **Buat Kegiatan** di Ringkasan dihapus; tetap tersedia di Kegiatan Saya.

Notifikasi, angka/rumus dan layanan pembacaan aktivitas existing tetap. Tidak
menambah grafik/analitik atau mendesain ulang halaman lain pada koreksi ini.
Salinan dashboard/sidebar sebelum koreksi berada pada direktori privat
organizer-refinement-review/before-requested-adjustments. Jangan reset branch
untuk mengembalikan satu bagian karena branch memuat tahap pertama juga.
Kontrak route tambahan, desain dan UAT diselaraskan; README setup tetap berlaku.

Verifikasi koreksi: **44 tes / 319 assertion lulus** pada MySQL skillmatch_testing:
OrganizerApplicantOverviewTest (baru, pembatas pemilik/role, hitungan mengecualikan
draf dan kosong), ActivityProfileSummaryTest (assertion penghapusan/urutan),
FoundationAccessTest, A3ExecutionTest. Build, view:cache dan diff --check lulus.
Empat kondisi render HTTP 200 dengan fixture sintetis di-rollback, terpisah dari
database kerja. Edge headless: 12 kombinasi pada 390/768/1440 lulus (konten,
tanpa overflow, Alpine dan drawer). Screenshot Ringkasan desktop dan Kelola
Pelamar mobile diperiksa langsung. Artefak koreksi:
storage/app/private/organizer-sidebar-adjustments.

Demo: login EO aktif -> Ringkasan Aktivitas; periksa statistik lalu Prioritas
Anda tanpa dua panel/tombol yang dihapus. Klik Kelola Pelamar -> Lihat pelamar
pada kegiatan -> kandidat existing. UAT/review tim dan submit browser dengan
akun nyata belum dilakukan. Pekerjaan tetap lokal, belum commit/push/merge.

---

# Perapian tampilan Organizer

14 November 2026 (WIB). Branch `feature/organizer-ui-refinement`, dibuat dari
`main` / `31d1fcf` (Merge PR #40). Working tree awal bersih. Perubahan masih lokal,
belum commit, push, PR atau merge. Pengguna menyetujui seluruh arah brainstorming
EO: sidebar ringkas, dashboard berfokus tindak lanjut, daftar kegiatan dengan
thumbnail kecil, pengelompokan detail dan konsistensi label. Ini keputusan tampilan
terbaru untuk lingkup tersebut, melanjutkan hasil FigCraft yang sudah terintegrasi.

## Hasil

- Sidebar 288 menjadi 256 px, offset konten/header ikut berubah. Menu menjadi
  Ringkasan Aktivitas dan Kegiatan Saya; ukuran/menu lebih padat, navy/biru tetap.
  Header menampilkan judul halaman dengan pemotongan visual untuk judul panjang.
- Dashboard menampilkan tindak lanjut sebelum empat statistik existing, dengan
  kartu, heading dan jarak yang lebih ringkas. Angka berasal query existing;
  badge jumlah tindak lanjut menyatakan jumlah yang ditampilkan, bukan total baru.
- Kegiatan Saya berubah dari grid kartu tinggi menjadi daftar baris ber-thumbnail,
  judul, jadwal WIB, lokasi, tiga status berkonteks dan aksi Kelola kegiatan.
  Modal buat, unggah cover, pilihan paket, error dan pagination tetap tersedia.
- Detail memiliki tautan bagian Ringkasan, Posisi/jadwal, Asesmen, Koordinasi,
  Paket/pembayaran dan Tindakan. Semua bagian tetap terbuka; koordinasi dipindah
  setelah asesmen. Cover dibatasi tingginya, status dan informasi lebih padat.
- Komponen baru `x-organizer-status` menerjemahkan status moderasi, publikasi,
  pelaksanaan dan order hanya untuk presentasi. `approved` tidak disatukan dengan
  `published`. Nilai backend/field/status tidak berubah. Lamaran dashboard memakai
  `x-application-status` existing agar makna evaluasi tidak diduplikasi.
- Label kegiatan/draf/asesmen diselaraskan pada halaman EO terkait. Assertion
  judul konfirmasi OrganizerAssessmentTest mengikuti label Asesmen yang baru;
  assertion transisi, validasi dan otorisasi existing tetap dipakai.

CSS tambahan dibatasi `.organizer-panel` dalam organizer-panel.css. Tidak mengubah
komponen global, template Volunteer/Admin, JavaScript, model, controller, service,
Policy, schema, migration, route, manifest atau lockfile. Form action, nama field,
method dan CSRF dipertahankan; teks konfirmasi tindakan diperbarui bahasanya.
PRD/kontrak/brief bisnis dan README setup tetap berlaku karena tidak mengubah
aturan, interface, dependensi atau cara menjalankan. Design dan UAT diperbarui.

## Bukti pemeriksaan

- Build final `npm.cmd run build`, `artisan view:cache` dengan PHP 8.4 Herd, dan
  `git diff --check` lulus.
- PHPUnit existing: **100 tes / 773 assertion lulus** pada MySQL khusus
  `skillmatch_testing` dengan guard Tests/TestCase.php. Suite ActivityProfileSummary,
  FoundationAccess, A2Event, A2Payment, OrganizerAssessment, EventCoverUpload,
  EventCoordination, A3Execution dan SessionAttendance. Mencakup akses langsung
  lintas role/pemilik, form, unggah, moderasi/publikasi, pembayaran idempoten,
  asesmen terkunci, koordinasi serta seleksi/kehadiran existing.
- Percobaan awal 99/100 dengan satu deadlock fixture position_skills saat render
  visual berjalan bersamaan. Suite A3Execution diulang sendiri: 17/112 lulus.
  Seluruh rangkaian 100/773 kemudian diulang berurutan dan lulus. Tidak mengubah
  backend/transaksi untuk menutupi error infrastruktur pengujian.
- **15 tes frontend lulus**: event-cover-upload, event-package-form, payment-status,
  melalui `node --test` pada tiga file existing di tests/Frontend.
- Render HTTP 200 pada **28 kondisi halaman EO**, menggunakan fixture sintetis
  dalam transaksi yang di-rollback; tidak mengubah database kerja/demo.
- Edge headless: **84 kombinasi halaman/ukuran (390/768/1440) lulus** untuk konten,
  overflow horizontal, Alpine, warna sidebar dan drawer mobile. Modal Buat Kegiatan,
  Edit Profil, tambah soal dan tambah skill diperiksa pada tiga ukuran tanpa submit.
- Setelah perapian judul header, delapan halaman dirender ulang HTTP 200. Tiga
  halaman utama diuji ulang pada tiga ukuran (**9 kombinasi**), termasuk judul
  header panjang, tujuan anchor, urutan prioritas, drawer dan modal Buat Kegiatan.
- Screenshot desktop dashboard, daftar kegiatan, detail published serta detail
  draf mobile diperiksa langsung. Artefak sebelum tetap di organizer-design-review;
  hasil baru berada di `storage/app/private/organizer-refinement-review` (ignored).
  Skrip QA awal perlu akses browser di luar sandbox dan waktu tunggu inisialisasi
  halaman yang memadai; setelah diperbaiki, seluruh 84 pemeriksaan lulus.

## Batas dan cara meninjau

Belum UAT/review tim, submit seluruh alur lewat browser dengan akun nyata,
transaksi Sandbox end-to-end baru, browser selain Edge, atau perbandingan visual
Volunteer/Admin terbaru. Regresi role lain diperiksa pada lingkup diff (template
role lain tetap, semua CSS tambahan scoped), bukan klaim screenshot identik.
Fixture visual bukan data nyata atau bukti transaksi/pengajuan end-to-end.

Gunakan setup README existing, `npm.cmd run dev` atau `npm.cmd run build`, dan
server Laravel memakai PHP kompatibel. Login EO aktif lalu buka:
`/organizer/aktivitas` -> `/organizer/events` -> Kelola kegiatan. Periksa anchor
bagian, form koordinasi, posisi/asesmen, paket, pelamar dan kehadiran, serta modal
Buat Kegiatan. Profil EO pending/revisi tetap dapat diakses pada route existing.
Tidak membutuhkan migration atau seeder baru. Muat ulang browser jika aset lama
masih tersimpan. PIC SF/A2/A3/A4 dapat meninjau presentasi modulnya; penerimaan
visual pengguna dan review/UAT tim belum diklaim selesai.


### Penyempurnaan EO 15 November 2026: lima perbaikan
- Basis feature/organizer-ui-refinement @367a7de; mempertahankan perubahan lokal dan ukuran kartu.
- Penutupan seleksi dinonaktifkan sebelum tenggat pendaftaran, dengan waktu tersedia WIB.
- Penyelesaian menampilkan jadwal, seleksi/asesmen, dan jumlah peserta yang sesi/absensinya belum final; tombol terkunci sampai siap. Konfirmasi nol hanya untuk nol peserta. Validasi server tetap berlaku.
- Fase tampilan mengikuti waktu melalui Event::executionPhase(), tanpa menulis status atau menyelesaikan otomatis.
- Pencarian peserta 350 ms, hasil HTML server diperbarui tanpa mengganti input/filter; permintaan lama dibatalkan, paginasi mempertahankan pencarian. Tanpa JS tetap GET/Enter. Pencarian nama memakai LIKE literal.
- Istilah antarmuka EO: kegiatan, asesmen, penyaringan, pelamar, skor kecocokan/keahlian. Identifier integrasi dan default komponen role lain tetap.
- Tidak ada migration, dependensi, atau route baru. Hasil validasi terbaru tercatat di bawah.

Validasi akhir: 40 tes backend/387 asersi lulus pada MySQL tes terpisah; 6 tes frontend lulus (debounce, balapan respons, CSRF/bootstrap); npm build dan diff --check lulus. Fixture execution dan execution-final HTTP 200, transaksi rollback; browser lulus 6 kombinasi halaman/viewport (390/768/1440), Alpine dan drawer. Pemeriksaan ini memakai fixture statis, bukan sesi HTTP pengguna nyata. Tidak ada perubahan kontrak status/schema/route; PRD dan kontrak bisnis tetap berlaku.


## Usia yang disarankan per posisi ? keputusan pengguna 15 November 2026
Posisi memiliki suggested_min_age dan suggested_max_age nullable unsigned tiny integer (0?255 sebagai batas penyimpanan), masing-masing opsional. Jika keduanya diisi, maksimum harus >= minimum. Kosong berarti tidak ada saran usia pada sisi tersebut; posisi existing tetap null. EO mengatur saat konfigurasi posisi mengikuti otorisasi dan kunci publikasi existing. Form serta detail posisi EO/relawan menampilkan usia pada tanggal mulai kegiatan sebagai informasi, bukan syarat wajib. Relawan di luar rentang tetap dapat melamar; screening, assessment, matching 50/30/20 dan snapshot evaluasi tidak memakai kolom ini. Profil relawan tetap menyimpan birth_date, tidak menambah kolom umur profil.
Migration tambahan: database/migrations/2026_11_15_160000_add_suggested_age_to_event_positions.php. Tidak ada route baru. UI berbagi components/position-suggested-age.blade.php. A2 pemilik kolom dan konfigurasi; A3/A4 tidak perlu menambahkan pembatasan umur.
UAT: kosong/satu sisi/rentang valid tersimpan dan tampil, rentang terbalik/negatif/non-integer ditolak, kosongkan kedua nilai menghapus saran, pemilik lain ditolak, posisi published tetap terkunci, snapshot evaluasi identik setelah hanya usia diubah.

Basis feature/organizer-ui-refinement @367a7de. Lingkup disetujui pengguna: informasi usia opsional, tidak memengaruhi kelolosan atau skor. Seluruh perubahan lokal sebelumnya dipertahankan.

Validasi usia posisi: 25 tes (PositionSuggestedAgeTest, A2EventTest, PositionScheduleModeTest), 174 asersi lulus pada MySQL skillmatch_testing terpisah; npm build lulus; diff --check lulus. Migration terarah juga diterapkan pada database lokal tanpa penghapusan data. Tes mencakup simpan/hapus/rentang satu sisi/invalid/owner/published dan snapshot evaluasi identik. Tampilan HTML form dan detail EO diverifikasi lewat tes; review visual browser untuk tambahan usia belum dilakukan.

Keputusan label usia 15 November 2026: sisi relawan hanya menampilkan Usia pendaftar dan rentangnya, tanpa keterangan peserta di luar rentang boleh melamar. Sisi EO mempertahankan label saran usia dan penjelasan. Perubahan presentasi saja; tidak ada pembatasan atau eliminasi otomatis. Komponen position-suggested-age memakai forVolunteer, default false.


## Usia pelamar pada detail EO ? 15 November 2026
ApplicationService::submit menambah profile.birth_date (YYYY-MM-DD nullable) ke snapshot lamaran baru. Ini data pribadi untuk owner terotorisasi, bukan bagian audit/notifikasi atau rumus evaluasi. Detail lamaran EO menghitung tahun penuh pada tanggal starts_at dalam WIB dan menampilkan usia bersama saran usia posisi. Tidak ada eliminasi otomatis atau perubahan bobot/score.
Lamaran lama tanpa key birth_date memakai tanggal lahir profil saat ini dan menampilkan sumbernya secara eksplisit; snapshot lama tidak ditulis ulang. Snapshot dengan tanggal kosong/tidak valid atau tanggal lahir setelah tanggal kegiatan menampilkan Belum tersedia. Tidak ada migration atau route baru untuk usia pelamar.
UAT: ulang tahun tepat hari kegiatan, sehari sesudahnya, starts_at UTC yang masuk tanggal WIB berikutnya, data lama/missing/invalid, perubahan profil setelah submit tidak mengubah usia dari snapshot, owner lain ditolak. Alur terintegrasi diuji dari fixture pembuatan event/posisi, asesmen terbit, ajukan moderasi, admin approve/publikasi paket gratis, submit lamaran di luar saran usia, penyaringan/pencocokan/asesmen, accepted, absensi sesi, completed, riwayat dan final lock. Pada DatabaseTransactions, afterCommit tertunda sampai rollback; evaluasi nyata dipanggil eksplisit dalam tes, bukan bukti worker/queue produksi.

Hasil verifikasi usia pelamar/alur EO (feature/organizer-ui-refinement @367a7de):
- 155 skenario backend diuji. Run regresi terakhir: 154 lulus/155, satu ekspektasi kapitalisasi Unduh Dokumen lama; setelah diselaraskan ke Unduh dokumen, tes tersebut diulang dan lulus (1 tes/12 asersi). Tidak ada kegagalan tersisa pada skenario yang diuji.
- Tes perjalanan terintegrasi memakai layanan nyata untuk event/posisi, moderasi dan publikasi gratis, lamaran di luar saran usia, penyaringan/pencocokan/asesmen, seleksi, attendance sesi, completion/final lock dan riwayat; snapshot birth_date tetap setelah profil berubah. Tes usia menguji batas ulang tahun/WIB, data historis tidak valid dan akses lintas pemilik.
- Ekspektasi tes UI lama diselaraskan dengan keputusan pengguna sebelumnya (Skor kecocokan, penyaringan, pelamar, asesmen, CTA yang nyata, penghapusan penjelasan asesmen). Tidak ada perubahan aturan seleksi demi meluluskan tes.
- 6 tes frontend dan npm build lulus; diff --check lulus. MySQL khusus testing dengan rollback, database kerja tidak dipakai untuk fixture.
- Satu deadlock awal terjadi ketika render fixture berjalan bersamaan dengan suite pada database tes; diulang secara berurutan tanpa error tersebut.
- Pengujian pembayaran memakai lingkungan test/mocking yang sudah ada, bukan bukti transaksi Sandbox gateway nyata; worker/afterCommit produksi dan sesi pengguna nyata belum diuji end-to-end.
- Tidak ada migration atau route baru pada langkah ini. Snapshot profile.birth_date adalah tambahan kontrak A3; A4 tidak menjadikannya syarat atau komponen skor. Tidak ada commit/push/merge.

Visual: 4 halaman fixture (detail pelamar, daftar, kosong, filter kosong) HTTP 200; browser lulus 12 kombinasi 390/768/1440, Alpine/sidebar/drawer dan overflow. Detail pelamar 1440 ditinjau via screenshot; usia dan saran usia berada dalam kartu informasi pelamar. Fixture statis, bukan sesi HTTP produksi.
