<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users' => User::count(),
            'total_learners' => User::where('role', 'learner')->count(),
            'total_mentors' => User::where('role', 'mentor')->count(),
            'total_admins' => User::whereIn('role', ['admin', 'superadmin'])->count(),
            'active_users' => User::where('status', 'active')->count(),
            'suspended_users' => User::where('status', 'suspended')->count(),
            'new_users_this_month' => User::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];

        // Recent users
        $recent_users = User::latest()->take(5)->get();

        // Recent activity from audit logs
        $recent_activities = AuditLog::with('user')
            ->latest()
            ->take(10)
            ->get();

        return view('admin.dashboard', compact('stats', 'recent_users', 'recent_activities'));
    }
}