# Desain Kegiatan Dibatalkan

11 November 2026. Branch feature/volunteer-design, dasar 1a8323a.
Permintaan pengguna: mengikuti Kegiatan dibatalkan.svg (1440 × 1568).
Lokal tanpa staging, commit atau push; perubahan lokal lainnya dipertahankan.

Sebelumnya cancelled hanya memakai shell navy, dengan gaya konten bawaan dan
rincian tertutup. Kini kelas volunteer-application-cancelled memakai ulang kartu
detail putih, banner netral, timeline, status/tanggal/alasan kanan, jadwal posisi,
informasi posisi dan dokumen hanya-baca. Screening, assessment dan Waktu event
terbuka default; native details tetap dapat dilipat. Accepted pada event cancelled
juga mendapat gaya pembatalan, tanpa banner hijau/koordinasi peserta.

File: applications/show, partial progress/information; CSS dasar draft/submitted
dan gaya netral volunteer-application-withdrawn.css diperluas ke cancelled.
Tidak menambah komponen duplikat. Route, layanan, schema, migration, Policy dan
transisi tetap. Data/tanggal/alasan nyata dan escaping Blade dipertahankan.
PRD/kontrak dan README setup tetap berlaku; desain/UAT diperbarui.

Validasi: A3ExecutionTest + VolunteerAssessmentInfoTest + EventCoordinationTest
lulus 29 tes/295 assertion pada MySQL skillmatch_testing dengan guard existing,
tanpa reset database kerja. Build Vite 8.3.1 dan artisan view:cache lulus.
Render fixture tanpa simpan: cancelled, alasan markup/kosong, tanggal kosong,
screening kosong, attempt cancelled/submitted, dokumen ready; tidak ada aksi edit
atau mulai assessment. Alasan markup tampil sebagai teks aman.
Chrome CDP 1440/768/390 tidak overflow; screenshot desktop/mobile diperiksa
terhadap SVG. Preview berisi fixture sintetis, bukan pembatalan data nyata.
Artefak QA di storage/app diabaikan Git. git diff --check lulus.

Demo: login Volunteer, buka detail lamaran cancelled milik akun. Periksa status,
alasan/tanggal bila tersedia, rincian dan dokumen. Tidak perlu membatalkan event
nyata untuk meninjau preview. Alur login/navigasi browser akun nyata belum diuji;
tidak ada dependensi baru. PIC A3/A4 dapat meninjau data aktual. Bukan klaim semua
UAT/review tim lulus.
