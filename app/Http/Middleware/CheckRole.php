<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Check if user has any of the required roles
        if (!in_array($user->role, $roles)) {
            // Log unauthorized access attempt
            AuditLog::log('unauthorized_access', $user, [
                'description' => "User attempted to access {$request->path()} without permission",
                'old_values' => [
                    'required_roles' => $roles,
                    'user_role' => $user->role,
                    'url' => $request->fullUrl()
                ]
            ]);

            // Redirect to appropriate dashboard with error message
            $redirectRoute = match($user->role) {
                'superadmin', 'admin' => 'admin.dashboard',
                'mentor' => 'mentor.dashboard',
                'learner' => 'learner.dashboard',
                default => 'login'
            };

            return redirect()->route($redirectRoute)
                ->with('error', 'You do not have permission to access that page.');
        }

        return $next($request);
    }
}