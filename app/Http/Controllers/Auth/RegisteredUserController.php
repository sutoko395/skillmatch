<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                'unique:'.User::class,
            ],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:volunteer,organizer'],
        ], [
            'email.unique' => 'Email sudah terdaftar. Silakan login.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'is_active' => true,
            'organizer_status' => 'pending',
        ]);

        Auth::login($user);

        $request->session()->regenerate();

        if ($user->role === 'organizer') {
            return redirect()->route('organizer.profile.edit');
        }

        try {
            event(new Registered($user));
        } catch (TransportExceptionInterface) {
            // The account is already created: keep the session and allow a retry.
            return redirect()->route('verification.notice')->with('status', 'verification-send-failed');
        }

        return redirect()->route('verification.notice')->with('status', 'verification-link-sent');
    }
}
