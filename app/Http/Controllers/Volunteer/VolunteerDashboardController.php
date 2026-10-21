<?php

namespace App\Http\Controllers\Volunteer;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Services\ProfileEligibilityService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VolunteerDashboardController extends Controller
{
    public function index(Request $request, ProfileEligibilityService $profiles): View
    {
        $user = $request->user();
        $eligibility = $profiles->check($user);

        $applications = Application::where('volunteer_id', $user->id)
            ->where('status', '!=', 'draft')
            ->with(['event', 'position'])
            ->latest('submitted_at')
            ->get();

        return view('volunteer.dashboard', [
            'user' => $user,
            'volunteerProfile' => $user->volunteerProfile,
            'eligibility' => $eligibility,
            'skills' => $user->volunteerSkills,
            'slots' => $user->availabilitySlots->sortBy('starts_at'),
            'applications' => $applications,
        ]);
    }
}
