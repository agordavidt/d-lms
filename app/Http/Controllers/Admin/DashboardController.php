<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cohort;
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
        // Key Performance Indicators
        $stats = [
            // Users
            'total_users' => User::count(),
            'total_learners' => User::where('role', 'learner')->count(),
            'total_mentors' => User::where('role', 'mentor')->count(),
            'active_users' => User::where('status', 'active')->count(),
            'new_users_this_month' => User::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            
            // Programs & Cohorts
            'total_programs' => Program::count(),
            'active_programs' => Program::where('status', 'active')->count(),
            'total_cohorts' => Cohort::count(),
            'active_cohorts' => Cohort::whereIn('status', ['upcoming', 'ongoing'])->count(),
            
            // Enrollments
            'total_enrollments' => Enrollment::count(),
            'active_enrollments' => Enrollment::where('status', 'active')->count(),
            'pending_enrollments' => Enrollment::where('status', 'pending')->count(),
            'enrollments_this_month' => Enrollment::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            
            // Sessions
            'total_sessions' => LiveSession::count(),
            'upcoming_sessions' => LiveSession::where('status', 'scheduled')
                ->where('start_time', '>', now())
                ->count(),
            'completed_sessions' => LiveSession::where('status', 'completed')->count(),
            'sessions_this_week' => LiveSession::whereBetween('start_time', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ])->count(),
            
            // Revenue
            'total_revenue' => Payment::where('status', 'successful')->sum('final_amount'),
            'revenue_this_month' => Payment::where('status', 'successful')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('final_amount'),
            'pending_payments' => Payment::where('status', 'pending')->count(),
            'successful_payments' => Payment::where('status', 'successful')->count(),
        ];

        // User Registration Trend (Last 6 months)
        $userRegistrations = User::where('created_at', '>=', now()->subMonths(6))
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('count', 'month')
            ->toArray();

        // Fill missing months with 0
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i)->format('Y-m');
            $months[$month] = $userRegistrations[$month] ?? 0;
        }

        // Revenue Trend (Last 6 months)
        $revenueTrend = Payment::where('status', 'successful')
            ->where('created_at', '>=', now()->subMonths(6))
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, SUM(final_amount) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('total', 'month')
            ->toArray();

        $revenueMonths = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i)->format('Y-m');
            $revenueMonths[$month] = $revenueTrend[$month] ?? 0;
        }

        // Enrollment by Program
        $enrollmentsByProgram = Enrollment::join('programs', 'enrollments.program_id', '=', 'programs.id')
            ->selectRaw('programs.name, COUNT(*) as count')
            ->groupBy('programs.name')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        // User Roles Distribution
        $userRoles = User::selectRaw('role, COUNT(*) as count')
            ->groupBy('role')
            ->get();

        // Recent successful payments
        $recentPayments = Payment::with(['user', 'program'])
            ->where('status', 'successful')
            ->latest()
            ->take(5)
            ->get();

        // Upcoming sessions (next 5)
        $upcomingSessions = LiveSession::with(['program', 'cohort', 'mentor'])
            ->where('status', 'scheduled')
            ->where('start_time', '>', now())
            ->orderBy('start_time')
            ->take(5)
            ->get();

        // Top performing programs (by enrollment)
        $topPrograms = Program::withCount(['enrollments' => function($query) {
                $query->where('status', 'active');
            }])
            ->orderByDesc('enrollments_count')
            ->take(5)
            ->get();

        // Calculate growth rates
        $lastMonthUsers = User::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();
        
        $stats['user_growth_rate'] = $lastMonthUsers > 0 
            ? round((($stats['new_users_this_month'] - $lastMonthUsers) / $lastMonthUsers) * 100, 1)
            : 100;

        $lastMonthRevenue = Payment::where('status', 'successful')
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->sum('final_amount');
        
        $stats['revenue_growth_rate'] = $lastMonthRevenue > 0 
            ? round((($stats['revenue_this_month'] - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1)
            : 100;

        return view('admin.dashboard', compact(
            'stats',
            'months',
            'revenueMonths',
            'enrollmentsByProgram',
            'userRoles',
            'recentPayments',
            'upcomingSessions',
            'topPrograms'
        ));
    }
}