@extends('organizer.layouts.sidebar')

@section('title', 'Konfigurasi Posisi')

@section('content')
<div class="mx-auto max-w-5xl">
    <x-page-header
        title="Konfigurasi Posisi"
        :description="$event->title"
    />

    @php
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

                <template x-for="(slot, i) in schedules" :key="i">
                    <div class="grid gap-4 rounded-xl bg-slate-50 p-4 md:grid-cols-3">
                        <label class="text-sm font-medium text-slate-700">
                            Mulai

                            <input
                                class="mt-2 w-full rounded-xl border-slate-300"
                                type="datetime-local"
                                :name="`schedules[${i}][starts_at]`"
                                x-model="slot.starts_at"
                                required
                            >
                        </label>

                        <label class="text-sm font-medium text-slate-700">
                            Selesai

                            <input
                                class="mt-2 w-full rounded-xl border-slate-300"
                                type="datetime-local"
                                :name="`schedules[${i}][ends_at]`"
                                x-model="slot.ends_at"
                                required
                            >
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
                        starts_at: '',
                        ends_at: ''
                    })"
                >
                    + Tambah jadwal
                </button>
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