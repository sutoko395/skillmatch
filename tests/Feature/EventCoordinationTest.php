<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\EventCoordination;
use App\Models\NotificationOutbox;
use App\Models\User;
use App\Services\EventCoordinationService;
use App\Services\NotificationService;
use App\Services\SelectionService;
use Tests\DatabaseTestCase;
use Tests\Support\A3ExecutionFixture;

class EventCoordinationTest extends DatabaseTestCase
{
    use A3ExecutionFixture;

    private function input(int $revision = 0): array
    {
        return ['revision' => $revision, 'group_url' => 'https://example.com/invite/private-test-group',
            'pic_name' => 'PIC Pengujian', 'pic_contact' => 'pic-test@example.com'];
    }

    public function test_published_owner_can_update_without_changing_recruitment_payment_or_snapshot(): void
    {
        $application = $this->reviewApplication();
        $event = $application->event;
        $before = $event->getAttributes();
        $snapshot = $application->snapshot_json;
        $this->actingAs($event->organizer)->get(route('organizer.events.show', $event))->assertOk()->assertSee('Koordinasi Peserta');
        $this->put(route('organizer.events.coordination.update', $event), $this->input())->assertRedirect(route('organizer.events.show', $event));
        $this->assertSame($before, $event->fresh()->getAttributes());
        $this->assertSame($snapshot, $application->fresh()->snapshot_json);
        $this->assertDatabaseHas('event_coordinations', ['event_id' => $event->id, 'revision' => 1]);
        $this->assertSame(0, NotificationOutbox::where('type', 'event.coordination_updated')->where('application_id', $application->id)->count());
        $this->actingAs($application->volunteer)->get(route('volunteer.applications.show', $application))->assertOk()->assertDontSee($this->input()['group_url'])->assertDontSee($this->input()['pic_contact']);
        app(SelectionService::class)->accept($application, $event->organizer);
        $this->get(route('volunteer.applications.show', $application))->assertOk()->assertSee('Gabung grup peserta')->assertSee($this->input()['group_url'])->assertSee($this->input()['pic_contact'])->assertSee('noopener noreferrer');
        $this->get(route('events.show', $event))->assertOk()->assertDontSee($this->input()['group_url'])->assertDontSee($this->input()['pic_contact']);
        $row = EventCoordination::where('event_id', $event->id)->firstOrFail();
        $this->assertArrayNotHasKey('group_url', $row->toArray());
        $this->assertArrayNotHasKey('pic_contact', $row->toArray());
    }

    public function test_only_owner_may_edit_and_only_accepted_owner_may_read_private_details(): void
    {
        $application = $this->reviewApplication();
        $event = $application->event;
        app(EventCoordinationService::class)->update($event, $event->organizer, $this->input());
        foreach ([['role' => 'organizer', 'organizer_status' => 'active'], ['role' => 'admin'], ['role' => 'volunteer']] as $attributes) {
            $user = User::factory()->create($attributes);
            $this->actingAs($user)->putJson(route('organizer.events.coordination.update', $event), $this->input(1))->assertForbidden();
            $this->assertNull(app(EventCoordinationService::class)->forParticipant($application, $user));
        }
        $this->actingAs(User::factory()->create(['role' => 'volunteer']))->get(route('volunteer.applications.show', $application))->assertForbidden();
        $staleApplication = $application->fresh();
        foreach (['draft', 'submitted', 'assessment_pending', 'under_review', 'rejected', 'screening_failed', 'withdrawn', 'cancelled'] as $status) {
            $application->forceFill(['status' => $status])->save();
            $this->assertNull(app(EventCoordinationService::class)->forParticipant($staleApplication, $application->volunteer));
        }
        $application->forceFill(['status' => 'accepted'])->save();
        $this->assertSame($this->input()['group_url'], app(EventCoordinationService::class)->forParticipant($staleApplication, $application->volunteer)['group_url']);
        $event->forceFill(['lifecycle_status' => 'cancelled'])->save();
        $this->assertNull(app(EventCoordinationService::class)->forParticipant($staleApplication, $application->volunteer));
        $this->actingAs($application->volunteer)->get(route('volunteer.applications.show', $application))->assertOk()->assertDontSee($this->input()['group_url']);
    }

