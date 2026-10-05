@extends('admin.layouts.sidebar')
@section('title','Detail Transaksi')
@section('content')
@include('components.flash')
<x-page-header title="Detail Transaksi" description="Sandbox - simulasi pembayaran" />
@include('orders.detail',['admin'=>true])
@endsection
