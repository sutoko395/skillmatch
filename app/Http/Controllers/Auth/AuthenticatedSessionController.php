<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
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

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user();

        return match ($user->role) {
            'organizer' => $user->organizerProfile
                ? ($user->organizer_status === 'active'
                    ? redirect('/organizer/aktivitas')
                    : redirect()->route('organizer.profile.pending'))
                : redirect()->route('organizer.profile.edit'),

            'admin' => redirect('/admin/dashboard'),

            'volunteer' => redirect('/volunteer/aktivitas'),

            default => $this->logoutAndRedirect($request),
        };
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