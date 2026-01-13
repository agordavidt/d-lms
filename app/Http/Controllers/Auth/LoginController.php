<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return $this->redirectToDashboard(Auth::user());
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Rate limiting
        $key = 'login.' . $request->ip();
        
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            
            throw ValidationException::withMessages([
                'email' => ["Too many login attempts. Please try again in {$seconds} seconds."],
            ]);
        }

        $user = User::where('email', $request->email)->first();

        // Check if user exists
        if (!$user) {
            RateLimiter::hit($key, 60);
            
            throw ValidationException::withMessages([
                'email' => ['These credentials do not match our records.'],
            ]);
        }

        // Check if account is locked
        if ($user->isLocked()) {
            $minutesRemaining = now()->diffInMinutes($user->locked_until);
            
            throw ValidationException::withMessages([
                'email' => ["Account locked due to multiple failed attempts. Try again in {$minutesRemaining} minutes."],
            ]);
        }

        // Verify password
        if (!Hash::check($request->password, $user->password)) {
            $user->recordLoginAttempt();
            RateLimiter::hit($key, 60);
            
            AuditLog::log('failed_login', $user, [
                'description' => 'Failed login attempt - incorrect password'
            ]);
            
            throw ValidationException::withMessages([
                'email' => ['These credentials do not match our records.'],
            ]);
        }

        // Check user status
        if (!$user->isActive()) {
            $message = $user->isSuspended() 
                ? 'Your account has been suspended. Please contact support.'
                : 'Your account is inactive. Please contact support.';
            
            throw ValidationException::withMessages([
                'email' => [$message],
            ]);
        }

        // Clear rate limiter
        RateLimiter::clear($key);

        // Record successful login
        $user->recordLogin($request->ip());
        
        // Logout other devices if needed (optional - uncomment if desired)
        // Auth::logoutOtherDevices($request->password);

        // Login user
        Auth::login($user, $request->filled('remember'));

        // Regenerate session
        $request->session()->regenerate();

        // Log successful login
        AuditLog::log('login', $user, [
            'description' => 'User logged in successfully'
        ]);

        // Redirect to appropriate dashboard
        $notification = [
            'message' => 'Welcome back, ' . $user->name . '!',
            'alert-type' => 'success'
        ];

        return redirect()->intended($this->redirectPath($user))
            ->with($notification);
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        
        if ($user) {
            AuditLog::log('logout', $user, [
                'description' => 'User logged out'
            ]);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $notification = [
            'message' => 'You have been logged out successfully.',
            'alert-type' => 'info'
        ];

        return redirect()->route('login')->with($notification);
    }

    protected function redirectPath(User $user): string
    {
        return match($user->role) {
            'superadmin', 'admin' => route('admin.dashboard'),
            'mentor' => route('mentor.dashboard'),
            'learner' => route('learner.dashboard'),
            default => route('home')
        };
    }

    protected function redirectToDashboard(User $user)
    {
        return redirect($this->redirectPath($user));
    }
}