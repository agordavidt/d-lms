<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\LiveSession;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $mentor = auth()->user();

        // Get mentor's sessions
        $sessions = LiveSession::where('mentor_id', $mentor->id)
            ->with(['program', 'cohort'])
            ->latest('start_time')
            ->get();

        // Upcoming sessions
        $upcomingSessions = $sessions->filter(function($session) {
            return $session->start_time->isFuture() && $session->status === 'scheduled';
        })->take(5);

        // Today's sessions
        $todaySessions = $sessions->filter(function($session) {
            return $session->start_time->isToday();
        });

        // Recent completed sessions
        $recentSessions = $sessions->filter(function($session) {
            return $session->status === 'completed';
        })->take(5);

        // Get cohorts where mentor is teaching
        $cohortIds = $sessions->pluck('cohort_id')->unique();
        
        // Get students in mentor's cohorts
        $students = Enrollment::whereIn('cohort_id', $cohortIds)
            ->where('status', 'active')
            ->with(['user', 'program', 'cohort'])
            ->get();

        // Calculate stats
        $stats = [
            'total_sessions' => $sessions->count(),
            'upcoming_sessions' => $upcomingSessions->count(),
            'completed_sessions' => $sessions->where('status', 'completed')->count(),
            'total_students' => $students->count(),
            'sessions_this_week' => $sessions->filter(function($session) {
                return $session->start_time->isCurrentWeek();
            })->count(),
            'average_attendance' => $this->calculateAverageAttendance($sessions),
        ];

        return view('mentor.dashboard', compact(
            'upcomingSessions',
            'todaySessions',
            'recentSessions',
            'students',
            'stats'
        ));
    }

    private function calculateAverageAttendance($sessions)
    {
        $completedSessions = $sessions->where('status', 'completed');
        
        if ($completedSessions->isEmpty()) {
            return 0;
        }

        $totalAttendance = $completedSessions->sum('total_attendees');
        $sessionCount = $completedSessions->count();

        return $sessionCount > 0 ? round($totalAttendance / $sessionCount, 1) : 0;
    }
}