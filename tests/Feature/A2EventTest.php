<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\EventEntitlement;
use App\Models\Order;
use App\Models\User;
use App\Services\EntitlementService;
use App\Services\EventCancellationService;
use App\Services\EventPublicationService;
use App\Services\EventService;
use App\Services\PositionSnapshotService;
use Illuminate\Validation\ValidationException;
use Tests\DatabaseTestCase;
use Tests\Support\A2Fixture;

class A2EventTest extends DatabaseTestCase
{
    use A2Fixture;

    public function test_draft_round_trip_is_owned_utc_and_renders_all_edit_pages(): void
    {
        [$owner,$event,$position] = $this->fixture();
        $this->assertSame('02:00', $event->starts_at->format('H:i'));
        $this->assertSame('unpublished', $event->publication_status);
        $this->assertSame(1, $position->schedules()->count());
        $this->actingAs($owner);
        foreach (['/organizer/events', '/organizer/events/create', "/organizer/events/$event->id", "/organizer/events/$event->id/edit", "/organizer/events/$event->id/package", "/organizer/events/$event->id/positions/create", "/organizer/events/$event->id/positions/$position->id/edit"] as $path) {
            $this->get($path)->assertOk();
        }
        $other = User::factory()->create(['role' => 'organizer', 'organizer_status' => 'active']);
        $this->actingAs($other)->get("/organizer/events/$event->id")->assertForbidden();
        $this->put("/organizer/events/$event->id", ['organizer_id' => $other->id])->assertForbidden();
        $this->post("/organizer/events/$event->id/orders")->assertForbidden();
        $this->actingAs(User::factory()->create())->get('/organizer/events')->assertForbidden();
        $this->get('/admin/packages')->assertForbidden();
    }

    public function test_nested_position_cannot_be_changed_through_another_event(): void
    {
        [$owner,$event] = $this->fixture();
        [,,$otherPosition] = $this->fixture();
        $this->actingAs($owner)->get("/organizer/events/$event->id/positions/$otherPosition->id/edit")->assertNotFound();
        $this->delete("/organizer/events/$event->id/positions/$otherPosition->id")->assertNotFound();
        $this->assertDatabaseHas('event_positions', ['id' => $otherPosition->id]);
    }

    public function test_missing_assessment_blocks_submission_without_audit_or_state_change(): void
    {
        [$owner,$event] = $this->fixture();
        $before = AuditLog::count();
        $this->actingAs($owner)->postJson("/organizer/events/$event->id/submit")->assertUnprocessable()->assertJsonValidationErrors('assessment');
        $this->assertSame('draft', $event->fresh()->status);
        $this->assertSame($before, AuditLog::count());
        $event->forceFill(['status' => 'approved'])->save();
        $this->postJson("/organizer/events/$event->id/publish")->assertUnprocessable();
        $this->assertSame(0, $event->entitlement()->count());
    }

    public function test_free_publication_once_and_snapshot_contract(): void
    {
        $this->assessmentReady();
        [$owner,$event,$position] = $this->fixture();
        app(EventService::class)->submit($event, $owner);
        $this->assertSame('pending', $event->fresh()->status);
        $event->forceFill(['status' => 'approved'])->save();
        $service = app(EventPublicationService::class);
        $first = $service->publish($event, $owner);
        $second = $service->publish($event, $owner);
        $this->assertTrue($first->published_at->equalTo($second->published_at));
        $this->assertSame(1, EventEntitlement::where('event_id', $event->id)->count());
        $this->assertSame(0, Order::where('event_id', $event->id)->count());
        $snapshot = app(PositionSnapshotService::class)->build($position);
        $this->assertSame('match-v1.4', $snapshot['rule_version']);
        $this->assertSame(1, $snapshot['assessment_version']);
        $this->assertTrue($snapshot['skills'][0]['is_required']);
        $this->assertStringContainsString('+00:00', $snapshot['schedules'][0]['start']);
        $this->actingAs($owner)->get("/organizer/events/$event->id/edit")->assertRedirect();
        $this->postJson("/organizer/events/$event->id/positions", [])->assertUnprocessable();
    }

    public function test_catalog_filters_and_hidden_event_statuses(): void
    {
        $this->assessmentReady();
        [$owner,$event] = $this->fixture();
        $this->get("/events/$event->id")->assertNotFound();
        $event->forceFill(['status' => 'approved'])->save();
        $this->get("/events/$event->id")->assertNotFound();
        app(EventPublicationService::class)->publish($event, $owner);
        $this->get('/events?search='.urlencode($event->title))->assertOk()->assertSee($event->title);
        $this->get('/events?search=nomatchunique')->assertDontSee($event->title);
        $this->get('/events?city_id=999999')->assertDontSee($event->title);
        $this->get('/events?date='.$event->starts_at->setTimezone('Asia/Jakarta')->format('Y-m-d'))->assertSee($event->title);
        $this->get("/events/$event->id")->assertOk()->assertSee('WIB')->assertSee('Pengajuan lamaran belum tersedia');
        $owner->update(['is_active' => false]);
        $this->get("/events/$event->id")->assertNotFound();
        $this->get('/events')->assertDontSee($event->title);
    }

