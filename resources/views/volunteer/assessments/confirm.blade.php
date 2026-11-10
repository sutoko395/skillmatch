@extends('volunteer.layouts.sidebar')
@section('title', 'Mulai assessment')
@section('page-class', 'volunteer-assessment-confirm')
@section('content')
<div class="vac-confirm mx-auto max-w-3xl space-y-6">
    <x-page-header title="Mulai assessment" :description="$application->event->title . ' · ' . $application->position->name" />
    <section class="vac-card" aria-labelledby="before-assessment-title">
        @include('volunteer.assessments.partials.confirm-summary')
        @include('volunteer.assessments.partials.confirm-rules')
        <x-input-error :messages="$errors->all()" class="mt-3" />
        @include('volunteer.assessments.partials.confirm-actions')
    </section>
</div>
@endsection
