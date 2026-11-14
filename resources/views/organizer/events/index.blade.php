@extends('organizer.layouts.sidebar')

@section('title', 'Kegiatan Saya')

@section('content')
<div
    class="w-full max-w-7xl mx-auto space-y-6"
    x-data="{ openCreateEvent: @js($errors->any()), busy: false }"
>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">
                Kegiatan Saya
            </h1>

            <p class="mt-1 text-sm text-slate-500">
                Atur kegiatan, pantau status, dan lanjutkan persiapan rekrutmen.
            </p>
        </div>

        <button
            type="button"
            @click="openCreateEvent = true"
            class="organizer-create-event inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-6 py-3 text-base font-semibold text-white transition hover:bg-indigo-700"
        >
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="M12 5v14M5 12h14" /></svg>
            <span>Buat Kegiatan</span>
        </button>
    </div>

    <form method="GET" action="{{ route('organizer.events.index') }}" class="organizer-event-filters" role="search" aria-label="Cari dan filter kegiatan">
        <div class="organizer-event-search">
            <label for="event-search">Cari kegiatan</label>
            <input id="event-search" type="search" name="search" value="{{ $search }}" maxlength="255" placeholder="Cari nama kegiatan...">
            @error('search')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="event-status">Status moderasi</label>
            <select id="event-status" name="status">
                <option value="">Semua status moderasi</option>
                @foreach(['draft' => 'Draf', 'pending' => 'Menunggu moderasi', 'approved' => 'Disetujui', 'rejected' => 'Ditolak'] as $value => $label)
                    <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('status')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <button type="submit" class="organizer-action-button">Terapkan</button>
        @if($search !== '' || $status !== '')
            <a href="{{ route('organizer.events.index') }}" class="organizer-event-filter-reset">Reset</a>
        @endif
    </form>

    <section class="organizer-event-list" aria-label="Daftar kegiatan">
        @forelse($events as $event)
            <article class="organizer-event-row">
                <x-event-cover :event="$event" class="organizer-event-thumbnail" />
                <div class="min-w-0">
                    <a href="{{ route('organizer.events.show', $event) }}" class="organizer-event-title">{{ $event->title }}</a>
                    <div class="organizer-event-metadata">
                        <div class="organizer-event-schedule">
                            <span class="organizer-event-meta-item">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 3v4m8-4v4M4 10h16M6 5h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z" /></svg>
                                <span><span class="sr-only">Tanggal kegiatan: </span>{{ $event->starts_at?->setTimezone('Asia/Jakarta')->format('d/m/Y') ?? 'Jadwal belum diisi' }}</span>
                            </span>
                            @if($event->starts_at)
                                <span class="organizer-event-meta-item">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><circle cx="12" cy="12" r="9" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 2" /></svg>
                                    <span><span class="sr-only">Jam kegiatan: </span>{{ $event->starts_at->setTimezone('Asia/Jakarta')->format('H:i') }} WIB</span>
                                </span>
                            @endif
                        </div>
                        <div class="organizer-event-meta-item organizer-event-location">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 10c0 5-7 11-7 11S5 15 5 10a7 7 0 1 1 14 0Z" /><circle cx="12" cy="10" r="2.5" /></svg>
                            <span><span class="sr-only">Lokasi kegiatan: </span>{{ $event->location }}{{ $event->location && $event->city ? ' · ' : '' }}{{ $event->city }}</span>
                        </div>
                    </div>
                    <div class="organizer-event-badges mt-3 flex flex-wrap gap-2">
                        <x-organizer-status :status="$event->status" context="Moderasi" class="organizer-list-status--green" />
                        <x-organizer-status :status="$event->publication_status" context="Publikasi" class="organizer-list-status--blue" />
                        <x-organizer-status :status="$event->lifecycle_status" context="Pelaksanaan" class="organizer-list-status--yellow" />
                    </div>
                </div>
                <a href="{{ route('organizer.events.show', $event) }}" class="organizer-action-button organizer-event-manage" aria-label="Kelola kegiatan {{ $event->title }}">Kelola kegiatan</a>
            </article>
        @empty
            <div class="p-6">
                @if($search !== '' || $status !== '')
                    <x-empty-state title="Kegiatan tidak ditemukan" description="Coba kata kunci atau status moderasi lain." />
                @else
                    <x-empty-state title="Belum ada kegiatan" description="Buat kegiatan pertama Anda untuk mulai menyiapkan rekrutmen relawan." />
                @endif
            </div>
        @endforelse
    </section>

    @if($events->hasPages())
        <div>
            {{ $events->links() }}
        </div>
    @endif

    <div
        x-show="openCreateEvent"
        x-cloak
        x-transition.opacity
        class="fixed inset-0 z-50 overflow-y-auto"
    >
        <div
            class="fixed inset-0 bg-slate-900/50"
            @click="openCreateEvent = false"
        ></div>

        <div class="relative flex min-h-full items-center justify-center p-4">
            <div
                class="relative w-full max-w-3xl rounded-2xl bg-white shadow-2xl"
                @click.stop
                x-show="openCreateEvent"
                x-transition
            >
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
                    <div>
                        <h2 class="text-xl font-bold text-slate-800">
                            Buat Kegiatan
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Lengkapi informasi dasar kegiatan.
                        </p>
                    </div>

                    <button
                        type="button"
                        @click="openCreateEvent = false"
                        class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                    >
                        ✕
                    </button>
                </div>

                <form
                    method="POST"
                    enctype="multipart/form-data"
                    action="{{ route('organizer.events.store') }}"
                    class="space-y-5 p-6"
                    x-data="eventPackageForm(@js($packages), @js(old('package_id', '')), @js(old('registration_opens_at', '')), @js(old('registration_deadline', '')))"
                    @submit="submit($event)"
                >
                    @csrf

                    <x-event-package-selector :packages="$packages" />
                    <h2 class="text-lg font-semibold text-slate-800">2. Informasi dan jadwal kegiatan</h2>

                    <x-form-field
                        name="title"
                        label="Judul kegiatan"
                        required
                        maxlength="255"
                    />

                    <x-event-cover-upload />

                    <x-form-field
                        name="description"
                        label="Deskripsi"
                        required
                    >
                        <textarea
                            id="create_description"
                            name="description"
                            rows="4"
                            required
                            maxlength="10000"
                            class="mt-2 w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >{{ old('description') }}</textarea>
                    </x-form-field>

                    <x-form-field
                        name="location"
                        label="Alamat/lokasi kegiatan"
                        required
                    />

                    <div class="grid gap-5 md:grid-cols-2">
                        <x-event-city-field :cities="$cities" />

                        <x-form-field
                            name="category_id"
                            label="Kategori"
                            required
                        >
                            <select
                                id="create_category_id"
                                name="category_id"
                                required
                                class="mt-2 w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="">Pilih Kategori</option>

                                @foreach($categories as $category)
                                    <option
                                        value="{{ $category->id }}"
                                        @selected(old('category_id') == $category->id)
                                    >
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </x-form-field>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        <x-form-field
                            name="starts_at"
                            label="Awal kegiatan (WIB)"
                            type="datetime-local"
                            required
                        />

                        <x-form-field
                            name="ends_at"
                            label="Akhir kegiatan (WIB)"
                            type="datetime-local"
                            required
                        />

                        <x-form-field
                            name="registration_opens_at"
                            label="Pendaftaran dibuka (WIB)"
                            type="datetime-local"
                            x-model="opens"
                            required
                        />

                        <x-form-field
                            name="registration_deadline"
                            label="Deadline pendaftaran (WIB)"
                            type="datetime-local"
                            x-model="deadline"
                            required
                        />
                    </div>

                    <x-event-recruitment-fields />

                    <x-event-package-warning />

                    <div class="flex justify-end gap-3 border-t border-slate-100 pt-5">
                        <button
                            type="button"
                            @click="openCreateEvent = false"
                            class="rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100"
                        >
                            Batal
                        </button>

                        <x-button
                            type="submit"
                            x-bind:disabled="busy || exceedsLimit || !selectedPackage"
                        >
                            <span x-text="busy ? 'Menyimpan...' : 'Simpan Draft'">
                                Simpan Draft
                            </span>
                        </x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
