<?php

namespace App\Http\Controllers\Learner;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = auth()->user();
        return view('learner.profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        try {
            $oldValues = $user->only(['first_name', 'last_name', 'email', 'phone']);

            $data = $request->only(['first_name', 'last_name', 'email', 'phone']);

            // Handle avatar upload
            if ($request->hasFile('avatar')) {
                // Delete old avatar if exists
                if ($user->avatar) {
                    Storage::disk('public')->delete($user->avatar);
                }

                $avatarPath = $request->file('avatar')->store('avatars', 'public');
                $data['avatar'] = $avatarPath;
            }

            $user->update($data);

            AuditLog::log('profile_updated', $user, [
                'description' => 'User updated their profile',
                'model_type' => get_class($user),
                'model_id' => $user->id,
                'old_values' => $oldValues,
                'new_values' => $user->only(['first_name', 'last_name', 'email', 'phone'])
            ]);

            return back()->with([
                'message' => 'Profile updated successfully!',
                'alert-type' => 'success'
            ]);

        } catch (\Exception $e) {
            return back()->with([
                'message' => 'Failed to update profile: ' . $e->getMessage(),
                'alert-type' => 'error'
            ]);
        }
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
        ]);

        $user = auth()->user();

        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->with([
                'message' => 'Current password is incorrect!',
                'alert-type' => 'error'
            ]);
        }

        try {
            $user->update([
                'password' => Hash::make($request->password)
            ]);

            AuditLog::log('password_changed', $user, [
                'description' => 'User changed their password',
                'model_type' => get_class($user),
                'model_id' => $user->id,
            ]);

            return back()->with([
                'message' => 'Password updated successfully!',
                'alert-type' => 'success'
            ]);

        } catch (\Exception $e) {
            return back()->with([
                'message' => 'Failed to update password: ' . $e->getMessage(),
                'alert-type' => 'error'
            ]);
        }
    }
}