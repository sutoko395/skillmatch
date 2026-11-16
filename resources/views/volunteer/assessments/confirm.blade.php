@extends('volunteer.layouts.sidebar')
@section('title', 'Mulai asesmen')
@section('page-class', 'volunteer-assessment-confirm')
@section('content')
<div class="vac-confirm mx-auto max-w-3xl space-y-6">
    <a class="volunteer-action volunteer-action--back" href="{{ route('volunteer.applications.show', $application) }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5m7 7-7-7 7-7" /></svg> Kembali</a>
    <x-page-header title="Mulai asesmen" :description="$application->event->title . ' · ' . $application->position->name" />
    <section class="vac-card" aria-labelledby="before-assessment-title">
        @include('volunteer.assessments.partials.confirm-summary')
        @include('volunteer.assessments.partials.confirm-rules')
        <x-input-error :messages="$errors->all()" class="mt-3" />
        @include('volunteer.assessments.partials.confirm-actions')
    </section>
</div>
@endsection
