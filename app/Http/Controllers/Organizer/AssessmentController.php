<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\EventPosition;
use App\Services\EventService;
use App\Services\OrganizerAssessmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AssessmentController extends Controller
{
    public function edit(EventPosition $position)
    {
        Gate::authorize('update', $position->event);
        $assessment = $position->assessments()->with('questions.options')->first();
        $editable = false;
        try {
            app(EventService::class)->editable($position->event);
            $editable = ! $assessment?->is_published;
        } catch (ValidationException) {
            // Read-only view for frozen event configurations.
        }
        $questions = $assessment?->questions->map(fn ($question) => [
            'text' => $question->text ?? '', 'options' => $question->options->pluck('text', 'label')->all(),
            'correct' => $question->options->firstWhere('is_correct', true)?->label ?? '',
        ])->all() ?? [];

        return view('organizer.assessments.edit', compact('position', 'assessment', 'editable', 'questions'));
    }

    public function update(Request $request, EventPosition $position, OrganizerAssessmentService $service)
    {
        Gate::authorize('update', $position->event);
        $action = Validator::make($request->all(), ['action' => 'required|in:draft,preview,publish'])->validate()['action'];
        $input = $request->all();
        $input['questions'] ??= [];
        if ($action === 'preview') {
            app(EventService::class)->editable($position->event);
            abort_if($position->assessments()->where('is_published', true)->exists(), 409);
            $data = $service->validateInput($input, true);

            return view('organizer.assessments.confirm', compact('position', 'data'));
        }
        if ($action === 'publish') {
            Validator::make($input, ['confirmed' => 'accepted'])->validate();
        }
        $service->save($position, $request->user(), $input, $action === 'publish');

        return redirect()->route('organizer.assessments.edit', $position)
            ->with('success', $action === 'publish' ? 'Assessment dipublikasikan dan dikunci.' : 'Draft assessment tersimpan.');
    }
}
