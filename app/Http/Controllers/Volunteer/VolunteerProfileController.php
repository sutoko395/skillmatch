<?php

namespace App\Http\Controllers\Volunteer;

use App\Http\Controllers\Controller;
use App\Models\Skill;
use App\Models\VolunteerProfile;
use App\Models\VolunteerSkill;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VolunteerProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user();

        $skills = Skill::orderBy('name')->get();

        $volunteerProfile = VolunteerProfile::where('user_id', $user->id)->first();

        $userSkills = VolunteerSkill::where('user_id', $user->id)
            ->pluck('level', 'skill_id')
            ->toArray();

        return view('volunteer.profile.edit', compact(
            'user',
            'skills',
            'volunteerProfile',
            'userSkills'
        ));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'phone' => ['required', 'string', 'max:30'],
            'city' => ['required', 'string', 'max:255'],
            'birth_date' => ['required', 'date'],
            'gender' => ['required', 'in:male,female'],
            'address' => ['required', 'string'],
            'bio' => [
                'required',
                'string',
                'max:700',
                function ($attribute, $value, $fail) {
                    $wordCount = str_word_count(strip_tags($value));

                    if ($wordCount > 100) {
                        $fail('Bio maksimal 100 kata.');
                    }
                },
            ],
            'availability' => ['required', 'in:Weekend,Weekday,Flexibel'],
            'skills' => ['required', 'array', 'min:1'],
            'skills.*.skill_id' => ['required', 'integer', 'exists:skills,id'],
            'skills.*.level' => ['required', 'in:beginner,intermediate,advanced,expert'],
        ]);

        VolunteerProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'phone' => $validated['phone'] ?? null,
                'city' => $validated['city'],
                'birth_date' => $validated['birth_date'] ?? null,
                'gender' => $validated['gender'] ?? null,
                'address' => $validated['address'] ?? null,
                'bio' => $validated['bio'] ?? null,
                'availability' => $validated['availability'],
            ]
        );

        VolunteerSkill::where('user_id', $user->id)->delete();

        foreach ($validated['skills'] ?? [] as $skill) {
            if (
                empty($skill['skill_id']) ||
                empty($skill['level'])
            ) {
                continue;
            }

            VolunteerSkill::create([
                'user_id' => $user->id,
                'skill_id' => $skill['skill_id'],
                'level' => $skill['level'],
            ]);
        }

        return redirect()
            ->route('volunteer.profile.edit')
            ->with('success', 'Profil Volunteer berhasil diperbarui!');
    }
}