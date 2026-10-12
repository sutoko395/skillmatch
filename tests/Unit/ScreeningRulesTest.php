<?php

namespace Tests\Unit;

use App\Models\Application;
use App\Models\Event;
use App\Models\ScreeningResult;
use App\Models\User;
use App\Services\ScreeningRules;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ScreeningRulesTest extends TestCase
{
    private function snapshot(): array
    {
        return [
            'rule_version' => 'match-v1.4', 'city_id' => 1,
            'profile_eligibility' => ['complete' => true, 'missing_fields' => []],
            'profile' => ['city_id' => 1, 'skills' => [['id' => 1, 'name' => 'Komunikasi', 'level' => 'intermediate']],
                'availability_slots' => [
                    ['starts_at' => '2026-10-15T01:00:00+00:00', 'ends_at' => '2026-10-15T03:00:00+00:00'],
                    ['starts_at' => '2026-10-15T02:30:00+00:00', 'ends_at' => '2026-10-15T04:00:00+00:00'],
                    ['starts_at' => '2026-10-15T04:00:00+00:00', 'ends_at' => '2026-10-15T05:00:00+00:00'],
                ]],
            'skills' => [['id' => 1, 'minimum_level' => 'intermediate', 'is_required' => true]],
            'schedules' => [['start' => '2026-10-15T08:00:00+07:00', 'end' => '2026-10-15T12:00:00+07:00']],
            'required_full_availability' => true, 'required_same_city' => true,
            'requirements' => [['name' => 'CV', 'kind' => 'document', 'document_type' => 'cv', 'is_required' => true]],
            'documents' => [['id' => 1, 'document_type' => 'cv', 'ready_at' => '2026-10-11T01:00:00+00:00']],
        ];
    }

    public function test_overlapping_adjacent_intervals_and_timezone_offsets_cover_schedule(): void
    {
        $result = app(ScreeningRules::class)->evaluate($this->snapshot());
        $this->assertTrue($result['passed']);
        $this->assertSame('match-v1.4', $result['rule_version']);
        $this->assertTrue(collect($result['checks'])->every('passed'));
    }

    public static function unmetCases(): array
    {
        return [['profile'], ['skill'], ['missing_skill'], ['document'], ['availability'], ['city']];
    }

    #[DataProvider('unmetCases')]
    public function test_each_mandatory_failure_has_a_reason(string $case): void
    {
        $snapshot = $this->snapshot();
        switch ($case) {
            case 'profile':
                $snapshot['profile_eligibility'] = ['complete' => false, 'missing_fields' => ['bio']];
                break;
            case 'skill':
                $snapshot['profile']['skills'][0]['level'] = 'beginner';
                break;
            case 'missing_skill':
                $snapshot['skills'][0]['id'] = 2;
                break;
            case 'document':
                $snapshot['documents'] = [];
                break;
            case 'availability':
                // A one-second gap cannot be hidden by merging or equal total duration.
                $snapshot['profile']['availability_slots'][2]['starts_at'] = '2026-10-15T04:00:01+00:00';
                break;
            case 'city':
                $snapshot['profile']['city_id'] = 2;
                break;
        }
        $result = app(ScreeningRules::class)->evaluate($snapshot);
        $this->assertFalse($result['passed']);
        $failures = collect($result['checks'])->filter(fn ($check) => $check['required'] && ! $check['passed']);
        $this->assertCount(1, $failures);
        $this->assertNotEmpty($failures->first()['reason']);
    }

    public function test_preferences_and_manual_requirements_do_not_fail_screening(): void
    {
        $snapshot = $this->snapshot();
        $snapshot['skills'][0]['is_required'] = false;
        $snapshot['profile']['skills'][0]['level'] = 'beginner';
        $snapshot['required_same_city'] = false;
        $snapshot['profile']['city_id'] = 2;
        $snapshot['required_full_availability'] = false;
        $snapshot['profile']['availability_slots'] = [];
        $snapshot['requirements'][0]['is_required'] = false;
        $snapshot['documents'] = [];
        $snapshot['requirements'][] = ['name' => 'Pengalaman', 'kind' => 'manual', 'document_type' => null, 'is_required' => true];
        $result = app(ScreeningRules::class)->evaluate($snapshot);
        $this->assertTrue($result['passed']);
        $manual = collect($result['checks'])->firstWhere('manual', true);
        $this->assertFalse($manual['required']);
        $this->assertStringContainsString('manual', $manual['reason']);
    }

    public static function malformedCases(): array
    {
        return [['legacy'], ['inconsistent'], ['interval'], ['timezone'], ['level'], ['missing_document_type']];
    }

    #[DataProvider('malformedCases')]
    public function test_incomplete_historical_or_invalid_data_is_deferred_not_marked_failed(string $case): void
    {
        $snapshot = $this->snapshot();
        switch ($case) {
            case 'legacy': unset($snapshot['profile_eligibility']);
                break;
            case 'inconsistent': $snapshot['profile_eligibility']['complete'] = false;
                break;
            case 'interval': $snapshot['schedules'][0]['end'] = $snapshot['schedules'][0]['start'];
                break;
            case 'timezone': $snapshot['schedules'][0]['start'] = '2026-10-15T08:00:00';
                break;
            case 'level': $snapshot['skills'][0]['minimum_level'] = 'unknown';
                break;
            case 'missing_document_type': unset($snapshot['requirements'][0]['document_type']);
                break;
        }
        $this->expectException(ValidationException::class);
        app(ScreeningRules::class)->evaluate($snapshot);
    }

    public function test_results_render_differently_without_exposing_html_and_policies_limit_access(): void
    {
        $snapshot = $this->snapshot();
        $snapshot['profile']['skills'][0]['name'] = '<script>unsafe</script>';
        $snapshot['profile']['skills'][0]['level'] = 'beginner';
        $evaluation = app(ScreeningRules::class)->evaluate($snapshot);
        $application = new Application(['status' => 'screening_failed', 'volunteer_id' => 10]);
        $application->setRelation('event', new Event(['organizer_id' => 20]));
        $application->setRelation('screeningResult', new ScreeningResult([
            'passed' => false, 'checks_json' => $evaluation['checks'], 'evaluated_at' => now(),
        ]));
        $html = Blade::render('<x-application-screening :application="$application" />', compact('application'));
        $this->assertStringContainsString('Lamaranmu belum bisa dilanjutkan ke tahap selanjutnya.', $html);
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringNotContainsString('<table', $html);
        $eo = Blade::render('<x-application-screening :application="$application" :detailed="true" />', compact('application'));
        $this->assertStringContainsString('<table', $eo);
        $this->assertStringContainsString('Keterangan', $eo);

        foreach ([['volunteer', 10, true], ['volunteer', 11, false], ['organizer', 20, true], ['organizer', 21, false], ['admin', 20, false]] as [$role, $id, $allowed]) {
            $actor = new User(['role' => $role, 'is_active' => true, 'email_verified_at' => now()]);
            $actor->forceFill(['email_verified_at' => now()]);
            $actor->id = $id;
            $this->assertSame($allowed, Gate::forUser($actor)->allows('view', $application));
            $actor->is_active = false;
            $this->assertFalse(Gate::forUser($actor)->allows('view', $application));
        }
    }

    public function test_pending_and_passed_cards_do_not_claim_assessment_is_ready(): void
    {
        $application = new Application(['status' => 'submitted']);
        $application->setRelation('screeningResult', null);
        $html = Blade::render('<x-application-screening :application="$application" />', compact('application'));
        $this->assertStringContainsString('Lamaran menunggu evaluasi.', $html);
        $evaluation = app(ScreeningRules::class)->evaluate($this->snapshot());
        $application->setRelation('screeningResult', new ScreeningResult([
            'passed' => true, 'checks_json' => $evaluation['checks'], 'evaluated_at' => now(),
        ]));
        $html = Blade::render('<x-application-screening :application="$application" />', compact('application'));
        $this->assertStringContainsString('Lolos screening', $html);
        $this->assertStringContainsString('menunggu proses pencocokan', $html);
        $this->assertStringNotContainsString('Mulai assessment', $html);
        $application->status = 'draft';
        $this->assertStringNotContainsString('Hasil screening', Blade::render('<x-application-screening :application="$application" />', compact('application')));
    }
}
