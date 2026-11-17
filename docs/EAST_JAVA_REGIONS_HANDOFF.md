# Wilayah Jawa Timur — handoff

## Lingkup

- Menambahkan tabel `provinces` dan kolom `cities.province_id`, `cities.code`, `cities.type`.
- Menyediakan 38 kabupaten/kota Jawa Timur (29 kabupaten + 9 kota) melalui `EastJavaRegionSeeder`.
- Form event, profil Volunteer, profil Organizer, dan filter katalog memakai `Kota/Kabupaten` yang dikelompokkan per provinsi.
- Profil Organizer memakai `city_id` dengan fallback teks kota lama. Perubahan kota tetap memicu aturan verifikasi ulang identitas; pemetaan teks lama yang setara (mis. `Malang` ke `Kota Malang`) tidak memaksa review ulang.

## Migration dan seeder

Migration: `2026_11_17_000001_create_provinces_and_extend_cities.php` dan `2026_11_17_000002_add_city_to_organizer_profiles.php`.

```powershell
php artisan migrate
php artisan db:seed --class=EastJavaRegionSeeder
```

`DatabaseSeeder` menjalankan `EastJavaRegionSeeder` setelah `MasterDataSeeder`. Seeder idempoten dan tidak menghapus kota lain. ID data lama dipertahankan:

| Lama | Baru | Kode |
|---|---|---|
| Malang | Kota Malang | 3573 |
| Surabaya | Kota Surabaya | 3578 |
| Sidoarjo | Kabupaten Sidoarjo | 3515 |

Jika baris legacy dan baris kanonis keduanya ada, baris legacy dinonaktifkan agar tidak muncul di pilihan baru; data historis yang mereferensikannya tetap tersimpan.

## Tampilan dan data

- `City` memiliki relasi `province()`; `Province` memiliki relasi `cities()`.
- Komponen `city-options` menampilkan `<optgroup>` per provinsi.
- Snapshot lamaran baru menyimpan `profile.city_province`; snapshot lama tetap memakai `city_name` tanpa provinsi.
- Matching dan screening tetap memakai `city_id`; rumus tidak berubah.

## Pengujian

- `tests/Feature/EastJavaRegionTest.php`: jumlah wilayah, tipe, kode, idempotensi, dan tampilan pilihan.
- Tes terkait kota/profil: `EventCityTest`, `ProfileTest`, `FoundationIntegrityTest`, `DemoAccountsCommandTest`.

## Batas

- Hanya Jawa Timur yang tersedia; pengguna luar Jawa Timur belum punya pilihan domisili.
- Kecamatan/kelurahan/kode pos tidak dimasterkan.
- Profil Organizer lama tanpa `city_id` tetap menampilkan teks kota lama sampai pengguna memilih kota dari master.
- Data wilayah mengikuti kode BPS; perbarui seeder jika ada perubahan wilayah resmi.