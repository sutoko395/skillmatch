@extends('layouts.public')
@section('title', $event->title)
@section('content')
@include('events.partials.detail', ['catalogRoute' => 'events.index'])
@endsection