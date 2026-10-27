<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\AuditLog;
use App\Models\MatchResult;
use App\Models\ScreeningResult;
use App\Models\User;
use App\Services\AuditService;
use App\Services\MatchingEvaluationService;
use App\Services\ScreeningService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\DatabaseTestCase;
use Tests\Support\A2Fixture;
use Tests\Support\MatchingFixture;

class MatchingTest extends DatabaseTestCase
{
    use A2Fixture, MatchingFixture;

    private function application(?bool $screeningPassed = true): Application
    {
        [$owner, $event, $position] = $this->fixture();
        $volunteer = User::factory()->create(['role' => 'volunteer']);
        $snapshot = $this->matchingSnapshot();
        $snapshot['city_id'] = $event->city_id;
        $snapshot['profile']['city_id'] = $event->city_id;
        $application = Application::create([
            'volunteer_id' => $volunteer->id, 'event_id' => $event->id, 'event_position_id' => $position->id,
            'status' => 'submitted', 'submitted_at' => now(), 'snapshot_json' => $snapshot, 'revision' => 2,
        ]);
        if ($screeningPassed !== null) {
            ScreeningResult::create(['application_id' => $application->id, 'passed' => $screeningPassed,
                'checks_json' => [], 'rule_version' => 'match-v1.4', 'evaluated_at' => now()]);
        }

        return $application;
    }

    public function test_matching_persists_scores_and_advances_only_once(): void
    {
        $application = $this->application();
        $service = app(MatchingEvaluationService::class);
        $result = $service->evaluate($application);
        $this->assertEqualsWithDelta(80, $result->total_score, 1e-10);
        $this->assertSame('assessment_pending', $application->fresh()->status);
        $this->assertSame(3, $application->fresh()->revision);
        $application->volunteer->update(['name' => 'Profil berubah sesudah melamar']);
        $this->assertSame($result->id, $service->evaluate($application)->id);
        $this->assertSame(1, MatchResult::where('application_id', $application->id)->count());
        $this->assertSame(1, AuditLog::where('action', 'application.matched')->where('subject_id', $application->id)->count());
        $this->assertSame(3, $application->fresh()->revision);
    }

    public function test_matching_requires_a_passed_screening_result(): void
    {
        foreach ([null, false] as $passed) {
            $application = $this->application($passed);
            $this->assertNull(app(MatchingEvaluationService::class)->evaluate($application));
            $this->assertSame('submitted', $application->fresh()->status);
            $this->assertFalse(MatchResult::where('application_id', $application->id)->exists());
        }
    }

    public static function terminalCases(): array
    {
        return [['draft'], ['screening_failed'], ['withdrawn'], ['rejected'], ['cancelled'], ['accepted'], ['under_review']];
    }

    #[DataProvider('terminalCases')]
    public function test_late_evaluation_does_not_reactivate_other_states(string $status): void
    {
        $application = $this->application();
        $application->update(['status' => $status]);
        $this->assertNull(app(MatchingEvaluationService::class)->evaluate($application));
        $this->assertSame($status, $application->fresh()->status);
        $this->assertFalse(MatchResult::where('application_id', $application->id)->exists());
    }

    public function test_cancelled_event_prevents_matching(): void
    {
        $application = $this->application();
        $application->event->update(['lifecycle_status' => 'cancelled']);
        $this->assertNull(app(MatchingEvaluationService::class)->evaluate($application));
        $this->assertSame('submitted', $application->fresh()->status);
    }

    public function test_invalid_snapshot_does_not_advance_status_or_save_a_zero(): void
    {
        $application = $this->application();
        $snapshot = $application->snapshot_json;
        $snapshot['schedules'] = [];
        $application->update(['snapshot_json' => $snapshot]);
        try {
            app(MatchingEvaluationService::class)->evaluate($application);
            $this->fail('Invalid matching input must remain pending.');
        } catch (ValidationException) {
            $this->assertSame('submitted', $application->fresh()->status);
            $this->assertFalse(MatchResult::where('application_id', $application->id)->exists());
        }
    }

