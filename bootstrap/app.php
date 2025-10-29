<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Configure guest middleware to redirect authenticated users to dashboard
        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(fn () => route('dashboard'));

        // Register role-based middleware
        $middleware->alias([
            'root' => \App\Http\Middleware\RootMiddleware::class,
            'user' => \App\Http\Middleware\UserMiddleware::class,
            'active' => \App\Http\Middleware\CheckActiveStatus::class,
        ]);

        // Apply active status check to web routes
        $middleware->web(append: [
            \App\Http\Middleware\CheckActiveStatus::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
