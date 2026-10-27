<?php

namespace App\Services;

use App\Models\Application;
use App\Models\Assessment;
use App\Models\AssessmentAnswer;
use App\Models\AssessmentAttempt;
use App\Models\Event;
use App\Models\User;
use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AssessmentService
{
    private function fail(string $message): never
    {
        throw ValidationException::withMessages(['assessment' => $message]);
    }

    // Same lock order as submit/screening/matching: volunteer -> event -> application -> attempt.
    private function locked(Application $application, ?User $actor, Closure $callback): mixed
    {
        return DB::transaction(function () use ($application, $actor, $callback) {
            $volunteer = User::whereKey($application->volunteer_id)->lockForUpdate()->firstOrFail();
            if ($actor) {
                abort_unless($actor->id === $volunteer->id && $volunteer->role === 'volunteer' && $volunteer->is_active, 403);
            }
            $event = Event::whereKey($application->event_id)->lockForUpdate()->firstOrFail();
            $locked = Application::whereKey($application->id)->lockForUpdate()->firstOrFail();
            $attempt = AssessmentAttempt::where('application_id', $locked->id)->lockForUpdate()->first();
            if ($attempt?->status === 'in_progress') {
                if ($locked->status !== 'assessment_pending' || ! $volunteer->is_active
                    || in_array($event->lifecycle_status, ['cancelled', 'completed'], true)) {
                    $attempt->forceFill(['status' => 'cancelled', 'submitted_at' => now(), 'revision' => $attempt->revision + 1])->save();
                    app(AuditService::class)->record(null, 'assessment.cancelled', $attempt, ['after' => ['status' => 'cancelled']]);
                } elseif (now()->greaterThanOrEqualTo($attempt->expires_at)) {
                    $this->finish($locked, $attempt, 'expired', null);
                }
            }

            return $callback($locked, $event, $attempt);
        }, 3);
    }

    private function eligible(Application $application, Event $event): CarbonImmutable
    {
        if ($application->status !== 'assessment_pending' || ! $application->screeningResult?->passed || ! $application->matchResult) {
            $this->fail('Assessment hanya tersedia setelah lolos screening dan pencocokan selesai.');
        }
        $date = $application->snapshot_json['assessment_deadline'] ?? null;
        if (! $date) {
            $this->fail('Tenggat assessment belum tersedia untuk lamaran ini.');
        }
        $deadline = CarbonImmutable::parse($date)->utc()->min($event->starts_at);
        if ($event->lifecycle_status !== 'upcoming' || $event->publication_status !== 'published' || ! $event->published_at
            || now()->greaterThanOrEqualTo($deadline)) {
            $this->fail('Assessment tidak dapat dimulai karena tenggat telah berakhir atau event tidak tersedia.');
        }

        return $deadline;
    }

    private function assessment(Application $application): Assessment
    {
        $version = $application->snapshot_json['assessment_version'] ?? null;
        $assessment = Assessment::where('event_position_id', $application->event_position_id)
            ->where('version', $version)->where('is_published', true)->withCount('questions')->first();
        if (! $assessment) {
            $this->fail('Assessment yang sesuai dengan lamaran belum tersedia.');
        }

        return $assessment;
    }

    public function inspect(Application $application, User $actor): array
    {
        return $this->locked($application, $actor, function ($locked, $event, $attempt) {
            if ($attempt) {
                return ['attempt' => $attempt];
            }
            $deadline = $this->eligible($locked, $event);
            $assessment = $this->assessment($locked);

            return ['attempt' => null, 'assessment' => $assessment,
                'expires_at' => CarbonImmutable::now()->addMinutes($assessment->duration_minutes)->min($deadline)];
        });
    }

    public function start(Application $application, User $actor): AssessmentAttempt
    {
        return $this->locked($application, $actor, function ($locked, $event, $attempt) use ($actor) {
            if ($attempt) {
                return $attempt;
            }
            $deadline = $this->eligible($locked, $event);
            $assessment = $this->assessment($locked);
            $assessment->load('questions.options');
            // Reuse the organizer validator for the pinned version, never a newer version.
            app(OrganizerAssessmentService::class)->validateInput([
                'duration_minutes' => $assessment->duration_minutes, 'revision' => $assessment->revision,
                'questions' => $assessment->questions->map(fn ($q) => ['text' => $q->text,
                    'options' => $q->options->pluck('text', 'label')->all(),
                    'correct' => $q->options->where('is_correct', true)->count() === 1 ? $q->options->firstWhere('is_correct', true)->label : null])->all(),
            ], true);
            $started = CarbonImmutable::now()->utc();
            $attempt = AssessmentAttempt::create(['application_id' => $locked->id, 'assessment_id' => $assessment->id,
                'version' => $assessment->version, 'status' => 'in_progress', 'started_at' => $started,
                'expires_at' => $started->addMinutes($assessment->duration_minutes)->min($deadline), 'total' => $assessment->questions_count]);
            app(AuditService::class)->record($actor, 'assessment.started', $attempt, ['after' => ['status' => 'in_progress']]);

            return $attempt;
        });
    }

    public function save(AssessmentAttempt $attempt, User $actor, array $input, bool $finalize = false): AssessmentAttempt
    {
        return $this->locked($attempt->application, $actor, function ($application, $event, $locked) use ($input, $finalize, $actor) {
            if (! $locked || $locked->status !== 'in_progress') {
                return $locked;
            }
            $data = Validator::make($input, ['revision' => 'required|integer|min:0', 'answers' => 'present|array|max:50',
                'answers.*' => 'nullable|integer|min:1'])->validate();
            if ((int) $data['revision'] !== $locked->revision) {
                $this->fail('Jawaban telah diperbarui di tab lain. Muat ulang halaman sebelum melanjutkan.');
            }
            $questions = $locked->assessment->questions()->with('options')->get()->keyBy('id');
            foreach ($data['answers'] as $questionId => $optionId) {
                $question = $questions->get($questionId);
                if (! $question || ($optionId !== null && ! $question->options->contains('id', (int) $optionId))) {
                    $this->fail('Pilihan jawaban tidak sesuai dengan soal assessment ini.');
                }
            }
            foreach ($data['answers'] as $questionId => $optionId) {
                AssessmentAnswer::updateOrCreate(['assessment_attempt_id' => $locked->id, 'assessment_question_id' => $questionId],
                    ['assessment_option_id' => $optionId]);
            }
            $locked->forceFill(['revision' => $locked->revision + 1])->save();
            if ($finalize) {
                $this->finish($application, $locked, 'submitted', $actor);
            }

            return $locked;
        });
    }

    private function finish(Application $application, AssessmentAttempt $attempt, string $status, ?User $actor): void
    {
        $correct = AssessmentAnswer::where('assessment_attempt_id', $attempt->id)
            ->join('assessment_options', 'assessment_options.id', '=', 'assessment_answers.assessment_option_id')
            ->where('assessment_options.is_correct', true)->count();
        $attempt->forceFill(['status' => $status, 'score' => $correct / $attempt->total * 100,
            'correct' => $correct, 'submitted_at' => now(), 'revision' => $attempt->revision + 1])->save();
        $application->forceFill(['status' => 'under_review', 'revision' => $application->revision + 1])->save();
        app(AuditService::class)->record($actor, 'assessment.finalized', $attempt, ['after' => ['status' => $status]]);
        app(AuditService::class)->record($actor, 'application.assessed', $application, ['after' => ['status' => 'under_review', 'revision' => $application->revision]]);
    }

    public function expire(Application $application): void
    {
        $this->locked($application, null, fn () => null);
    }
}
