# PRD SkillMatch Volunteer — acuan tim empat anggota

## Keputusan assessment Organizer — 10 Oktober 2026

Assessment per posisi terdiri dari 1–50 soal dengan tepat empat opsi A–D dan satu kunci benar setiap soal, durasi 1–120 menit. Organizer bebas menentukan jumlah/durasi dalam rentang tersebut. Simpan Draft boleh belum lengkap (termasuk tanpa soal/durasi); draft belum memenuhi readiness. Publikasikan Assessment memvalidasi kelengkapan, lalu menampilkan konfirmasi posisi/jumlah/durasi dan konsekuensi penguncian. Konfirmasi publikasi membekukan soal, opsi, kunci dan durasi saat assessment published, sebelum event published; tidak ada pembukaan kembali/revisi published pada tahap ini. Publikasi assessment tidak menerbitkan event. Versi published tetap immutable dan terikat snapshot; nilai assessment tetap terpisah dari matching. Keputusan ini menetapkan angka durasi/jumlah soal yang sebelumnya belum final. Batas panjang teknis input: pertanyaan 5.000 karakter, opsi 2.000 karakter.

## Keputusan jadwal posisi — 10 Oktober 2026

Posisi baru default mengikuti jadwal event; Organizer dapat memilih jadwal tugas khusus dalam rentang event. Jadwal event selalu terlihat pada form posisi. Untuk event satu hari, tanggal mengikuti event dan Organizer cukup mengubah jam; event beberapa hari memakai tanggal/jam. Mode mengikuti event disimpan pada `event_positions.follows_event_schedule`; interval UTC tetap disimpan di `position_schedules` untuk kompatibilitas snapshot, screening, matching dan seleksi. Posisi existing tetap jadwal khusus tanpa backfill interval. Perubahan jadwal/deadline event yang masih editable menyelaraskan posisi mengikuti event dalam transaksi yang sama dan ditolak bila jadwal khusus menjadi tidak valid. Freeze event published dan snapshot lamaran tetap berlaku. Ketentuan ini menggantikan kewajiban mengisi ulang jadwal posisi secara manual; rumus matching tidak berubah.

Versi 1.4.2 • 4 Oktober 2026 • Berbasis `skillmatch-main.zip` dan PRD v1.3.

## 1. Tujuan dan otoritas

Menyelesaikan website rekrutmen volunteer dari registrasi, publikasi event, lamaran, screening, assessment, seleksi, hingga pelaksanaan dan riwayat. Empat anggota mengembangkan satu aplikasi Laravel monolitik dan satu database dengan kontrak yang sama.

Dokumen ini adalah sumber aturan bisnis. [Kontrak integrasi](docs/KONTRAK_INTEGRASI.md) menetapkan nama status/data dan interface; [design.md](design.md) menetapkan UI; brief anggota menjelaskan kepemilikan pekerjaan. Kode ZIP adalah baseline, bukan bukti seluruh persyaratan telah terpenuhi.

### 1.1 Keputusan tetap dan asumsi implementasi

| Jenis | Keputusan |
|---|---|
| Instruksi pengguna | Total empat anggota; pekerjaan A1 pada ZIP dihitung; melanjutkan teknologi dan identitas visual ZIP; upload tanpa ClamAV; login bersama dengan panel admin terpisah; attendance manual oleh Organizer. |
| Kebutuhan dari PRD v1.3 | Screening otomatis, assessment pilihan ganda, matching 50/30/20, pembayaran sandbox, notifikasi internal, riwayat, admin analitik, Ringkasan Aktivitas pengguna. |
| Penyesuaian v1.4.2 | Login bersama dan pembagian SF/A1; MySQL/InnoDB tetap sebagai database; validasi upload sinkron menggantikan karantina/pemindaian; level skill ZIP digunakan dengan aturan pada bagian 7. |
| Baseline implementasi yang ditetapkan paket ini | Satu lamaran per Volunteer per event; satu attendance per peserta per event; satu paket per event; satu attempt assessment; status dan transisi pada kontrak. |
| Belum keputusan bisnis final | Harga/manfaat paket, kategori awal, durasi/jumlah soal, dan angka retensi. Pakai fixture demo berlabel dan konfigurasi; tidak diperlakukan sebagai harga produksi. |

## 2. Pengguna dan batas akses

