@extends('layouts.user')
@section('title', 'Form Event')
@section('content')
<x-page-header :title="$event->exists ? 'Edit Event' : 'Buat Event'" description="Tanggal dan jam diisi dalam WIB. Lengkapi posisi dan pilih paket setelah menyimpan draft." />
<x-section-card><form class="space-y-5" method="POST" action="{{ $event->exists ? route('organizer.events.update',$event) : route('organizer.events.store') }}" x-data="{busy:false}" @submit="busy=true">
@csrf @if($event->exists) @method('PUT') @endif
<x-form-field name="title" label="Judul kegiatan" :value="$event->title" required maxlength="255" />
<x-form-field name="description" label="Deskripsi" required><textarea id="description" name="description" rows="5" required maxlength="10000" class="mt-2 w-full rounded-lg border-slate-300">{{ old('description',$event->description) }}</textarea></x-form-field>
<x-form-field name="location" label="Alamat/lokasi kegiatan" :value="$event->location" required />
<div class="grid gap-5 sm:grid-cols-2">
@foreach(['city_id'=>['Kota',$cities], 'category_id'=>['Kategori',$categories]] as $field=>$options)
<x-form-field :name="$field" :label="$options[0]" required><select id="{{ $field }}" name="{{ $field }}" required class="mt-2 w-full rounded-lg border-slate-300"><option value="">Pilih {{ $options[0] }}</option>@foreach($options[1] as $option)<option value="{{ $option->id }}" @selected(old($field,$event->$field)==$option->id)>{{ $option->name }}</option>@endforeach</select></x-form-field>
@endforeach
@foreach(['starts_at'=>'Awal kegiatan (WIB)','ends_at'=>'Akhir kegiatan (WIB)','registration_opens_at'=>'Pendaftaran dibuka (WIB)','registration_deadline'=>'Deadline pendaftaran (WIB)'] as $field=>$label)
<x-form-field :name="$field" :label="$label" type="datetime-local" :value="$event->$field?->setTimezone('Asia/Jakarta')->format('Y-m-d\TH:i')" required />
@endforeach
</div><x-button type="submit" x-bind:disabled="busy"><span x-text="busy ? 'Menyimpan...' : 'Simpan draft'">Simpan draft</span></x-button>
<a class="ml-4 text-indigo-700" href="{{ route('organizer.events.index') }}">Kembali</a>
</form></x-section-card>
@endsection
