<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\AuditLog;
use App\Models\Event;
use App\Models\User;
use App\Services\EventPublicationService;
use App\Services\EventService;
use App\Services\PositionSnapshotService;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\DatabaseTestCase;
use Tests\Support\A2Fixture;

class RecruitmentScheduleTest extends DatabaseTestCase
{
    use A2Fixture;

    private function input(Event $event): array
    {
        $input = $event->only(['title', 'description', 'location', 'city_id', 'category_id']);
        $input['package_id'] = $event->package_snapshot['id'];
        foreach (['starts_at', 'ends_at', 'registration_opens_at', 'registration_deadline', 'assessment_deadline', 'decision_deadline'] as $field) {
            $input[$field] = $event->$field?->setTimezone('Asia/Jakarta')->format('Y-m-d\TH:i');
        }

        return $input;
    }

    public function test_create_and_edit_endpoints_store_wib_as_utc_and_render_both_forms(): void
    {
        [$owner, $event] = $this->fixture();
        $input = $this->input($event);
        $input['title'] = 'Jadwal rekrutmen baru';
        $this->actingAs($owner)->post(route('organizer.events.store'), $input)->assertRedirect();
        $created = Event::where('title', $input['title'])->firstOrFail();
        $this->assertSame('19:00', $created->assessment_deadline->format('H:i'));
        $this->assertSame('23:00', $created->decision_deadline->format('H:i'));
        $this->assertTrue($created->assessment_deadline->greaterThan($created->registration_deadline));
        $this->assertTrue($created->decision_deadline->lessThan($created->starts_at));
        foreach (['organizer.events.index', 'organizer.events.create'] as $route) {
            $this->get(route($route))->assertOk()->assertSee('name="assessment_deadline"', false)
                ->assertSee('name="decision_deadline"', false)->assertSee('WIB');
        }
        $this->get(route('organizer.events.edit', $created))->assertOk()
            ->assertSee('value="'.$input['assessment_deadline'].'"', false);
        $input['assessment_deadline'] = $created->assessment_deadline->setTimezone('Asia/Jakarta')->addHour()->format('Y-m-d\TH:i');
        $this->put(route('organizer.events.update', $created), $input)->assertRedirect();
        $this->assertSame($input['assessment_deadline'], $created->fresh()->assessment_deadline->setTimezone('Asia/Jakarta')->format('Y-m-d\TH:i'));
        $this->get(route('organizer.events.show', $created))->assertOk()
            ->assertSee('Tenggat assessment (WIB)')->assertSee('Batas akhir keputusan EO (WIB)');
    }

    public static function invalidCases(): array
    {
        return [['assessment_equal_registration', 'assessment_deadline'], ['assessment_before_registration', 'assessment_deadline'],
            ['decision_equal_assessment', 'decision_deadline'], ['decision_equal_start', 'decision_deadline'],
            ['decision_after_start', 'decision_deadline'], ['assessment_missing', 'assessment_deadline'],
            ['decision_missing', 'decision_deadline'], ['invalid_format', 'assessment_deadline']];
    }

    #[DataProvider('invalidCases')]
    public function test_invalid_schedule_rejects_without_changing_event_or_audit(string $case, string $field): void
    {
        [$owner, $event] = $this->fixture();
        $input = $this->input($event);
        switch ($case) {
            case 'assessment_equal_registration': $input['assessment_deadline'] = $input['registration_deadline'];
                break;
            case 'assessment_before_registration': $input['assessment_deadline'] = $event->registration_deadline->setTimezone('Asia/Jakarta')->subMinute()->format('Y-m-d\TH:i');
                break;
            case 'decision_equal_assessment': $input['decision_deadline'] = $input['assessment_deadline'];
                break;
            case 'decision_equal_start': $input['decision_deadline'] = $input['starts_at'];
                break;
            case 'decision_after_start': $input['decision_deadline'] = $event->starts_at->setTimezone('Asia/Jakarta')->addMinute()->format('Y-m-d\TH:i');
                break;
            case 'assessment_missing': $input['assessment_deadline'] = null;
                break;
            case 'decision_missing': $input['decision_deadline'] = null;
                break;
            case 'invalid_format': $input['assessment_deadline'] = 'tomorrow';
                break;
        }
        $before = $event->getRawOriginal();
        $auditCount = AuditLog::count();
        $this->actingAs($owner)->putJson(route('organizer.events.update', $event), $input)
            ->assertUnprocessable()->assertJsonValidationErrors($field);
        $this->assertSame($before, $event->fresh()->getRawOriginal());
        $this->assertSame($auditCount, AuditLog::count());
    }

