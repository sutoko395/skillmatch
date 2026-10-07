<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\LoginDestination;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailVerificationPromptController extends Controller
{
    public function __invoke(Request $request): RedirectResponse|View
    {
        $user = $request->user();

        if ($user->role === 'organizer') {
            return redirect(app(LoginDestination::class)->defaultFor($user));
        }

        return $user->hasVerifiedEmail()
            ? redirect(app(LoginDestination::class)->resolve($request))
            : view('auth.verify-email');
    }
}