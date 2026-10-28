<?php

namespace App\Http\Controllers\Volunteer;

use App\Http\Controllers\Controller;
use App\Models\Application;
use Illuminate\Http\Request;

class HistoryController extends Controller
{
    public function index(Request $request)
    {
        $applications = Application::where('volunteer_id', $request->user()->id)->whereNotNull('submitted_at')
            ->whereHas('event', fn ($q) => $q->whereIn('lifecycle_status', ['completed', 'cancelled']))
            ->with(['event', 'position', 'attendance'])->latest('submitted_at')->paginate(15);

        return view('volunteer.history.index', compact('applications'));
    }
}
