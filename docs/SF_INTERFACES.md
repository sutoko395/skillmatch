# Interface fondasi SF — 5 Oktober 2026

Penjabaran implementasi kontrak tim 1.4.2. Tidak menambah status bisnis atau tabel milik A2/A3/A4. Koordinasikan perubahan interface ini dengan pemakai sebelum integrasi.

## Profil dan master

`app(ProfileEligibilityService::class)->check(User $user)` membaca ulang relasi profil, bukan cache relasi lama. Hasil:

```php
[
    'complete' => true,
    'missing_fields' => [],
    'city_id' => 1,
    'skills' => [['id' => 1, 'level' => 'advanced']],
    'availability_slots' => [
        ['start' => '2026-11-01T02:00:00+00:00', 'end' => '2026-11-01T05:00:00+00:00'],
    ],
]
```

ID contoh bukan key fixture. Cari seed melalui nama master/email demo.

- Profil Volunteer lengkap: name/email, phone/birth_date/gender/address/bio seperti form baseline, kota aktif, minimal satu skill aktif dengan level sah, minimal satu interval berdurasi positif. `missing_fields` memakai nama field tersebut, `role`, `skills`, atau `availability_slots`.
- Eligibility ini memeriksa kelengkapan data. Akun aktif, verifikasi email, deadline, Policy lamaran dan screening tetap diperiksa pemilik modul; `complete` bukan izin melamar otomatis.
- Skill keluar berbentuk `id,level`; availability keluar `start,end` ISO-8601 UTC. Penyimpanan memakai `skill_id` dan `starts_at,ends_at`. Empat level tetap beginner/intermediate/advanced/expert.
- `VolunteerProfile::cityRecord()` adalah relasi ke City. Nama ini menghindari benturan atribut teks legacy `city`; FK tetap `city_id` sesuai kontrak.
- `User::availabilitySlots()` mengembalikan AvailabilitySlot dengan immutable datetime UTC. Interval identik pada form dideduplikasi; interval overlap tetap disimpan. A4 menggabungkan irisan ketika menghitung skor dari snapshot, bukan mengubah profil.
- `VolunteerProfileService::save(actor, validatedData)` untuk input tervalidasi VolunteerProfileRequest; pemeriksaan ulang role/akun/master dilakukan di transaksi dengan lock User lalu master. Pemilik selalu actor. Name, profil, skill, availability dan audit disimpan atomik.
- `skills/categories/cities.is_active` boolean. Master nonaktif tidak dipilih untuk konfigurasi profil baru; referensi lama tetap disimpan. FK city/skill/category melindungi referensi dari delete. UI CRUD/nonaktifkan lengkap tetap A1. MasterDataSeeder tidak mengaktifkan ulang data yang telah dinonaktifkan.

## Audit

```php
DB::transaction(function () use ($actor, $subject) {
    // Mutasi bisnis pada koneksi yang sama.
    app(AuditService::class)->record(
        $actor, 'module.subject.updated', $subject,
        ['before' => ['status' => 'pending'], 'after' => ['status' => 'active']],
        'review-approved',
    );
});
```

Signature: `record(?User $actor, string $action, Model $subject, array $changes = [], ?string $reason = null): AuditLog`.

- Subject harus sudah tersimpan. Action adalah key operasional huruf kecil, angka, titik, underscore atau dash (maksimal 100 karakter); actor null dipakai job/sistem.
- Penjabaran keamanan SF: `reason` berupa **kode alasan operasional** dengan format key yang sama, bukan request string bebas. Pemilik modul menyimpan alasan naratif tervalidasi pada objek bisnis; log hanya menyimpan kode minimum yang aman. Jangan kirim password, token, path berkas, isi dokumen atau seluruh request/model.
- `changes` memakai key `before`/`after`. Allowlist saat ini: is_active, organizer_status, status, publication_status, lifecycle_status, city_id, skill_id, level, starts_at, ends_at, revision, changed_fields. Nilai scalar dibatasi; object/array tidak disalin kecuali changed_fields berisi nama field yang diizinkan.
- Log profil mencatat nama field dan city_id, bukan alamat, telepon atau isi bio. Tambahan kebutuhan audit modul harus memperluas allowlist terarah dengan tes redaksi.
- Insert memakai koneksi subject, sehingga ikut commit/rollback transaksi pemanggil. Tidak memakai koneksi audit terpisah atau afterCommit yang dapat kehilangan audit setelah perubahan bisnis sukses. Pemanggil wajib memasukkan mutasi dan audit ke transaksi yang sama.
- AuditLog menolak update/delete; trigger MySQL juga menolak query-builder update/delete. Tidak ada endpoint edit/hapus audit. Tidak ada FK polymorphic subject karena objek berasal banyak tabel; actor FK restrict. Backup/maintenance schema tetap tanggung jawab administrator.

## Auth dan route

Satu guard `web`, GET/POST `/login`, POST `/logout`. User implements MustVerifyEmail. `account.active` tersedia dan juga dipasang pada grup web agar request berikutnya dari sesi akun nonaktif langsung ditolak 403 dan sesi diakhiri. RoleMiddleware tetap dipakai. Untuk route modul: `auth`, `account.active`, `role:<role>`, `verified`, lalu Policy objek; Organizer rekrutmen juga `organizer.active`.

Profil/pending Organizer merupakan pengecualian: akun harus aktif dan role organizer, tetapi email/organisasi boleh belum terverifikasi. Edit kontak tidak mengubah is_active/organizer_status. Nama organisasi yang sudah active dibekukan sementara sampai A1 menyediakan verifikasi ulang; kontak, alamat, kota, deskripsi dan website masih dapat diperbarui tanpa dokumen.

`LoginDestination::defaultFor(user)` menentukan tujuan final; `resolve(request)` mengonsumsi intended URL sekali. Hanya path GET tanpa objek dari allowlist yang diterima, absolute URL harus cocok APP_URL scheme/host/port. URL eksternal, backslash, encoding ambigu, unknown dan lintas role dibuang. Query/fragment tidak diteruskan. Modul baru dengan intended objek wajib menambah pemeriksaan Policy; jangan memperluas menjadi prefix role semata.

Policy bersama: UserPolicy untuk profil sendiri, panel admin, target role pengguna, penolakan delete akun; AdminResourcePolicy dipetakan ke Skill/Category/Event baseline. Ini tidak memberikan hak membaca dokumen pelamar/organisasi secara otomatis.

## Layout

`@extends('layouts.public')` / `layouts.user` memakai `@section('title')` dan `@section('content')`. `admin.layouts.sidebar` tetap shell admin. Komponen Breeze tetap dasar input/tombol/modal; wrapper baru: page-header, section-card, button, form-field, status-badge, empty-state, flash, confirm-dialog. Font dari layouts/fonts; Alpine hanya resources/js/app.js.

Form-field menerima name/label/value/type/required/helper dan optional slot kontrol khusus. Gunakan ID kontrol sesuai name agar label/error terhubung. Wrapper button menerima variant primary/secondary/danger. Confirm-dialog membungkus modal Breeze; isi form, CSRF, alasan dan tindakan tetap milik modul. Flash dirender shell user; admin dapat memakai komponen yang sama tanpa menduplikasi pesan.
