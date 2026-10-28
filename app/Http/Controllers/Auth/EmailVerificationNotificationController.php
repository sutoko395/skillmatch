<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\LoginDestination;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

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

        try {
            $user->sendEmailVerificationNotification();
        } catch (TransportExceptionInterface) {
            return redirect()->route('verification.notice')->with('status', 'verification-send-failed');
        }

        return redirect()->route('verification.notice')->with('status', 'verification-link-sent');
    }
}