    public function test_limits_are_transactional_and_package_changes_do_not_change_snapshot(): void
    {
        $this->assessmentReady();
        [$owner,$event,,$package] = $this->fixture();
        $package->update(['price' => 999, 'max_applications' => 100]);
        $this->assertSame(0, $event->fresh()->package_snapshot['price']);
        $event->forceFill(['status' => 'approved'])->save();
        app(EventPublicationService::class)->publish($event, $owner);
        app(EntitlementService::class)->consumeApplication($event);
        $this->expectException(ValidationException::class);
        app(EntitlementService::class)->consumeApplication($event);
    }

    public function test_pending_inactive_and_unverified_organizers_cannot_manage_events(): void
    {
        foreach (['pending', 'inactive'] as $status) {
            $u = User::factory()->create(['role' => 'organizer', 'organizer_status' => $status]);
            $this->actingAs($u)->get('/organizer/events')->assertRedirect(route('organizer.profile.pending'));
        }
        $u = User::factory()->create(['role' => 'organizer', 'organizer_status' => 'active', 'email_verified_at' => null]);
        $this->actingAs($u)->get('/organizer/events')->assertRedirect(route('verification.notice'));
        $u->update(['is_active' => false]);
        $this->get('/organizer/events')->assertForbidden();
    }

    public function test_invalid_schedule_and_duplicate_skill_are_rejected(): void
    {
        [$owner,$event,$position] = $this->fixture();
        $payload = ['name' => 'Invalid', 'quota' => 0, 'required_full_availability' => 0, 'required_same_city' => 0, 'skills' => [['skill_id' => 999999, 'minimum_level' => 'expert', 'is_required' => 1]], 'schedules' => []];
        $this->actingAs($owner)->postJson("/organizer/events/$event->id/positions", $payload)->assertUnprocessable()->assertJsonValidationErrors(['quota', 'skills.0.skill_id', 'schedules']);
        $this->assertSame(1, $event->positions()->count());
    }

    public function test_cancellation_waits_for_a3_without_mutating_event(): void
    {
        [$owner,$event] = $this->fixture();
        $this->actingAs($owner)->postJson("/organizer/events/$event->id/cancel", ['reason' => 'Kegiatan dibatalkan'])->assertUnprocessable()->assertJsonValidationErrors('cancellation');
        $this->assertSame('upcoming', $event->fresh()->lifecycle_status);
    }

    public function test_admin_package_pages_and_server_side_package_validation(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get('/admin/packages')->assertOk();
        $this->get('/admin/packages/create')->assertOk();
        $this->get('/admin/orders')->assertOk();
        $this->postJson('/admin/packages', ['name' => 'Invalid', 'price' => -1])->assertUnprocessable();
        $this->post('/admin/packages', ['name' => 'Package '.uniqid(), 'price' => 0, 'max_positions' => 1, 'max_applications' => 1, 'max_registration_days' => 1, 'is_active' => 1])->assertRedirect('/admin/packages');
    }

    public function test_moderation_uses_existing_admin_routes_and_free_activation(): void
    {
        $this->assessmentReady();
        [$owner,$event] = $this->fixture();
        app(EventService::class)->submit($event, $owner);
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get("/admin/event-verification/$event->id")->assertOk()->assertSee('A2 test skill');
        $this->patch("/admin/event-verification/$event->id/approve")->assertRedirect();
        $this->assertSame('published', $event->fresh()->publication_status);
        $this->assertSame(1, $event->entitlement()->count());
        $this->patch("/admin/event-verification/$event->id/reject", ['verification_note' => 'Stale review'])->assertConflict();
        $this->assertSame('approved', $event->fresh()->status);
    }

    public function test_event_request_ignores_forged_owner_status_and_price(): void
    {
        [$owner,$event] = $this->fixture();
        $data = ['title' => 'Round trip edited', 'description' => 'Edited description', 'location' => 'Edited location', 'city_id' => $event->city_id, 'category_id' => $event->category_id,
            'starts_at' => $event->starts_at->setTimezone('Asia/Jakarta')->format('Y-m-d\TH:i'), 'ends_at' => $event->ends_at->setTimezone('Asia/Jakarta')->format('Y-m-d\TH:i'),
            'registration_opens_at' => $event->registration_opens_at->setTimezone('Asia/Jakarta')->format('Y-m-d\TH:i'), 'registration_deadline' => $event->registration_deadline->setTimezone('Asia/Jakarta')->format('Y-m-d\TH:i'),
            'organizer_id' => 999999, 'status' => 'approved', 'publication_status' => 'published', 'package_snapshot' => ['price' => 0]];
        $this->actingAs($owner)->put("/organizer/events/$event->id", $data)->assertRedirect();
        $saved = $event->fresh();
        $this->assertSame($owner->id, $saved->organizer_id);
        $this->assertSame('draft', $saved->status);
        $this->assertSame('unpublished', $saved->publication_status);
        $this->get("/organizer/events/$event->id")->assertSee('Round trip edited');
        $this->assertSame($event->package_snapshot, $saved->package_snapshot);
    }

