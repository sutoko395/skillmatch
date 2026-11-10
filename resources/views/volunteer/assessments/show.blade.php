@extends('volunteer.layouts.sidebar')
@section('title', 'Kerjakan assessment')
@section('page-class', 'volunteer-assessment-work')
@section('content')
<div class="vaa-attempt mx-auto max-w-6xl space-y-6" x-data="assessmentAttempt(@js([
    'saveUrl' => route('volunteer.assessments.answers', $attempt),
    'submitUrl' => route('volunteer.assessments.submit', $attempt),
    'returnUrl' => route('volunteer.applications.show', $application),
    'revision' => $attempt->revision, 'expiresAt' => $attempt->expires_at->toIso8601String(),
    'serverNow' => now()->toIso8601String(),
    'questionIds' => $questions->pluck('id')->values()->all(),
    'answeredIds' => array_keys(array_filter($answers, fn ($answer) => $answer !== null)),
]))">
    <x-page-header title="Assessment" :description="$application->event->title . ' · ' . $application->position->name" />
    @include('volunteer.assessments.partials.attempt-status')
    <form x-ref="answers" method="POST" action="{{ route('volunteer.assessments.submit', $attempt) }}" @change="answerChanged()" @submit.prevent="submit()" class="space-y-6">
        @csrf
        <input type="hidden" name="revision" value="{{ $attempt->revision }}" :value="revision" />
        <div class="vaa-grid grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_15rem]">
            @include('volunteer.assessments.partials.question-navigation')
            @include('volunteer.assessments.partials.question-panel')
        </div>
        @include('volunteer.assessments.partials.attempt-actions')
    </form>
</div>
@endsection
