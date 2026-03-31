<?php

use App\Http\Middleware\CheckRole;
use App\Http\Middleware\CheckUserStatus;
use App\Http\Middleware\NoCacheHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Register middleware aliases
        $middleware->alias([
            'check.user.status' => CheckUserStatus::class,
            'no.cache' => NoCacheHeaders::class,
            'check.role' => CheckRole::class,
        ]);

        // Global middleware
        $middleware->web(append: [
            // Add any global web middleware here if needed
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
        // Return JSON for CSRF token mismatch on AJAX requests
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Your session has expired. Please refresh the page and try again.'
                ], 419);
            }
        });

        // Expired or tampered verification link
        $exceptions->render(function (InvalidSignatureException $e, $request) {
            \Illuminate\Support\Facades\Log::warning('Invalid verification signature', [
                'ip'  => $request->ip(),
                'url' => $request->fullUrl(),
            ]);
    
            return response()->view('auth.verification-expired', [], 403);
        });
    })->create();