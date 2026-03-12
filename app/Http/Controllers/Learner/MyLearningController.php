<?php

namespace App\Http\Controllers\Learner;

use App\Http\Controllers\Controller;
use App\Models\ContentProgress;
use App\Models\LiveSession;
use App\Models\WeekProgress;
use Illuminate\Http\Request;

class MyLearningController extends Controller
{
    /**
     * My Learning — the learner home page.
     * Shows all enrollments (in-progress + completed) and upcoming schedule.
     */
    public function index()
    {
        try {
            $user = auth()->user();

            // ── Enrollments ──────────────────────────────────────────────
            $enrollments = $user->enrollments()
                ->with(['program', 'cohort'])
                ->whereIn('status', ['active', 'completed'])
                ->latest('enrolled_at')
                ->get()
                ->map(function ($enrollment) use ($user) {
                    $enrollment->progress_data = $this->resolveProgress($user, $enrollment);
                    return $enrollment;
                });

            // ── Pending enrollment (payment incomplete) ───────────────────
            $pendingEnrollment = $user->enrollments()
                ->with(['program', 'payments'])
                ->where('status', 'pending')
                ->first();

            // ── Upcoming live sessions for all active cohorts ─────────────
            $cohortIds = $user->enrollments()
                ->where('status', 'active')
                ->pluck('cohort_id')
                ->filter();

            $upcomingSessions = collect();

            if ($cohortIds->isNotEmpty()) {
                $upcomingSessions = LiveSession::whereIn('cohort_id', $cohortIds)
                    ->upcoming()
                    ->with(['mentor', 'cohort.program'])
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
     * AJAX — calendar events for the schedule widget on My Learning.
     * Returns events for a given date range (FullCalendar-compatible).
     */
    public function events(Request $request)
    {
        try {
            $user     = auth()->user();
            $cohortIds = $user->enrollments()
                ->where('status', 'active')
                ->pluck('cohort_id')
                ->filter();

            if ($cohortIds->isEmpty()) {
                return response()->json([]);
            }

            $query = LiveSession::whereIn('cohort_id', $cohortIds)
                ->with(['mentor', 'cohort.program']);

            if ($request->filled('start') && $request->filled('end')) {
                $query->whereBetween('start_time', [
                    $request->start,
                    $request->end,
                ]);
            }

            $events = $query->get()->map(fn ($s) => $s->calendar_event);

            return response()->json($events);

        } catch (\Exception $e) {
            return response()->json([], 500);
        }
    }

    // ── Private helpers ─────────────────────────────────────────────────────

    /**
     * Build a lightweight progress summary for a single enrollment.
     * Avoids N+1 by using aggregate queries.
     */
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

            // Has the learner started at all?
            $hasStarted = ContentProgress::where('user_id', $user->id)
                ->where('enrollment_id', $enrollment->id)
                ->exists();

            return [
                'percentage'     => $overallPct,
                'completed_weeks' => $completedWeeks,
                'total_weeks'    => $totalWeeks,
                'has_started'    => $hasStarted,
                'last_accessed'  => $lastAccessed,
            ];

        } catch (\Exception $e) {
            return [
                'percentage'     => 0,
                'completed_weeks' => 0,
                'total_weeks'    => 0,
                'has_started'    => false,
                'last_accessed'  => null,
            ];
        }
    }
}