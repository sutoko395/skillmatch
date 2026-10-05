@extends('layouts.user')
@section('title', 'Koreksi Teks Event')
@section('content')
<x-page-header title="Koreksi Teks Event" description="Perbaiki judul/deskripsi tanpa mengubah jadwal, syarat seleksi, posisi atau paket. Perubahan dicatat dalam audit." />
<x-section-card><form method="POST" action="{{ route('organizer.events.correct-text', $event) }}" class="space-y-5">
@csrf @method('PATCH')
<x-form-field name="title" label="Judul" :value="$event->title" required />
<x-form-field name="description" label="Deskripsi" required><textarea id="description" name="description" rows="6" maxlength="10000" required class="mt-2 w-full rounded-lg border-slate-300">{{ old('description', $event->description) }}</textarea></x-form-field>
<x-button>Simpan koreksi teks</x-button>
</form></x-section-card>
@endsection
