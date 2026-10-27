<?php

namespace Tests\Feature;

use App\Models\AssessmentAnswer;
use App\Models\AssessmentAttempt;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\AssessmentService;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\DatabaseTestCase;
use Tests\Support\ParticipantAssessmentFixture;

class ParticipantAssessmentTest extends DatabaseTestCase
{
    use ParticipantAssessmentFixture;

    public function test_confirmation_does_not_start_and_repeated_start_recovers_same_attempt_without_keys(): void
    {
        $application = $this->participantApplication();
        $this->actingAs($application->volunteer)->get(route('volunteer.assessments.show', $application))
            ->assertOk()->assertSee('Mulai sekarang')->assertSee('30 menit')->assertDontSee('Soal pertama')->assertDontSee('is_correct');
        $this->assertSame(0, AssessmentAttempt::count());
        $this->postJson(route('volunteer.assessments.start', $application))->assertUnprocessable();
        $this->post(route('volunteer.assessments.start', $application), ['confirmed' => 1])->assertRedirect();
        $attempt = AssessmentAttempt::sole();
        $expires = $attempt->expires_at;
        $this->travel(3)->minutes();
        $this->post(route('volunteer.assessments.start', $application), ['confirmed' => 1])->assertRedirect();
        $this->assertSame(1, AssessmentAttempt::count());
        $this->assertTrue($attempt->fresh()->expires_at->equalTo($expires));
        $this->get(route('volunteer.assessments.show', $application))->assertOk()->assertSee('Soal pertama')->assertSee('Opsi B')
            ->assertDontSee('is_correct')->assertDontSee('correct&quot;', false);
        $this->assertSame(1, AuditLog::where('action', 'assessment.started')->count());
        $this->travelBack();
    }

    public function test_saved_answers_recover_and_submit_calculates_score_once_independently_of_matching(): void
    {
        $application = $this->participantApplication();
        $service = app(AssessmentService::class);
        $attempt = $service->start($application, $application->volunteer);
        $questions = $attempt->assessment->questions()->with('options')->get();
        $answer = [$questions[0]->id => $questions[0]->options->firstWhere('label', 'B')->id];
        $this->actingAs($application->volunteer)->putJson(route('volunteer.assessments.answers', $attempt), ['revision' => 0, 'answers' => $answer])
            ->assertOk()->assertJsonPath('revision', 1)->assertJsonMissingPath('score')->assertJsonMissingPath('correct');
        $this->get(route('volunteer.assessments.show', $application))->assertOk()->assertSee('checked', false);
        $this->putJson(route('volunteer.assessments.answers', $attempt), ['revision' => 0, 'answers' => []])->assertUnprocessable();
        $this->postJson(route('volunteer.assessments.submit', $attempt), ['revision' => 1, 'answers' => $answer, 'score' => 100])
            ->assertOk()->assertJsonPath('status', 'submitted');
        $this->postJson(route('volunteer.assessments.submit', $attempt), ['revision' => 1, 'answers' => []])->assertOk();
        $this->assertSame(50.0, $attempt->fresh()->score);
        $this->assertSame('under_review', $application->fresh()->status);
        $this->assertSame(4, $application->fresh()->revision);
        $this->assertEquals(80, $application->matchResult->total_score);
        $this->assertSame(1, AuditLog::where('action', 'assessment.finalized')->count());
        $this->get(route('volunteer.applications.show', $application))->assertOk()->assertSee('Assessment selesai.')->assertDontSee('Mulai assessment');
        $this->actingAs($application->event->organizer)->get(route('organizer.applications.show', $application))
            ->assertOk()->assertSee('50,00/100')->assertSee('Nilai assessment terpisah');
    }

    public function test_option_from_other_question_is_rejected_atomically(): void
    {
        $application = $this->participantApplication();
        $attempt = app(AssessmentService::class)->start($application, $application->volunteer);
        $questions = $attempt->assessment->questions()->with('options')->get();
        $this->actingAs($application->volunteer)->putJson(route('volunteer.assessments.answers', $attempt), [
            'revision' => 0, 'answers' => [$questions[0]->id => $questions[1]->options[0]->id],
        ])->assertUnprocessable();
        $this->assertSame(0, AssessmentAnswer::count());
        $this->assertSame(0, $attempt->fresh()->revision);
    }

