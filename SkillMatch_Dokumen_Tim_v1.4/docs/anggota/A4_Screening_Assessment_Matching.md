# Brief A4 — screening, assessment, matching, aktivitas

## Konsumsi jadwal posisi — 10 Oktober 2026

Form A2 default mengikuti jadwal event dengan opsi jadwal khusus. Kedua mode tetap disimpan pada `position_schedules` dan disalin ke snapshot `[start,end]`; A4 memakai snapshot immutable untuk screening ketersediaan/matching. Rumus 50/30/20 dan batas waktu assessment tetap sama. Jangan membaca ulang waktu event sebagai pengganti snapshot jadwal historis.

Versi 1.4.2 • PIC: A4. Baca [PRD](../../PRD_SkillMatch_Tim.md), [kontrak](../KONTRAK_INTEGRASI.md), [design](../../design.md), [UAT](../UAT.md).

## Dependensi fondasi bersama

Gunakan [SF](SHARED_FOUNDATION.md) yang sudah diimplementasikan; gate/review final tetap mengikuti handoff. Pengguna melanjutkan A2 sesuai keputusan 5 Oktober 2026. PIC A4 melanjutkan modul ini tanpa membuat ulang fondasi; aktivitas dan notifikasi tetap bagian A4.

## Titik mulai

ZIP baru menyimpan skill/level Volunteer dan requirement posisi, belum memiliki screening, assessment atau engine matching. Dashboard Volunteer yang ada hanya ringkasan profil; halaman aktivitas perlu data lamaran dan tugas nyata.

## Pekerjaan berurutan

| Tahap | Deliverable | Penerimaan |
|---|---|---|
| 1 | Interface dan fixture | Kontrak snapshot A1/A2/A3, fixture skor 80,00, schema results/attempt/notification. |
| 2 | Assessment organizer | CRUD set pilihan ganda per posisi, satu kunci tiap soal, durasi, versi; dikunci setelah publikasi. |
| 3 | Screening | Evaluasi snapshot dan dokumen ready; hasil/alasan per syarat; retry tidak menggandakan status. |
| 4 | Matching | Skill 50%, availability 30%, lokasi 20%; level minimum, irisan interval, city ID; histori tetap. |
| 5 | Assessment peserta | Satu attempt, timer server, autosave/recovery, finalize timeout dan submit idempoten, nilai terpisah. |
| 6 | Aktivitas pengguna | Volunteer/Organizer navbar web utama; tugas relevan data sendiri; tidak ada grafik/dashboard. |
| 7 | Notifikasi bersama | NotificationService/outbox/dedupe/retry, daftar/read milik akun; integrasi trigger A1/A2/A3. |

## Rumus dan batas

MatchingService menerima snapshot immutable, bukan membaca ulang profil yang mungkin sudah berubah. Skill cocok jika ID dan minimum level memenuhi; gabungkan interval sebelum menghitung availability; lokasi kota sama=100, berbeda=0. Total 0,50S + 0,30A + 0,20L. Rule version match-v1.4; dua desimal di UI.

Assessment score = benar/total ×100, tidak masuk rumus matching. Tidak ada ambang gagal otomatis baseline. Hanya screening menentukan akses assessment; seleksi final milik Organizer. Daftar Siap Ditinjau hanya under_review, diurutkan match, assessment, waktu submit, ID.

Timer frontend bukan otoritas. Timer mulai dari server, tersimpan saat start pertama; batas akhir tidak melewati starts_at event dan attempt tidak dapat dimulai setelah batas itu. Saat browser ditutup, scheduler tetap memfinalkan timeout; request terlambat menggunakan finalisasi yang sama. Attempt cancelled/lamaran withdrawn atau rejected tidak boleh menjadi under_review lagi. Kunci jawaban tidak muncul pada JSON, HTML tersembunyi, Alpine data, atau response peserta.

## Serah-terima

SF menyediakan profil kota/availability dan layout/audit; A1 memeliharanya setelah serah-terima. A2 menyediakan snapshot posisi dan tab assessment; A4 menyediakan `assessment ready` check untuk publikasi. A3 menyediakan snapshot lamaran dan transisi submitted; A4 menulis hasil dan status sampai under_review, A3 mengambil alih keputusan seleksi. A4 menyediakan notifikasi, masing-masing PIC tetap bertugas memasang trigger dalam modulnya.

Aktivitas hanya membaca status sumber. Jangan membuat tabel status aktivitas sendiri yang bisa berbeda dari applications/orders/events. Endpoint assessment/notification harus memakai Policy milik peserta.

## Uji minimum

UAT-17, 18, 19, 20, 29, 30; ikut UAT-11, 21 dan 31. Tes perhitungan berbasis contoh manual, overlap interval, minimum level, kota berbeda, refresh timer, double submit dan perubahan profil sesudah melamar.

## Demo selesai

Pelamar gagal mendapat alasan jelas; yang lolos bisa assessment; skor contoh 80,00 konsisten; refresh tidak mengulang waktu; nilai assessment tampil terpisah; aktivitas menampilkan tugas, dan notifikasi tidak ganda saat event bisnis diulang.

## Batas tugas

Tidak menjadi penanggung jawab semua merge, bug, atau instalasi anggota. Tidak membangun AI, rekomendasi mesin belajar, attendance/performance, atau payment. Setiap anggota menyelesaikan frontend/backend dan pengujian modulnya.

## Batas UI setelah koreksi baseline - 5 Oktober 2026

