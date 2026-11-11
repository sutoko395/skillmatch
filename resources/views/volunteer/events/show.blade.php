@extends('volunteer.layouts.sidebar')
@section('title', $event->title)
@section('page-class', 'volunteer-applications')
@section('content')
@include('events.partials.detail', ['catalogRoute' => 'volunteer.events.index'])
@endsection