    public function test_deadline_limits_duration_late_answers_are_ignored_and_timeout_command_is_idempotent(): void
    {
        $application = $this->participantApplication();
        $deadline = CarbonImmutable::parse($application->snapshot_json['assessment_deadline']);
        $this->travelTo($deadline->subMinutes(10));
        $service = app(AssessmentService::class);
        $attempt = $service->start($application, $application->volunteer);
        $this->assertTrue($attempt->expires_at->equalTo($deadline));
        $question = $attempt->assessment->questions()->with('options')->first();
        $answers = [$question->id => $question->options->firstWhere('label', 'B')->id];
        $service->save($attempt, $application->volunteer, ['revision' => 0, 'answers' => $answers]);
        $this->travelTo($deadline);
        $this->actingAs($application->volunteer)->postJson(route('volunteer.assessments.submit', $attempt), ['revision' => 1, 'answers' => []])
            ->assertOk()->assertJsonPath('status', 'expired');
        $this->artisan('a4:expire-assessments')->assertSuccessful();
        $this->assertSame(50.0, $attempt->fresh()->score);
        $this->assertSame('under_review', $application->fresh()->status);
        $this->assertSame(1, AuditLog::where('action', 'assessment.finalized')->count());
        $this->travelBack();
    }

    public function test_scheduler_finalizes_closed_browser_but_never_started_application_has_no_attempt_or_zero(): void
    {
        $application = $this->participantApplication();
        $unstarted = $this->participantApplication();
        $attempt = app(AssessmentService::class)->start($application, $application->volunteer);
        $this->travelTo(CarbonImmutable::parse($unstarted->snapshot_json['assessment_deadline']));
        $this->artisan('a4:expire-assessments')->assertSuccessful();
        $this->artisan('a4:expire-assessments')->assertSuccessful();
        $this->assertSame('expired', $attempt->fresh()->status);
        $this->assertSame(0.0, $attempt->fresh()->score);
        $this->assertNull($unstarted->assessmentAttempt);
        $this->assertSame('assessment_pending', $unstarted->fresh()->status);
        $this->actingAs($unstarted->volunteer)->postJson(route('volunteer.assessments.start', $unstarted), ['confirmed' => 1])->assertUnprocessable();
        $this->travelBack();
    }

    public static function terminalStates(): array
    {
        return [['withdrawn'], ['rejected'], ['cancelled'], ['accepted']];
    }

    #[DataProvider('terminalStates')]
    public function test_terminal_application_is_never_revived_by_late_request(string $status): void
    {
        $application = $this->participantApplication();
        $attempt = app(AssessmentService::class)->start($application, $application->volunteer);
        $application->update(['status' => $status]);
        $this->actingAs($application->volunteer)->postJson(route('volunteer.assessments.submit', $attempt), ['revision' => 0, 'answers' => []])
            ->assertOk()->assertJsonPath('status', 'cancelled');
        $this->assertSame($status, $application->fresh()->status);
        $this->assertNull($attempt->fresh()->score);
    }

    public function test_cancellation_of_event_stops_attempt_even_before_deadline(): void
    {
        $application = $this->participantApplication();
        $attempt = app(AssessmentService::class)->start($application, $application->volunteer);
        $application->event->update(['lifecycle_status' => 'cancelled']);
        $this->artisan('a4:expire-assessments')->assertSuccessful();
        $this->assertSame('cancelled', $attempt->fresh()->status);
        $this->assertSame('assessment_pending', $application->fresh()->status);
    }

    public function test_access_is_denied_across_owners_roles_and_inactive_accounts(): void
    {
        $application = $this->participantApplication();
        $attempt = app(AssessmentService::class)->start($application, $application->volunteer);
        $this->get(route('volunteer.assessments.show', $application))->assertRedirect(route('login'));
        foreach (['volunteer', 'organizer', 'admin'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role, 'organizer_status' => 'active']));
            $this->get(route('volunteer.assessments.show', $application))->assertForbidden();
            $this->postJson(route('volunteer.assessments.start', $application), ['confirmed' => 1])->assertForbidden();
            $this->putJson(route('volunteer.assessments.answers', $attempt), ['revision' => 0, 'answers' => []])->assertForbidden();
            $this->postJson(route('volunteer.assessments.submit', $attempt), ['revision' => 0, 'answers' => []])->assertForbidden();
        }
        $application->volunteer->update(['is_active' => false]);
        $this->actingAs($application->volunteer)->get(route('volunteer.assessments.show', $application))->assertForbidden();
    }

    public function test_screening_matching_version_and_legacy_deadline_cannot_be_bypassed(): void
    {
        foreach (['screening', 'matching', 'version', 'deadline', 'status'] as $invalid) {
            $application = $this->participantApplication();
            if ($invalid === 'screening') {
                $application->screeningResult->update(['passed' => false]);
            }
            if ($invalid === 'matching') {
                $application->matchResult->delete();
            }
            if ($invalid === 'status') {
                $application->update(['status' => 'submitted']);
            }
            if (in_array($invalid, ['version', 'deadline'])) {
                $snapshot = $application->snapshot_json;
                unset($snapshot[$invalid === 'version' ? 'assessment_version' : 'assessment_deadline']);
                $application->update(['snapshot_json' => $snapshot]);
            }
            $this->actingAs($application->volunteer)->postJson(route('volunteer.assessments.start', $application), ['confirmed' => 1])->assertUnprocessable();
        }
        $this->assertSame(0, AssessmentAttempt::count());
    }
}
