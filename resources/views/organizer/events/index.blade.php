@extends('organizer.layouts.sidebar')

@section('title', 'Event Saya')

@section('content')
<div
    class="w-full max-w-7xl mx-auto space-y-6"
    x-data="{ openCreateEvent: @js($errors->any()), busy: false }"
>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">
                Event Saya
            </h1>

            <p class="mt-1 text-sm text-slate-500">
                Kelola draft, posisi, paket, dan publikasi kegiatan Anda.
            </p>
        </div>

        <button
            type="button"
            @click="openCreateEvent = true"
            class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700"
        >
            Buat Event
        </button>
    </div>

    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
        @forelse($events as $event)
            <x-section-card class="p-6">
                <div class="flex h-full flex-col">
                    <div>
                        <a
                            href="{{ route('organizer.events.show', $event) }}"
                            class="break-words text-lg font-bold text-indigo-700 hover:text-indigo-800"
                        >
                            {{ $event->title }}
                        </a>

                        <p class="mt-3 text-sm text-slate-500">
                            {{ $event->starts_at?->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }}
                            WIB
                        </p>

                        <p class="mt-1 text-sm text-slate-500">
                            {{ $event->city }}
                        </p>

                        <p class="mt-1 text-sm text-slate-500">
                            {{ $event->location }}
                        </p>
                    </div>

                    <div class="mt-5 flex flex-wrap gap-2">
                        <span class="rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">
                            {{ $event->status }}
                        </span>

                        <span class="rounded-lg bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-600">
                            {{ $event->publication_status }}
                        </span>

                        <span class="rounded-lg bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-600">
                            {{ $event->lifecycle_status }}
                        </span>
                    </div>

                    <div class="mt-6 border-t border-slate-100 pt-4">
                        <a
                            href="{{ route('organizer.events.show', $event) }}"
                            class="text-sm font-semibold text-indigo-700 hover:underline"
                        >
                            Kelola event →
                        </a>
                    </div>
                </div>
            </x-section-card>
        @empty
            <div class="md:col-span-2 xl:col-span-3">
                <x-empty-state
                    title="Belum ada event"
                    description="Mulai dengan membuat draft kegiatan."
                />
            </div>
        @endforelse
    </div>

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
                            Buat Event
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
                    action="{{ route('organizer.events.store') }}"
                    class="space-y-5 p-6"
                    x-data="eventPackageForm(@js($packages), @js(old('package_id', '')), @js(old('registration_opens_at', '')), @js(old('registration_deadline', '')))"
                    @submit="submit($event)"
                >
                    @csrf

                    <x-event-package-selector :packages="$packages" />
                    <h2 class="text-lg font-semibold text-slate-800">2. Informasi dan jadwal event</h2>

                    <x-form-field
                        name="title"
                        label="Judul kegiatan"
                        required
                        maxlength="255"
                    />

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