    public function test_incomplete_draft_can_be_saved_but_not_submitted(): void
    {
        [$owner, $event] = $this->fixture();
        $this->assessmentReady();
        $input = $this->input($event);
        $input['assessment_deadline'] = '';
        $input['decision_deadline'] = '';
        $saved = app(EventService::class)->save($owner, $input, $event);
        $this->assertNull($saved->assessment_deadline);
        $this->assertNull($saved->decision_deadline);
        $beforeAudit = AuditLog::count();
        $this->actingAs($owner)->postJson(route('organizer.events.submit', $saved))
            ->assertUnprocessable()->assertJsonValidationErrors('assessment_deadline');
        $this->assertSame('draft', $saved->fresh()->status);
        $this->assertSame($beforeAudit, AuditLog::count());
    }

    public function test_existing_save_callers_can_omit_fields_without_losing_configured_deadlines(): void
    {
        [$owner, $event] = $this->fixture();
        $input = $this->input($event);
        unset($input['assessment_deadline'], $input['decision_deadline']);
        $saved = app(EventService::class)->save($owner, $input, $event);
        $this->assertTrue($saved->assessment_deadline->equalTo($event->assessment_deadline));
        $this->assertTrue($saved->decision_deadline->equalTo($event->decision_deadline));
    }

    public function test_new_snapshot_has_deadlines_and_old_snapshot_is_not_backfilled(): void
    {
        [$owner, $event, $position] = $this->fixture();
        $this->assessmentReady();
        $old = Application::create(['event_id' => $event->id, 'event_position_id' => $position->id,
            'volunteer_id' => User::factory()->create(['role' => 'volunteer'])->id, 'status' => 'submitted',
            'snapshot_json' => ['legacy' => true], 'submitted_at' => now(), 'revision' => 1]);
        $snapshot = app(PositionSnapshotService::class)->build($position);
        $this->assertSame($event->assessment_deadline->toIso8601String(), $snapshot['assessment_deadline']);
        $this->assertSame($event->decision_deadline->toIso8601String(), $snapshot['decision_deadline']);
        $this->assertSame(['legacy' => true], $old->fresh()->snapshot_json);
    }

    public function test_other_owner_and_other_roles_cannot_change_dates(): void
    {
        [$owner, $event] = $this->fixture();
        $input = $this->input($event);
        $input['assessment_deadline'] = $event->assessment_deadline->setTimezone('Asia/Jakarta')->addHour()->format('Y-m-d\TH:i');
        foreach (['organizer', 'volunteer', 'admin'] as $role) {
            $actor = User::factory()->create(['role' => $role, 'organizer_status' => 'active']);
            $this->actingAs($actor)->putJson(route('organizer.events.update', $event), $input)->assertForbidden();
        }
        $this->assertTrue($event->fresh()->assessment_deadline->equalTo($event->assessment_deadline));
    }

    public function test_published_legacy_event_is_not_unlocked_or_given_guessed_dates(): void
    {
        $this->assessmentReady();
        [$owner, $event, $position] = $this->fixture();
        $event->forceFill(['status' => 'approved', 'assessment_deadline' => null, 'decision_deadline' => null])->save();
        $published = app(EventPublicationService::class)->publish($event, $owner);
        $input = $this->input($published);
        $input['assessment_deadline'] = $published->registration_deadline->setTimezone('Asia/Jakarta')->addHour()->format('Y-m-d\TH:i');
        $input['decision_deadline'] = $published->starts_at->setTimezone('Asia/Jakarta')->subHour()->format('Y-m-d\TH:i');
        $this->actingAs($owner)->putJson(route('organizer.events.update', $published), $input)->assertUnprocessable();
        $this->assertNull($published->fresh()->assessment_deadline);
        $this->assertNull($published->fresh()->decision_deadline);
        $this->get(route('organizer.events.show', $published))->assertOk()->assertSee('Belum diisi');
        $snapshot = app(PositionSnapshotService::class)->build($position);
        $this->assertNull($snapshot['assessment_deadline']);
        $this->assertNull($snapshot['decision_deadline']);
        $this->assertSame($published->published_at->toIso8601String(), app(EventPublicationService::class)->publish($published, $owner)->published_at->toIso8601String());
    }
}
