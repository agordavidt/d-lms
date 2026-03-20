<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\LiveSession;
use App\Models\Payment;
use App\Models\Program;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            // Users
            'total_learners'       => User::where('role', 'learner')->count(),
            'total_mentors'        => User::where('role', 'mentor')->count(),
            'new_users_this_month' => User::whereMonth('created_at', now()->month)
                                         ->whereYear('created_at', now()->year)->count(),

            // Programs
            'active_programs'      => Program::where('status', 'active')->count(),
            'pending_review'       => Program::where('status', 'under_review')->count(),
            'draft_programs'       => Program::where('status', 'draft')->count(),

            // Enrollments
            'active_enrollments'   => Enrollment::where('status', 'active')->count(),
            'enrollments_this_month' => Enrollment::whereMonth('created_at', now()->month)
                                           ->whereYear('created_at', now()->year)->count(),

            // Graduations
            'pending_graduations'  => Enrollment::where('graduation_status', 'pending_review')->count(),

            // Revenue
            'total_revenue'        => Payment::where('status', 'successful')->sum('final_amount'),
            'revenue_this_month'   => Payment::where('status', 'successful')
                                         ->whereMonth('paid_at', now()->month)
                                         ->whereYear('paid_at', now()->year)
                                         ->sum('final_amount'),

            // Sessions
            'upcoming_sessions'    => LiveSession::where('status', 'scheduled')
                                         ->where('start_time', '>', now())->count(),
        ];

        // Revenue trend — last 6 months
        $revenueTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $month  = now()->subMonths($i);
            $key    = $month->format('M Y');
            $revenueTrend[$key] = Payment::where('status', 'successful')
                ->whereMonth('paid_at', $month->month)
                ->whereYear('paid_at', $month->year)
                ->sum('final_amount');
        }

        // Enrollment trend — last 6 months
        $enrollmentTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $month  = now()->subMonths($i);
            $key    = $month->format('M Y');
            $enrollmentTrend[$key] = Enrollment::whereMonth('created_at', $month->month)
                ->whereYear('created_at', $month->year)->count();
        }

        // Programs pending review
        $pendingPrograms = Program::with('mentor')
            ->where('status', 'under_review')
            ->orderBy('submitted_at')
            ->take(5)
            ->get();

        // Upcoming sessions (next 5)
        $upcomingSessions = LiveSession::with(['program', 'mentor'])
            ->where('status', 'scheduled')
            ->where('start_time', '>', now())
            ->orderBy('start_time')
            ->take(5)
            ->get();

        // Recent payments
        $recentPayments = Payment::with(['user', 'program'])
            ->where('status', 'successful')
            ->latest('paid_at')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'stats', 'revenueTrend', 'enrollmentTrend',
            'pendingPrograms', 'upcomingSessions', 'recentPayments'
        ));
    }
}