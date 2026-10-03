<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrganizerDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        return view('organizer.dashboard', compact('user'));
    }
}