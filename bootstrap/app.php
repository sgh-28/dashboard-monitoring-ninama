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
        
        // Register middleware aliases
        $middleware->alias([
            'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
        
        // Redirect guests to login
        $middleware->redirectGuestsTo('/login');
        
        // Redirect authenticated users away from login
        $middleware->redirectUsersTo('/');
        
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Custom exception handling
    })->create();