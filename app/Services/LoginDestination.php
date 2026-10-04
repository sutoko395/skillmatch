<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;

class LoginDestination
{
    public function defaultFor(User $user): string
    {
        if (!$user->hasVerifiedEmail()) return '/verify-email';
        if ($user->role === 'organizer' && $user->organizer_status !== 'active') return '/organizer/profile/pending';
        return match ($user->role) {
            'admin' => '/admin/dashboard',
            'volunteer' => '/volunteer/aktivitas',
            'organizer' => '/organizer/aktivitas',
            default => '/login',
        };
    }

    public function resolve(Request $request): string
    {
        $user = $request->user();
        $fallback = $this->defaultFor($user);
        $intended = $request->session()->pull('url.intended');
        if (!is_string($intended) || preg_match('/[\\\\\x00-\x20%]/', $intended)) return $fallback;
        $parts = parse_url($intended);
        if (!$parts || isset($parts['user']) || isset($parts['pass']) || str_starts_with($intended, '//')) return $fallback;
        if (isset($parts['host']) || isset($parts['scheme'])) {
            $origin = parse_url(config('app.url'));
            if (($parts['scheme'] ?? '') !== ($origin['scheme'] ?? '')
                || ($parts['host'] ?? '') !== ($origin['host'] ?? '')
                || ($parts['port'] ?? null) !== ($origin['port'] ?? null)) return $fallback;
        }
        $path = $parts['path'] ?? '';
        // Exact, object-free GET destinations. Future object routes require their own Policy check.
        $allowed = match ($user->role) {
            'admin' => ['/admin/dashboard', '/admin/organizers', '/admin/volunteers', '/admin/master-data/skills', '/admin/master-data/event-categories', '/admin/event-verification'],
            'volunteer' => ['/volunteer/profile', '/volunteer/aktivitas'],
            'organizer' => ['/organizer/profile', '/organizer/profile/pending', '/organizer/aktivitas'],
            default => [],
        };
        if (!$user->hasVerifiedEmail()) $allowed = $user->role === 'organizer' ? ['/organizer/profile', '/organizer/profile/pending'] : [];
        if ($user->role === 'organizer' && $user->organizer_status !== 'active') $allowed = ['/organizer/profile', '/organizer/profile/pending'];
        return in_array($path, $allowed, true) ? $path : $fallback;
    }
}
