@extends('volunteer.layouts.sidebar')

@section('title', 'Profil Volunteer')

@section('content')
<div class="w-full max-w-7xl mx-auto space-y-6">

    @if(session('success'))
        <div class="flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-800">
            <div class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-100">
                <svg class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>

            <div>
                <p class="text-sm font-semibold">Berhasil</p>
                <p class="mt-0.5 text-sm">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-red-800">
            <p class="text-sm font-semibold">Periksa kembali data yang diisi.</p>

            <ul class="mt-2 list-disc space-y-1 pl-5 text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">

        <div class="border-b border-gray-100 px-6 py-5 sm:px-8">
            <div class="flex items-center gap-4">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                    </svg>
                </div>

                <div>
                    <h1 class="text-lg font-bold text-gray-900">
                        Profil Volunteer
                    </h1>

                    <p class="text-sm text-gray-500">
                        Informasi ini digunakan untuk proses pencocokan volunteer dengan event.
                    </p>
                </div>
            </div>
        </div>

        <form
            action="{{ route('volunteer.profile.update') }}"
            method="POST"
            id="profile-form"
        >
            @csrf

            <div class="space-y-8 p-6 sm:p-8">

                <section>
                    <div class="mb-5">
                        <h3 class="text-sm font-bold uppercase tracking-wider text-gray-900">
                            Informasi Pribadi
                        </h3>

                        <p class="mt-1 text-sm text-gray-500">
                            Lengkapi informasi dasar mengenai diri Anda.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700">
                                Nomor Telepon / WhatsApp
                            </label>

                            <input
                                id="phone"
                                type="text"
                                name="phone"
                                value="{{ old('phone', $volunteerProfile->phone ?? '') }}"
                                required
                                placeholder="08xxxxxxxxxx"
                                class="mt-2 block w-full rounded-xl border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                        </div>

                        <div>
                            <label for="city" class="block text-sm font-medium text-gray-700">
                                Kota Domisili
                            </label>

                            <input
                                id="city"
                                type="text"
                                name="city"
                                value="{{ old('city', $volunteerProfile->city ?? '') }}"
                                placeholder="Contoh: Malang"
                                required
                                class="mt-2 block w-full rounded-xl border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                        </div>

                        <div>
                            <label for="birth_date" class="block text-sm font-medium text-gray-700">
                                Tanggal Lahir
                            </label>

                            <input
                                id="birth_date"
                                type="date"
                                name="birth_date"
                                value="{{ old('birth_date', optional($volunteerProfile?->birth_date)->format('Y-m-d')) }}"
                                required
                                class="mt-2 block w-full rounded-xl border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                        </div>

                        <div>
                            <label for="gender" class="block text-sm font-medium text-gray-700">
                                Jenis Kelamin
                            </label>

                            <select
                                id="gender"
                                name="gender"
                                required
                                class="mt-2 block w-full rounded-xl border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="">Pilih jenis kelamin</option>
                                <option value="male" {{ old('gender', $volunteerProfile->gender ?? '') === 'male' ? 'selected' : '' }}>
                                    Laki-laki
                                </option>
                                <option value="female" {{ old('gender', $volunteerProfile->gender ?? '') === 'female' ? 'selected' : '' }}>
                                    Perempuan
                                </option>
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label for="address" class="block text-sm font-medium text-gray-700">
                                Alamat
                            </label>

                            <textarea
                                id="address"
                                name="address"
                                rows="3"
                                placeholder="Masukkan alamat lengkap"
                                required
                                class="mt-2 block w-full rounded-xl border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >{{ old('address', $volunteerProfile->address ?? '') }}</textarea>
                        </div>

                        <div class="md:col-span-2">
                            <label for="bio" class="block text-sm font-medium text-gray-700">
                                Bio Singkat
                            </label>

                            <textarea
                                id="bio"
                                name="bio"
                                rows="5"
                                maxlength="700"
                                required
                                placeholder="Ceritakan tentang pengalaman, kemampuan, atau minat Anda..."
                                class="mt-2 block w-full rounded-xl border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >{{ old('bio', $volunteerProfile->bio ?? '') }}</textarea>

                            <p class="mt-1 text-xs text-gray-400">
                                Maksimal 100 kata.
                            </p>
                        </div>

                    </div>
                </section>

                <section class="border-t border-gray-100 pt-8">
                    <div class="mb-5">
                        <h3 class="text-sm font-bold uppercase tracking-wider text-gray-900">
                            Ketersediaan
                        </h3>

                        <p class="mt-1 text-sm text-gray-500">
                            Tentukan pola waktu yang paling sesuai dengan kegiatan volunteer Anda.
                        </p>
                    </div>

                    <div>
                        <label for="availability" class="block text-sm font-medium text-gray-700">
                            Ketersediaan Waktu
                        </label>

                        <select
                            id="availability"
                            name="availability"
                            required
                            class="mt-2 block w-full rounded-xl border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                            <option value="">Pilih ketersediaan</option>

                            <option value="Weekend" {{ old('availability', $volunteerProfile->availability ?? '') === 'Weekend' ? 'selected' : '' }}>
                                Weekend
                            </option>

                            <option value="Weekday" {{ old('availability', $volunteerProfile->availability ?? '') === 'Weekday' ? 'selected' : '' }}>
                                Weekday
                            </option>

                            <option value="Flexibel" {{ old('availability', $volunteerProfile->availability ?? '') === 'Flexibel' ? 'selected' : '' }}>
                                Fleksibel
                            </option>
                        </select>
                    </div>
                </section>

                <section class="border-t border-gray-100 pt-8">
                    <div class="mb-5 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-sm font-bold uppercase tracking-wider text-gray-900">
                                Skill & Tingkat Kemampuan
                            </h3>

                            <p class="mt-1 text-sm text-gray-500">
                                Tambahkan skill yang Anda miliki beserta tingkat kemampuan Anda.
                            </p>
                        </div>

                        <button
                            type="button"
                            id="add-skill"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14M5 12h14"/>
                            </svg>

                            Tambah Skill & Kemampuan
                        </button>
                    </div>

                    <div id="skills-container" class="space-y-3"></div>

                    <div
                        id="empty-skills"
                        class="rounded-xl border border-dashed border-gray-300 bg-gray-50 px-5 py-8 text-center"
                    >
                        <p class="text-sm font-medium text-gray-600">
                            Belum ada skill yang ditambahkan.
                        </p>

                        <p class="mt-1 text-xs text-gray-400">
                            Klik tombol "Tambah Skill & Kemampuan" untuk menambahkan skill.
                        </p>
                    </div>

                    <template id="skill-template">
                        <div class="skill-row rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-[1fr_1fr_auto] md:items-end">

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">
                                        Skill
                                    </label>

                                    <select
                                        class="skill-select mt-2 block w-full rounded-xl border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        required
                                    >
                                        <option value="">Pilih skill</option>

                                        @foreach($skills as $skill)
                                            <option value="{{ $skill->id }}">
                                                {{ $skill->name }}
                                                @if($skill->category)
                                                    — {{ $skill->category }}
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">
                                        Tingkat Kemampuan
                                    </label>

                                    <select
                                        class="level-select mt-2 block w-full rounded-xl border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                         required
                                         disabled
                                    >
                                        <option value="">Pilih tingkat</option>
                                        <option value="beginner">Beginner</option>
                                        <option value="intermediate">Intermediate</option>
                                        <option value="advanced">Advanced</option>
                                        <option value="expert">Expert</option>
                                    </select>
                                </div>

                                <button
                                    type="button"
                                    class="remove-skill inline-flex h-11 items-center justify-center rounded-xl border border-red-200 px-4 text-sm font-semibold text-red-600 transition hover:bg-red-50"
                                >
                                    Hapus
                                </button>

                            </div>
                        </div>
                    </template>
                </section>

            </div>

            <div class="flex flex-col gap-3 border-t border-gray-100 bg-gray-50 px-6 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-8">
                <p class="text-xs text-gray-500">
                    Pastikan informasi yang Anda masukkan sudah sesuai.
                </p>

                <button
                    type="submit"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>

                    Simpan Profil
                </button>
            </div>

        </form>
    </div>
