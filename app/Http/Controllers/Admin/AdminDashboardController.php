<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Skill;
use App\Models\User;
use App\Models\Event;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $totalVolunteers = User::where('role', 'volunteer')->count();
        $totalOrganizers = User::where('role', 'organizer')->count();
        $totalSkills = Skill::count();
        $totalCategories = Category::count();
        $pendingEvents = Event::where('status', 'pending')->count();

        return view('admin.dashboard', compact(
            'totalVolunteers',
            'totalOrganizers',
            'totalSkills',
            'totalCategories',
            'pendingEvents'
        ));
    }
}