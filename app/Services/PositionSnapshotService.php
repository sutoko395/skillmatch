<?php

namespace App\Services;

use App\Models\EventPosition;

class PositionSnapshotService
{
    public function build(EventPosition $position): array
    {
        $position->load(['event', 'positionSkills', 'schedules', 'requirements']);

        return [
            'position_id' => $position->id, 'event_id' => $position->event_id, 'city_id' => $position->event->city_id,
            'skills' => $position->positionSkills->map(fn ($s) => ['id' => $s->skill_id, 'minimum_level' => $s->minimum_level, 'is_required' => $s->is_required])->all(),
            'schedules' => $position->schedules->map(fn ($s) => ['start' => $s->starts_at->toIso8601String(), 'end' => $s->ends_at->toIso8601String()])->all(),
            'requirements' => $position->requirements->map->only(['name', 'description', 'kind', 'document_type', 'is_required'])->all(),
            'required_full_availability' => $position->required_full_availability, 'required_same_city' => $position->required_same_city,
            'assessment_version' => app(A2Dependencies::class)->assessmentVersion($position), 'rule_version' => 'match-v1.4',
        ];
    }
}
