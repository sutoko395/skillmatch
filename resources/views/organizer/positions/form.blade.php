@extends('organizer.layouts.sidebar')

@section('title', 'Konfigurasi Posisi')

@section('content')
<div class="mx-auto max-w-5xl">
    <x-page-header
        title="Konfigurasi Posisi"
        :description="$event->title"
    />

    @php
        $eventStart = $event->starts_at->copy()->setTimezone('Asia/Jakarta')->format('Y-m-d\TH:i');
        $eventEnd = $event->ends_at->copy()->setTimezone('Asia/Jakarta')->format('Y-m-d\TH:i');
        $singleDay = substr($eventStart, 0, 10) === substr($eventEnd, 0, 10);
        $followsEvent = (bool) old('follows_event_schedule', $position->exists ? $position->follows_event_schedule : true);
        $initialSkills = old(
            'skills',
            $position->positionSkills
                ->map(fn ($skill) => array_merge(
                    $skill->only(['skill_id', 'minimum_level']),
                    ['is_required' => (int) $skill->is_required]
                ))
                ->all()
        );

        $initialSchedules = old(
            'schedules',
            $position->schedules
                ->map(fn ($schedule) => [
                    'starts_at' => $schedule->starts_at
                        ->setTimezone('Asia/Jakarta')
                        ->format('Y-m-d\TH:i'),
                    'ends_at' => $schedule->ends_at
                        ->setTimezone('Asia/Jakarta')
                        ->format('Y-m-d\TH:i'),
                ])
                ->all()
        );

        $initialRequirements = old(
            'requirements',
            $position->requirements
                ->map(fn ($requirement) => array_merge(
                    $requirement->only([
                        'name',
                        'description',
                        'kind',
                        'document_type',
                    ]),
                    ['is_required' => (int) $requirement->is_required]
                ))
                ->all()
        );
        if (! $initialSchedules) {
            $initialSchedules = [['starts_at' => $eventStart, 'ends_at' => $eventEnd]];
        }
    @endphp

    <x-section-card>
        <form
            method="POST"
            action="{{ $position->exists
                ? route('organizer.events.positions.update', [$event, $position])
                : route('organizer.events.positions.store', $event) }}"
            class="space-y-8"
            x-data="{
                busy: false,
                followsEvent: @js($followsEvent),
                eventStart: @js($eventStart),
                eventEnd: @js($eventEnd),
                skills: @js($initialSkills),
                schedules: @js($initialSchedules),
                requirements: @js($initialRequirements)
            }"
            @submit="busy = true"
        >
            @csrf

            @if($position->exists)
                @method('PUT')
            @endif

            <div class="grid gap-6 md:grid-cols-2">
                <x-form-field
                    name="name"
                    label="Nama posisi"
                    :value="$position->name"
                    required
                />

                <x-form-field
                    name="quota"
                    label="Kuota"
                    type="number"
                    :value="$position->quota ?? 1"
                    min="1"
                    max="100000"
                    required
                />
            </div>

            <x-form-field
                name="description"
                label="Deskripsi tugas"
                :value="$position->description"
            />

            <div class="grid gap-6 md:grid-cols-2">
                @foreach([
                    'required_full_availability' => 'Wajib tersedia sepanjang jadwal',
                    'required_same_city' => 'Wajib berdomisili di kota event',
                ] as $field => $label)
                    <x-form-field
                        :name="$field"
                        :label="$label"
                    >
                        <select
                            id="{{ $field }}"
                            name="{{ $field }}"
                            class="w-full rounded-xl border-slate-300"
                        >
                            <option value="0" @selected(! old($field, $position->$field))>
                                Tidak wajib
                            </option>
                            <option value="1" @selected(old($field, $position->$field))>
                                Wajib
                            </option>
                        </select>
                    </x-form-field>
                @endforeach
            </div>

            <fieldset class="space-y-4">
                <legend class="text-base font-semibold text-slate-900">
                    Skill dan level minimum
                </legend>

                <template x-for="(skill, i) in skills" :key="i">
                    <div class="grid gap-4 rounded-xl bg-slate-50 p-4 md:grid-cols-4">
                        <label class="text-sm font-medium text-slate-700">
                            Skill

                            <select
                                class="mt-2 w-full rounded-xl border-slate-300"
                                :name="`skills[${i}][skill_id]`"
                                x-model="skill.skill_id"
                                required
                            >
                                <option value="">Pilih skill</option>

                                @foreach($skills as $skillOption)
                                    <option value="{{ $skillOption->id }}">
                                        {{ $skillOption->name }}
                                    </option>
                                @endforeach
                            </select>
                        </label>

                        <label class="text-sm font-medium text-slate-700">
                            Level

                            <select
                                class="mt-2 w-full rounded-xl border-slate-300"
                                :name="`skills[${i}][minimum_level]`"
                                x-model="skill.minimum_level"
                            >
                                @foreach(['beginner', 'intermediate', 'advanced', 'expert'] as $level)
                                    <option value="{{ $level }}">
                                        {{ $level }}
                                    </option>
                                @endforeach
                            </select>
                        </label>

                        <label class="text-sm font-medium text-slate-700">
                            Syarat

                            <select
                                class="mt-2 w-full rounded-xl border-slate-300"
                                :name="`skills[${i}][is_required]`"
                                x-model="skill.is_required"
                            >
                                <option value="1">Wajib</option>
                                <option value="0">Preferensi</option>
                            </select>
                        </label>

                        <div class="flex items-end">
                            <button
                                type="button"
                                class="text-sm font-semibold text-rose-600 hover:text-rose-700"
                                @click="skills.splice(i, 1)"
                            >
                                Hapus skill
                            </button>
                        </div>
                    </div>
                </template>

                <button
                    type="button"
                    class="text-sm font-semibold text-indigo-700 hover:underline"
                    @click="skills.push({
                        skill_id: '',
                        minimum_level: 'beginner',
                        is_required: 1
                    })"
                >
                    + Tambah skill
                </button>
            </fieldset>

            <fieldset class="space-y-4">
                <legend class="text-base font-semibold text-slate-900">
                    Jadwal posisi (WIB)
                </legend>

                <div class="rounded-xl border border-indigo-100 bg-indigo-50 p-4 text-sm text-indigo-950">
                    <p class="font-semibold">Jadwal event</p>
                    <p class="mt-1">{{ $event->starts_at->copy()->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }} – {{ $event->ends_at->copy()->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</p>
                </div>
                <div class="space-y-3">
                    <label class="flex items-center gap-3 text-sm font-medium">
                        <input type="radio" name="follows_event_schedule" value="1" @checked($followsEvent) @change="followsEvent = true" class="text-indigo-600 focus:ring-indigo-500">
                        Ikuti jadwal event
                    </label>
                    <p class="pl-7 text-sm text-slate-600">Posisi bertugas sepanjang jadwal event dan mengikuti perubahan jadwal event draft.</p>
                    <label class="flex items-center gap-3 text-sm font-medium">
                        <input type="radio" name="follows_event_schedule" value="0" @checked(! $followsEvent) @change="followsEvent = false" class="text-indigo-600 focus:ring-indigo-500">
                        Atur jadwal tugas khusus
                    </label>
                </div>
                <x-input-error :messages="$errors->get('follows_event_schedule')" />
                <x-input-error :messages="$errors->get('schedules')" />

                <div x-show="! followsEvent" class="space-y-4">
                @if($singleDay)
                    <p class="text-sm text-slate-600">Tanggal tugas: {{ $event->starts_at->copy()->setTimezone('Asia/Jakarta')->format('d/m/Y') }}. Ubah jam sesuai kebutuhan posisi.</p>
                @else
                    <p class="text-sm text-slate-600">Pilih tanggal dan jam tugas dalam rentang event.</p>
                @endif
                <template x-for="(slot, i) in schedules" :key="i">
                    <div class="grid gap-4 rounded-xl bg-slate-50 p-4 md:grid-cols-3">
                        <label class="text-sm font-medium text-slate-700">
                            Mulai

                            @if($singleDay)
                            <input type="hidden" :name="`schedules[${i}][starts_at]`" :value="slot.starts_at" :disabled="followsEvent">
                            <input type="time" :value="slot.starts_at.slice(11, 16)" @input="slot.starts_at = eventStart.slice(0, 11) + $event.target.value" :disabled="followsEvent" :required="! followsEvent" class="mt-2 w-full rounded-xl border-slate-300">
                            @else
                            <input
                                class="mt-2 w-full rounded-xl border-slate-300"
                                type="datetime-local"
                                :name="`schedules[${i}][starts_at]`"
                                x-model="slot.starts_at"
                                :disabled="followsEvent"
                                :required="! followsEvent"
                            >
                            @endif
                        </label>

                        <label class="text-sm font-medium text-slate-700">
                            Selesai

                            @if($singleDay)
                            <input type="hidden" :name="`schedules[${i}][ends_at]`" :value="slot.ends_at" :disabled="followsEvent">
                            <input type="time" :value="slot.ends_at.slice(11, 16)" @input="slot.ends_at = eventEnd.slice(0, 11) + $event.target.value" :disabled="followsEvent" :required="! followsEvent" class="mt-2 w-full rounded-xl border-slate-300">
                            @else
                            <input
                                class="mt-2 w-full rounded-xl border-slate-300"
                                type="datetime-local"
                                :name="`schedules[${i}][ends_at]`"
                                x-model="slot.ends_at"
                                :disabled="followsEvent"
                                :required="! followsEvent"
                            >
                            @endif
                        </label>

                        <div class="flex items-end">
                            <button
                                type="button"
                                class="text-sm font-semibold text-rose-600 hover:text-rose-700"
                                @click="schedules.splice(i, 1)"
                            >
                                Hapus jadwal
                            </button>
                        </div>
                    </div>
                </template>

                <button
                    type="button"
                    class="text-sm font-semibold text-indigo-700 hover:underline"
                    @click="schedules.push({
                        starts_at: eventStart,
                        ends_at: eventEnd
                    })"
                >
                    + Tambah jadwal
                </button>
                <p class="text-sm text-slate-600">Jadwal tugas harus berada dalam rentang event. Waktu ditampilkan dalam WIB.</p>
                </div>
            </fieldset>

            <fieldset class="space-y-4">
                <legend class="text-base font-semibold text-slate-900">
                    Persyaratan tambahan
                </legend>

                <template x-for="(requirement, i) in requirements" :key="i">
                    <div class="grid gap-4 rounded-xl bg-slate-50 p-4 md:grid-cols-2">
                        <label class="text-sm font-medium text-slate-700">
                            Nama syarat

                            <input
                                class="mt-2 w-full rounded-xl border-slate-300"
                                type="text"
                                :name="`requirements[${i}][name]`"
                                x-model="requirement.name"
                                required
                            >
                        </label>

                        <label class="text-sm font-medium text-slate-700">
                            Keterangan

                            <input
                                class="mt-2 w-full rounded-xl border-slate-300"
                                type="text"
                                :name="`requirements[${i}][description]`"
                                x-model="requirement.description"
                            >
                        </label>

                        <label class="text-sm font-medium text-slate-700">
                            Jenis

                            <select
                                class="mt-2 w-full rounded-xl border-slate-300"
                                :name="`requirements[${i}][kind]`"
                                x-model="requirement.kind"
                            >
                                <option value="manual">Tinjauan manual</option>
                                <option value="document">Dokumen</option>
                            </select>
                        </label>

                        <label class="text-sm font-medium text-slate-700">
                            Tipe dokumen

                            <select
                                class="mt-2 w-full rounded-xl border-slate-300"
                                :name="`requirements[${i}][document_type]`"
                                x-model="requirement.document_type"
                            >
                                <option value="">Tidak berlaku</option>
                                <option value="cv">CV (PDF)</option>
                                <option value="supporting">Pendukung</option>
                            </select>
                        </label>

                        <label class="text-sm font-medium text-slate-700">
                            Syarat

                            <select
                                class="mt-2 w-full rounded-xl border-slate-300"
                                :name="`requirements[${i}][is_required]`"
                                x-model="requirement.is_required"
                            >
                                <option value="1">Wajib</option>
                                <option value="0">Preferensi</option>
                            </select>
                        </label>

                        <div class="flex items-end">
                            <button
                                type="button"
                                class="text-sm font-semibold text-rose-600 hover:text-rose-700"
                                @click="requirements.splice(i, 1)"
                            >
                                Hapus syarat
                            </button>
                        </div>
                    </div>
                </template>

                <button
                    type="button"
                    class="text-sm font-semibold text-indigo-700 hover:underline"
                    @click="requirements.push({
                        name: '',
                        description: '',
                        kind: 'manual',
                        document_type: '',
                        is_required: 1
                    })"
                >
                    + Tambah syarat
                </button>

                <p class="text-sm text-slate-500">
                    Syarat manual tetap ditinjau manusia; isi CV tidak dinilai otomatis.
                </p>
            </fieldset>

            <noscript>
                Aktifkan JavaScript untuk menyunting daftar skill, jadwal, dan syarat.
            </noscript>

            <div class="flex flex-wrap items-center gap-4 border-t border-slate-100 pt-6">
                <x-button
                    type="submit"
                    x-bind:disabled="busy"
                >
                    <span x-text="busy ? 'Menyimpan...' : 'Simpan posisi'">
                        Simpan posisi
                    </span>
                </x-button>

                <a
                    href="{{ route('organizer.events.show', $event) }}"
                    class="text-sm font-semibold text-indigo-700 hover:underline"
                >
                    Kembali
                </a>
            </div>
        </form>
    </x-section-card>
</div>
@endsection
