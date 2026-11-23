<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\OrganizerDocument;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\DatabaseTestCase;

class OrganizerKtpUploadTest extends DatabaseTestCase
{
    public static function verificationDocuments(): array
    {
        return [
            'NIB from current form' => ['nib', 'nib-test.pdf'],
            'KTP from current form' => ['nib', 'ktp-test.pdf'],
            'KTP from legacy field' => ['ktp', 'ktp-legacy-test.pdf'],
        ];
    }

    #[DataProvider('verificationDocuments')]
    public function test_verification_document_is_saved_and_previewed_only_by_owner_or_admin(string $field, string $filename): void
    {
        Storage::fake('local');
        $owner = User::factory()->unverified()->create(['role' => 'organizer']);
        $bytes = "%PDF-1.4\n% synthetic upload fixture\n".str_repeat('x', 300 * 1024)."\n%%EOF";
        $file = UploadedFile::fake()->createWithContent($filename, $bytes);

        $this->actingAs($owner)->get('/organizer/profile')->assertOk()->assertSee('Dokumen verifikasi (NIB atau KTP)');
        $this->post('/organizer/profile', $this->profileData() + [$field => $file])
            ->assertSessionHasNoErrors()->assertRedirect(route('organizer.profile.pending'));
        $document = OrganizerDocument::where('user_id', $owner->id)->sole();
        $this->assertSame('ktp', $document->document_type);
        $this->assertSame($filename, $document->document_name);
        $this->assertDatabaseHas('organizer_profiles', ['user_id' => $owner->id, 'organization_name' => 'Organisasi uji upload']);
        Storage::disk('local')->assertExists($document->file_path);
        $this->assertSame($bytes, Storage::disk('local')->get($document->file_path));
        $this->assertSame('pending', $owner->fresh()->organizer_status);
        $this->assertFalse($owner->fresh()->hasVerifiedEmail());

        $preview = route('organizer.profile.documents.view', $document);
        $this->get('/organizer/profile')->assertOk()->assertSee($preview);
        $this->get($preview)->assertOk()->assertHeader('Content-Type', 'application/pdf')
            ->assertStreamedContent($bytes);
        $this->get('/storage/'.$document->file_path)->assertForbidden();
        $this->actingAs(User::factory()->create(['role' => 'organizer']))->get($preview)->assertForbidden();
        $this->actingAs(User::factory()->create(['role' => 'volunteer']))->get($preview)->assertForbidden();
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('admin.organizers.documents.view', $document))->assertOk()->assertStreamedContent($bytes);
        $this->post('/logout');
        $this->get($preview)->assertRedirect('/login');
    }

    public function test_first_submission_requires_a_verification_document(): void
    {
        $owner = User::factory()->create(['role' => 'organizer']);
        $this->actingAs($owner)->post('/organizer/profile', $this->profileData())
            ->assertSessionHasErrors(['nib' => 'Unggah satu dokumen verifikasi, NIB atau KTP, untuk pengajuan pertama.']);
        $this->assertDatabaseMissing('organizer_profiles', ['user_id' => $owner->id]);
    }

    public static function failedUploads(): array
    {
        return [
            'partial transfer' => [UPLOAD_ERR_PARTIAL, 'Upload KTP terputus sebelum seluruh berkas diterima. Pilih ulang berkas dan coba kembali.'],
            'missing temporary folder' => [UPLOAD_ERR_NO_TMP_DIR, 'Folder sementara upload pada server tidak tersedia. Hubungi pengelola untuk memperbaiki konfigurasi PHP.'],
            'temporary write failed' => [UPLOAD_ERR_CANT_WRITE, 'Server gagal menulis berkas sementara KTP. Hubungi pengelola untuk memeriksa izin folder dan ruang penyimpanan.'],
            'PHP extension stopped transfer' => [UPLOAD_ERR_EXTENSION, 'Upload KTP dihentikan oleh ekstensi PHP pada server. Hubungi pengelola.'],
        ];
    }

    #[DataProvider('failedUploads')]
    public function test_php_failed_upload_is_rejected_before_any_profile_or_document_is_saved(int $error, string $message): void
    {
        Storage::fake('local');
        $owner = User::factory()->create(['role' => 'organizer']);
        $file = new UploadedFile('', 'identity-test.pdf', 'application/pdf', $error, true);
        $this->actingAs($owner)->post('/organizer/profile', $this->profileData() + ['ktp' => $file])
            ->assertSessionHasErrors(['ktp' => $message]);
        $this->assertDatabaseMissing('organizer_documents', ['user_id' => $owner->id]);
        $this->assertDatabaseMissing('organizer_profiles', ['user_id' => $owner->id]);
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    private function profileData(): array
    {
        return [
            'organization_name' => 'Organisasi uji upload', 'contact_person' => 'Kontak uji',
            'phone' => '081234567890', 'address' => 'Alamat uji',
            'city_id' => City::firstOrCreate(['name' => 'Kota Upload Uji'], ['is_active' => true])->id,
            'description' => 'Organisasi uji penyimpanan',
        ];
    }
}
