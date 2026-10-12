<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\AuditLog;
use App\Models\ScreeningResult;
use App\Models\User;
use App\Services\AuditService;
use App\Services\ScreeningService;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\DatabaseTestCase;
use Tests\Support\A2Fixture;

class ScreeningTest extends DatabaseTestCase
{
    use A2Fixture;

    private function application(bool $passed = true): Application
    {
        [$owner, $event, $position] = $this->fixture();
        $volunteer = User::factory()->create(['role' => 'volunteer']);

        return Application::create([
            'volunteer_id' => $volunteer->id, 'event_id' => $event->id, 'event_position_id' => $position->id,
            'status' => 'submitted', 'submitted_at' => now(), 'revision' => 1,
            'snapshot_json' => [
                'rule_version' => 'match-v1.4', 'city_id' => $event->city_id,
                'profile_eligibility' => ['complete' => true, 'missing_fields' => []],
                'profile' => ['city_id' => $event->city_id,
                    'skills' => [['id' => 1, 'level' => $passed ? 'intermediate' : 'beginner']],
                    'availability_slots' => [['starts_at' => '2026-10-20T01:00:00+00:00', 'ends_at' => '2026-10-20T05:00:00+00:00']]],
                'skills' => [['id' => 1, 'minimum_level' => 'intermediate', 'is_required' => true]],
                'schedules' => [['start' => '2026-10-20T01:00:00+00:00', 'end' => '2026-10-20T05:00:00+00:00']],
                'required_full_availability' => true, 'required_same_city' => true,
                'requirements' => [], 'documents' => [],
            ],
        ]);
    }

    public function test_failed_screening_is_persisted_once_and_is_not_recomputed_from_current_profile(): void
    {
        $application = $this->application(false);
        $service = app(ScreeningService::class);
        $result = $service->evaluate($application);
        $this->assertFalse($result['passed']);
        $this->assertSame('screening_failed', $application->fresh()->status);
        $this->assertSame(2, $application->fresh()->revision);
        $application->volunteer->update(['name' => 'Profil diubah']);
        $this->assertSame($result, $service->evaluate($application));
        $this->assertSame(1, ScreeningResult::where('application_id', $application->id)->count());
        $this->assertSame(1, AuditLog::where('action', 'application.screened')->where('subject_id', $application->id)->count());
    }

    public function test_passed_screening_waits_for_matching_and_cannot_open_assessment(): void
    {
        $application = $this->application();
        $service = app(ScreeningService::class);
        $this->assertTrue($service->evaluate($application)['passed']);
        $this->assertSame('submitted', $application->fresh()->status);
        $service->evaluate($application->fresh());
        $this->assertSame(2, $application->fresh()->revision);
        $this->artisan('a4:retry-screening')->assertSuccessful();
        $this->assertSame(1, ScreeningResult::where('application_id', $application->id)->count());
    }

    public static function closedCases(): array
    {
        return [['draft'], ['withdrawn'], ['rejected'], ['cancelled'], ['screening_failed'], ['under_review'], ['accepted']];
    }

    #[DataProvider('closedCases')]
    public function test_only_submitted_applications_are_evaluated(string $status): void
    {
        $application = $this->application();
        $application->update(['status' => $status]);
        $this->assertNull(app(ScreeningService::class)->evaluate($application));
        $this->assertNull($application->fresh()->screeningResult);
        $this->assertSame($status, $application->fresh()->status);
    }

    public function test_cancelled_event_and_legacy_snapshot_do_not_produce_false_results(): void
    {
        $application = $this->application();
        $application->event->update(['lifecycle_status' => 'cancelled']);
        $this->assertNull(app(ScreeningService::class)->evaluate($application));
        $application->event->update(['lifecycle_status' => 'upcoming']);
        $snapshot = $application->snapshot_json;
        unset($snapshot['profile_eligibility']);
        $application->update(['snapshot_json' => $snapshot]);
        try {
            app(ScreeningService::class)->evaluate($application);
            $this->fail('Legacy profile evidence must not be fabricated.');
        } catch (ValidationException) {
            $this->assertNull($application->fresh()->screeningResult);
            $this->assertSame('submitted', $application->fresh()->status);
        }
        $this->artisan('a4:retry-screening')->assertFailed();
    }

    public function test_audit_failure_rolls_back_result_and_status(): void
    {
        $application = $this->application(false);
        $this->mock(AuditService::class)->shouldReceive('record')->once()->andThrow(new \RuntimeException('audit-failed'));
        try {
            app(ScreeningService::class)->evaluate($application);
            $this->fail('Expected rollback.');
        } catch (\RuntimeException $e) {
            $this->assertSame('audit-failed', $e->getMessage());
        }
        $this->assertNull($application->fresh()->screeningResult);
        $this->assertSame('submitted', $application->fresh()->status);
        $this->assertSame(1, $application->fresh()->revision);
    }

    public function test_authorized_detail_pages_show_results_and_reject_other_accounts(): void
    {
        $application = $this->application(false);
        app(ScreeningService::class)->evaluate($application);
        $volunteer = $application->volunteer;
        $volunteer->forceFill(['email_verified_at' => now()])->save();
        $this->actingAs($volunteer)->get(route('volunteer.applications.show', $application))
            ->assertOk()->assertSee('Lamaranmu belum bisa dilanjutkan ke tahap selanjutnya.');
        $this->actingAs($application->event->organizer)->get(route('organizer.applications.show', $application))
            ->assertOk()->assertSee('Hasil screening')->assertSee('Tidak terpenuhi');
        $otherVolunteer = User::factory()->create(['role' => 'volunteer']);
        $this->actingAs($otherVolunteer)->get(route('volunteer.applications.show', $application))->assertForbidden();
        $otherOrganizer = User::factory()->create(['role' => 'organizer', 'organizer_status' => 'active']);
        $this->actingAs($otherOrganizer)->get(route('organizer.applications.show', $application))->assertForbidden();
    }
}
