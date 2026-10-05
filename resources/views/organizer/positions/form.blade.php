@extends('layouts.user')
@section('title', 'Konfigurasi Posisi')
@section('content')
<x-page-header title="Konfigurasi Posisi" :description="$event->title" />
@php
$initialSkills=old('skills',$position->positionSkills->map(fn($s)=>array_merge($s->only(['skill_id','minimum_level']),['is_required'=>(int)$s->is_required]))->all());
$initialSchedules=old('schedules',$position->schedules->map(fn($s)=>['starts_at'=>$s->starts_at->setTimezone('Asia/Jakarta')->format('Y-m-d\TH:i'),'ends_at'=>$s->ends_at->setTimezone('Asia/Jakarta')->format('Y-m-d\TH:i')])->all());
$initialRequirements=old('requirements',$position->requirements->map(fn($r)=>array_merge($r->only(['name','description','kind','document_type']),['is_required'=>(int)$r->is_required]))->all());
@endphp
<x-section-card><form class="space-y-6" method="POST" action="{{ $position->exists ? route('organizer.events.positions.update',[$event,$position]) : route('organizer.events.positions.store',$event) }}" x-data="{busy:false, skills: @js($initialSkills), schedules: @js($initialSchedules), requirements: @js($initialRequirements)}" @submit="busy=true">
@csrf @if($position->exists) @method('PUT') @endif
<x-form-field name="name" label="Nama posisi" :value="$position->name" required />
<x-form-field name="description" label="Deskripsi tugas" :value="$position->description" />
<x-form-field name="quota" label="Kuota" type="number" :value="$position->quota ?? 1" min="1" max="100000" required />
@foreach(['required_full_availability'=>'Wajib tersedia sepanjang jadwal', 'required_same_city'=>'Wajib berdomisili di kota event'] as $field=>$label)
<x-form-field :name="$field" :label="$label"><select id="{{ $field }}" name="{{ $field }}" class="w-full rounded-lg border-slate-300"><option value="0" @selected(!old($field,$position->$field))>Tidak wajib</option><option value="1" @selected(old($field,$position->$field))>Wajib</option></select></x-form-field>
@endforeach
<fieldset class="space-y-3"><legend class="font-semibold">Skill dan level minimum</legend>
<template x-for="(skill,i) in skills" :key="i"><div class="grid gap-3 rounded-lg bg-slate-50 p-4 sm:grid-cols-4">
<label>Skill<select class="mt-1 w-full rounded-lg border-slate-300" :name="`skills[${i}][skill_id]`" x-model="skill.skill_id" required><option value="">Pilih skill</option>@foreach($skills as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach</select></label>
<label>Level<select class="mt-1 w-full rounded-lg border-slate-300" :name="`skills[${i}][minimum_level]`" x-model="skill.minimum_level">@foreach(['beginner','intermediate','advanced','expert'] as $level)<option>{{ $level }}</option>@endforeach</select></label>
<label>Syarat<select class="mt-1 w-full rounded-lg border-slate-300" :name="`skills[${i}][is_required]`" x-model="skill.is_required"><option value="1">Wajib</option><option value="0">Preferensi</option></select></label><button type="button" class="text-rose-700" @click="skills.splice(i,1)">Hapus skill</button>
</div></template><button type="button" class="text-indigo-700 underline" @click="skills.push({skill_id:'',minimum_level:'beginner',is_required:1})">Tambah skill</button>
</fieldset>
<fieldset class="space-y-3"><legend class="font-semibold">Jadwal posisi (WIB)</legend>
<template x-for="(slot,i) in schedules" :key="i"><div class="grid gap-3 rounded-lg bg-slate-50 p-4 sm:grid-cols-3"><label>Mulai<input class="mt-1 w-full rounded-lg border-slate-300" type="datetime-local" :name="`schedules[${i}][starts_at]`" x-model="slot.starts_at" required></label><label>Selesai<input class="mt-1 w-full rounded-lg border-slate-300" type="datetime-local" :name="`schedules[${i}][ends_at]`" x-model="slot.ends_at" required></label><button type="button" class="text-rose-700" @click="schedules.splice(i,1)">Hapus jadwal</button></div></template>
<button type="button" class="text-indigo-700 underline" @click="schedules.push({starts_at:'',ends_at:''})">Tambah jadwal</button>
</fieldset>
<fieldset class="space-y-3"><legend class="font-semibold">Persyaratan tambahan</legend>
<template x-for="(req,i) in requirements" :key="i"><div class="grid gap-3 rounded-lg bg-slate-50 p-4 sm:grid-cols-2"><label>Nama syarat<input class="mt-1 w-full rounded-lg border-slate-300" :name="`requirements[${i}][name]`" x-model="req.name" required></label><label>Keterangan<input class="mt-1 w-full rounded-lg border-slate-300" :name="`requirements[${i}][description]`" x-model="req.description"></label><label>Jenis<select class="mt-1 w-full rounded-lg border-slate-300" :name="`requirements[${i}][kind]`" x-model="req.kind"><option value="manual">Tinjauan manual</option><option value="document">Dokumen</option></select></label><label>Tipe dokumen<select class="mt-1 w-full rounded-lg border-slate-300" :name="`requirements[${i}][document_type]`" x-model="req.document_type"><option value="">Tidak berlaku</option><option value="cv">CV (PDF)</option><option value="supporting">Pendukung</option></select></label><label>Syarat<select class="mt-1 w-full rounded-lg border-slate-300" :name="`requirements[${i}][is_required]`" x-model="req.is_required"><option value="1">Wajib</option><option value="0">Preferensi</option></select></label><button type="button" class="text-rose-700" @click="requirements.splice(i,1)">Hapus syarat</button></div></template>
<button type="button" class="text-indigo-700 underline" @click="requirements.push({name:'',description:'',kind:'manual',document_type:'',is_required:1})">Tambah syarat</button>
<p class="text-sm text-slate-600">Syarat manual tetap ditinjau manusia; isi CV tidak dinilai otomatis.</p></fieldset>
<noscript>Aktifkan JavaScript untuk menyunting daftar skill, jadwal dan syarat.</noscript>
<x-button type="submit" x-bind:disabled="busy"><span x-text="busy ? 'Menyimpan...' : 'Simpan posisi'">Simpan posisi</span></x-button>
<a class="ml-4 text-indigo-700" href="{{ route('organizer.events.show',$event) }}">Kembali</a>
</form></x-section-card>
@endsection
