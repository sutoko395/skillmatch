@extends('layouts.public')
@section('title', 'Jelajahi Event')
@section('content')
<header class="eb-catalog-heading"><p class="eb-eyebrow">TEMUKAN RUANG KONTRIBUSIMU</p><h1 class="eb-display">Ada kegiatan.<br>Ada tempat untukmu.</h1><p>Temukan kegiatan yang sesuai dengan keterampilan, lokasi dan waktu Anda.</p></header>
<form method="GET" action="{{ route('events.index') }}" class="eb-catalog-filters">
    <x-form-field name="search" label="Judul event" :value="request('search')" maxlength="100" />
    @foreach(['city_id' => ['Kota', $cities], 'category_id' => ['Kategori', $categories]] as $field => $options)
    <x-form-field :name="$field" :label="$options[0]"><select id="{{ $field }}" name="{{ $field }}" class="mt-2 w-full rounded-lg border-slate-300"><option value="">Semua</option>@foreach($options[1] as $option)<option value="{{ $option->id }}" @selected(request($field) == $option->id)>{{ $option->name }}</option>@endforeach</select></x-form-field>
    @endforeach
    <x-form-field name="date" label="Tanggal (WIB)" type="date" :value="request('date')" />
    <div class="eb-filter-actions"><button class="eb-button" type="submit">Cari →</button><a class="eb-text-link" href="{{ route('events.index') }}">Reset</a></div>
</form>
<p class="eb-catalog-count" role="status">{{ $events->total() }} event sesuai pencarian.</p>
<div class="eb-event-grid">
    @forelse($events as $event)<x-event-card :event="$event" />
    @empty<div class="eb-empty"><x-empty-state title="Belum ada event yang sesuai" description="Coba ubah filter atau kunjungi kembali nanti." /></div>@endforelse
</div>
<div class="eb-pagination">{{ $events->links() }}</div>
@endsection
