<?php

namespace App\Http\Controllers\Volunteer;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\AssessmentAttempt;
use App\Services\AssessmentService;
use Illuminate\Http\Request;

class AssessmentController extends Controller
{
    public function show(Request $request, Application $application, AssessmentService $service)
    {
        $state = $service->inspect($application, $request->user());
        $attempt = $state['attempt'];
        if ($attempt && $attempt->status !== 'in_progress') {
            return redirect()->route('volunteer.applications.show', $application);
        }
        if (! $attempt) {
            return view('volunteer.assessments.confirm', ['application' => $application, ...$state]);
        }
        // Whitelist participant fields. Never hydrate answer keys into view or JSON data.
        $questions = $attempt->assessment->questions()->select(['id', 'assessment_id', 'sort_order', 'text'])
            ->with(['options' => fn ($query) => $query->select(['id', 'assessment_question_id', 'label', 'text'])])->get();

        return view('volunteer.assessments.show', ['application' => $application, 'attempt' => $attempt,
            'questions' => $questions, 'answers' => $attempt->answers()->pluck('assessment_option_id', 'assessment_question_id')->all()]);
    }

    public function start(Request $request, Application $application, AssessmentService $service)
    {
        $request->validate(['confirmed' => 'accepted']);
        $service->start($application, $request->user());

        return redirect()->route('volunteer.assessments.show', $application);
    }

    public function answers(Request $request, AssessmentAttempt $attempt, AssessmentService $service)
    {
        $request->mergeIfMissing(['answers' => []]);

        return $this->result($request, $service->save($attempt, $request->user(), $request->all()));
    }

    public function submit(Request $request, AssessmentAttempt $attempt, AssessmentService $service)
    {
        $request->mergeIfMissing(['answers' => []]);

        return $this->result($request, $service->save($attempt, $request->user(), $request->all(), true));
    }

    private function result(Request $request, AssessmentAttempt $attempt)
    {
        if ($request->expectsJson()) {
            return response()->json(['status' => $attempt->status, 'revision' => $attempt->revision,
                'expires_at' => $attempt->expires_at->toIso8601String(), 'server_now' => now()->toIso8601String()]);
        }

        return redirect()->route('volunteer.applications.show', $attempt->application_id);
    }
}
