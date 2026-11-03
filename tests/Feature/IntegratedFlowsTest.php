<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\NotificationOutbox;
use App\Models\User;
use App\Services\ActivityReadService;
use App\Services\AssessmentService;
use App\Services\AuditService;
use App\Services\NotificationService;
use App\Services\RecruitmentScheduleService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\DatabaseTestCase;
use Tests\Support\ParticipantAssessmentFixture;

class IntegratedFlowsTest extends DatabaseTestCase
{
    use ParticipantAssessmentFixture;

    private function profileData(): array
    {
        return ['organization_name' => 'Organisasi Uji', 'contact_person' => 'Kontak Uji',
            'phone' => '08123456789', 'address' => 'Alamat Uji', 'city' => 'Malang', 'description' => 'Deskripsi Uji'];
    }

    private function organizer(): User
    {
        Storage::fake('local');
        $owner = User::factory()->create(['role' => 'organizer', 'organizer_status' => 'active']);
        $owner->organizerProfile()->create($this->profileData());
        Storage::disk('local')->put('organizers/fixture.pdf', '%PDF-1.4 test');
        $owner->organizerDocuments()->create(['document_type' => 'ktp', 'document_name' => 'fixture.pdf', 'file_path' => 'organizers/fixture.pdf']);

        return $owner;
    }

    public function test_internal_notification_dedupe_rollback_ownership_and_email_isolation(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $service = app(NotificationService::class);
        $key = 'event.published:123:1:'.$user->id;
        $row = $service->enqueue($key, $user, ['type' => 'event.published']);
        $this->assertSame($row->id, $service->enqueue($key, $user, ['type' => 'event.published'])->id);
        DB::beginTransaction();
        $rolledBack = $service->enqueue('event.published:124:1:'.$user->id, $user, ['type' => 'event.published']);
        DB::rollBack();
        $this->assertNull($rolledBack->fresh());
        $this->actingAs($other)->get(route('notifications.index'))->assertOk()->assertDontSee($row->label());
        $this->patch(route('notifications.read', $row))->assertForbidden();
        $this->actingAs($user)->get(route('notifications.index'))->assertOk()->assertSee($row->label());
        $this->patch(route('notifications.read', $row))->assertRedirect();
        $this->assertNotNull($row->fresh()->read_at);
        $this->artisan('a4:send-volunteer-emails')->assertSuccessful();
        $this->assertSame(0, $row->fresh()->attempts);
    }

}