| Aktor | Akses |
|---|---|
| Pengunjung | Landing page, katalog event published, detail, login/registrasi. |
| Volunteer | Profil, availability, lamaran, dokumen sendiri, assessment sendiri, aktivitas, riwayat dan notifikasi sendiri. |
| Organizer aktif/terverifikasi | Event sendiri, posisi/assessment, paket/transaksi sendiri, kandidat dan berkas lamaran terkait, keputusan seleksi, attendance, penyelesaian event. |
| Organizer pending/revisi | Melengkapi profil dan dokumen verifikasi; belum dapat menerbitkan atau mengelola rekrutmen. |
| Admin | Akun, verifikasi organizer/event, master data, paket/transaksi, konten/pengaturan, audit dan analitik platform melalui panel tersendiri. |

`users.is_active` berarti akun boleh mengakses fungsi terproteksi. `organizer_status` berarti hasil verifikasi organisasi; pending tidak identik dengan akun ditangguhkan. Admin dapat melihat dokumen verifikasi organisasi untuk moderasi, tetapi tidak otomatis berhak mengunduh CV semua pelamar. Keputusan seleksi rutin tetap milik Organizer.

### Login bersama dan otorisasi (revisi 1.4.2)

Semua role masuk melalui `/login`, tanpa pemilih role. Role dibaca dari database; registrasi publik tidak menerima role admin. Akun admin dibuat melalui seeder lokal atau prosedur internal yang terdokumentasi. Satu guard session `web` dipakai seluruh aplikasi; akses admin ditegakkan dengan middleware akun aktif dan role admin, serta Policy per objek.

Tujuan default: Admin `/admin/dashboard`, Volunteer `/volunteer/aktivitas`, Organizer `/organizer/aktivitas`. Akun yang wajib verifikasi email diarahkan ke alur verifikasi dahulu; Organizer pending/inactive yang akunnya aktif diarahkan ke `/organizer/profile/pending` untuk melengkapi profil. Status organisasi tidak sama dengan suspensi akun. Intended URL hanya boleh lokal dan sesuai hak akses; tujuan eksternal/lintas role yang tidak sah dibuang dan memakai tujuan default. Guest menuju route terproteksi diarahkan ke `/login`; non-admin yang sudah login meminta `/admin/*` ditolak 403.

Semua tombol keluar memakai POST `/logout` dengan CSRF, invalidasi sesi dan regenerasi token, lalu kembali ke `/login`. Tidak dibuat route autentikasi `/admin/login` atau `/admin/logout`; jika pernah ada, hapus referensi form/route lama saat migrasi. Pemisahan layout dashboard admin tetap berlaku.

## 3. Ruang lingkup

### P0 wajib

Autentikasi dan otorisasi; profil dan ketersediaan; master skill/kategori/kota; verifikasi organizer; event/posisi/persyaratan; moderasi dan publikasi; katalog; lamaran dan dokumen privat tanpa scanner; screening; assessment pilihan ganda; Match Score; seleksi manual dengan kuota/konflik jadwal; notifikasi internal; paket dan Midtrans Sandbox; attendance manual; penyelesaian dan riwayat; admin terpisah dan analitik; Ringkasan Aktivitas Volunteer/Organizer.

### P1, tidak memblokir MVP

Sertifikat digital, email kegiatan di luar email autentikasi, ekspor lanjutan, daftar tunggu, akun PIC, attendance per sesi/shift, serta penilaian performa. Usulan performance yang muncul pada menu ZIP tidak otomatis menjadi P0.

### Di luar MVP

AI/ML, chat real-time, native mobile, proctoring, GPS/biometrik/QR, self check-in, penggajian, pembayaran uang nyata, refund otomatis, serta integrasi portal eksternal. ClamAV dan scanner antivirus tidak menjadi dependensi atau syarat selesai versi ini.

## 4. Pembagian dan ukuran selesai

