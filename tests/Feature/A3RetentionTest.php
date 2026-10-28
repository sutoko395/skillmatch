<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\DocumentPurge;
use App\Models\User;
use App\Services\DocumentRetentionService;
use App\Services\DocumentStorageService;
use App\Services\SelectionService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\DatabaseTestCase;
use Tests\Support\A3ExecutionFixture;

class A3RetentionTest extends DatabaseTestCase
{
    use A3ExecutionFixture;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('private');
        Storage::fake('private_documents');
    }

    private function document(Application $application): ApplicationDocument
    {
        $application->forceFill(['status' => 'draft'])->save();
        $document = app(DocumentStorageService::class)->store(UploadedFile::fake()->create('sample.pdf', 10, 'application/pdf'),
            ['type' => 'application', 'application' => $application, 'document_type' => 'cv'], $application->volunteer);
        $application->forceFill(['status' => 'under_review'])->save();

        return $document;
    }

    public function test_retention_purges_only_expired_application_files_and_replays_tombstone_after_restore(): void
    {
        $a = $this->reviewApplication();
        $document = $this->document($a);
        $b = $this->reviewApplication();
        $keep = $this->document($b);
        Storage::disk('private')->put('organizers/keep.pdf', 'Organization fixture');
        $a->event->forceFill(['lifecycle_status' => 'completed', 'completed_at' => now()->subDays(91)])->save();
        $service = app(DocumentRetentionService::class);
        $service->cleanup();
        $this->assertSame('purged', $document->fresh()->status);
        Storage::disk($document->disk)->assertMissing($document->storage_key);
        Storage::disk($keep->disk)->assertExists($keep->storage_key);
        Storage::disk('private')->assertExists('organizers/keep.pdf');
        // Simulate older SQL + file restore while retaining the latest independent ledger.
        DocumentPurge::query()->delete();
        $document->forceFill(['status' => 'ready', 'purged_at' => null])->save();
        Storage::disk($document->disk)->put($document->storage_key, 'Restored fixture');
        $service->recover();
        $this->assertSame('purged', $document->fresh()->status);
        Storage::disk($document->disk)->assertMissing($document->storage_key);
        $this->actingAs($a->volunteer)->get(route('documents.download', $document))->assertNotFound();
    }

    public function test_abandoned_draft_cleanup_preserves_submitted_history_and_recent_drafts(): void
    {
        $a = $this->reviewApplication();
        $b = $this->reviewApplication();
        // Real drafts have no attempt/screening results; independent fixture follows that contract.
        $draft = Application::create(['event_id' => $a->event_id, 'event_position_id' => $a->event_position_id,
            'volunteer_id' => User::factory()->create(['role' => 'volunteer'])->id, 'status' => 'draft']);
        $recent = Application::create(['event_id' => $b->event_id, 'event_position_id' => $b->event_position_id,
            'volunteer_id' => User::factory()->create(['role' => 'volunteer'])->id, 'status' => 'draft']);
        $document = app(DocumentStorageService::class)->store(UploadedFile::fake()->create('sample.pdf', 10, 'application/pdf'),
            ['type' => 'application', 'application' => $draft, 'document_type' => 'cv'], $draft->volunteer);
        DB::table('applications')->where('id', $draft->id)->update(['updated_at' => now()->subDays(8)]);
        app(DocumentRetentionService::class)->cleanup();
        $this->assertNull($draft->fresh());
        $this->assertNotNull($a->fresh());
        $this->assertNotNull($recent->fresh());
        Storage::disk($document->disk)->assertMissing($document->storage_key);
        $this->assertDatabaseHas('document_purges', ['storage_key' => $document->storage_key]);
    }

    public function test_missing_file_is_marked_missing_and_never_downloaded(): void
    {
        $a = $this->reviewApplication();
        $document = $this->document($a);
        $originalBytes = Storage::disk($document->disk)->get($document->storage_key);
        Storage::disk($document->disk)->delete($document->storage_key);
        app(DocumentRetentionService::class)->recover();
        $this->assertSame('missing', $document->fresh()->status);
        $this->actingAs($a->volunteer)->get(route('documents.download', $document))->assertNotFound();
        Storage::disk($document->disk)->put($document->storage_key, $originalBytes);
        app(DocumentRetentionService::class)->recover();
        $this->assertSame('ready', $document->fresh()->status);
    }

    public function test_outer_rollback_orphan_is_recovered_without_deleting_a_new_upload(): void
    {
        $a = $this->reviewApplication();
        $a->forceFill(['status' => 'draft'])->save();
        $key = null;
        try {
            DB::transaction(function () use ($a, &$key) {
                $document = app(DocumentStorageService::class)->store(UploadedFile::fake()->create('rollback.pdf', 10, 'application/pdf'),
                    ['type' => 'application', 'application' => $a, 'document_type' => 'cv'], $a->volunteer);
                $key = $document->storage_key;
                throw new \RuntimeException('Outer rollback');
            });
        } catch (\RuntimeException) {
            Storage::disk('private_documents')->assertExists($key);
        }
        app(DocumentRetentionService::class)->recover();
        Storage::disk('private_documents')->assertExists($key);
        $this->travel(25)->hours();
        app(DocumentRetentionService::class)->recover();
        Storage::disk('private_documents')->assertMissing($key);
        $this->travelBack();
    }

    public function test_replacement_keeps_old_file_until_commit_and_purge_recovery(): void
    {
        $a = $this->reviewApplication();
        $old = $this->document($a);
        $a->forceFill(['status' => 'draft'])->save();
        $new = app(DocumentStorageService::class)->store(UploadedFile::fake()->create('replacement.pdf', 10, 'application/pdf'),
            ['type' => 'application', 'application' => $a, 'document_type' => 'cv'], $a->volunteer);
        $this->assertSame('purged', $old->fresh()->status);
        $this->assertSame('ready', $new->status);
        app(DocumentRetentionService::class)->recover();
        Storage::disk($old->disk)->assertMissing($old->storage_key);
        Storage::disk($new->disk)->assertExists($new->storage_key);
        $this->assertSame(1, $a->documents()->where('status', 'ready')->count());
    }

    public function test_withdrawal_revokes_organizer_document_access_but_owner_keeps_access_and_download_audit(): void
    {
        $a = $this->reviewApplication();
        $document = $this->document($a);
        $this->actingAs($a->event->organizer)->get(route('documents.download', $document))->assertOk()->assertHeader('x-content-type-options', 'nosniff');
        app(SelectionService::class)->withdraw($a, $a->volunteer);
        $this->get(route('documents.download', $document))->assertForbidden();
        $this->actingAs($a->volunteer)->get(route('documents.download', $document))->assertOk();
        $this->assertDatabaseHas('audit_logs', ['action' => 'document.downloaded', 'subject_id' => $document->id]);
    }

    public function test_metadata_failure_compensates_new_file_and_preserves_previous_cv(): void
    {
        $a = $this->reviewApplication();
        $old = $this->document($a);
        $a->forceFill(['status' => 'draft'])->save();
        ApplicationDocument::creating(fn () => throw new \RuntimeException('Metadata failure fixture'));
        try {
            app(DocumentStorageService::class)->store(UploadedFile::fake()->create('replacement.pdf', 10, 'application/pdf'),
                ['type' => 'application', 'application' => $a, 'document_type' => 'cv'], $a->volunteer);
            $this->fail('Metadata failure must abort upload.');
        } catch (\RuntimeException) {
            $this->assertSame('ready', $old->fresh()->status);
            Storage::disk($old->disk)->assertExists($old->storage_key);
            $this->assertCount(1, Storage::disk('private_documents')->allFiles('applications'));
        } finally {
            ApplicationDocument::flushEventListeners();
        }
    }

    public function test_organizer_access_expires_without_waiting_for_cleanup_and_checksum_mismatch_is_missing(): void
    {
        $a = $this->reviewApplication();
        $document = $this->document($a);
        $a->event->forceFill(['lifecycle_status' => 'completed', 'completed_at' => now()->subDays(91)])->save();
        $this->actingAs($a->event->organizer)->get(route('documents.download', $document))->assertForbidden();
        $this->actingAs($a->volunteer)->get(route('documents.download', $document))->assertOk();
        Storage::disk($document->disk)->put($document->storage_key, 'Corrupt restored fixture');
        app(DocumentRetentionService::class)->recover();
        $this->assertSame('missing', $document->fresh()->status);
    }

    public function test_shared_organization_validator_uses_separate_policy_and_has_no_applicant_access_for_admin(): void
    {
        $a = $this->reviewApplication();
        $service = app(DocumentStorageService::class);
        $metadata = $service->validateOrganizationUpload(UploadedFile::fake()->create('identity.pdf', 10, 'application/pdf'), $a->event->organizer, $a->event->organizer);
        $this->assertSame('application/pdf', $metadata['mime_type']);
        $this->assertArrayNotHasKey('status', $metadata);
        $this->expectException(AuthorizationException::class);
        $service->validateOrganizationUpload(UploadedFile::fake()->create('identity.pdf', 10, 'application/pdf'), $a->event->organizer, $a->volunteer);
    }
}
