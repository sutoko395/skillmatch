# Detail lamaran: penarikan

Tanggal: 11 November 2026. Branch `feature/volunteer-design`, dasar `a8c7925`.
Permintaan pengguna: mengikuti `Penarikan pada detail lamaran.svg` (1440 × 2008).
Perubahan lokal; tanpa commit atau push. Sembilan perubahan lokal lain dipertahankan.

Sebelumnya under_review hanya mendapat shell baru, rincian dan penarikan tertutup,
serta tombol penarikan memakai warna bawaan. Kini status tersebut menggunakan
ulang CSS detail submitted: kartu putih, layout dua kolom, tombol merah, rincian
screening/assessment, Waktu event dan panel penarikan terbuka sesuai SVG.
Native details tetap dapat dilipat. Komponen application-selection dipakai ulang;
alasan opsional, peringatan final, POST dan CSRF tetap berlaku.

File integrasi: applications/show.blade.php serta partial progress, information,
withdrawal. Tidak ada route, layanan, migration atau dependensi baru.
PRD/kontrak tetap berlaku; ini perubahan presentasi, bukan transisi status.
Data dan pesan sukses menggunakan hasil nyata, bukan isi contoh pada SVG.

Validasi: A3ExecutionTest + VolunteerAssessmentInfoTest lulus 24 tes/217 assertion
pada MySQL skillmatch_testing dengan guard existing; tanpa reset database kerja.
Build Vite dan view:cache lulus. Preview memakai model fixture tidak disimpan;
tidak mengirim penarikan dari browser atau mengubah lamaran nyata.
Chrome CDP 1440/768/390 tidak overflow; screenshot desktop/mobile diperiksa.
Tinggi halaman mengikuti data aktual (pesan sukses SVG hanya tampil jika ada
hasil aksi nyata). Hasil QA tersimpan di storage/app yang diabaikan Git.
Login/navigasi dan penarikan end-to-end browser akun nyata belum diuji.

Demo: login Volunteer, buka detail lamaran sendiri berstatus Siap Ditinjau.
Periksa rincian terbuka, panel dapat dilipat, alasan opsional dan tombol merah.
Penarikan bersifat final; lakukan demo pengiriman hanya pada data tes.
PIC A3/A4 dapat memakai komponen existing; tidak ada integrasi baru tertunda.
