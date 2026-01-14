<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index()
    {
        return view('admin.users.index');
    }

    public function getUsersData(Request $request)
    {
        $query = User::query();

        // Filter by role if provided
        if ($request->has('role') && $request->role != '') {
            $query->where('role', $request->role);
        }

        // Filter by status if provided
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Search functionality
        if ($request->has('search') && $request->search['value'] != '') {
            $search = $request->search['value'];
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $totalRecords = User::count();
        $filteredRecords = $query->count();

        // Sorting
        if ($request->has('order')) {
            $columnIndex = $request->order[0]['column'];
            $columnName = $request->columns[$columnIndex]['data'];
            $sortDirection = $request->order[0]['dir'];
            
            if (in_array($columnName, ['first_name', 'last_name', 'email', 'role', 'status', 'created_at'])) {
                $query->orderBy($columnName, $sortDirection);
            }
        } else {
            $query->latest();
        }

        // Pagination
        $start = $request->start ?? 0;
        $length = $request->length ?? 10;
        
        $users = $query->skip($start)->take($length)->get();

        $data = $users->map(function($user) {
            return [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?? 'N/A',
                'role' => $user->role,
                'status' => $user->status,
                'created_at' => $user->created_at->format('M d, Y'),
                'avatar_url' => $user->avatar_url,
                'actions' => $user->id
            ];
        });

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data
        ]);
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['nullable', 'string', 'max:20'],
            'role' => ['required', 'in:admin,mentor,learner'],
            'status' => ['required', 'in:active,suspended,inactive'],
            'password' => ['required', Password::min(8)->mixedCase()->numbers()->symbols()],
        ]);

        try {
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'role' => $request->role,
                'status' => $request->status,
                'password' => Hash::make($request->password),
                'email_verified_at' => now(),
            ]);

            // Log the action
            AuditLog::log('user_created', auth()->user(), [
                'description' => 'Admin created new user',
                'model_type' => User::class,
                'model_id' => $user->id,
                'new_values' => $user->only(['first_name', 'last_name', 'email', 'role', 'status'])
            ]);

            $notification = [
                'message' => 'User created successfully!',
                'alert-type' => 'success'
            ];

            return redirect()->route('admin.users.index')->with($notification);

        } catch (\Exception $e) {
            $notification = [
                'message' => 'Failed to create user. Please try again.',
                'alert-type' => 'error'
            ];

            return back()->withInput()->with($notification);
        }
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        
        // Prevent editing super admin by regular admin
        if ($user->role === 'superadmin' && !auth()->user()->isSuperAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Prevent editing super admin by regular admin
        if ($user->role === 'superadmin' && !auth()->user()->isSuperAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'role' => ['required', 'in:admin,mentor,learner,superadmin'],
            'status' => ['required', 'in:active,suspended,inactive'],
            'password' => ['nullable', Password::min(8)->mixedCase()->numbers()->symbols()],
        ]);

        try {
            $oldValues = $user->only(['first_name', 'last_name', 'email', 'phone', 'role', 'status']);

            $user->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'role' => $request->role,
                'status' => $request->status,
            ]);

            // Update password if provided
            if ($request->filled('password')) {
                $user->update(['password' => Hash::make($request->password)]);
            }

            $newValues = $user->only(['first_name', 'last_name', 'email', 'phone', 'role', 'status']);

            // Log the action
            AuditLog::log('user_updated', auth()->user(), [
                'description' => 'Admin updated user information',
                'model_type' => User::class,
                'model_id' => $user->id,
                'old_values' => $oldValues,
                'new_values' => $newValues
            ]);

            $notification = [
                'message' => 'User updated successfully!',
                'alert-type' => 'success'
            ];

            return redirect()->route('admin.users.index')->with($notification);

        } catch (\Exception $e) {
            $notification = [
                'message' => 'Failed to update user. Please try again.',
                'alert-type' => 'error'
            ];

            return back()->withInput()->with($notification);
        }
    }

    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);

            // Prevent deleting super admin
            if ($user->role === 'superadmin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete Super Admin account.'
                ], 403);
            }

            // Prevent self-deletion
            if ($user->id === auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot delete your own account.'
                ], 403);
            }

            // Log before deletion
            AuditLog::log('user_deleted', auth()->user(), [
                'description' => 'Admin deleted user',
                'model_type' => User::class,
                'model_id' => $user->id,
                'old_values' => $user->only(['first_name', 'last_name', 'email', 'role', 'status'])
            ]);

            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user.'
            ], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            // Prevent changing super admin status
            if ($user->role === 'superadmin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot change Super Admin status.'
                ], 403);
            }

            $request->validate([
                'status' => ['required', 'in:active,suspended,inactive']
            ]);

            $oldStatus = $user->status;
            $user->update(['status' => $request->status]);

            // Log the action
            AuditLog::log('user_status_changed', auth()->user(), [
                'description' => "Admin changed user status from {$oldStatus} to {$request->status}",
                'model_type' => User::class,
                'model_id' => $user->id,
                'old_values' => ['status' => $oldStatus],
                'new_values' => ['status' => $request->status]
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User status updated successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status.'
            ], 500);
        }
    }
}