<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\AuditLog;
use App\Models\AvailabilitySlot;
use App\Models\EventEntitlement;
use App\Models\User;
use App\Models\VolunteerProfile;
use App\Models\VolunteerSkill;
use App\Services\ApplicationService;
use App\Services\EventPublicationService;
use Illuminate\Validation\ValidationException;
use Tests\DatabaseTestCase;
use Tests\Support\A2Fixture;

class A3ApplicationTest extends DatabaseTestCase
{
    use A2Fixture;

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

    public function test_volunteer_can_create_draft_application_for_published_event(): void
    {
        $this->assessmentReady();
        [$owner, $event, $position] = $this->fixture();
        $event->forceFill(['status' => 'approved'])->save();
        app(EventPublicationService::class)->publish($event, $owner);

        $volunteer = $this->createCompleteVolunteer($event->cityRecord, $position->positionSkills->first()->skill);

        // Check catalog CTA rendering
        $this->actingAs($volunteer)
            ->get("/events/{$event->id}")
            ->assertOk()
            ->assertSee('Pilih posisi dan mulai lamaran');

        // Store draft via POST endpoint
        $response = $this->post("/volunteer/positions/{$position->id}/applications");
        $response->assertRedirect();

        $draft = Application::where('volunteer_id', $volunteer->id)->where('event_id', $event->id)->first();
        $this->assertNotNull($draft);
        $this->assertSame('draft', $draft->status);
        $this->assertSame($position->id, $draft->event_position_id);

        // Access detail draft
        $this->get("/volunteer/applications/{$draft->id}")
            ->assertOk()
            ->assertSee($event->title)
            ->assertSee($position->name)
            ->assertSee('Kirimkan Lamaran');
    }

    public function test_incomplete_profile_blocks_draft_creation(): void
    {
        $this->assessmentReady();
        [$owner, $event, $position] = $this->fixture();
        $event->forceFill(['status' => 'approved'])->save();
        app(EventPublicationService::class)->publish($event, $owner);

        // Incomplete profile volunteer (no skills or availability)
        $incompleteVolunteer = User::factory()->create([
            'role' => 'volunteer',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($incompleteVolunteer);
        $response = $this->postJson("/volunteer/positions/{$position->id}/applications");
        $response->assertStatus(422)
            ->assertJsonValidationErrors('profile');

        $this->assertDatabaseMissing('applications', ['volunteer_id' => $incompleteVolunteer->id]);
    }

    public function test_store_draft_is_single_per_volunteer_per_event(): void
    {
        $this->assessmentReady();
        [$owner, $event, $position] = $this->fixture();
        $event->forceFill(['status' => 'approved'])->save();
        app(EventPublicationService::class)->publish($event, $owner);

        $volunteer = $this->createCompleteVolunteer($event->cityRecord, $position->positionSkills->first()->skill);

        $service = app(ApplicationService::class);
        $draft1 = $service->storeDraft($volunteer, $position);
        $draft2 = $service->storeDraft($volunteer, $position);

        $this->assertSame($draft1->id, $draft2->id);
        $this->assertSame(1, Application::where('volunteer_id', $volunteer->id)->where('event_id', $event->id)->count());
    }

    public function test_successful_submit_updates_status_snapshot_and_audit(): void
    {
        $this->assessmentReady();
        [$owner, $event, $position] = $this->fixture();
        $event->forceFill(['status' => 'approved'])->save();
        app(EventPublicationService::class)->publish($event, $owner);

        $volunteer = $this->createCompleteVolunteer($event->cityRecord, $position->positionSkills->first()->skill);
        $service = app(ApplicationService::class);
        $draft = $service->storeDraft($volunteer, $position);

        $submitted = $service->submit($draft, $volunteer);

        $this->assertSame('submitted', $submitted->status);
        $this->assertNotNull($submitted->submitted_at);
        $this->assertNotNull($submitted->snapshot_json);
        $this->assertSame('match-v1.4', $submitted->snapshot_json['rule_version']);

        // Check entitlement counter incremented
        $entitlement = EventEntitlement::where('event_id', $event->id)->first();
        $this->assertSame(1, $entitlement->submitted_applications);

        // Check audit log recorded
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'application.submitted',
            'subject_id' => $submitted->id,
        ]);
    }

