<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\LiveSession;
use App\Models\User;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    /**
     * Display students in mentor's cohorts
     */
    public function index(Request $request)
    {
        $mentor = auth()->user();

        // Get cohorts where mentor is teaching
        $cohortIds = LiveSession::where('mentor_id', $mentor->id)
            ->distinct()
            ->pluck('cohort_id');

        $query = Enrollment::whereIn('cohort_id', $cohortIds)
            ->where('status', 'active')
            ->with(['user', 'program', 'cohort']);

        // Filter by program if provided
        if ($request->program_id) {
            $query->where('program_id', $request->program_id);
        }

        // Filter by cohort if provided
        if ($request->cohort_id) {
            $query->where('cohort_id', $request->cohort_id);
        }

        // Search
        if ($request->search) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $students = $query->paginate(20);

        // Get unique programs and cohorts for filters
        $programs = Enrollment::whereIn('cohort_id', $cohortIds)
            ->with('program')
            ->get()
            ->pluck('program')
            ->unique('id');

        $cohorts = Enrollment::whereIn('cohort_id', $cohortIds)
            ->with('cohort')
            ->get()
            ->pluck('cohort')
            ->unique('id');

        return view('mentor.students.index', compact('students', 'programs', 'cohorts'));
    }

    /**
     * Show student details and progress
     */
    public function show($id)
    {
        $mentor = auth()->user();
        
        // Get cohorts where mentor is teaching
        $cohortIds = LiveSession::where('mentor_id', $mentor->id)
            ->distinct()
            ->pluck('cohort_id');

        $student = User::findOrFail($id);

        // Get student's enrollments in mentor's cohorts
        $enrollments = Enrollment::where('user_id', $student->id)
            ->whereIn('cohort_id', $cohortIds)
            ->with(['program', 'cohort'])
            ->get();

        if ($enrollments->isEmpty()) {
            abort(403, 'You do not teach this student.');
        }

        // Get student's attendance in mentor's sessions
        $sessions = LiveSession::where('mentor_id', $mentor->id)
            ->whereIn('cohort_id', $enrollments->pluck('cohort_id'))
            ->with(['program', 'cohort'])
            ->orderBy('start_time', 'desc')
            ->get();

        $attendedSessions = $sessions->filter(function($session) use ($student) {
            return in_array($student->id, $session->attendees ?? []);
        });

        $stats = [
            'total_sessions' => $sessions->where('status', 'completed')->count(),
            'attended_sessions' => $attendedSessions->count(),
            'attendance_percentage' => $sessions->where('status', 'completed')->count() > 0
                ? round(($attendedSessions->count() / $sessions->where('status', 'completed')->count()) * 100, 1)
                : 0,
        ];

        return view('mentor.students.show', compact('student', 'enrollments', 'sessions', 'attendedSessions', 'stats'));
    }
}