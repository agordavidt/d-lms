<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        if (Auth::check()) {
            return redirect()->route('learner.dashboard');
        }

        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::min(8)
                ->mixedCase()
                ->numbers()
                ->symbols()],
        ], [
            'password.min' => 'Password must be at least 8 characters.',
            'password.mixed' => 'Password must contain both uppercase and lowercase letters.',
            'password.numbers' => 'Password must contain at least one number.',
            'password.symbols' => 'Password must contain at least one special character.',
        ]);

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'learner', // Default role
                'status' => 'active',
                'last_login_ip' => $request->ip(),
            ]);

            // Log registration
            AuditLog::log('registration', $user, [
                'description' => 'New user registered'
            ]);

            // Fire registered event (for email verification if needed)
            event(new Registered($user));

            // Auto-login the user
            Auth::login($user);

            $notification = [
                'message' => 'Account created successfully! Welcome to G-Luper Learning.',
                'alert-type' => 'success'
            ];

            return redirect()->route('learner.dashboard')->with($notification);

        } catch (\Exception $e) {
            $notification = [
                'message' => 'Registration failed. Please try again.',
                'alert-type' => 'error'
            ];

            return back()->withInput()->with($notification);
        }
    }
}