    public function test_submit_is_idempotent(): void
    {
        $this->assessmentReady();
        [$owner, $event, $position] = $this->fixture();
        $event->forceFill(['status' => 'approved'])->save();
        app(EventPublicationService::class)->publish($event, $owner);

        $volunteer = $this->createCompleteVolunteer($event->cityRecord, $position->positionSkills->first()->skill);
        $service = app(ApplicationService::class);
        $draft = $service->storeDraft($volunteer, $position);

        $first = $service->submit($draft, $volunteer);
        $second = $service->submit($first, $volunteer);

        $this->assertSame($first->id, $second->id);
        $this->assertSame('submitted', $second->status);

        // Counter remains 1
        $entitlement = EventEntitlement::where('event_id', $event->id)->first();
        $this->assertSame(1, $entitlement->submitted_applications);
    }

    public function test_package_quota_limit_rejects_subsequent_submissions(): void
    {
        $this->assessmentReady();
        [$owner, $event, $position, $package] = $this->fixture();
        $event->forceFill(['status' => 'approved'])->save();
        app(EventPublicationService::class)->publish($event, $owner);

        // Package max_applications is 1
        $volunteer1 = $this->createCompleteVolunteer($event->cityRecord, $position->positionSkills->first()->skill);
        $volunteer2 = $this->createCompleteVolunteer($event->cityRecord, $position->positionSkills->first()->skill);

        $service = app(ApplicationService::class);
        $draft1 = $service->storeDraft($volunteer1, $position);
        $draft2 = $service->storeDraft($volunteer2, $position);

        // First submit succeeds
        $service->submit($draft1, $volunteer1);

        // Second submit fails with quota limit error
        $this->expectException(ValidationException::class);
        $service->submit($draft2, $volunteer2);
    }

    public function test_submission_failure_rolls_back_transaction_and_entitlement_counter(): void
    {
        // Without calling $this->assessmentReady(), PositionSnapshotService will fail due to missing AssessmentReadiness binding
        [$owner, $event, $position] = $this->fixture();
        $event->forceFill(['status' => 'approved'])->save();

        // Manually create entitlement
        $entitlement = new EventEntitlement;
        $entitlement->forceFill([
            'event_id' => $event->id,
            'package_snapshot' => $event->package_snapshot,
            'max_positions' => 2,
            'max_applications' => 5,
            'max_registration_days' => 30,
            'submitted_applications' => 0,
            'activated_at' => now(),
        ])->save();

        $volunteer = $this->createCompleteVolunteer($event->cityRecord, $position->positionSkills->first()->skill);
        $service = app(ApplicationService::class);

        // Force draft creation bypassing event publication check for failure test
        $draft = Application::create([
            'event_id' => $event->id,
            'event_position_id' => $position->id,
            'volunteer_id' => $volunteer->id,
            'status' => 'draft',
            'revision' => 0,
        ]);

        $beforeAuditCount = AuditLog::count();

        try {
            $service->submit($draft, $volunteer);
            $this->fail('Expected ValidationException was not thrown');
        } catch (ValidationException $e) {
            // Assert exception caught
        }

        // Verify draft status unchanged
        $freshDraft = $draft->fresh();
        $this->assertSame('draft', $freshDraft->status);
        $this->assertNull($freshDraft->submitted_at);

        // Verify entitlement counter remains 0
        $this->assertSame(0, $entitlement->fresh()->submitted_applications);

        // Verify audit log rolled back
        $this->assertSame($beforeAuditCount, AuditLog::count());
    }

    public function test_other_volunteer_cannot_access_or_submit_others_application(): void
    {
        $this->assessmentReady();
        [$owner, $event, $position] = $this->fixture();
        $event->forceFill(['status' => 'approved'])->save();
        app(EventPublicationService::class)->publish($event, $owner);

        $volunteer1 = $this->createCompleteVolunteer($event->cityRecord, $position->positionSkills->first()->skill);
        $volunteer2 = $this->createCompleteVolunteer($event->cityRecord, $position->positionSkills->first()->skill);

        $service = app(ApplicationService::class);
        $draft1 = $service->storeDraft($volunteer1, $position);

        $this->actingAs($volunteer2);
        $this->get("/volunteer/applications/{$draft1->id}")->assertForbidden();
        $this->post("/volunteer/applications/{$draft1->id}/submit")->assertForbidden();
    }
}