    public function test_updates_notify_accepted_only_and_retries_are_idempotent_with_private_audit(): void
    {
        $application = $this->reviewApplication();
        app(SelectionService::class)->accept($application, $application->event->organizer);
        $pending = $this->secondCandidate($application);
        $route = route('organizer.events.coordination.update', $application->event);
        $this->actingAs($application->event->organizer)->put($route, $this->input())->assertRedirect();
        $this->put($route, $this->input())->assertRedirect();
        $notifications = NotificationOutbox::where('type', 'event.coordination_updated')->where('application_id', $application->id)->get();
        $this->assertCount(1, $notifications);
        $this->assertSame(0, NotificationOutbox::where('type', 'event.coordination_updated')->where('application_id', $pending->id)->count());
        $this->assertStringNotContainsString('private-test-group', $notifications->toJson());
        $this->assertStringNotContainsString('pic-test@', $notifications->toJson());
        $audit = AuditLog::where('action', 'event.coordination_updated')->where('subject_id', $application->event_id)->sole();
        $this->assertEqualsCanonicalizing(['group_url', 'pic_name', 'pic_contact'], $audit->after['changed_fields']);
        $this->assertStringNotContainsString('private-test-group', $audit->toJson());
        $this->putJson($route, array_replace($this->input(), ['pic_name' => 'Stale overwrite']))->assertUnprocessable()->assertJsonValidationErrors('revision');
        $this->put($route, ['revision' => 1, 'group_url' => null, 'pic_name' => null, 'pic_contact' => null])->assertRedirect();
        $this->assertSame(2, NotificationOutbox::where('type', 'event.coordination_updated')->where('application_id', $application->id)->count());
        $this->actingAs($application->volunteer)->get(route('volunteer.applications.show', $application))->assertOk()->assertSee('Organizer belum membagikan tautan grup')->assertDontSee('Gabung grup peserta');
    }

    public function test_unsafe_links_and_terminal_edits_are_rejected_and_empty_coordination_remains_optional(): void
    {
        $application = $this->reviewApplication();
        $event = $application->event;
        $route = route('organizer.events.coordination.update', $event);
        $this->actingAs($event->organizer);
        foreach (['javascript:alert(1)', '//example.com/group', 'http://example.com/group', 'https://user:pass@example.com/group'] as $url) {
            $this->putJson($route, array_replace($this->input(), ['group_url' => $url]))->assertUnprocessable()->assertJsonValidationErrors('group_url');
        }
        $this->put($route, ['revision' => 0, 'group_url' => '', 'pic_name' => '', 'pic_contact' => ''])->assertRedirect();
        $this->assertDatabaseMissing('event_coordinations', ['event_id' => $event->id]);
        app(SelectionService::class)->accept($application, $event->organizer);
        $this->actingAs($application->volunteer)->get(route('volunteer.applications.show', $application))->assertOk()->assertSee('Organizer belum membagikan tautan grup');
        foreach (['completed', 'cancelled'] as $status) {
            $event->forceFill(['lifecycle_status' => $status])->save();
            $this->actingAs($event->organizer)->putJson($route, $this->input())->assertConflict();
        }
    }

    public function test_notification_failure_rolls_back_coordination_and_audit(): void
    {
        $application = $this->reviewApplication();
        app(SelectionService::class)->accept($application, $application->event->organizer);
        $this->mock(NotificationService::class)->shouldReceive('enqueue')->once()->andThrow(new \RuntimeException('Synthetic notification failure'));
        try {
            app(EventCoordinationService::class)->update($application->event, $application->event->organizer, $this->input());
            $this->fail('Expected atomic rollback.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Synthetic notification failure', $exception->getMessage());
        }
        $this->assertDatabaseMissing('event_coordinations', ['event_id' => $application->event_id]);
        $this->assertSame(0, AuditLog::where('action', 'event.coordination_updated')->where('subject_id', $application->event_id)->count());
    }
}
