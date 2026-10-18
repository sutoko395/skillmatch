<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\LoginDestination;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(
        LoginRequest $request,
        LoginDestination $destination
    ): RedirectResponse {
        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user();

        if (! in_array($user->role, [
            'organizer',
            'admin',
            'volunteer',
        ], true)) {
            return $this->logoutAndRedirect($request);
        }

        return redirect()->to($destination->resolve($request));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login');
    }

    private function logoutAndRedirect(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