| PIC | Tanggung jawab utama | Hasil demonstrasi |
|---|---|---|
| SF — kamu sebelum A2 | Shared foundation: MySQL, auth bersama, profil, layout, audit, seed dan README awal | Fondasi dapat dipakai A1–A4 dan lulus gerbang serah-terima SF. |
| A1 | Melanjutkan admin: moderasi, pengguna, master data, audit UI, konten, analitik | Memakai fondasi SF; moderasi bekerja; analitik membaca data nyata. |
| A2 | Event/posisi/katalog/paket/payment | Event dibuat, disetujui, dibayar bila perlu, kemudian dapat dilamar. |
| A3 | Lamaran/dokumen/seleksi/attendance/riwayat | Berkas hanya terlihat perekrut terkait, seleksi konsisten, kehadiran masuk riwayat. |
| A4 | Screening/assessment/matching/aktivitas/notifikasi | Pelamar mendapat alasan screening, menyelesaikan assessment, skor terjelaskan, tugas muncul pada aktivitas. |

SF adalah tahap kerja kamu, bukan anggota kelima. Setelah SF diserahterimakan, kamu melanjutkan A2. Rincian ada pada [brief shared foundation](docs/anggota/SHARED_FOUNDATION.md). Ringkasan Aktivitas dan notifikasi tetap milik A4; pemindahan tugas tambahan harus dicatat lebih dahulu. A1 memiliki pekerjaan lama yang cukup luas. A2 mengerjakan UI admin paket/transaksi; A3 menyediakan layanan dokumen yang juga dipakai A1; A4 menyediakan notifikasi yang dipanggil setiap modul. Masing-masing mengerjakan controller, view, validation, policy, migration, seeder dan tes modulnya. Tidak ada satu anggota yang hanya mengerjakan frontend, atau bertanggung jawab memperbaiki semua integrasi.

Keadilan dinilai dari kompleksitas dan hasil, bukan jumlah menu. Evaluasi setelah milestone kedua: bila ada blokir teknis, anggota yang lebih dahulu selesai membantu pekerjaan terbatas tanpa mengganti pemilik kontrak. Detail ada pada brief anggota.

## 5. Alur akhir yang wajib sama

### 5.1 Organizer dan publikasi

Registrasi → verifikasi email → lengkapi organisasi dan dokumen → Admin menyetujui organisasi → buat draft event/posisi/jadwal/persyaratan/assessment → pilih paket → ajukan moderasi → Admin setujui atau minta revisi → bayar Sandbox jika berbayar → publikasi setelah semua syarat terpenuhi.

Event published hanya muncul di katalog jika akun Organizer aktif dan organisasi terverifikasi. Menonaktifkan akun menutup akses dan visibilitas publik tanpa mengubah histori pembayaran. Pemulihan tidak menerbitkan event cancelled/completed atau melewati syarat publikasi.

### 5.2 Volunteer dan seleksi

Registrasi/verifikasi email → isi profil, skill, kota, availability → cari event published → pilih satu posisi → buat draft lamaran → upload dokumen → submit sebelum deadline → simpan snapshot → screening otomatis → jika lolos, assessment → simpan skor matching dan assessment terpisah → status under_review → Organizer menerima/menolak secara manual.

Organizer dapat melihat metadata semua lamaran terkirim pada tab Semua Pelamar. Tindakan menerima/menolak hanya untuk under_review. Draft tidak terlihat Organizer. Gagal screening tidak diteruskan ke assessment.

### 5.3 Pelaksanaan

Accepted → otomatis masuk daftar pelaksanaan → Organizer memeriksa peserta di lokasi dan mencatat present/absent → periksa rekap → selesaikan event setelah jadwal akhir dan semua attendance dicatat → Volunteer melihat riwayat. Volunteer tidak menandai kehadiran sendiri.

### 5.4 Komunikasi

Keputusan moderasi, hasil screening, keputusan seleksi, dan pembayaran terverifikasi menghasilkan notifikasi di website. Notifikasi lamaran baru juga tersedia untuk Organizer. Status disimpan lebih dahulu; notifikasi diproses setelah commit dengan deduplikasi dan retry. Tidak memerlukan WebSocket.

## 6. Kebutuhan fungsional dan pemilik

ID FR-01–22 mempertahankan padanan PRD v1.3; FR-23 ditambahkan untuk memperjelas verifikasi organisasi yang sudah ada di ZIP.

