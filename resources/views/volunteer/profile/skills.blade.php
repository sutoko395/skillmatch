<x-section-card>

    <div class="border-b border-slate-100 pb-4">
        <h2 class="text-lg font-bold text-slate-800">
            Skill dan Level
        </h2>

        <p class="mt-1 text-sm text-slate-500">
            Tambahkan skill yang Anda kuasai beserta level kemampuan.
        </p>
    </div>

    <div
        data-repeater="skills"
        class="space-y-4"
    >

        <div data-rows class="space-y-4">

            @foreach(
                old(
                    'skills',
                    collect($userSkills)
                        ->map(fn ($level, $id) => [
                            'skill_id' => $id,
                            'level' => $level,
                        ])
                        ->values()
                        ->all()
                    ?: [
                        [
                            'skill_id' => '',
                            'level' => 'beginner',
                        ],
                    ]
                ) as $index => $row
            )

                @include('volunteer.profile.skill-row')

            @endforeach

        </div>

        <template data-template>
            @include('volunteer.profile.skill-row', [
                'index' => '__INDEX__',
                'row' => [
                    'skill_id' => '',
                    'level' => 'beginner',
                ],
            ])
        </template>

        <div>
            <x-secondary-button data-add>
                Tambah skill
            </x-secondary-button>
        </div>

    </div>

</x-section-card>