    public function test_failed_audit_rolls_back_scores_status_and_revision(): void
    {
        $application = $this->application();
        $this->mock(AuditService::class)->shouldReceive('record')->once()->andThrow(new \RuntimeException('audit-failed'));
        try {
            app(MatchingEvaluationService::class)->evaluate($application);
            $this->fail('Expected audit rollback.');
        } catch (\RuntimeException $e) {
            $this->assertSame('audit-failed', $e->getMessage());
        }
        $this->assertSame('submitted', $application->fresh()->status);
        $this->assertSame(2, $application->fresh()->revision);
        $this->assertFalse(MatchResult::where('application_id', $application->id)->exists());
    }

    public function test_retry_picks_previously_passed_applications_and_is_idempotent(): void
    {
        $application = $this->application();
        $this->artisan('a4:retry-matching')->assertSuccessful();
        $this->assertSame('assessment_pending', $application->fresh()->status);
        $this->artisan('a4:retry-matching')->assertSuccessful();
        $this->assertSame(1, MatchResult::where('application_id', $application->id)->count());
        $this->artisan('a4:retry-matching', ['--limit' => 0])->assertFailed();
    }

    public function test_screening_triggers_matching_only_after_outer_commit(): void
    {
        $application = $this->application(null);
        DB::transaction(function () use ($application) {
            $this->assertTrue(app(ScreeningService::class)->evaluate($application)['passed']);
            $this->assertSame('submitted', $application->fresh()->status);
            $this->assertFalse(MatchResult::where('application_id', $application->id)->exists());
        });
        $this->assertSame('assessment_pending', $application->fresh()->status);
        $this->assertEqualsWithDelta(80, $application->fresh()->matchResult->total_score, 1e-10);
    }

    public function test_outer_rollback_prevents_matching_from_running(): void
    {
        $application = $this->application(null);
        try {
            DB::transaction(function () use ($application) {
                app(ScreeningService::class)->evaluate($application);
                throw new \RuntimeException('outer-rollback');
            });
            $this->fail('Expected outer rollback.');
        } catch (\RuntimeException $e) {
            $this->assertSame('outer-rollback', $e->getMessage());
        }
        $this->assertFalse(MatchResult::where('application_id', $application->id)->exists());
        $this->assertFalse(ScreeningResult::where('application_id', $application->id)->exists());
        $this->assertSame('submitted', $application->fresh()->status);
    }

    public function test_matching_failure_preserves_passed_screening_for_controlled_retry(): void
    {
        $application = $this->application(null);
        $this->mock(MatchingEvaluationService::class)->shouldReceive('evaluate')->once()->andThrow(new \RuntimeException('matching-unavailable'));
        try {
            app(ScreeningService::class)->evaluate($application);
            $this->fail('Expected retryable matching failure.');
        } catch (\RuntimeException $e) {
            $this->assertSame('matching-unavailable', $e->getMessage());
        }
        $this->assertTrue($application->fresh()->screeningResult->passed);
        $this->assertSame('submitted', $application->fresh()->status);
        $this->assertFalse(MatchResult::where('application_id', $application->id)->exists());
    }

    public function test_scores_are_visible_only_to_the_event_owner_and_not_the_volunteer(): void
    {
        $application = $this->application();
        app(MatchingEvaluationService::class)->evaluate($application);
        $owner = $application->event->organizer;
        $this->actingAs($owner)->get(route('organizer.applications.show', $application))
            ->assertOk()->assertSee('Kecocokan kandidat')->assertSee('80,00');
        $this->get(route('organizer.applications.index', $application->event))
            ->assertOk()->assertSee('Match Score')->assertSee('80,00');

        $this->actingAs($application->volunteer)->get(route('volunteer.applications.show', $application))
            ->assertOk()->assertSee('Menunggu assessment')
            ->assertSee('Pencocokan selesai. Lamaranmu siap lanjut ke assessment.')
            ->assertDontSee('Kecocokan kandidat')->assertDontSee('Match Score')->assertDontSee('80,00');
        $this->get(route('organizer.applications.show', $application))->assertForbidden();
        $otherOrganizer = User::factory()->create(['role' => 'organizer', 'organizer_status' => 'active']);
        $this->actingAs($otherOrganizer)->get(route('organizer.applications.show', $application))->assertForbidden();
        $otherVolunteer = User::factory()->create(['role' => 'volunteer']);
        $this->actingAs($otherVolunteer)->get(route('volunteer.applications.show', $application))->assertForbidden();
        $owner->update(['is_active' => false]);
        $this->actingAs($owner)->get(route('organizer.applications.show', $application))->assertForbidden();
    }
}
