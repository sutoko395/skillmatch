<?php

namespace Tests\Support;

trait MatchingFixture
{
    private function matchingSnapshot(): array
    {
        return [
            'rule_version' => 'match-v1.4', 'city_id' => 1,
            'profile_eligibility' => ['complete' => true, 'missing_fields' => []],
            'profile' => [
                'city_id' => 1,
                'skills' => [['id' => 1, 'level' => 'intermediate'], ['id' => 2, 'level' => 'advanced'], ['id' => 3, 'level' => 'beginner']],
                'availability_slots' => [
                    ['starts_at' => '2026-11-01T00:00:00+00:00', 'ends_at' => '2026-11-01T04:00:00+00:00'],
                    ['starts_at' => '2026-11-01T03:00:00+00:00', 'ends_at' => '2026-11-01T06:00:00+00:00'],
                ],
            ],
            'skills' => [
                ['id' => 1, 'minimum_level' => 'intermediate', 'is_required' => true],
                ['id' => 2, 'minimum_level' => 'beginner', 'is_required' => false],
                ['id' => 3, 'minimum_level' => 'beginner', 'is_required' => false],
                ['id' => 4, 'minimum_level' => 'intermediate', 'is_required' => false],
            ],
            'schedules' => [['start' => '2026-11-01T07:00:00+07:00', 'end' => '2026-11-01T15:00:00+07:00']],
            'required_full_availability' => false, 'required_same_city' => false,
            'requirements' => [], 'documents' => [],
        ];
    }
}
