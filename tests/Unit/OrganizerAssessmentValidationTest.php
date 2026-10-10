<?php

namespace Tests\Unit;

use App\Models\AssessmentOption;
use App\Models\Event;
use App\Models\User;
use App\Services\OrganizerAssessmentService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class OrganizerAssessmentValidationTest extends TestCase
{
    public function test_owner_policy_and_key_serialization(): void
    {
        $event = new Event(['organizer_id' => 10]);
        $owner = new User(['role' => 'organizer', 'organizer_status' => 'active', 'is_active' => true]);
        $owner->id = 10;
        $this->assertTrue(Gate::forUser($owner)->allows('update', $event));
        $other = clone $owner;
        $other->id = 11;
        $this->assertFalse(Gate::forUser($other)->allows('update', $event));
        foreach (['volunteer', 'admin'] as $role) {
            $other->id = 10;
            $other->role = $role;
            $this->assertFalse(Gate::forUser($other)->allows('update', $event));
        }
        $owner->is_active = false;
        $this->assertFalse(Gate::forUser($owner)->allows('update', $event));
        $option = new AssessmentOption(['label' => 'A', 'text' => 'Test', 'is_correct' => true]);
        $this->assertArrayNotHasKey('is_correct', $option->toArray());
    }

    private function complete(): array
    {
        return ['revision' => 0, 'duration_minutes' => 30, 'questions' => [[
            'text' => 'Pilih jawaban', 'options' => ['A' => 'Satu', 'B' => 'Dua', 'C' => 'Tiga', 'D' => 'Empat'], 'correct' => 'B',
        ]]];
    }

    public function test_incomplete_draft_can_be_saved_but_not_published(): void
    {
        $service = app(OrganizerAssessmentService::class);
        $input = ['revision' => 0, 'duration_minutes' => null, 'questions' => []];
        $this->assertSame([], $service->validateInput($input, false)['questions']);
        $input['questions'] = [['text' => null, 'options' => ['A' => null, 'B' => null, 'C' => null, 'D' => null], 'correct' => null]];
        $this->assertCount(1, $service->validateInput($input, false)['questions']);
        $this->expectException(ValidationException::class);
        $service->validateInput($input, true);
    }

    public function test_publication_accepts_boundary_values(): void
    {
        $service = app(OrganizerAssessmentService::class);
        $input = $this->complete();
        $input['duration_minutes'] = 1;
        $this->assertCount(1, $service->validateInput($input, true)['questions']);
        $input['duration_minutes'] = 120;
        $input['questions'] = array_fill(0, 50, $input['questions'][0]);
        $this->assertCount(50, $service->validateInput($input, true)['questions']);
    }

    public function test_invalid_duration_count_options_and_key_are_rejected(): void
    {
        $service = app(OrganizerAssessmentService::class);
        $cases = [];
        foreach ([0, 121, -1, 1.5] as $duration) {
            $input = $this->complete();
            $input['duration_minutes'] = $duration;
            $cases[] = $input;
        }
        $input = $this->complete();
        $input['questions'] = [];
        $cases[] = $input;
        $input = $this->complete();
        $input['questions'] = array_fill(0, 51, $input['questions'][0]);
        $cases[] = $input;
        $input = $this->complete();
        unset($input['questions'][0]['options']['D']);
        $cases[] = $input;
        $input = $this->complete();
        $input['questions'][0]['options']['E'] = 'Lima';
        $cases[] = $input;
        $input = $this->complete();
        $input['questions'][0]['correct'] = ['A', 'B'];
        $cases[] = $input;
        $input = $this->complete();
        $input['questions'][0]['correct'] = 'E';
        $cases[] = $input;
        foreach ($cases as $case) {
            try {
                $service->validateInput($case, true);
                $this->fail('Invalid assessment passed validation.');
            } catch (ValidationException $error) {
                $this->assertNotEmpty($error->errors());
            }
        }
    }
}
