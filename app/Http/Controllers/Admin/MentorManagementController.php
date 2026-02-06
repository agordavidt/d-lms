<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\MentorAccountCreated;
use App\Models\AuditLog;
use App\Models\User;
use App\Models\Cohort;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;

class MentorManagementController extends Controller
{
    /**
     * Display all mentors with backend filtering
     */
    public function index(Request $request)
    {
        $query = User::where('role', 'mentor')
            ->withCount('cohorts');

        // Search by name or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', '%' . $search . '%')
                  ->orWhere('last_name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $mentors = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        return view('admin.mentors.index', compact('mentors'));
    }

    /**
     * Show create mentor form
     */
    public function create()
    {
        return view('admin.mentors.create');
    }

    /**
     * Store new mentor
     */
    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20',
            'password' => ['required', Password::min(8)->mixedCase()->numbers()->symbols()],
        ]);

        try {
            $mentor = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'role' => 'mentor',
                'status' => 'active',
                'password' => Hash::make($request->password),
                'email_verified_at' => now(),
            ]);

            AuditLog::log('mentor_created', auth()->user(), [
                'description' => 'Admin created new mentor account',
                'model_type' => User::class,
                'model_id' => $mentor->id,
                'new_values' => $mentor->only(['first_name', 'last_name', 'email'])
            ]);

            // Send welcome email
            try {
                Mail::to($mentor->email)->send(new MentorAccountCreated($mentor, $request->password));
            } catch (\Exception $e) {
                \Log::error('Failed to send mentor creation email: ' . $e->getMessage());
            }

            $notification = [
                'message' => 'Mentor account created successfully! Login credentials sent via email.',
                'alert-type' => 'success'
            ];

            return redirect()->route('admin.mentors.index')->with($notification);

        } catch (\Exception $e) {
            $notification = [
                'message' => 'Failed to create mentor account. Please try again.',
                'alert-type' => 'error'
            ];

            return back()->withInput()->with($notification);
        }
    }

    /**
     * Show edit mentor form
     */
    public function edit($id)
    {
        $mentor = User::where('role', 'mentor')->findOrFail($id);
        return view('admin.mentors.edit', compact('mentor'));
    }

    /**
     * Update mentor
     */
    public function update(Request $request, $id)
    {
        $mentor = User::where('role', 'mentor')->findOrFail($id);

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $mentor->id,
            'phone' => 'nullable|string|max:20',
            'status' => 'required|in:active,suspended,inactive',
            'password' => ['nullable', Password::min(8)->mixedCase()->numbers()->symbols()],
        ]);

        try {
            $oldValues = $mentor->only(['first_name', 'last_name', 'email', 'phone', 'status']);

            $mentor->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'status' => $request->status,
            ]);

            if ($request->filled('password')) {
                $mentor->update(['password' => Hash::make($request->password)]);
            }

            $newValues = $mentor->only(['first_name', 'last_name', 'email', 'phone', 'status']);

            AuditLog::log('mentor_updated', auth()->user(), [
                'description' => 'Admin updated mentor information',
                'model_type' => User::class,
                'model_id' => $mentor->id,
                'old_values' => $oldValues,
                'new_values' => $newValues
            ]);

            $notification = [
                'message' => 'Mentor updated successfully!',
                'alert-type' => 'success'
            ];

            return redirect()->route('admin.mentors.index')->with($notification);

        } catch (\Exception $e) {
            $notification = [
                'message' => 'Failed to update mentor. Please try again.',
                'alert-type' => 'error'
            ];

            return back()->withInput()->with($notification);
        }
    }

    /**
     * Update mentor status
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $mentor = User::where('role', 'mentor')->findOrFail($id);

            $request->validate([
                'status' => 'required|in:active,suspended,inactive'
            ]);

            $mentor->update(['status' => $request->status]);

            return response()->json([
                'success' => true,
                'message' => 'Mentor status updated successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status.'
            ], 500);
        }
    }

    /**
     * Delete mentor
     */
    public function destroy($id)
    {
        try {
            $mentor = User::where('role', 'mentor')->findOrFail($id);

            // Check if mentor has active cohorts
            if ($mentor->cohorts()->where('status', 'active')->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete mentor with active cohorts.'
                ], 403);
            }

            AuditLog::log('mentor_deleted', auth()->user(), [
                'description' => 'Admin deleted mentor',
                'model_type' => User::class,
                'model_id' => $mentor->id,
                'old_values' => $mentor->only(['first_name', 'last_name', 'email'])
            ]);

            $mentor->delete();

            return response()->json([
                'success' => true,
                'message' => 'Mentor deleted successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete mentor.'
            ], 500);
        }
    }
}