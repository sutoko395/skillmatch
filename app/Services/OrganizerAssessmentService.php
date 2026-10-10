<?php

namespace App\Services;

use App\Contracts\AssessmentReadiness;
use App\Models\Assessment;
use App\Models\Event;
use App\Models\EventPosition;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class OrganizerAssessmentService implements AssessmentReadiness
{
    public function validateInput(array $input, bool $publish): array
    {
        $required = $publish ? 'required' : 'nullable';

        return Validator::make($input, [
            'duration_minutes' => [$required, 'integer', 'min:1', 'max:120'],
            'questions' => ['present', 'array', $publish ? 'min:1' : 'min:0', 'max:50'],
            'questions.*.text' => [$required, 'string', 'max:5000'],
            'questions.*.options' => ['required', 'array:A,B,C,D', 'size:4'],
            'questions.*.options.A' => [$required, 'string', 'max:2000'],
            'questions.*.options.B' => [$required, 'string', 'max:2000'],
            'questions.*.options.C' => [$required, 'string', 'max:2000'],
            'questions.*.options.D' => [$required, 'string', 'max:2000'],
            'questions.*.correct' => [$required, 'in:A,B,C,D'],
            'revision' => ['required', 'integer', 'min:0'],
        ], [
            'questions.min' => 'Publikasi memerlukan minimal satu soal lengkap.',
            'questions.max' => 'Assessment maksimal 50 soal.',
            'duration_minutes.min' => 'Durasi minimal 1 menit.',
            'duration_minutes.max' => 'Durasi maksimal 120 menit.',
            'questions.*.text.required' => 'Isi pertanyaan setiap soal.',
            'questions.*.correct.required' => 'Pilih satu kunci jawaban setiap soal.',
            'questions.*.options.*.required' => 'Lengkapi keempat opsi jawaban setiap soal.',
        ])->validate();
    }

    public function save(EventPosition $position, User $actor, array $input, bool $publish): Assessment
    {
        $data = $this->validateInput($input, $publish);

        return DB::transaction(function () use ($position, $actor, $data, $publish) {
            $actor = User::lockForUpdate()->findOrFail($actor->id);
            $event = Event::lockForUpdate()->findOrFail($position->event_id);
            Gate::forUser($actor)->authorize('update', $event);
            abort_unless($actor->hasVerifiedEmail(), 403);
            $position = $event->positions()->lockForUpdate()->findOrFail($position->id);
            $assessment = $position->assessments()->lockForUpdate()->first();
            if ($assessment?->is_published) {
                if ($publish) {
                    return $assessment; // Replay cannot edit the frozen set or duplicate its audit.
                }
                throw ValidationException::withMessages(['assessment' => 'Assessment sudah dipublikasikan dan terkunci.']);
            }
            app(EventService::class)->editable($event);
            if ((int) $data['revision'] !== ($assessment?->revision ?? 0)) {
                throw ValidationException::withMessages(['assessment' => 'Draft telah berubah di tab lain. Muat ulang sebelum menyimpan.']);
            }
            $assessment ??= new Assessment(['event_position_id' => $position->id, 'version' => 1]);
            $assessment->fill([
                'duration_minutes' => $data['duration_minutes'] ?? null,
                'revision' => $assessment->revision + 1,
                'is_published' => $publish,
            ])->save();
            $assessment->questions()->delete();
            foreach (array_values($data['questions']) as $index => $row) {
                $question = $assessment->questions()->create(['sort_order' => $index + 1, 'text' => $row['text'] ?? null]);
                foreach (['A', 'B', 'C', 'D'] as $label) {
                    $question->options()->create([
                        'label' => $label, 'text' => $row['options'][$label] ?? null,
                        'is_correct' => ($row['correct'] ?? null) === $label,
                    ]);
                }
            }
            $event->increment('revision');
            app(AuditService::class)->record($actor, $publish ? 'assessment.published' : 'assessment.draft_saved', $assessment,
                ['after' => ['revision' => $assessment->revision]]);

            return $assessment;
        }, 3);
    }

    public function publishedVersion(EventPosition $position): int
    {
        $assessment = $position->assessments()->where('is_published', true)->with('questions.options')->first();
        if (! $assessment || $assessment->version < 1) {
            throw ValidationException::withMessages(['assessment' => "Assessment posisi {$position->name} belum dipublikasikan."]);
        }
        $rows = $assessment->questions->map(fn ($question) => [
            'text' => $question->text,
            'options' => $question->options->pluck('text', 'label')->all(),
            'correct' => $question->options->where('is_correct', true)->count() === 1
                ? $question->options->firstWhere('is_correct', true)->label : null,
        ])->all();
        $this->validateInput(['duration_minutes' => $assessment->duration_minutes, 'questions' => $rows, 'revision' => $assessment->revision], true);

        return $assessment->version;
    }
}