    public function test_schedule_outside_range_and_duplicate_skill_are_atomic(): void
    {
        [$owner,$event,$position] = $this->fixture();
        $skill = $position->positionSkills()->first();
        $data = ['name' => 'Should not save', 'quota' => 1, 'required_full_availability' => 0, 'required_same_city' => 0,
            'skills' => [['skill_id' => $skill->skill_id, 'minimum_level' => 'expert', 'is_required' => 1]],
            'schedules' => [['starts_at' => $event->starts_at->subDay()->format('Y-m-d').'T09:00', 'ends_at' => $event->starts_at->subDay()->format('Y-m-d').'T10:00']]];
        $this->actingAs($owner)->putJson("/organizer/events/$event->id/positions/$position->id", $data)->assertUnprocessable()->assertJsonValidationErrors('schedules');
        $data['skills'][] = $data['skills'][0];
        $this->putJson("/organizer/events/$event->id/positions/$position->id", $data)->assertUnprocessable()->assertJsonValidationErrors('skills.0.skill_id');
        $this->assertSame('Petugas', $position->fresh()->name);
        $this->assertSame('beginner', $skill->fresh()->minimum_level);
    }

    public function test_cancellation_integration_is_atomic_and_not_replayed(): void
    {
        [$owner,$event] = $this->fixture();
        $collaborator = \Mockery::mock();
        $collaborator->shouldReceive('cancelApplications')->once();
        $this->app->instance(EventCancellationService::class, $collaborator);
        app(EventPublicationService::class)->cancel($event, $owner, 'Kegiatan dibatalkan');
        app(EventPublicationService::class)->cancel($event, $owner, 'Kegiatan dibatalkan');
        $this->assertSame('cancelled', $event->fresh()->lifecycle_status);
        $this->assertSame('unpublished', $event->fresh()->publication_status);
        $this->assertSame(1, AuditLog::where('subject_id', $event->id)->where('action', 'event.cancelled')->count());
    }

    public function test_intended_catalog_return_is_local_and_still_public(): void
    {
        $this->assessmentReady();
        [$owner,$event] = $this->fixture();
        $event->forceFill(['status' => 'approved'])->save();
        app(EventPublicationService::class)->publish($event, $owner);
        $volunteer = User::factory()->create();
        $this->get("/events/$event->id/join")->assertRedirect('/login');
        $this->post('/login', ['email' => $volunteer->email, 'password' => 'password'])->assertRedirect('/events/'.$event->id);
        $this->post('/logout');
        $event->forceFill(['publication_status' => 'suspended'])->save();
        $this->withSession(['url.intended' => '/events/'.$event->id.'/join'])->post('/login', ['email' => $volunteer->email, 'password' => 'password'])->assertRedirect('/volunteer/aktivitas');
    }

    public function test_only_empty_unsubmitted_drafts_can_be_deleted(): void
    {
        [$owner,$event,$position] = $this->fixture();
        $this->actingAs($owner)->deleteJson('/organizer/events/'.$event->id)->assertUnprocessable();
        $this->get('/organizer/events/'.$event->id.'/positions')->assertRedirect('/organizer/events/'.$event->id);
        $this->get('/organizer/events/'.$event->id.'/positions/'.$position->id)->assertRedirect('/organizer/events/'.$event->id.'#position-'.$position->id);
        $this->delete('/organizer/events/'.$event->id.'/positions/'.$position->id)->assertRedirect();
        $this->delete('/organizer/events/'.$event->id)->assertRedirect('/organizer/events');
        $this->assertDatabaseMissing('events', ['id' => $event->id]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'event.draft_deleted', 'subject_id' => $event->id]);
    }

    public function test_published_text_correction_is_audited_without_changing_rules(): void
    {
        $this->assessmentReady();
        [$owner,$event,$position] = $this->fixture();
        $event->forceFill(['status' => 'approved'])->save();
        app(EventPublicationService::class)->publish($event, $owner);
        $originalStart = $event->fresh()->starts_at;
        $this->actingAs($owner)->get('/organizer/events/'.$event->id.'/text')->assertOk();
        $this->patch('/organizer/events/'.$event->id.'/text', ['title' => 'Koreksi judul', 'description' => 'Koreksi ejaan'])->assertRedirect();
        $this->assertSame('Koreksi judul', $event->fresh()->title);
        $this->assertTrue($originalStart->equalTo($event->fresh()->starts_at));
        $this->assertDatabaseHas('audit_logs', ['action' => 'event.text_corrected', 'subject_id' => $event->id]);
        $this->patchJson('/organizer/events/'.$event->id.'/text', ['title' => 'Tidak disimpan', 'description' => 'Koreksi', 'starts_at' => '2030-01-01T00:00'])->assertUnprocessable();
        $this->assertSame('Koreksi judul', $event->fresh()->title);
        $this->assertSame(1, $position->schedules()->count());
    }
}
