<?php

namespace App\Http\Controllers;

use App\Models\Event;

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

        return view('welcome', compact('events'));
    }
}
