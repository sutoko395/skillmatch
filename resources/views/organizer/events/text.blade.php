@extends('organizer.layouts.sidebar')

@section('title', 'Koreksi Teks Event')

@section('content')
<div class="w-full max-w-7xl mx-auto space-y-6">

    <div>
        <h1 class="text-2xl font-bold text-slate-800">
            Koreksi Teks Event
        </h1>

        <p class="mt-1 text-sm text-slate-500">
            Perbaiki judul dan deskripsi tanpa mengubah jadwal, syarat seleksi, posisi, atau paket.
        </p>
    </div>

    <x-section-card class="p-6 md:p-7">
        <form
            method="POST"
            action="{{ route('organizer.events.correct-text', $event) }}"
            class="space-y-6"
        >
            @csrf
            @method('PATCH')

            <x-form-field
                name="title"
                label="Judul"
                :value="$event->title"
                required
            />

            <x-form-field
                name="description"
                label="Deskripsi"
                required
            >
                <textarea
                    id="description"
                    name="description"
                    rows="7"
                    maxlength="10000"
                    required
                    class="mt-2 w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                >{{ old('description', $event->description) }}</textarea>
            </x-form-field>

            <div class="flex flex-wrap items-center gap-3 border-t border-slate-100 pt-6">
                <x-button type="submit">
                    Simpan Koreksi
                </x-button>
                <a href="{{ route('organizer.events.show', $event) }}#ringkasan" class="organizer-secondary-button organizer-position-delete">Batal</a>
            </div>
        </form>
    </x-section-card>

</div>
@endsection