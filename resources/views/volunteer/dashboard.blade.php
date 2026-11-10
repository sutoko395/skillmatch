@extends('volunteer.layouts.sidebar')

@section('title', 'Aktivitas Volunteer')
@section('page-class', 'volunteer-activity')

@section('content')
<div class="va-dashboard mx-auto max-w-7xl space-y-6">
    @include('volunteer.activity.welcome')
    @include('volunteer.activity.stats')
    <div class="va-profile-grid grid gap-6 lg:grid-cols-3">
        @include('volunteer.activity.profile-readiness')
        @include('volunteer.activity.skills')
    </div>
    @include('volunteer.activity.availability')
    @include('volunteer.activity.applications')
    {{ $applications->links() }}
    @include('volunteer.activity.next-steps')
    @include('notifications.summary')
</div>
@endsection
