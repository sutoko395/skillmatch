<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OrganizerActiveMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || $user->role !== 'organizer') {
            abort(403);
        }

        if ($user->organizer_status !== 'active') {
            return redirect()->route('organizer.profile.pending');
        }

        return $next($request);
    }
}