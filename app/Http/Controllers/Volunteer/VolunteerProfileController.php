<?php

namespace App\Http\Controllers\Volunteer;

use App\Http\Controllers\Controller;
use App\Http\Requests\VolunteerProfileRequest;
use App\Models\City;
use App\Models\Skill;
use App\Models\VolunteerProfile;
use App\Models\VolunteerSkill;
use App\Services\VolunteerProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class VolunteerProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user();

        Gate::authorize('updateProfile', $user);
        $skills = Skill::where('is_active', true)->orderBy('name')->get();
        $cities = City::where('is_active', true)->orderBy('name')->get();
        $slots = $user->availabilitySlots()->orderBy('starts_at')->get();

        $volunteerProfile = VolunteerProfile::where('user_id', $user->id)->first();

        $userSkills = VolunteerSkill::where('user_id', $user->id)
            ->pluck('level', 'skill_id')
            ->toArray();

        return view('volunteer.profile.edit', compact(
            'user',
            'skills',
            'volunteerProfile',
            'userSkills', 'cities', 'slots'
        ));
    }

    public function update(VolunteerProfileRequest $request, VolunteerProfileService $profiles): RedirectResponse
    {
        $profiles->save($request->user(), $request->validated());

        return redirect()->route('volunteer.profile.edit')->with('success', 'Profil Volunteer berhasil diperbarui.');
    }
}
