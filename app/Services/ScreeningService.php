<?php

namespace App\Services;

use App\Models\Application;
use App\Models\Event;
use App\Models\ScreeningResult;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ScreeningService
{
    public function __construct(private ScreeningRules $rules, private AuditService $audit) {}

    public function evaluate(Application $application): ?array
    {
        return DB::transaction(function () use ($application) {
            User::whereKey($application->volunteer_id)->lockForUpdate()->firstOrFail();
            $event = Event::whereKey($application->event_id)->lockForUpdate()->firstOrFail();
            $locked = Application::whereKey($application->id)->lockForUpdate()->firstOrFail();
            $existing = $locked->screeningResult()->first();
            if ($existing) {
                return $existing->evaluation();
            }
            if ($locked->status !== 'submitted' || in_array($event->lifecycle_status, ['cancelled', 'completed'], true)) {
                return null;
            }

            $evaluation = $this->rules->evaluate($locked->snapshot_json ?? []);
            ScreeningResult::create([
                'application_id' => $locked->id, 'passed' => $evaluation['passed'],
                'checks_json' => $evaluation['checks'], 'rule_version' => $evaluation['rule_version'], 'evaluated_at' => now(),
            ]);
            $before = ['status' => $locked->status, 'revision' => $locked->revision];
            // Matching must persist its score before the assessment_pending transition.
            $locked->forceFill(['status' => $evaluation['passed'] ? 'submitted' : 'screening_failed', 'revision' => $locked->revision + 1])->save();
            $this->audit->record(null, 'application.screened', $locked,
                ['before' => $before, 'after' => ['status' => $locked->status, 'revision' => $locked->revision]],
                $evaluation['passed'] ? 'screening-passed' : 'screening-failed');

            return $evaluation;
        });
    }
}
