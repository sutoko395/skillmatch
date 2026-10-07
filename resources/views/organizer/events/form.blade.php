@extends('organizer.layouts.sidebar')

@section('title', $event->exists ? 'Edit Event' : 'Buat Event')

@section('content')
<div class="w-full max-w-7xl mx-auto space-y-6">

    <div>
        <h1 class="text-2xl font-bold text-slate-800">
            {{ $event->exists ? 'Edit Event' : 'Buat Event' }}
        </h1>

        <p class="mt-1 text-sm text-slate-500">
            Tanggal dan jam diisi dalam WIB. Lengkapi informasi event sebelum melanjutkan konfigurasi.
        </p>
    </div>

    <x-section-card class="p-6 md:p-7">
        <form
            method="POST"
            action="{{ $event->exists ? route('organizer.events.update', $event) : route('organizer.events.store') }}"
            class="space-y-6"
            x-data="{ busy: false }"
            @submit="busy = true"
        >
            @csrf

            @if($event->exists)
                @method('PUT')
            @endif

            <x-form-field
                name="title"
                label="Judul kegiatan"
                :value="$event->title"
                required
                maxlength="255"
            />

            <x-form-field
                name="description"
                label="Deskripsi"
                required
            >
                <textarea
                    name="description"
                    rows="6"
                    required
                    maxlength="10000"
                    class="mt-2 w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                >{{ old('description', $event->description) }}</textarea>
            </x-form-field>

            <x-form-field
                name="location"
                label="Alamat/lokasi kegiatan"
                :value="$event->location"
                required
            />

            <div class="grid gap-6 md:grid-cols-2">
                <x-form-field
                    name="city"
                    label="Kota"
                    :value="$event->city"
                    required
                    maxlength="100"
                />

                <x-form-field
                    name="category_id"
                    label="Kategori"
                    required
                >
                    <select
                        name="category_id"
                        required
                        class="mt-2 w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="">Pilih Kategori</option>

                        @foreach($categories as $category)
                            <option
                                value="{{ $category->id }}"
                                @selected(old('category_id', $event->category_id) == $category->id)
                            >
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </x-form-field>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                @foreach([
                    'starts_at' => 'Awal kegiatan (WIB)',
                    'ends_at' => 'Akhir kegiatan (WIB)',
                    'registration_opens_at' => 'Pendaftaran dibuka (WIB)',
                    'registration_deadline' => 'Deadline pendaftaran (WIB)',
                ] as $field => $label)
                    <x-form-field
                        name="{{ $field }}"
                        label="{{ $label }}"
                        type="datetime-local"
                        :value="$event->$field?->setTimezone('Asia/Jakarta')->format('Y-m-d\TH:i')"
                        required
                    />
                @endforeach
            </div>

            <div class="flex flex-wrap items-center gap-3 border-t border-slate-100 pt-6">
                <x-button
                    type="submit"
                    x-bind:disabled="busy"
                >
                    <span x-text="busy ? 'Menyimpan...' : 'Simpan draft'">
                        Simpan draft
                    </span>
                </x-button>

                <a
                    href="{{ route('organizer.events.index') }}"
                    class="text-sm font-semibold text-indigo-700 hover:underline"
                >
                    Kembali
                </a>
            </div>
        </form>
    </x-section-card>

</div>
@endsection