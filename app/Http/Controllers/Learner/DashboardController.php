<?php

namespace App\Http\Controllers\Learner;

use App\Http\Controllers\Controller;
use App\Models\LiveSession;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Get active enrollments
        $enrollments = $user->enrollments()
            ->with(['program', 'cohort', 'payments'])
            ->whereIn('status', ['active', 'pending'])
            ->latest()
            ->get();

        // Get cohort IDs for enrolled programs
        $cohortIds = $enrollments->pluck('cohort_id');

        // Upcoming sessions (next 7 days)
        $upcomingSessions = LiveSession::whereIn('cohort_id', $cohortIds)
            ->where('start_time', '>', now())
            ->where('start_time', '<=', now()->addDays(7))
            ->where('status', 'scheduled')
            ->with(['program', 'cohort', 'mentor'])
            ->orderBy('start_time')
            ->limit(5)
            ->get();

        // Today's sessions
        $todaySessions = LiveSession::whereIn('cohort_id', $cohortIds)
            ->whereDate('start_time', today())
            ->where('status', 'scheduled')
            ->with(['program', 'cohort', 'mentor'])
            ->orderBy('start_time')
            ->get();

        // Recent sessions (for attendance tracking)
        $recentSessions = LiveSession::whereIn('cohort_id', $cohortIds)
            ->where('end_time', '<=', now())
            ->where('end_time', '>=', now()->subDays(30))
            ->with(['program', 'cohort', 'mentor'])
            ->orderBy('end_time', 'desc')
            ->limit(5)
            ->get();

        // Calculate stats
        $stats = [
            'active_programs' => $enrollments->where('status', 'active')->count(),
            'pending_enrollments' => $enrollments->where('status', 'pending')->count(),
            'upcoming_sessions' => $upcomingSessions->count(),
            'sessions_attended' => $this->getAttendedSessionsCount($user, $cohortIds),
            'total_sessions' => LiveSession::whereIn('cohort_id', $cohortIds)
                ->where('status', 'completed')
                ->count(),
        ];

        // Calculate attendance percentage
        $stats['attendance_percentage'] = $stats['total_sessions'] > 0 
            ? round(($stats['sessions_attended'] / $stats['total_sessions']) * 100, 1)
            : 0;

        return view('learner.dashboard', compact(
            'enrollments',
            'upcomingSessions',
            'todaySessions',
            'recentSessions',
            'stats'
        ));
    }

    private function getAttendedSessionsCount($user, $cohortIds)
    {
        return LiveSession::whereIn('cohort_id', $cohortIds)
            ->where('status', 'completed')
            ->whereJsonContains('attendees', $user->id)
            ->count();
    }
}