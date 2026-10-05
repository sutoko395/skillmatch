<div data-row class="rounded-xl border border-slate-200 p-4">
<div class="grid gap-4 sm:grid-cols-2">
    <div><label for="skill-{{ $index }}" class="block text-sm font-medium">Skill *</label>
        <select id="skill-{{ $index }}" name="skills[{{ $index }}][skill_id]" required class="mt-2 w-full rounded-xl border-slate-300">
            <option value="">Pilih skill aktif</option>
            @foreach($skills as $skill)<option value="{{ $skill->id }}" @selected(($row['skill_id'] ?? '') == $skill->id)>{{ $skill->name }}</option>@endforeach
        </select>
        <x-input-error :messages="$errors->get('skills.'.$index.'.skill_id')" />
    </div>
    <div><label for="level-{{ $index }}" class="block text-sm font-medium">Level *</label>
        <select id="level-{{ $index }}" name="skills[{{ $index }}][level]" class="mt-2 w-full rounded-xl border-slate-300" required>
        @foreach(['beginner', 'intermediate', 'advanced', 'expert'] as $level)<option @selected(($row['level'] ?? '') === $level) value="{{ $level }}">{{ ucfirst($level) }}</option>@endforeach
        </select>
        <x-input-error :messages="$errors->get('skills.'.$index.'.level')" />
    </div>
</div><x-secondary-button data-remove class="mt-3">Hapus skill</x-secondary-button>
</div>
