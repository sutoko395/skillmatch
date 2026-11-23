# Samakan desain detail event publik

## Perapian detail publik

Pada branch `feature/skillmatch-ui-and-features`, poster dan susunan kartu
existing dipertahankan. Ringkasan waktu satu hari diringkas dan jarak dalam
kartu informasi dan posisi dipadatkan. Tombol
masuk atau verifikasi diberi gaya aksi utama. Deskripsi posisi yang sama
persis dengan seluruh deskripsi event atau satu paragraf utuh tidak diulang
di detail publik. Panel Volunteer tetap menampilkan deskripsi posisi seperti
sebelumnya. Data, route, hak akses, dan aturan pendaftaran tidak berubah.

Vite dan kompilasi Blade lulus. `VolunteerCatalogTest` (2 tes/32 asersi)
dan `A2EventTest` (17 tes/118 asersi) mencakup render detail dan alur
pendaftaran pada database tes terpisah. Pemeriksaan visual langsung di
browser belum dilakukan.

11 November 2026, branch feature/volunteer-design, dasar b57112a.
Persetujuan pengguna: detail event website utama mengikuti detail panel Volunteer
yang baru dirapikan. Perubahan lokal tanpa commit/push; perubahan lain dipertahankan.

Konten detail diekstrak dari volunteer/events/show ke events/partials/detail.
Wrapper publik events/show dan wrapper Volunteer memakai konten yang sama,
termasuk partial posisi/summary/apply dan CSS .ve-detail existing. Parameter
catalogRoute menentukan tautan kembali: events.index untuk publik, dan
volunteer.events.index untuk panel. Tidak mengubah data, layout publik, navbar,
footer, query publik, route/controller, Policy atau POST lamaran. Partial apply
tetap menangani guest, email belum terverifikasi, role, waktu dan limit existing.

Hasil: kartu putih, dua kolom desktop/satu kolom mobile, ringkasan lokasi/tanggal,
badge kuota/skill, jadwal/persyaratan terpisah dan tombol biru normal case.
Kuota tetap jumlah posisi, bukan sisa tempat. Data/isi asli dipertahankan.
PRD/kontrak dan README setup tetap berlaku; design/UAT diperbarui.

Validasi: VolunteerCatalogTest + A2EventTest lulus 19 tes/145 assertion pada
MySQL skillmatch_testing dengan guard existing. Tes tambahan memeriksa guest
melihat desain baru dan CTA login, tautan kembali publik, tanpa sidebar Volunteer.
Build Vite 8.3.1, view:cache, git diff --check lulus. Chrome CDP fixture tanpa
simpan 1440/768/390 tidak overflow; screenshot desktop/mobile diperiksa visual.
Preview lokal menghapus modul JS untuk render statis, sehingga tampilan menu
mobile pada preview bukan bukti interaksi menu. Navbar/menu runtime tidak diubah.
Login/klik/melamar end-to-end browser akun nyata belum diuji. Artefak QA di
storage/app diabaikan Git. Tidak ada migration/dependensi baru.

Demo: buka katalog /events lalu detail event published sebagai guest atau akun
aktif. Periksa kartu, ringkasan dan CTA sesuai hak akses. Pada panel Volunteer,
detail tetap memakai sidebar serta tautan kembali panel. PIC A2/SF memakai
partial bersama agar kedua detail tetap selaras. Bukan klaim semua UAT lulus.
