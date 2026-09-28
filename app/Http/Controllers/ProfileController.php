<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\OrganizerProfile;
use App\Models\Skill;
use App\Models\VolunteerProfile;
use App\Models\VolunteerSkill;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form (Default Breeze).
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information (Default Breeze).
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account (Default Breeze).
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Custom SkillMatch Profile Setup Page.
     */
    public function editProfile(Request $request): View
    {
        $user = $request->user();
        $skills = Skill::all();
        $volunteerProfile = VolunteerProfile::where('user_id', $user->id)->first();
        $userSkills = VolunteerSkill::where('user_id', $user->id)->pluck('level', 'skill_id')->toArray();
        $organizerProfile = OrganizerProfile::where('user_id', $user->id)->first();

        return view('profile.edit-skillmatch', compact('user', 'skills', 'volunteerProfile', 'userSkills', 'organizerProfile'));
    }

    /**
     * Update Volunteer Profile & Skill Preferences.
     */
    public function updateVolunteerProfile(Request $request): RedirectResponse
    {
        $user = $request->user();

        $request->validate([
            'phone' => 'nullable|string',
            'location' => 'required|string',
            'availability' => 'required|string',
            'bio' => 'nullable|string',
            'skills' => 'nullable|array',
        ]);

        VolunteerProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'phone' => $request->phone,
                'location' => $request->location,
                'availability' => $request->availability,
                'bio' => $request->bio,
            ]
        );

        VolunteerSkill::where('user_id', $user->id)->delete();
        if ($request->has('skills')) {
            foreach ($request->skills as $skillId => $level) {
                if ($level) {
                    VolunteerSkill::create([
                        'user_id' => $user->id,
                        'skill_id' => $skillId,
                        'level' => $level,
                    ]);
                }
            }
        }

        return back()->with('success', 'Profil Volunteer berhasil diperbarui!');
    }

    /**
     * Update Organizer Profile Information.
     */
    public function updateOrganizerProfile(Request $request): RedirectResponse
    {
        $user = $request->user();

        $request->validate([
            'organization_name' => 'required|string|max:255',
            'contact_person' => 'required|string|max:255',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'description' => 'nullable|string',
            'website' => 'nullable|string',
        ]);

        OrganizerProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'organization_name' => $request->organization_name,
                'contact_person' => $request->contact_person,
                'phone' => $request->phone,
                'address' => $request->address,
                'description' => $request->description,
                'website' => $request->website,
            ]
        );

        return back()->with('success', 'Profil Organizer berhasil diperbarui!');
    }
}