Beranda publik dipulihkan oleh SF dan dilanjutkan A2; dashboard dasar admin dipertahankan untuk A1. A4 tetap melengkapi ringkasan profil pada Aktivitas Volunteer/Organizer dengan tugas dan data kegiatan pemilik nyata, serta mengerjakan assessment/matching/notifikasi. Gunakan navbar SF; jangan menghidupkan kembali dashboard analitik/sidebar pengguna. Pemulihan beranda bukan bukti engine matching atau aktivitas sudah selesai.

## Pemulihan ringkasan pengguna - 5 Oktober 2026

Aktivitas dasar kini menampilkan ringkasan profil nyata dari controller/view dashboard baseline yang disesuaikan. Volunteer memakai ProfileEligibilityService, skill/kota sendiri dan interval WIB; Organizer memakai profil/kontak/status sendiri. A4 melanjutkan kartu ini dengan data tugas, lamaran, assessment dan notifikasi melalui ActivityReadService, bukan menggantinya dengan shell kosong. Nama route activity.index, navbar dan proteksi SF tetap. Query approved sebagai event tersedia, angka nol hardcoded dan link kosong tidak dipulihkan.

## Integrasi A2 - 5 Oktober 2026

A2 menyediakan `App\Contracts\AssessmentReadiness::publishedVersion(EventPosition): int`. Bind implementasi A4 yang memvalidasi assessment lengkap/published, pertanyaan, opsi, tepat satu kunci, durasi dan versi. Kegagalan melempar ValidationException; binding yang belum tersedia menahan submit/publish dengan pesan jelas. Fake keberhasilan hanya dipasang pada tes A2, tidak di provider aplikasi.

Review interface tersebut bersama A2 sebelum integrasi. A2 sudah memasang pemanggilan NotificationService.enqueue dengan key stabil; `a2:retry-notifications` memproses audit/payment ledger yang tertunda menuju outbox A4. Ini bukan implementasi outbox/delivery atau bukti integrasi notifikasi selesai.

## Integrasi implementasi A2 terbaru - 5 Oktober 2026

### Assessment dan snapshot

Gunakan schema/model EventPosition, PositionSkill, PositionSchedule dan PositionRequirement A2, bukan schema posisi baru. PositionSnapshotService.build memakai skills id/minimum_level/is_required, schedules start/end ISO UTC, requirements kind/document_type/is_required, city_id, flag required_full_availability/required_same_city, assessment_version serta rule_version match-v1.4. A4 harus memproses snapshot immutable saat submit, bukan membaca ulang profil/master yang telah berubah. Tampilkan waktu WIB, simpan/finalisasi dengan otoritas waktu server.

Implementasikan dan bind App\Contracts\AssessmentReadiness::publishedVersion(EventPosition): int. Nilai positif harus berasal versi assessment valid yang benar-benar siap; incomplete melempar ValidationException. Jangan memasang binding sukses konstan untuk membuka publikasi. Review kontrak versi/freeze bersama A2, karena pemeriksaan dipanggil pada pengajuan, publikasi dan snapshot A3. CRUD/entry point assessment Organizer A4 dipasang pada alur posisi/event A2 menggunakan Policy pemilik.

### Paket dan notifikasi

Semua tier awal Free/Standard/Premium memiliki rencana fitur screening/assessment/seleksi yang sama; perbedaan paket adalah harga dan limit posisi/lamaran/hari sesuai PRD. Admin dapat mengubahnya. Jangan menjadikan Free tanpa assessment atau memakai nama paket sebagai bypass screening. Batas submit adalah entitlement A2/A3, bukan rumus matching atau batas attempt baru.

A2Dependencies sudah memanggil App\Services\NotificationService.enqueue(string key, User recipient, array payload). Implementasi A4 harus menggunakan transaksi/outbox dan unique dedupe key, bukan mengirim HTTP/email di dalam transaksi domain. Key A2: `event.published:<event_id>:<revision>:<recipient_id>` atau `event.cancelled:<event_id>:<revision>:<recipient_id>`, serta `payment.verified:<payment_event_id>:1:<recipient_id>`. Replay melalui php artisan a2:retry-notifications harus aman diulang dengan key yang sama. notified_at pada ledger pembayaran berarti diteruskan ke layanan/outbox, bukan bukti delivery. Worker/retry/delivery/read milik A4; trigger lain tetap dipasang PIC modul masing-masing.

### Cancellation, aktivitas dan UI

A3 menyediakan EventCancellationService dan menghubungkannya dengan pembatalan attempt A4 di transaksi event. Attempt/worker terlambat tidak boleh mengubah cancelled/withdrawn/rejected menjadi under_review. Review lock dan idempotensi lintas A2/A3/A4 sebelum mengklaim pembatalan terintegrasi.

ActivityReadService membaca status sumber events/orders/applications milik pengguna yang sah. approved bukan published dan redirect checkout bukan paid. Pertahankan ringkasan profil serta navbar SF; tambah tugas nyata, tanpa angka hardcoded atau dashboard analitik pengguna. Beranda/kartu paket publik serta UI paket/transaksi tetap A2, panel analitik admin A1. Login/register sudah dirapikan tetapi guard/route/redirect tetap SF.

### Gate integrasi yang masih diperlukan

Uji assessment incomplete menahan submit/publish, versi snapshot tetap setelah edit, kesiapan valid membuka alur sesuai seluruh syarat publikasi, replay/outbox ganda tidak menggandakan notifikasi, rollback domain/outbox konsisten, attempt cancelled tidak hidup lagi, serta aktivitas hanya membaca data pengguna yang berwenang. Tes mock A4 pada A2 bukan bukti gate ini lulus. Harga/limit awal dan bukti A2 tersedia di [A2_HANDOFF](../../../docs/A2_HANDOFF.md); pastikan commit A2 disepakati tersedia pada branch A4, tidak mengasumsikan sudah merge ke main.
