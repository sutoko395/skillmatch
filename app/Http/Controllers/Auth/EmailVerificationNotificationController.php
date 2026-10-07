<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\LoginDestination;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->role === 'organizer') {
            return redirect(app(LoginDestination::class)->defaultFor($user));
        }

        if ($user->hasVerifiedEmail()) {
            return redirect(app(LoginDestination::class)->resolve($request));
        }

        $user->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }
}