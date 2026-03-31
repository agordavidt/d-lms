<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
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
            'first_name' => ['required', 'string', 'max:255'],
            'last_name'  => ['required', 'string', 'max:255'],
            'email'      => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password'   => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
        ], [
            'first_name.required' => 'First name is required.',
            'last_name.required'  => 'Last name is required.',
            'email.unique' => 'Unable to create account with these details.',
            'password.min'        => 'Password must be at least 8 characters.',
            'password.mixed'      => 'Password must contain both uppercase and lowercase letters.',
            'password.numbers'    => 'Password must contain at least one number.',
            'password.symbols'    => 'Password must contain at least one special character.',
        ]);

        try {
            $user = User::create([
                'first_name'    => $request->first_name,
                'last_name'     => $request->last_name,
                'email'         => $request->email,
                'password'      => Hash::make($request->password),
                'role'          => 'learner',
                'status'        => 'active',
                'last_login_ip' => $request->ip(),
            ]);

            AuditLog::log('registration', $user, ['description' => 'New learner registered']);

            // Fires sendEmailVerificationNotification() via the Registered listener.
            // That method in User.php queues App\Mail\VerifyEmail — the only mail sent.
            event(new Registered($user));

            // Auto-login to allow access to the verification notice page (behind auth middleware).
            $verifyRoute = route('verification.notice');

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'redirect' => $verifyRoute]);
            }
            return redirect($verifyRoute);

        } catch (\Exception $e) {
            Log::error('Registration failed for ' . $request->email . ': ' . $e->getMessage());

            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Registration failed. Please try again.'], 500);
            }

            return back()->withInput()->with(['message' => 'Registration failed. Please try again.', 'alert-type' => 'error']);
        }
    }
}