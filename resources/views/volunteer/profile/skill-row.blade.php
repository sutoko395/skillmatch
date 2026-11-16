<div data-row class="rounded-xl border border-slate-200 bg-slate-50/50 p-4">

    <div class="grid gap-5 sm:grid-cols-2">

        <div>
            <label
                for="skill-{{ $index }}"
                class="block text-sm font-semibold text-slate-700"
            >
                Keahlian *
            </label>

            <select
                id="skill-{{ $index }}"
                name="skills[{{ $index }}][skill_id]"
                required
                class="mt-2 w-full rounded-xl border-slate-200 text-sm transition focus:border-orange-500 focus:ring-orange-500"
            >
                <option value="">
                    Pilih keahlian aktif
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
            class="volunteer-action volunteer-action--danger"
         class="volunteer-action volunteer-action--danger">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M9 6V4h6v2M5 6l1 14h12l1-14M10 10v6m4-6v6" /></svg> Hapus keahlian
        </x-secondary-button>
    </div>

</div>