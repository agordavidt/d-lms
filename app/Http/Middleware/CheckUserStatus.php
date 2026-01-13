<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserStatus
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Check if user is suspended
            if ($user->isSuspended()) {
                AuditLog::log('suspended_access_attempt', $user, [
                    'description' => 'User attempted to access system while suspended'
                ]);

                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')
                    ->with('error', 'Your account has been suspended. Please contact support.');
            }

            // Check if user is locked
            if ($user->isLocked()) {
                $minutesRemaining = now()->diffInMinutes($user->locked_until);
                
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')
                    ->with('warning', "Account locked due to multiple failed login attempts. Try again in {$minutesRemaining} minutes.");
            }

            // Check if user is inactive
            if (!$user->isActive()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')
                    ->with('info', 'Your account is inactive. Please contact support to activate.');
            }
        }

        return $next($request);
    }
}