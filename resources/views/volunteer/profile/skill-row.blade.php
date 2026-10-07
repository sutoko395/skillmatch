<div data-row class="rounded-xl border border-slate-200 bg-slate-50/50 p-4">

    <div class="grid gap-5 sm:grid-cols-2">

        <div>
            <label
                for="skill-{{ $index }}"
                class="block text-sm font-semibold text-slate-700"
            >
                Skill *
            </label>

            <select
                id="skill-{{ $index }}"
                name="skills[{{ $index }}][skill_id]"
                required
                class="mt-2 w-full rounded-xl border-slate-200 text-sm transition focus:border-orange-500 focus:ring-orange-500"
            >
                <option value="">
                    Pilih skill aktif
                </option>

                @foreach($skills as $skill)
                    <option
                        value="{{ $skill->id }}"
                        @selected(($row['skill_id'] ?? '') == $skill->id)
                    >
                        {{ $skill->name }}
                    </option>
                @endforeach
            </select>

            <x-input-error
                :messages="$errors->get('skills.'.$index.'.skill_id')"
                class="mt-1"
            />
        </div>

        <div>
            <label
                for="level-{{ $index }}"
                class="block text-sm font-semibold text-slate-700"
            >
                Level *
            </label>

            <select
                id="level-{{ $index }}"
                name="skills[{{ $index }}][level]"
                required
                class="mt-2 w-full rounded-xl border-slate-200 text-sm transition focus:border-orange-500 focus:ring-orange-500"
            >
                @foreach(['beginner', 'intermediate', 'advanced', 'expert'] as $level)
                    <option
                        value="{{ $level }}"
                        @selected(($row['level'] ?? '') === $level)
                    >
                        {{ ucfirst($level) }}
                    </option>
                @endforeach
            </select>

            <x-input-error
                :messages="$errors->get('skills.'.$index.'.level')"
                class="mt-1"
            />
        </div>

    </div>

    <div class="mt-4 flex justify-end">
        <x-secondary-button
            type="button"
            data-remove
        >
            Hapus skill
        </x-secondary-button>
    </div>

</div>