<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Package;

class HomeController extends Controller
{
    public function __invoke()
    {
        $events = Event::publiclyVisible()
            ->where('registration_opens_at', '<=', now())
            ->where('registration_deadline', '>', now())
            ->whereHas('entitlement', fn ($query) => $query->whereColumn('submitted_applications', '<', 'max_applications'))
            ->with(['cityRecord', 'category', 'organizer.organizerProfile'])->withCount('positions')
            ->orderBy('starts_at')->orderBy('id')->limit(6)->get();

        $packages = Package::where('is_active', true)->orderBy('price')->orderBy('id')->get();

        return view('welcome', compact('events', 'packages'));
    }
}
