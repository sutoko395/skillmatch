<?php

namespace Tests\Support;

use App\Models\Application;
use App\Models\MatchResult;
use App\Models\ScreeningResult;
use App\Models\User;
use App\Services\MatchingService;
use App\Services\OrganizerAssessmentService;
use App\Services\PositionSnapshotService;

trait ParticipantAssessmentFixture
{
    use A2Fixture, MatchingFixture;

    public function participantApplication(): Application
    {
        [$owner, $event, $position] = $this->fixture();
        app(OrganizerAssessmentService::class)->save($position, $owner, [
            'revision' => 0, 'duration_minutes' => 30, 'questions' => [
                ['text' => 'Soal pertama', 'options' => ['A' => 'Opsi A', 'B' => 'Opsi B', 'C' => 'Opsi C', 'D' => 'Opsi D'], 'correct' => 'B'],
                ['text' => 'Soal kedua', 'options' => ['A' => 'Pilihan A', 'B' => 'Pilihan B', 'C' => 'Pilihan C', 'D' => 'Pilihan D'], 'correct' => 'C'],
            ],
        ], true);
        $snapshot = array_replace($this->matchingSnapshot(), array_intersect_key(
            app(PositionSnapshotService::class)->build($position),
            array_flip(['event_id', 'position_id', 'assessment_version', 'assessment_deadline', 'decision_deadline'])
        ));
        $event->forceFill(['status' => 'approved', 'publication_status' => 'published', 'published_at' => now()])->save();
        $application = Application::create(['event_id' => $event->id, 'event_position_id' => $position->id,
            'volunteer_id' => User::factory()->create(['role' => 'volunteer'])->id, 'status' => 'assessment_pending',
            'snapshot_json' => $snapshot, 'submitted_at' => now(), 'revision' => 3]);
        ScreeningResult::create(['application_id' => $application->id, 'passed' => true,
            'checks_json' => [], 'rule_version' => 'match-v1.4', 'evaluated_at' => now()]);
        $scores = app(MatchingService::class)->calculate($snapshot);
        MatchResult::create(['application_id' => $application->id, 'skill_score' => $scores['skill_score'], 'availability_score' => $scores['availability_score'],
            'location_score' => $scores['location_score'], 'total_score' => $scores['total_score'], 'rule_version' => 'match-v1.4', 'breakdown_json' => $scores['breakdown']]);

        return $application;
    }
}
