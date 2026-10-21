<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    private const MAX_ATTEMPTS = 5;

    private const DECAY_SECONDS = 60;

    private const MAX_LOCKOUT_CYCLES = 3;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function authenticate(): void
    {
        $email = Str::lower($this->string('email')->toString());

        $user = User::where('email', $email)->first();

        if ($user?->login_suspended_until?->isFuture()) {
            throw ValidationException::withMessages([
                'email' => 'akun anda terkena suspend karena mencoba melakukan spam login, silahkan login kembali dalam waktu 24 jam',
            ]);
        }

        $this->ensureIsNotRateLimited($user);

        if (! Auth::guard('web')->attempt([
            'email' => $email,
            'password' => $this->input('password'),
            'is_active' => true,
        ], $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey(), self::DECAY_SECONDS);

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());

        if ($user) {
            $user->forceFill([
                'login_lockout_cycles' => 0,
            ])->save();
        }
    }

    public function ensureIsNotRateLimited(?User $user = null): void
    {
        if (! RateLimiter::tooManyAttempts(
            $this->throttleKey(),
            self::MAX_ATTEMPTS
        )) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        if ($user) {
            $cycleKey = 'login-lockout-cycle:'.$user->id.':'.sha1($this->ip());

            if (RateLimiter::attempts($cycleKey) === 0) {
                RateLimiter::hit($cycleKey, self::DECAY_SECONDS);

                $user->increment('login_lockout_cycles');

                if ($user->login_lockout_cycles >= self::MAX_LOCKOUT_CYCLES) {
                    $user->forceFill([
                        'login_suspended_until' => now()->addHours(24),
                    ])->save();

                    throw ValidationException::withMessages([
                        'email' => 'akun anda terkena suspend karena mencoba melakukan spam login, silahkan login kembali dalam waktu 24 jam',
                    ]);
                }
            }
        }

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(
            Str::lower($this->string('email')->toString()).'|'.$this->ip()
        );
    }
}
