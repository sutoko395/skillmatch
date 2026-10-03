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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')
            ->with('status', 'profile-updated');
    }

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

    public function editProfile(Request $request): View
    {
        $user = $request->user();

        $skills = Skill::orderBy('name')->get();

        $volunteerProfile = $user->volunteerProfile;

        $userSkills = $user->volunteerSkills()
            ->pluck('level', 'skill_id')
            ->toArray();

        $organizerProfile = $user->organizerProfile;

        return view('profile.edit-skillmatch', [
            'user' => $user,
            'skills' => $skills,
            'volunteerProfile' => $volunteerProfile,
            'userSkills' => $userSkills,
            'organizerProfile' => $organizerProfile,
        ]);
    }

    public function updateVolunteerProfile(Request $request): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user->role === 'volunteer', 403);

        $validated = $request->validate([
            'phone' => ['nullable', 'string', 'max:30'],
            'birth_date' => ['nullable', 'date'],
            'gender' => ['nullable', 'in:male,female'],
            'city' => ['required', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:1000'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'availability' => ['required', 'string', 'max:255'],
            'skills' => ['nullable', 'array'],
            'skills.*' => ['nullable', 'in:beginner,intermediate,advanced,expert'],
        ]);

        DB::transaction(function () use ($user, $validated) {
            $user->volunteerProfile()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'phone' => $validated['phone'] ?? null,
                    'birth_date' => $validated['birth_date'] ?? null,
                    'gender' => $validated['gender'] ?? null,
                    'city' => $validated['city'],
                    'address' => $validated['address'] ?? null,
                    'bio' => $validated['bio'] ?? null,
                    'availability' => $validated['availability'],
                ]
            );

            $user->volunteerSkills()->delete();

            foreach ($validated['skills'] ?? [] as $skillId => $level) {
                if (blank($level)) {
                    continue;
                }

                $skillExists = Skill::whereKey($skillId)->exists();

                if (!$skillExists) {
                    continue;
                }

                $user->volunteerSkills()->create([
                    'skill_id' => $skillId,
                    'level' => $level,
                ]);
            }
        });

        return back()->with(
            'success',
            'Profil Volunteer berhasil diperbarui.'
        );
    }

    public function updateOrganizerProfile(Request $request): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user->role === 'organizer', 403);

        $validated = $request->validate([
            'organization_name' => ['required', 'string', 'max:255'],
            'contact_person' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:1000'],
            'city' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:2000'],
            'website' => ['nullable', 'url', 'max:255'],
        ]);

        $user->organizerProfile()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'organization_name' => $validated['organization_name'],
                'contact_person' => $validated['contact_person'],
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'city' => $validated['city'],
                'description' => $validated['description'] ?? null,
                'website' => $validated['website'] ?? null,
            ]
        );

        return back()->with(
            'success',
            'Profil Organizer berhasil diperbarui.'
        );
    }
}