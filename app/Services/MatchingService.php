<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class MatchingService
{
    private const LEVELS = ['beginner' => 0, 'intermediate' => 1, 'advanced' => 2, 'expert' => 3];

    public function calculate(array $snapshot): array
    {
        Validator::make($snapshot, [
            'rule_version' => 'required|in:match-v1.4',
            'city_id' => 'required|integer|min:1',
            'profile.city_id' => 'required|integer|min:1',
            'skills' => 'required|array|min:1',
            'skills.*.id' => 'required|integer|min:1|distinct',
            'skills.*.minimum_level' => 'required|in:beginner,intermediate,advanced,expert',
            'profile.skills' => 'present|array',
            'profile.skills.*.id' => 'required|integer|min:1|distinct',
            'profile.skills.*.level' => 'required|in:beginner,intermediate,advanced,expert',
            'schedules' => 'required|array|min:1',
            'schedules.*.start' => 'required|date',
            'schedules.*.end' => 'required|date',
            'profile.availability_slots' => 'present|array',
            'profile.availability_slots.*.starts_at' => 'required|date',
            'profile.availability_slots.*.ends_at' => 'required|date',
        ])->validate();

        $candidateSkills = collect($snapshot['profile']['skills'])->keyBy('id');
        $skills = [];
        foreach ($snapshot['skills'] as $requirement) {
            $candidate = $candidateSkills->get($requirement['id']);
            $skills[] = [
                'skill_id' => $requirement['id'], 'minimum_level' => $requirement['minimum_level'],
                'candidate_level' => $candidate['level'] ?? null,
                'matched' => $candidate !== null && self::LEVELS[$candidate['level']] >= self::LEVELS[$requirement['minimum_level']],
            ];
        }
        $matched = count(array_filter($skills, fn ($skill) => $skill['matched']));
        $skillScore = 100 * $matched / count($skills);

        $schedules = $this->mergeIntervals($snapshot['schedules'], 'start', 'end');
        $availability = $this->mergeIntervals($snapshot['profile']['availability_slots'], 'starts_at', 'ends_at');
        $totalSeconds = array_sum(array_map(fn ($slot) => $slot[1] - $slot[0], $schedules));
        $coveredSeconds = 0;
        $index = 0;
        foreach ($schedules as [$start, $end]) {
            while (isset($availability[$index]) && $availability[$index][1] <= $start) {
                $index++;
            }
            for ($i = $index; isset($availability[$i]) && $availability[$i][0] < $end; $i++) {
                $coveredSeconds += max(0, min($end, $availability[$i][1]) - max($start, $availability[$i][0]));
            }
        }
        $availabilityScore = 100 * $coveredSeconds / $totalSeconds;
        $sameCity = (int) $snapshot['city_id'] === (int) $snapshot['profile']['city_id'];
        $locationScore = $sameCity ? 100 : 0;

        return [
            'skill_score' => $skillScore, 'availability_score' => $availabilityScore,
            'location_score' => $locationScore,
            'total_score' => 0.50 * $skillScore + 0.30 * $availabilityScore + 0.20 * $locationScore,
            'rule_version' => $snapshot['rule_version'],
            'breakdown' => [
                'skills' => ['matched' => $matched, 'total' => count($skills), 'items' => $skills],
                'availability' => ['covered_seconds' => $coveredSeconds, 'total_seconds' => $totalSeconds],
                'location' => ['same_city' => $sameCity],
            ],
        ];
    }

    private function mergeIntervals(array $rows, string $startKey, string $endKey): array
    {
        $intervals = [];
        foreach ($rows as $row) {
            foreach ([$startKey, $endKey] as $key) {
                if (! preg_match('/(?:Z|[+-]\d{2}:\d{2})$/', $row[$key])) {
                    throw ValidationException::withMessages(['snapshot' => 'Timezone jadwal snapshot tidak tersedia.']);
                }
            }
            $start = CarbonImmutable::parse($row[$startKey])->getTimestamp();
            $end = CarbonImmutable::parse($row[$endKey])->getTimestamp();
            if ($end <= $start) {
                throw ValidationException::withMessages(['snapshot' => 'Interval jadwal snapshot tidak valid.']);
            }
            $intervals[] = [$start, $end];
        }
        usort($intervals, fn ($a, $b) => $a[0] <=> $b[0]);
        $merged = [];
        foreach ($intervals as [$start, $end]) {
            $last = count($merged) - 1;
            if ($last >= 0 && $start <= $merged[$last][1]) {
                $merged[$last][1] = max($merged[$last][1], $end);
            } else {
                $merged[] = [$start, $end];
            }
        }

        return $merged;
    }
}
