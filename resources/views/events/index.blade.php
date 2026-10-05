@extends('layouts.public')
@section('title','Jelajahi Event')
@section('content')
<x-page-header title="Jelajahi Event" description="Temukan kegiatan yang sesuai dengan keterampilan, lokasi dan waktu Anda." />
<x-section-card><form method="GET" action="{{ route('events.index') }}" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
<x-form-field name="search" label="Judul event" :value="request('search')" maxlength="100" />
@foreach(['city_id'=>['Kota',$cities],'category_id'=>['Kategori',$categories]] as $field=>$options)
<x-form-field :name="$field" :label="$options[0]"><select id="{{ $field }}" name="{{ $field }}" class="mt-2 w-full rounded-lg border-slate-300"><option value="">Semua</option>@foreach($options[1] as $option)<option value="{{ $option->id }}" @selected(request($field)==$option->id)>{{ $option->name }}</option>@endforeach</select></x-form-field>
@endforeach
<x-form-field name="date" label="Tanggal (WIB)" type="date" :value="request('date')" />
<div class="flex items-end gap-3"><x-button>Cari</x-button><a class="text-indigo-700" href="{{ route('events.index') }}">Reset</a></div>
</form></x-section-card>
<p class="mt-6 text-sm text-slate-600">{{ $events->total() }} event sesuai pencarian.</p>
<div class="mt-6 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
@forelse($events as $event)<x-event-card :event="$event" />
@empty<x-empty-state title="Belum ada event yang sesuai" description="Coba ubah filter atau kunjungi kembali nanti." />@endforelse</div><div class="mt-6">{{ $events->links() }}</div>
@endsection
