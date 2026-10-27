<?php

namespace Tests\Unit;

use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\Event;
use App\Models\User;
use App\Policies\ApplicationDocumentPolicy;
use App\Policies\ApplicationPolicy;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class ApplicationDocumentPolicyTest extends TestCase
{
    private function actor(string $role, int $id): User
    {
        $actor = new User(['role' => $role, 'is_active' => true, 'organizer_status' => 'active']);
        $actor->id = $id;

        return $actor;
    }

    private function document(string $status = 'draft'): ApplicationDocument
    {
        $application = new Application(['status' => $status, 'volunteer_id' => 10]);
        $application->setRelation('event', new Event(['organizer_id' => 20]));
        $document = new ApplicationDocument;
        $document->setRelation('application', $application);

        return $document;
    }

    public function test_gate_discovers_separate_document_and_application_policies(): void
    {
        $this->assertInstanceOf(ApplicationPolicy::class, Gate::getPolicyFor(Application::class));
        $this->assertInstanceOf(ApplicationDocumentPolicy::class, Gate::getPolicyFor(ApplicationDocument::class));
    }

    public function test_only_active_volunteer_owner_can_upload_and_delete_draft_documents(): void
    {
        $document = $this->document();
        foreach ([['volunteer', 10, true], ['volunteer', 11, false], ['organizer', 20, false], ['admin', 10, false]] as [$role, $id, $allowed]) {
            $actor = $this->actor($role, $id);
            $gate = Gate::forUser($actor);
            $this->assertSame($allowed, $gate->allows('create', [ApplicationDocument::class, $document->application]));
            $this->assertSame($allowed, $gate->allows('delete', $document));
            $actor->is_active = false;
            $this->assertFalse($gate->allows('create', [ApplicationDocument::class, $document->application]));
            $this->assertFalse($gate->allows('delete', $document));
        }
    }

    public function test_upload_and_delete_are_denied_after_submission(): void
    {
        $actor = $this->actor('volunteer', 10);
        foreach (['submitted', 'screening_failed', 'assessment_pending', 'under_review', 'accepted', 'rejected', 'withdrawn', 'cancelled'] as $status) {
            $document = $this->document($status);
            $this->assertFalse(Gate::forUser($actor)->allows('create', [ApplicationDocument::class, $document->application]));
            $this->assertFalse(Gate::forUser($actor)->allows('delete', $document));
        }
    }

    public function test_download_respects_existing_application_view_permissions(): void
    {
        $document = $this->document();
        $volunteer = $this->actor('volunteer', 10);
        $organizer = $this->actor('organizer', 20);
        $this->assertTrue(Gate::forUser($volunteer)->allows('download', $document));
        $this->assertFalse(Gate::forUser($organizer)->allows('download', $document));
        $document->application->status = 'submitted';
        $this->assertTrue(Gate::forUser($organizer)->allows('download', $document));
        $organizer->organizer_status = 'pending';
        $this->assertFalse(Gate::forUser($organizer)->allows('download', $document));
        foreach ([$this->actor('volunteer', 11), $this->actor('organizer', 21), $this->actor('admin', 20)] as $actor) {
            $this->assertFalse(Gate::forUser($actor)->allows('download', $document));
        }
        $volunteer->is_active = false;
        $this->assertFalse(Gate::forUser($volunteer)->allows('download', $document));
    }
}