| ID | Kebutuhan dan kriteria penerimaan | PIC |
|---|---|---|
| FR-01 | Registrasi hanya Volunteer/Organizer; email unik; verifikasi email/reset/login/logout bersama; role dan kepemilikan diperiksa server; akun nonaktif ditolak. | SF |
| FR-02 | Profil lengkap untuk melamar: identitas, kota, minimal satu skill dan interval availability valid. Level mengikuti empat level ZIP. | SF |
| FR-03 | Admin mengelola skill, kategori dan kota; data direferensikan dinonaktifkan, tidak dihapus merusak histori. | SF schema/seed; A1 CRUD |
| FR-04 | Organizer aktif membuat event/posisi/kuota/jadwal/persyaratan; tanggal, interval dan kuota divalidasi. | A2 |
| FR-05 | Admin approve atau reject dengan catatan revisi; event belum disetujui tidak tampil publik. Revisi dapat diajukan ulang. | A1, kontrak A2 |
| FR-06 | Katalog mencari judul dan filter kategori/kota/tanggal; pagination; hanya event eligible published. | A2 |
| FR-07 | Satu lamaran per Volunteer per event; idempoten; deadline diperiksa menggunakan waktu server; snapshot submit disimpan. | A3 |
| FR-08 | PDF/JPG/PNG, maksimal 5 MB/file dan 5 file/lamaran; privat, validasi isi/MIME, authorization download; tanpa ClamAV. | A3 |
| FR-09 | Screening memeriksa profil, dokumen ready dan syarat wajib terstruktur; alasan per syarat; tidak mengklaim keaslian CV. | A4 |
| FR-10 | Assessment pilihan ganda per posisi, satu attempt, timer server, jawaban tersimpan, nilai di server; kunci tidak bocor. | A4 |
| FR-11 | Matching 0–100, komponen 50/30/20, snapshot dan rule_version; hasil deterministik. | A4 |
| FR-12 | Seleksi hanya under_review milik event Organizer; accepted tidak melebihi kuota/berbenturan jadwal; reject beralasan. | A3 |
| FR-13 | Notifikasi internal terarah dan tidak ganda; audit per perubahan modul. | A4 fondasi, semua produsen |
| FR-14 | Snapshot paket/harga, checkout Sandbox, verifikasi server, pembayaran dan entitlement idempoten. | A2 |
| FR-15 | Attendance manual oleh Organizer hanya peserta accepted; perubahan diaudit. | A3 |
| FR-16 | Completed menghasilkan riwayat; absent bukan partisipasi berhasil; cancelled tidak menghasilkan keberhasilan. | A3 |
| FR-17 | Dashboard Admin: indikator, tren, filter periode, keadaan kosong/gagal dan timestamp data. | A1 |
| FR-18 | Aktivitas Volunteer: profil belum lengkap, assessment, lamaran, jadwal accepted dan notifikasi sendiri; tanpa grafik platform. | A4 |
| FR-19 | Aktivitas Organizer: event, tugas review, moderasi/payment, tenggat dan attendance miliknya. | A4, data A2/A3 |
| FR-20 | /admin/* memakai guard web bersama dan middleware role admin; layout/navigasi/dashboard tetap khusus admin; non-admin ditolak server. | SF akses/layout; A1 fitur |
| FR-21 | Admin mengelola pengguna/event/master/konten/settings/audit; paket dan transaksi oleh A2 dalam layout Admin. Paid tidak bisa ditetapkan lewat form. | A1 + A2 |
| FR-22 | Satu GET/POST /login dan POST /logout untuk semua role; redirect berdasarkan role, intended URL lokal hanya jika terotorisasi; tidak ada form/guard login admin terpisah. | SF |
| FR-23 | Organizer mengajukan dokumen privat, Admin menilai; edit profil biasa tidak otomatis meminta upload ulang. Revisi identitas penting memerlukan verifikasi ulang terkontrol. | A1, utilitas A3 |

## 7. Aturan bisnis

### 7.1 Event dan paket

- Jadwal posisi harus berada dalam rentang event dan memiliki durasi positif. Deadline tidak melewati awal tugas paling awal.
- Waktu disimpan konsisten dalam UTC; input dan label UI WIB/Asia Jakarta. Tampilan tanggal tidak menggantikan pemeriksaan timestamp lengkap.
- Minimal satu posisi, satu skill per posisi, dan assessment valid sebelum pengajuan; daftar syarat membedakan wajib dan preferensi.
- Setelah published, aturan posisi, jadwal, assessment dan paket dibekukan pada MVP. Perubahan substantif menggunakan pembatalan dan event baru; koreksi teks ringan dapat diaudit tanpa mengubah aturan seleksi. Ini mencegah perubahan skor pelamar yang sudah masuk.
- Paket per event. Harga/manfaat berasal server dan disnapshot ketika order dibuat. Free/Standard/Premium memakai konfigurasi awal yang disepakati pengguna 5 Oktober 2026 (lihat tabel di bawah); admin dapat mengedit atau menonaktifkan paket tanpa mengubah snapshot lama. Paket Demo terpisah tetap hanya fixture.
- Entitlement mengatur maksimal posisi, maksimal lamaran terkirim per event dan maksimum durasi pembukaan pendaftaran. Draft tidak dihitung; slot lamaran yang sudah terkirim tidak dikembalikan karena withdrawn. Validasi limit dilakukan transaksional.
- Paket gratis tidak menghasilkan order paid palsu; entitlement gratis diaktifkan sekali setelah approval. Paket berbayar memerlukan pembayaran terverifikasi.
- Redirect checkout hanya membawa pengguna ke halaman status; server memverifikasi signature/status, order, nominal dan mata uang. Callback ganda/tidak berurutan tidak menggandakan hak atau menurunkan paid karena pesan pending lama.
- Pembayaran valid setelah event cancelled tetap dicatat historis dan ditandai perlu tindak lanjut, tetapi tidak mengaktifkan publikasi. Refund otomatis di luar MVP.

### 7.2 Upload tanpa ClamAV

1. Volunteer membuat draft miliknya; server memastikan posisi dan event valid.
2. Upload melalui form Laravel. CV hanya PDF; lampiran pendukung PDF/JPG/PNG, tiap file maksimal 5 MB, total lima file per lamaran. Dokumen organisasi maksimal lima file/pengajuan dengan format dan ukuran yang sama.
3. Server memeriksa ekstensi allowlist dan MIME berdasarkan isi; file kosong, tipe tidak cocok, executable, HTML/SVG/ZIP dan file terlalu besar ditolak. Pemeriksaan format tidak membuktikan file bebas malware.
4. Nama internal acak; file di disk `private_documents` tanpa public URL/symlink. Metadata menyimpan pemilik, jenis, ukuran, MIME, checksum, lokasi internal dan waktu.
5. Setelah file dan metadata tersimpan konsisten, status `ready`. Kegagalan simpan menghasilkan pesan gagal, cleanup file sementara, dan tidak menghasilkan ready palsu.
6. Submit hanya menerima dokumen ready milik draft itu. Setelah submit, dokumen dibekukan; perubahan profil berikutnya tidak mengubah bukti lamaran.
7. Organizer membaca dokumen melalui relasi event miliknya, sesudah submit. Download attachment melalui Policy, bukan Storage::url. Nama file di-escape, Content-Type ditetapkan dari hasil deteksi, nosniff, tanpa preview HTML/PDF iframe untuk dokumen pelamar.
8. Akses Organizer berhenti jika lamaran withdrawn, akun terkait ditangguhkan, event cancelled, atau periode akses berakhir. Volunteer pemilik dapat mengunduh dokumen sendiri selama akun aktif dan file belum dipurge.

Tidak ada status quarantined, scanning, scan_failed, klaim "lolos antivirus", konfigurasi scanner, worker scan, atau syarat UAT antivirus. Status ready hanya menandakan validasi format dan penyimpanan berhasil. UI cukup mengatakan "Dokumen tersimpan".

Retensi baseline demo: draft terbengkalai 7 hari, dokumen lamaran 90 hari setelah event completed/cancelled. Angka ini usulan yang dapat dikonfigurasi dan perlu diputuskan sebelum penggunaan publik. Job cleanup menyimpan metadata minimum/purge ledger untuk menjaga penghapusan setelah restore. Dokumen verifikasi organisasi mengikuti kebijakan tersendiri; tidak ikut terhapus oleh job lampiran lamaran.

### 7.3 Screening dan matching

Screening adalah pemeriksaan syarat wajib, bukan keputusan penerimaan. Minimal meliputi profil, dokumen wajib ready, skill wajib dan level minimum, serta cakupan jadwal penuh hanya jika posisi mensyaratkannya. Kota berbeda hanya menggagalkan jika ditandai wajib. Pengalaman dalam teks CV tetap ditinjau manusia.

`Match Score = 0,50 × S + 0,30 × A + 0,20 × L`.

- S = 100 × jumlah skill posisi yang cocok / jumlah skill posisi. Penyesuaian eksplisit v1.4 untuk ZIP: cocok jika ID sama dan level Volunteer memenuhi minimum_level; urutan beginner < intermediate < advanced < expert. Requirement level tunggal lama pada posisi menjadi fallback migrasi, lalu minimum per skill adalah sumber otoritatif.
- A = 100 × durasi irisan gabungan availability dengan gabungan jadwal posisi / durasi gabungan jadwal posisi. Interval tumpang tindih digabung; interval setengah terbuka [start,end), sehingga tugas berurutan tepat di batas tidak bentrok.
- L = 100 jika city_id sama, selain itu 0. Tidak menghitung GPS/jarak.
- Posisi tanpa skill/jadwal valid diblokir sebelum publikasi. Data kosong tidak diam-diam menghasilkan skor nol.
- Contoh S=75, A=75, L=100 menghasilkan 80,00. Presisi penuh disimpan; tampilan dua desimal.
- Rule version `match-v1.4`, snapshot profil, level, kota, jadwal, requirement dan versi assessment diikat pada submit. Perubahan profil tidak mengubah skor historis.
- Urutan kandidat siap ditinjau: match_score DESC, assessment_score DESC, submitted_at ASC, id ASC.

### 7.4 Assessment

Organizer mengisi set pilihan ganda per posisi; minimal satu soal, opsi dan tepat satu kunci benar per soal, serta durasi positif. Set dibekukan sebelum publikasi. A4 menyediakan halaman pengelolaan assessment sebagai tab event milik A2.

Hanya assessment_pending dapat memulai sebelum awal event. Batas akhir attempt adalah yang lebih awal antara waktu mulai + durasi dan starts_at event. Satu attempt; waktu akhir dihitung server dan refresh tidak mengulang timer. Jawaban disimpan berkala dan saat perpindahan soal, dengan indikator kegagalan simpan. Timer klien hanya tampilan. Scheduler/request pertama setelah batas waktu memfinalkan jawaban yang sudah tersimpan, termasuk saat browser ditutup. Dua proses submit menghasilkan satu hasil.

Nilai = jawaban benar / jumlah soal × 100; kosong salah. Nilai assessment terpisah dari matching dan tidak menjadi bobot keempat. Tidak ada ambang gagal otomatis pada baseline. Setelah attempt final, lamaran under_review; organizer menilai secara manual.

### 7.5 Seleksi, kehadiran dan riwayat

- Satu Volunteer satu lamaran per event, termasuk lamaran withdrawn; MVP tidak menyediakan submit ulang atau ganti posisi sesudah submit. Draft dapat diganti posisi dengan reset dokumen/requirement yang tidak sesuai.
- Volunteer dapat menarik lamaran sebelum accepted; setelah accepted, pengunduran diri ditangani Organizer sebelum kegiatan dimulai dengan alasan dan status withdrawn. Catat audit; jangan menghapus lamaran.
- Seleksi menerima under_review saja dan harus dilakukan sebelum starts_at event. Transaksi tulis memeriksa ulang kuota, entitlement, akun, lifecycle, dan bentrok dengan jadwal peserta accepted di event lain yang tidak cancelled. Transaksi MySQL/InnoDB dan urutan row lock pada baseline tools wajib diuji.
- Attendance: unrecorded, present, absent. Unrecorded tidak disamakan dengan absent. Satu catatan per lamaran accepted, dibuat saat acceptance.
- Organizer mencatat sejak awal event sampai diselesaikan. Timestamp pencatatan bukan jam kedatangan. MVP tidak mencatat check-in/out atau shift.
- Event baru dapat completed setelah akhir jadwal, tidak ada assessment/lamaran seleksi yang menggantung, dan seluruh peserta accepted memiliki attendance. Jika tidak ada peserta, completion diizinkan dengan konfirmasi jumlah nol.
- Lamaran nonfinal dapat ditutup Organizer sebelum completion melalui layanan penutupan dengan alasan, status rejected (termasuk assessment belum selesai); jangan mengubah yang sudah withdrawn/rejected/screening_failed. Ini jalur penutupan terkontrol, bukan seleksi penerimaan bypass.
- Koreksi attendance setelah completed hanya oleh Organizer pemilik dengan alasan dan audit; riwayat membaca status terbaru. Histori tidak disalin ke tabel kedua.
- Completed + accepted + present = partisipasi berhasil. Accepted + absent tetap tampil sebagai tidak hadir. Cancelled ditandai dibatalkan.

## 8. Admin dan Ringkasan Aktivitas

Dashboard hanya di /admin/dashboard. /volunteer/aktivitas dan /organizer/aktivitas menyajikan tugas/daftar operasional tanpa grafik lintas platform. Organizer boleh melihat rekap per event (pendaftar, accepted, hadir).

Analitik Admin: total akun saat ini; akun baru menurut created_at; event dibuat menurut created_at; event aktif published/noncancelled/noncompleted; lamaran submitted menurut submitted_at; jumlah order paid dan nominal snapshot menurut paid_at; aktivasi paket menurut activated_at. Filter 7/30 hari/rentang dalam WIB. Bedakan kartu kondisi saat ini dari indikator periode. Paid dan activated_at hanya dicatat pada perubahan pertama yang sah.

Antrean tindakan: organisasi pending, event pending, transaksi perlu diperiksa. Tidak ada antrean scan dokumen. UI kegagalan query tidak menyamar sebagai angka nol. Analitik membaca database melalui layanan/query agregasi yang dapat diuji.

## 9. Nonfungsional dan keamanan

- Policy per objek, validasi server, CSRF, hash password, rate limit, regenerasi sesi, invalidasi logout, secret di .env, dan APP_DEBUG=false di deployment.
- Gunakan satu guard session web dan middleware role/akun aktif; endpoint data admin dilindungi sama dengan halamannya. Tidak ada impersonation atau perubahan paid bebas.
- Harga, skor, role, owner dan status sensitif diturunkan server. Audit tidak berisi password/token/isi berkas.
- Setiap query daftar dipaginasi; profil/relasi kandidat menggunakan eager loading yang tepat.
- Responsif pada 360/768/1280 px; label, fokus keyboard, pesan error dan status teks tersedia.
- Target uji: respons server p95 <=2 detik untuk katalog/aktivitas/daftar kandidat pada 100 event, 1.000 lamaran dan 20 pengguna di lingkungan yang dicatat; target bukan hasil pengukuran saat ini.
- Backup MySQL/InnoDB dilakukan dengan snapshot/dump konsisten, terkoordinasi dengan file privat; hindari perubahan schema selama dump. Uji restore dan purge ledger pada database uji sebelum demo akhir.
- Audit Composer/npm dan temuan relevan dicatat. Penyimpanan privat dan validasi tipe bukan pengganti antivirus; versi ini secara sadar tidak menyediakan pemindaian malware.

## 10. Milestone bersama

| Urutan | Hasil integrasi | Ketergantungan |
|---|---|---|
| M0 | Sepakati dokumen, branch, migration owner, seeders dan fixture | Semua |
| M1 / SF | MySQL, login bersama/role, profil kota/availability, layout, audit, seed, README awal dan bukti tes | Kamu (SF); review anggota lain |
| M2 | Draft event/posisi/assessment/paket, moderasi dan katalog | A2 + A4 + A1 |
| M3 | Lamaran/dokumen → screening → assessment → under_review | A3 + A4 |
| M4 | Seleksi aman, payment terverifikasi, publikasi bersyarat, aktivitas data nyata | A2 + A3 + A4 |
| M5 | Attendance → completed → riwayat, analitik, retensi/backup | A3 + A1 |
| M6 | UAT lintas role, pembayaran ganda, request paralel, responsive, README dan demo | Semua |

Sesudah M1, A1 melanjutkan fitur admin dan kamu mengerjakan A2; fondasi notifikasi tetap dikerjakan PIC A4. A2/A3 boleh mulai modul sendiri dengan kontrak dan fixture; integrasi memakai commit SF yang disepakati. Notifikasi lengkap, payment dan dokumen privat A3 bukan syarat selesai SF.

M2 menggunakan fixture entitlement untuk pengembangan lokal yang diberi label; tidak boleh menjadi bypass publikasi di aplikasi final. Payment dapat dikembangkan paralel sejak M1. Jadwal kalender ditentukan tim; tabel ini urutan integrasi, bukan janji durasi.

## 11. Penerimaan dan perubahan

Semua kasus P0 pada [UAT](docs/UAT.md) harus punya bukti hasil, penguji dan commit. UAT scan antivirus lama diganti dengan validasi format/akses privat; UAT integritas, role, payment dan kuota tidak dihapus karena pergantian database.

Perubahan kebutuhan dicatat dengan tanggal, alasan, PIC dan pengaruh pada status/schema/UI/UAT. Harga/manfaat final, migrasi DB, penambahan scanner atau attendance per sesi memerlukan revisi acuan pusat sebelum implementasi.

## 12. Deliverable wajib: README menjalankan aplikasi

Aplikasi belum dinyatakan selesai tanpa README instalasi dan menjalankan yang telah diuji anggota lain. Isinya: prasyarat sesuai lockfile, setup MySQL dan database kosong, .env.example tanpa secret, instalasi dependensi, APP_KEY, migration/seeder, build frontend, server, worker/scheduler, email autentikasi, Midtrans Sandbox, storage privat tanpa ClamAV, akun demo lokal, pengujian MySQL khusus, troubleshooting, dan backup/restore. SF menulis README awal untuk fondasi; A1 menggabungkan dokumentasi akhir; setiap pemilik modul menyumbang konfigurasi dan instruksi yang relevan.

Menambahkan requirement ke PRD tidak otomatis mengubah README Laravel bawaan. Saat implementasi, pengembang/AI agent wajib menulis dan menguji README berdasarkan kode final. Migration membuat tabel; database kosong dan kredensial MySQL harus disediakan atau dibuat oleh skrip setup yang terdokumentasi.

## Keputusan pengguna 5 Oktober 2026 - pertahankan baseline

Beranda publik dan dashboard admin yang sudah berfungsi dipertahankan serta dikembangkan bertahap. SF tidak mengganti seluruh halaman dengan shell hanya karena fitur lanjutan belum selesai. Keterangan belum tersedia berlaku pada bagian yang memang belum ada, tanpa angka palsu atau klaim matching/payment aktif. Proteksi akses, satu login, dan komponen SF tetap digunakan.

A2 melanjutkan beranda publik (`home`, `welcome.blade.php`), katalog dan detail event; A1 melanjutkan dashboard dasar admin menjadi analitik lengkap. A3 tetap mengerjakan lamaran/dokumen/attendance, A4 tetap aktivitas/assessment/matching/notifikasi. Aktivitas pengguna boleh memakai shell sementara sampai data modul tersedia. Keputusan ini memperjelas pembagian dan pelestarian kontribusi, tidak mengubah schema, status, harga atau rumus. Pemulihan halaman bukan bukti seluruh P0/UAT selesai.

## Beranda publik informatif - 5 Oktober 2026

A2 mengembangkan beranda `home` agar pengunjung memahami tujuan SkillMatch, peran Volunteer/Organizer, dan langkah yang dapat dilakukan. Beranda memuat hero, pencarian judul ke katalog, penjelasan skill/waktu/lokasi, pratinjau event, panduan kedua peran, FAQ dan CTA autentikasi. Teks membedakan kemampuan yang tersedia dari lamaran/assessment/matching/notifikasi yang masih dikembangkan.

Pratinjau maksimal enam event memakai aturan publikasi katalog, ditambah periode pendaftaran terbuka dan kapasitas paket belum habis, diurutkan berdasarkan waktu mulai lalu ID. Kartu memuat informasi aktual, bukan rekomendasi personal atau statistik promosi. Keadaan kosong tetap menjelaskan langkah berikutnya. Pengembangan ini tidak mengubah syarat publikasi, status, harga atau kepemilikan A3/A4.

## Konfigurasi awal paket disepakati - 5 Oktober 2026

Keputusan pengguna berdasarkan gambar paket:

| Paket | Harga per event | Maksimal posisi | Maksimal lamaran terkirim | Hari pendaftaran |
|---|---:|---:|---:|---:|
| Free | Gratis | 2 | 30 | 7 |
| Standard | Rp30.000 | 5 | 100 | 30 |
| Premium | Rp50.000 | 10 | 300 | 60 |

Ini nilai awal yang dapat diedit admin, bukan konstanta harga checkout. Harga/limit berasal database dan disnapshot saat dipilih/dibeli. Alur screening/assessment/seleksi/dokumen/attendance pada kartu dijelaskan sebagai fitur dalam pengembangan sampai modul A3/A4 terintegrasi.