</div>

<script type="application/json" id="existing-skills-data">
    {!! json_encode($userSkills) !!}
</script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const container = document.getElementById('skills-container');
        const emptyState = document.getElementById('empty-skills');
        const addButton = document.getElementById('add-skill');
        const template = document.getElementById('skill-template');

        const form = document.getElementById('profile-form');

        form.addEventListener('submit', (event) => {
            const skillRows = document.querySelectorAll('.skill-row');

            if (skillRows.length === 0) {
                event.preventDefault();

                alert('Minimal tambahkan 1 skill dan tingkat kemampuan.');

                return;
            }

            let valid = true;

            skillRows.forEach(row => {
                const skillSelect = row.querySelector('.skill-select');
                const levelSelect = row.querySelector('.level-select');

                if (!skillSelect.value || !levelSelect.value) {
                    valid = false;
                }
            });

            if (!valid) {
                event.preventDefault();

                alert('Lengkapi skill dan tingkat kemampuan terlebih dahulu.');
            }
        });

        const existingSkills = JSON.parse(
            document.getElementById('existing-skills-data').textContent
        );

        let skillIndex = 0;

        function updateEmptyState() {
            emptyState.classList.toggle(
                'hidden',
                container.children.length > 0
            );
        }

        function updateSkillOptions() {
            const selectedValues = Array.from(
                document.querySelectorAll('.skill-select')
            )
                .map(select => select.value)
                .filter(Boolean);

            document.querySelectorAll('.skill-select').forEach(select => {
                const currentValue = select.value;

                Array.from(select.options).forEach(option => {
                    if (!option.value) {
                        return;
                    }

                    option.disabled =
                        selectedValues.includes(option.value) &&
                        option.value !== currentValue;
                });
            });
        }

        function addSkillRow(skillId = '', level = '') {
            const row = template.content.cloneNode(true);

            const wrapper = row.querySelector('.skill-row');
            const skillSelect = row.querySelector('.skill-select');
            const levelSelect = row.querySelector('.level-select');
            const removeButton = row.querySelector('.remove-skill');

            const index = skillIndex++;

            skillSelect.name = `skills[${index}][skill_id]`;
            levelSelect.name = `skills[${index}][level]`;

            skillSelect.value = String(skillId);

            if (skillId) {
                levelSelect.disabled = false;
                levelSelect.required = true;
                levelSelect.value = level;
            }

            skillSelect.addEventListener('change', () => {
                if (skillSelect.value) {
                    levelSelect.disabled = false;
                    levelSelect.required = true;
                } else {
                    levelSelect.disabled = true;
                    levelSelect.required = false;
                    levelSelect.value = '';
                }

                updateSkillOptions();
            });

            removeButton.addEventListener('click', () => {
                wrapper.remove();
                updateSkillOptions();
                updateEmptyState();
            });

            container.appendChild(row);

            updateSkillOptions();
            updateEmptyState();
        }

        addButton.addEventListener('click', () => {
            addSkillRow();
        });

        Object.entries(existingSkills).forEach(([skillId, level]) => {
            addSkillRow(skillId, level);
        });

        updateEmptyState();
    });
</script>
@endsection