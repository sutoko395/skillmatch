<?php

namespace Tests\Unit;

use App\Models\Application;
use App\Models\MatchResult;
use App\Models\ScreeningResult;
use App\Services\MatchingService;
use Illuminate\Support\Facades\Blade;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\MatchingFixture;
use Tests\TestCase;

class MatchingServiceTest extends TestCase
{
    use MatchingFixture;

    public function test_reference_example_has_score_eighty_and_eo_breakdown(): void
    {
        $scores = app(MatchingService::class)->calculate($this->matchingSnapshot());
        $this->assertEqualsWithDelta(75, $scores['skill_score'], 1e-10);
        $this->assertEqualsWithDelta(75, $scores['availability_score'], 1e-10);
        $this->assertEqualsWithDelta(100, $scores['location_score'], 1e-10);
        $this->assertEqualsWithDelta(80, $scores['total_score'], 1e-10);
        $this->assertSame(21600, $scores['breakdown']['availability']['covered_seconds']);
        $this->assertSame(28800, $scores['breakdown']['availability']['total_seconds']);
        $result = new MatchResult([...$scores, 'breakdown_json' => $scores['breakdown']]);
        $html = Blade::render('<x-application-matching :result="$result" />', compact('result'));
        $this->assertStringContainsString('80,00', $html);
        $this->assertStringContainsString('37,50', $html);
        $this->assertStringContainsString('assessment terpisah', $html);
    }

    public function test_all_position_skills_count_and_levels_above_minimum_give_no_extra_bonus(): void
    {
        $snapshot = $this->matchingSnapshot();
        $snapshot['profile']['skills'][0]['level'] = 'expert';
        $scores = app(MatchingService::class)->calculate($snapshot);
        $this->assertEqualsWithDelta(75, $scores['skill_score'], 1e-10);
        $snapshot['profile']['skills'][0]['level'] = 'beginner';
        $scores = app(MatchingService::class)->calculate($snapshot);
        $this->assertEqualsWithDelta(50, $scores['skill_score'], 1e-10);
    }

    public function test_location_is_binary_and_empty_availability_gives_zero(): void
    {
        $snapshot = $this->matchingSnapshot();
        $snapshot['profile']['city_id'] = 2;
        $snapshot['profile']['availability_slots'] = [];
        $scores = app(MatchingService::class)->calculate($snapshot);
        $this->assertEqualsWithDelta(0, $scores['location_score'], 1e-10);
        $this->assertEqualsWithDelta(0, $scores['availability_score'], 1e-10);
        $this->assertEqualsWithDelta(37.5, $scores['total_score'], 1e-10);
    }

    public function test_union_of_schedules_and_adjacent_availability_is_not_double_counted(): void
    {
        $snapshot = $this->matchingSnapshot();
        $snapshot['schedules'][] = ['start' => '2026-11-01T02:00:00+00:00', 'end' => '2026-11-01T04:00:00+00:00'];
        $snapshot['profile']['availability_slots'][] = ['starts_at' => '2026-11-01T06:00:00+00:00', 'ends_at' => '2026-11-01T08:00:00+00:00'];
        $scores = app(MatchingService::class)->calculate($snapshot);
        $this->assertEqualsWithDelta(100, $scores['availability_score'], 1e-10);
        $this->assertSame(28800, $scores['breakdown']['availability']['total_seconds']);
        $snapshot['profile']['availability_slots'] = [['starts_at' => '2026-11-01T08:00:00+00:00', 'ends_at' => '2026-11-01T09:00:00+00:00']];
        $this->assertEqualsWithDelta(0, app(MatchingService::class)->calculate($snapshot)['availability_score'], 1e-10);
    }

    public function test_full_precision_is_kept_before_ui_rounding(): void
    {
        $snapshot = $this->matchingSnapshot();
        array_pop($snapshot['skills']);
        $snapshot['profile']['skills'] = [$snapshot['profile']['skills'][0]];
        $scores = app(MatchingService::class)->calculate($snapshot);
        $this->assertEqualsWithDelta(100 / 3, $scores['skill_score'], 1e-12);
        $this->assertNotEquals(round($scores['skill_score'], 2), $scores['skill_score']);
    }

    public function test_volunteer_status_changes_without_rendering_scores_or_an_assessment_start_button(): void
    {
        $application = new Application(['status' => 'assessment_pending']);
        $application->setRelation('screeningResult', new ScreeningResult([
            'passed' => true, 'checks_json' => [], 'evaluated_at' => now(),
        ]));
        $html = Blade::render('<x-application-status :application="$application" /><x-application-screening :application="$application" />', compact('application'));
        $this->assertStringContainsString('Menunggu assessment', $html);
        $this->assertStringContainsString('Pencocokan selesai. Lamaranmu siap lanjut ke assessment.', $html);
        $this->assertStringNotContainsString('Match Score', $html);
        $this->assertStringNotContainsString('Kecocokan kandidat', $html);
        $this->assertStringNotContainsString('Mulai assessment', $html);
    }

    public static function invalidCases(): array
    {
        return [['skills'], ['schedules'], ['level'], ['duplicate'], ['city'], ['interval'], ['timezone'], ['version']];
    }

    #[DataProvider('invalidCases')]
    public function test_invalid_snapshot_is_not_converted_to_a_fake_zero_score(string $case): void
    {
        $snapshot = $this->matchingSnapshot();
        switch ($case) {
            case 'skills': $snapshot['skills'] = [];
                break;
            case 'schedules': $snapshot['schedules'] = [];
                break;
            case 'level': $snapshot['profile']['skills'][0]['level'] = 'unknown';
                break;
            case 'duplicate': $snapshot['skills'][] = $snapshot['skills'][0];
                break;
            case 'city': unset($snapshot['profile']['city_id']);
                break;
            case 'interval': $snapshot['schedules'][0]['end'] = $snapshot['schedules'][0]['start'];
                break;
            case 'timezone': $snapshot['schedules'][0]['start'] = '2026-11-01T07:00:00';
                break;
            case 'version': $snapshot['rule_version'] = 'unknown';
                break;
        }
        $this->expectException(ValidationException::class);
        app(MatchingService::class)->calculate($snapshot);
    }
}
