@extends('layouts.user')
@section('title','Status Pembayaran')
@section('content')
<x-page-header title="Status Pembayaran" description="Sandbox - simulasi pembayaran. Status diperiksa dari server, bukan dari redirect checkout." />
@include('orders.detail', ['admin'=>false])
@endsection
