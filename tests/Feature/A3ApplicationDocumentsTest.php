<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\AvailabilitySlot;
use App\Models\EventEntitlement;
use App\Models\User;
use App\Models\VolunteerProfile;
use App\Models\VolunteerSkill;
use App\Services\ApplicationService;
use App\Services\DocumentStorageService;
use App\Services\EventPublicationService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\DatabaseTestCase;
use Tests\Support\A2Fixture;

class A3ApplicationDocumentsTest extends DatabaseTestCase
{
    use A2Fixture;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('private');
    }

    protected function createCompleteVolunteer($city = null, $skill = null): User
    {
        $volunteer = User::factory()->create([
            'role' => 'volunteer',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        VolunteerProfile::create([
            'user_id' => $volunteer->id,
            'phone' => '081234567890',
            'birth_date' => '2000-01-01',
            'gender' => 'male',
            'city_id' => $city?->id,
            'address' => 'Jl. Uji Relawan',
            'bio' => 'Bio uji relawan',
        ]);

        if ($skill) {
            VolunteerSkill::create([
                'user_id' => $volunteer->id,
                'skill_id' => $skill->id,
                'level' => 'beginner',
            ]);
        }

        AvailabilitySlot::create([
            'user_id' => $volunteer->id,
            'starts_at' => now()->addDays(1),
            'ends_at' => now()->addDays(2),
        ]);

        return $volunteer;
    }

    public function test_volunteer_can_upload_cv_and_supporting_documents_to_draft(): void
    {
        $this->assessmentReady();
        [$owner, $event, $position] = $this->fixture();
        $event->forceFill(['status' => 'approved'])->save();
        app(EventPublicationService::class)->publish($event, $owner);

        $volunteer = $this->createCompleteVolunteer($event->cityRecord, $position->positionSkills->first()->skill);
        $draft = app(ApplicationService::class)->storeDraft($volunteer, $position);

        $cvFile = UploadedFile::fake()->create('curriculum_vitae.pdf', 150, 'application/pdf');
        $supportingFile = UploadedFile::fake()->image('certificate.png');

        $this->actingAs($volunteer);

        // Upload CV
        $responseCv = $this->post("/volunteer/applications/{$draft->id}/documents", [
            'document_type' => 'cv',
            'file' => $cvFile,
        ]);
        $responseCv->assertRedirect("/volunteer/applications/{$draft->id}");
        $responseCv->assertSessionHas('success');

        // Upload supporting
        $responseSup = $this->post("/volunteer/applications/{$draft->id}/documents", [
            'document_type' => 'supporting',
            'file' => $supportingFile,
        ]);
        $responseSup->assertRedirect("/volunteer/applications/{$draft->id}");

        $this->assertCount(2, $draft->documents);
        $cvDoc = $draft->documents()->where('document_type', 'cv')->first();
        $this->assertNotNull($cvDoc);
        $this->assertSame('ready', $cvDoc->status);
        $this->assertSame('curriculum_vitae.pdf', $cvDoc->original_name);
        $this->assertSame('application/pdf', $cvDoc->mime_type);
        $this->assertNotNull($cvDoc->ready_at);
        $this->assertNotEmpty($cvDoc->checksum_sha256);
        Storage::disk('private')->assertExists($cvDoc->storage_key);

        // Volunteer can see documents on application page
        $this->get("/volunteer/applications/{$draft->id}")
            ->assertOk()
            ->assertSee('curriculum_vitae.pdf')
            ->assertSee('certificate.png')
            ->assertSee('Dokumen tersimpan');
    }

    public function test_upload_validates_file_extension_and_content_mime_type(): void
    {
        $this->assessmentReady();
        [$owner, $event, $position] = $this->fixture();
        $event->forceFill(['status' => 'approved'])->save();
        app(EventPublicationService::class)->publish($event, $owner);

        $volunteer = $this->createCompleteVolunteer($event->cityRecord, $position->positionSkills->first()->skill);
        $draft = app(ApplicationService::class)->storeDraft($volunteer, $position);

        $this->actingAs($volunteer);

        // Upload non-pdf file as CV (e.g. text/plain)
        $invalidCv = UploadedFile::fake()->create('cv.txt', 50, 'text/plain');
        $response = $this->postJson("/volunteer/applications/{$draft->id}/documents", [
            'document_type' => 'cv',
            'file' => $invalidCv,
        ]);
        $response->assertStatus(422);

        // Upload invalid document type for supporting (e.g. zip)
        $invalidSupporting = UploadedFile::fake()->create('archive.zip', 50, 'application/zip');
        $response2 = $this->postJson("/volunteer/applications/{$draft->id}/documents", [
            'document_type' => 'supporting',
            'file' => $invalidSupporting,
        ]);
        $response2->assertStatus(422);

        $this->assertCount(0, $draft->documents);
    }

    public function test_upload_validates_max_file_size_and_max_documents_count(): void
    {
        $this->assessmentReady();
        [$owner, $event, $position] = $this->fixture();
        $event->forceFill(['status' => 'approved'])->save();
        app(EventPublicationService::class)->publish($event, $owner);

        $volunteer = $this->createCompleteVolunteer($event->cityRecord, $position->positionSkills->first()->skill);
        $draft = app(ApplicationService::class)->storeDraft($volunteer, $position);

        $this->actingAs($volunteer);

        // File exceeding 5 MB (5200 KB)
        $largeFile = UploadedFile::fake()->create('large.pdf', 5200, 'application/pdf');
        $response = $this->postJson("/volunteer/applications/{$draft->id}/documents", [
            'document_type' => 'cv',
            'file' => $largeFile,
        ]);
        $response->assertStatus(422);

        // Upload 5 valid supporting documents
        $storage = app(DocumentStorageService::class);
        for ($i = 1; $i <= 5; $i++) {
            $file = UploadedFile::fake()->create("doc_{$i}.pdf", 10, 'application/pdf');
            $storage->store($file, ['type' => 'application', 'application' => $draft, 'document_type' => 'supporting'], $volunteer);
        }

        $this->assertSame(5, $draft->documents()->count());

        // 6th upload should fail limit
        $extraFile = UploadedFile::fake()->create('extra.pdf', 10, 'application/pdf');
        $this->expectException(ValidationException::class);
        $storage->store($extraFile, ['type' => 'application', 'application' => $draft, 'document_type' => 'supporting'], $volunteer);
    }

    public function test_volunteer_can_delete_document_during_draft(): void
    {
        $this->assessmentReady();
        [$owner, $event, $position] = $this->fixture();
        $event->forceFill(['status' => 'approved'])->save();
        app(EventPublicationService::class)->publish($event, $owner);

        $volunteer = $this->createCompleteVolunteer($event->cityRecord, $position->positionSkills->first()->skill);
        $draft = app(ApplicationService::class)->storeDraft($volunteer, $position);

        $file = UploadedFile::fake()->create('to_delete.pdf', 50, 'application/pdf');
        $doc = app(DocumentStorageService::class)->store($file, ['type' => 'application', 'application' => $draft, 'document_type' => 'cv'], $volunteer);

        Storage::disk('private')->assertExists($doc->storage_key);

        $this->actingAs($volunteer);
        $response = $this->delete("/volunteer/documents/{$doc->id}");
        $response->assertRedirect("/volunteer/applications/{$draft->id}");
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('application_documents', ['id' => $doc->id]);
        Storage::disk('private')->assertMissing($doc->storage_key);
    }

    public function test_authorized_document_download_rules(): void
    {
        $this->assessmentReady();
        [$owner, $event, $position] = $this->fixture();
        $event->forceFill(['status' => 'approved'])->save();
        app(EventPublicationService::class)->publish($event, $owner);

        $volunteer1 = $this->createCompleteVolunteer($event->cityRecord, $position->positionSkills->first()->skill);
        $volunteer2 = $this->createCompleteVolunteer($event->cityRecord, $position->positionSkills->first()->skill);
        $otherOrganizer = User::factory()->create(['role' => 'organizer', 'is_active' => true, 'email_verified_at' => now()]);
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true, 'email_verified_at' => now()]);

        $draft = app(ApplicationService::class)->storeDraft($volunteer1, $position);

        $cvFile = UploadedFile::fake()->create('cv_volunteer.pdf', 50, 'application/pdf');
        $doc = app(DocumentStorageService::class)->store($cvFile, ['type' => 'application', 'application' => $draft, 'document_type' => 'cv'], $volunteer1);

        // 1. Volunteer 1 (owner) can download
        $this->actingAs($volunteer1);
        $this->get("/documents/{$doc->id}/download")->assertOk();

        // 2. Volunteer 2 cannot download (403)
        $this->actingAs($volunteer2);
        $this->get("/documents/{$doc->id}/download")->assertForbidden();

        // 3. Event organizer cannot download while application is still DRAFT (403)
        $this->actingAs($owner);
        $this->get("/documents/{$doc->id}/download")->assertForbidden();

        // 4. Submit application
        app(ApplicationService::class)->submit($draft, $volunteer1);

        // 5. Event organizer CAN download after submit
        $this->actingAs($owner);
        $this->get("/documents/{$doc->id}/download")->assertOk();

        // 6. Other organizer CANNOT download (403)
        $this->actingAs($otherOrganizer);
        $this->get("/documents/{$doc->id}/download")->assertForbidden();

        // 7. Admin CANNOT automatically download applicant private CV (403)
        $this->actingAs($admin);
        $this->get("/documents/{$doc->id}/download")->assertForbidden();
    }

    public function test_submit_stores_immutable_snapshot_and_profile_changes_do_not_affect_it(): void
    {
        $this->assessmentReady();
        [$owner, $event, $position] = $this->fixture();
        $event->forceFill(['status' => 'approved'])->save();
        app(EventPublicationService::class)->publish($event, $owner);

        $volunteer = $this->createCompleteVolunteer($event->cityRecord, $position->positionSkills->first()->skill);
        $draft = app(ApplicationService::class)->storeDraft($volunteer, $position);

        $cvFile = UploadedFile::fake()->create('cv_snapshot.pdf', 100, 'application/pdf');
        app(DocumentStorageService::class)->store($cvFile, ['type' => 'application', 'application' => $draft, 'document_type' => 'cv'], $volunteer);

        $submitted = app(ApplicationService::class)->submit($draft, $volunteer);

        $this->assertSame('submitted', $submitted->status);
        $snapshot = $submitted->snapshot_json;
        $this->assertNotNull($snapshot);
        $this->assertArrayHasKey('profile', $snapshot);
        $this->assertSame($volunteer->name, $snapshot['profile']['name']);
        $this->assertSame('081234567890', $snapshot['profile']['phone']);
        $this->assertCount(1, $snapshot['documents']);
        $this->assertSame('cv_snapshot.pdf', $snapshot['documents'][0]['original_name']);

        $originalName = $volunteer->name;
        // Now volunteer updates profile
        $volunteer->volunteerProfile->update([
            'phone' => '089999999999',
            'address' => 'Jl. Perubahan Baru',
        ]);
        $volunteer->update(['name' => 'Nama Baru Volunteer']);

        // Assert that the application snapshot remains exactly unchanged
        $freshApplication = Application::find($submitted->id);
        $this->assertEquals($snapshot, $freshApplication->snapshot_json);
        $this->assertSame('081234567890', $freshApplication->snapshot_json['profile']['phone']);
        $this->assertSame($originalName, $freshApplication->snapshot_json['profile']['name']);
    }

    public function test_organizer_can_view_submitted_applications_list_and_detail_with_status_menunggu_evaluasi(): void
    {
        $this->assessmentReady();
        [$owner, $event, $position] = $this->fixture();
        $event->forceFill(['status' => 'approved'])->save();
        app(EventPublicationService::class)->publish($event, $owner);

        $volunteer1 = $this->createCompleteVolunteer($event->cityRecord, $position->positionSkills->first()->skill);
        $volunteer2 = $this->createCompleteVolunteer($event->cityRecord, $position->positionSkills->first()->skill);

        // Draft application 1 (submitted)
        $draft1 = app(ApplicationService::class)->storeDraft($volunteer1, $position);
        $cvFile = UploadedFile::fake()->create('cv1.pdf', 50, 'application/pdf');
        app(DocumentStorageService::class)->store($cvFile, ['type' => 'application', 'application' => $draft1, 'document_type' => 'cv'], $volunteer1);
        $app1 = app(ApplicationService::class)->submit($draft1, $volunteer1);

        // Draft application 2 (not submitted, remains draft)
        $draft2 = app(ApplicationService::class)->storeDraft($volunteer2, $position);

        $this->actingAs($owner);

        // Organizer accesses applicants list for event
        $indexResponse = $this->get("/organizer/events/{$event->id}/applications");
        $indexResponse->assertOk();
        $indexResponse->assertSee($volunteer1->name);
        $indexResponse->assertSee('Menunggu evaluasi');
        // Draft applicant should not be listed
        $indexResponse->assertDontSee($volunteer2->name);

        // Organizer views applicant detail
        $showResponse = $this->get("/organizer/applications/{$app1->id}");
        $showResponse->assertOk();
        $showResponse->assertSee($volunteer1->name);
        $showResponse->assertSee('Menunggu evaluasi');
        $showResponse->assertSee('cv1.pdf');
        $showResponse->assertSee('Unduh Dokumen');

        // Other organizer cannot view
        $otherOrganizer = User::factory()->create(['role' => 'organizer', 'is_active' => true, 'organizer_status' => 'active', 'email_verified_at' => now()]);
        $this->actingAs($otherOrganizer);
        $this->get("/organizer/events/{$event->id}/applications")->assertForbidden();
        $this->get("/organizer/applications/{$app1->id}")->assertForbidden();
    }
}
