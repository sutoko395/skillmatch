<?php

namespace App\Services;

use App\Models\Application;
use App\Models\Event;
use App\Models\MatchResult;
use App\Models\ScreeningResult;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class MatchingEvaluationService
{
    public function __construct(private MatchingService $matching, private AuditService $audit) {}

    public function evaluate(Application $application): ?MatchResult
    {
        return DB::transaction(function () use ($application) {
            User::whereKey($application->volunteer_id)->lockForUpdate()->firstOrFail();
            $event = Event::whereKey($application->event_id)->lockForUpdate()->firstOrFail();
            $locked = Application::whereKey($application->id)->lockForUpdate()->firstOrFail();
            $existing = MatchResult::where('application_id', $locked->id)->first();
            if ($existing) {
                return $existing;
            }
            if ($locked->status !== 'submitted'
                || in_array($event->lifecycle_status, ['cancelled', 'completed'], true)
                || ! ScreeningResult::where('application_id', $locked->id)->where('passed', true)->exists()) {
                return null;
            }

            $scores = $this->matching->calculate($locked->snapshot_json ?? []);
            $result = MatchResult::create([
                'application_id' => $locked->id,
                'skill_score' => $scores['skill_score'], 'availability_score' => $scores['availability_score'],
                'location_score' => $scores['location_score'], 'total_score' => $scores['total_score'],
                'rule_version' => $scores['rule_version'], 'breakdown_json' => $scores['breakdown'],
            ]);
            $before = ['status' => $locked->status, 'revision' => $locked->revision];
            $locked->forceFill(['status' => 'assessment_pending', 'revision' => $locked->revision + 1])->save();
            $this->audit->record(null, 'application.matched', $locked, [
                'before' => $before, 'after' => ['status' => $locked->status, 'revision' => $locked->revision],
            ], 'matching-completed');

            return $result;
        });
    }
}
