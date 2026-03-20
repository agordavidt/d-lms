<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssessmentAttempt;
use App\Models\ContentProgress;
use App\Models\Enrollment;
use App\Models\Program;
use App\Models\User;
use App\Models\WeekContent;
use App\Models\WeekProgress;
use Illuminate\Http\Request;

class LearnerController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'learner')
            ->with(['enrollments' => fn ($q) => $q->whereIn('status', ['active', 'pending'])
                ->with('program')->latest()
            ]);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn ($q) => $q
                ->where('first_name', 'like', "%{$s}%")
                ->orWhere('last_name',  'like', "%{$s}%")
                ->orWhere('email',      'like', "%{$s}%")
            );
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('program_id')) {
            $query->whereHas('enrollments', fn ($q) => $q
                ->where('program_id', $request->program_id)
                ->whereIn('status', ['active', 'pending'])
            );
        }

        $learners = $query->orderByDesc('created_at')->paginate(20)->withQueryString();
        $programs = Program::where('status', 'active')->orderBy('name')->get(['id', 'name']);

        return view('admin.learners.index', compact('learners', 'programs'));
    }

    public function show($id)
    {
        $learner = User::where('role', 'learner')
            ->with(['enrollments.program', 'enrollments.payments'])
            ->findOrFail($id);

        $enrollment = $learner->enrollments()
            ->whereIn('status', ['active', 'completed', 'pending'])
            ->with(['program.modules.weeks.contents'])
            ->latest()
            ->first();

        $progressStats      = null;
        $assessmentStats    = null;
        $weeklyBreakdown    = collect();
        $recentAttempts     = collect();

        if ($enrollment) {
            // FIXED: removed ->where('status','published') — no status column in new schema
            $totalContent = WeekContent::whereHas('moduleWeek.programModule',
                fn ($q) => $q->where('program_id', $enrollment->program_id)
            )->count();

            $completedContent = ContentProgress::where('user_id', $learner->id)
                ->where('enrollment_id', $enrollment->id)
                ->where('is_completed', true)->count();

            $progressStats = [
                'total_content'        => $totalContent,
                'completed_content'    => $completedContent,
                'completion_percentage' => $totalContent > 0
                    ? round(($completedContent / $totalContent) * 100, 1) : 0,
            ];

            $weeklyBreakdown = $this->getWeeklyBreakdown($enrollment);
            $assessmentStats = $this->getAssessmentStats($enrollment);

            $recentAttempts = AssessmentAttempt::where('enrollment_id', $enrollment->id)
                ->where('status', 'submitted')
                ->with(['assessment.moduleWeek'])
                ->orderByDesc('submitted_at')
                ->take(10)
                ->get();
        }

        return view('admin.learners.show', compact(
            'learner', 'enrollment', 'progressStats',
            'assessmentStats', 'weeklyBreakdown', 'recentAttempts'
        ));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|in:active,suspended,inactive']);

        User::where('role', 'learner')->findOrFail($id)
            ->update(['status' => $request->status]);

        return response()->json(['success' => true, 'message' => 'Status updated.']);
    }

    public function showAssessmentAttempt($learnerId, $attemptId)
    {
        $learner = User::where('role', 'learner')->findOrFail($learnerId);
        $attempt = AssessmentAttempt::with(['assessment.questions', 'assessment.moduleWeek', 'enrollment'])
            ->findOrFail($attemptId);

        abort_if($attempt->user_id !== $learner->id, 404);

        return view('admin.learners.assessment-attempt', compact('learner', 'attempt'));
    }

    // ── Private helpers ────────────────────────────────────────────────────────

    private function getWeeklyBreakdown(Enrollment $enrollment)
    {
        return WeekProgress::where('enrollment_id', $enrollment->id)
            ->whereHas('moduleWeek', fn ($q) => $q->where('has_assessment', true)->whereHas('assessment'))
            ->with(['moduleWeek.assessment', 'moduleWeek.programModule'])
            ->orderBy('created_at')
            ->get()
            ->map(function ($wp) use ($enrollment) {
                $assessment = $wp->moduleWeek->assessment;
                $attempts   = AssessmentAttempt::where('enrollment_id', $enrollment->id)
                    ->where('assessment_id', $assessment->id)
                    ->where('status', 'submitted')
                    ->orderBy('submitted_at')
                    ->get();

                return [
                    'week'           => $wp->moduleWeek,
                    'module'         => $wp->moduleWeek->programModule,
                    'assessment'     => $assessment,
                    'week_progress'  => $wp,
                    'attempts'       => $attempts,
                    'best_score'     => $attempts->max('percentage'),
                    'attempts_count' => $attempts->count(),
                    'passed'         => $wp->assessment_passed,
                ];
            });
    }

    private function getAssessmentStats(Enrollment $enrollment): array
    {
        $allAttempts = AssessmentAttempt::where('enrollment_id', $enrollment->id)
            ->where('status', 'submitted')->get();

        $weekProgress = WeekProgress::where('enrollment_id', $enrollment->id)
            ->whereHas('moduleWeek', fn ($q) => $q->where('has_assessment', true)->whereHas('assessment'))
            ->get();

        $total     = $weekProgress->count();
        $attempted = $weekProgress->where('assessment_attempts', '>', 0)->count();
        $passed    = $weekProgress->where('assessment_passed', true)->count();

        return [
            'total_assessments'     => $total,
            'completed_assessments' => $attempted,
            'pending_assessments'   => $total - $attempted,
            'passed_assessments'    => $passed,
            'failed_assessments'    => $attempted - $passed,
            'total_attempts'        => $allAttempts->count(),
            'average_score'         => $enrollment->weekly_assessment_avg ?? 0,
            'highest_score'         => $allAttempts->max('percentage') ?? 0,
            'lowest_score'          => $allAttempts->min('percentage') ?? 0,
        ];
    }
}