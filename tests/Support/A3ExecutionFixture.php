<?php

namespace Tests\Support;

use App\Models\Application;
use App\Models\User;
use App\Services\AssessmentService;
use App\Services\EntitlementService;

trait A3ExecutionFixture
{
    use ParticipantAssessmentFixture;

    public function reviewApplication(): Application
    {
        $application = $this->participantApplication();
        app(EntitlementService::class)->activate($application->event); // Caller fixture transaction.
        $snapshot = $application->snapshot_json;
        $snapshot['schedules'] = $application->position->schedules->map(fn ($s) => ['start' => $s->starts_at->toISOString(), 'end' => $s->ends_at->toISOString()])->all();
        $application->forceFill(['snapshot_json' => $snapshot])->save();
        $attempt = app(AssessmentService::class)->start($application, $application->volunteer);
        app(AssessmentService::class)->save($attempt, $application->volunteer, ['revision' => 0, 'answers' => []], true);

        return $application->fresh();
    }

    public function secondCandidate(Application $application): Application
    {
        return Application::create(['event_id' => $application->event_id, 'event_position_id' => $application->event_position_id,
            'volunteer_id' => User::factory()->create(['role' => 'volunteer'])->id,
            'status' => 'under_review', 'submitted_at' => now(), 'snapshot_json' => $application->snapshot_json]);
    }
}
