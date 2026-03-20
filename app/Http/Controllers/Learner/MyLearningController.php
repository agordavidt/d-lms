<?php

namespace App\Http\Controllers\Learner;

use App\Http\Controllers\Controller;
use App\Models\ContentProgress;
use App\Models\Enrollment;
use App\Models\LiveSession;
use App\Models\WeekProgress;
use Illuminate\Http\Request;

class MyLearningController extends Controller
{
    public function index()
    {
        try {
            $user = auth()->user();

            // ── Enrollments ──────────────────────────────────────────────
            $enrollments = $user->enrollments()
                ->with(['program'])
                ->whereIn('status', ['active', 'completed'])
                ->latest('enrolled_at')
                ->get()
                ->map(function ($enrollment) use ($user) {
                    $enrollment->progress_data = $this->resolveProgress($user, $enrollment);
                    return $enrollment;
                });

            // ── Pending enrollment ────────────────────────────────────────
            $pendingEnrollment = $user->enrollments()
                ->with(['program', 'payments'])
                ->where('status', 'pending')
                ->first();

            // CHANGED: sessions belong to programs not cohorts
            $programIds = $user->enrollments()
                ->where('status', 'active')
                ->pluck('program_id')
                ->filter();

            $upcomingSessions = collect();

            if ($programIds->isNotEmpty()) {
                $upcomingSessions = LiveSession::whereIn('program_id', $programIds)
                    ->upcoming()
                    ->with(['mentor', 'program'])
                    ->orderBy('start_time')
                    ->limit(25)
                    ->get();
            }

            return view('learner.my-learning', compact(
                'enrollments',
                'pendingEnrollment',
                'upcomingSessions'
            ));

        } catch (\Exception $e) {
            return back()->with([
                'message'    => 'Unable to load your learning page. Please try again.',
                'alert-type' => 'error',
            ]);
        }
    }

    /**
     * AJAX — calendar events.
     * CHANGED: program_id replaces cohort_id; manual mapping replaces calendar_event accessor.
     */
    public function events(Request $request)
    {
        try {
            $user = auth()->user();

            $programIds = $user->enrollments()
                ->where('status', 'active')
                ->pluck('program_id')
                ->filter();

            if ($programIds->isEmpty()) {
                return response()->json([]);
            }

            $query = LiveSession::whereIn('program_id', $programIds)
                ->with(['mentor', 'program']);

            if ($request->filled('start') && $request->filled('end')) {
                $query->whereBetween('start_time', [$request->start, $request->end]);
            }

            $events = $query->get()->map(fn ($s) => [
                'id'    => $s->id,
                'title' => $s->title,
                'start' => $s->start_time->toIso8601String(),
                'end'   => $s->end_time->toIso8601String(),
                'extendedProps' => [
                    'program'   => $s->program?->name ?? '',
                    'meet_link' => $s->meet_link,
                    'mentor'    => $s->mentor
                                    ? $s->mentor->first_name . ' ' . $s->mentor->last_name
                                    : 'Admin',
                ],
            ]);

            return response()->json($events);

        } catch (\Exception $e) {
            return response()->json([], 500);
        }
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    private function resolveProgress($user, $enrollment): array
    {
        try {
            $totalWeeks = WeekProgress::where('enrollment_id', $enrollment->id)->count();

            $completedWeeks = WeekProgress::where('enrollment_id', $enrollment->id)
                ->where('is_completed', true)
                ->count();

            $lastAccessed = ContentProgress::where('user_id', $user->id)
                ->where('enrollment_id', $enrollment->id)
                ->orderByDesc('last_accessed_at')
                ->value('last_accessed_at');

            $overallPct = $totalWeeks > 0
                ? round(($completedWeeks / $totalWeeks) * 100)
                : 0;

            $hasStarted = ContentProgress::where('user_id', $user->id)
                ->where('enrollment_id', $enrollment->id)
                ->exists();

            return [
                'percentage'      => $overallPct,
                'completed_weeks' => $completedWeeks,
                'total_weeks'     => $totalWeeks,
                'has_started'     => $hasStarted,
                'last_accessed'   => $lastAccessed,
            ];

        } catch (\Exception $e) {
            return [
                'percentage'      => 0,
                'completed_weeks' => 0,
                'total_weeks'     => 0,
                'has_started'     => false,
                'last_accessed'   => null,
            ];
        }
    }
}


