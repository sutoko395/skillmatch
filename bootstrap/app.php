<?php

use App\Http\Middleware\EnsureAccountActive;
use App\Http\Middleware\OrganizerActiveMiddleware;
use App\Http\Middleware\RoleMiddleware;
use App\Services\LoginDestination;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->appendToGroup('web', EnsureAccountActive::class);
        $middleware->validateCsrfTokens(except: ['payments/midtrans/notification']);
        $middleware->redirectUsersTo(fn (Request $request) => app(LoginDestination::class)->defaultFor($request->user()));
        $middleware->alias([
            'account.active' => EnsureAccountActive::class,
            'role' => RoleMiddleware::class,
            'organizer.active' => OrganizerActiveMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })
    ->create();
