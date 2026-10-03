<?php

namespace App\Http\Controllers\Volunteer;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;

class VolunteerDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $volunteerProfile = $user->volunteerProfile;

        $totalSkills = $user->volunteerSkills()->count();

        $totalEvents = Event::where('status', 'approved')
            ->whereDate('registration_deadline', '>=', now()->toDateString())
            ->count();

        $profileCompleted = $volunteerProfile
            && $volunteerProfile->phone
            && $volunteerProfile->city
            && $volunteerProfile->availability
            && $totalSkills > 0;

        return view('volunteer.dashboard', compact(
            'user',
            'volunteerProfile',
            'totalSkills',
            'totalEvents',
            'profileCompleted'
        ));
    }
}