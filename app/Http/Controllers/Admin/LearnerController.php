<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Enrollment;
use App\Models\Program;
use App\Models\AssessmentAttempt;
use App\Models\WeekProgress;
use Illuminate\Http\Request;

class LearnerController extends Controller
{
    /**
     * Display all learners with backend filtering
     */
    public function index(Request $request)
    {
        $query = User::where('role', 'learner')
            ->with(['enrollments' => function($q) {
                $q->whereIn('status', ['active', 'pending'])
                  ->with(['program', 'cohort.mentor'])
                  ->latest();
            }]);

        // Search by name or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', '%' . $search . '%')
                  ->orWhere('last_name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by program
        if ($request->filled('program_id')) {
            $query->whereHas('enrollments', function($q) use ($request) {
                $q->where('program_id', $request->program_id)
                  ->whereIn('status', ['active', 'pending']);
            });
        }

        $learners = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        
        // Get programs for filter
        $programs = Program::active()->orderBy('name')->get(['id', 'name']);

        return view('admin.learners.index', compact('learners', 'programs'));
    }

    /**
     * Show learner details with comprehensive assessment tracking
     */
    public function show($id)
    {
        $learner = User::where('role', 'learner')
            ->with([
                'enrollments.program',
                'enrollments.cohort.mentor',
                'enrollments.payments',
            ])
            ->findOrFail($id);

        $enrollment = $learner->enrollments()
            ->whereIn('status', ['active', 'pending'])
            ->with(['program.modules.weeks.contents', 'cohort.mentor'])
            ->first();
        
        // Calculate progress statistics if enrolled
        $progressStats = null;
        $assessmentStats = null;
        $weeklyBreakdown = [];
        $recentAttempts = collect();
        
        if ($enrollment) {
            // Content Progress Stats
            $totalContent = 0;
            foreach ($enrollment->program->modules as $module) {
                foreach ($module->weeks as $week) {
                    $totalContent += $week->contents()->where('status', 'published')->count();
                }
            }

            $completedContent = $learner->contentProgress()
                ->where('is_completed', true)
                ->count();

            $progressStats = [
                'total_content' => $totalContent,
                'completed_content' => $completedContent,
                'completion_percentage' => $totalContent > 0 ? round(($completedContent / $totalContent) * 100, 2) : 0
            ];

            // Assessment Performance Stats
            $weeklyBreakdown = $this->getWeeklyAssessmentBreakdown($enrollment);
            $assessmentStats = $this->calculateAssessmentStats($enrollment);
            
            // Recent Assessment Attempts
            $recentAttempts = AssessmentAttempt::where('enrollment_id', $enrollment->id)
                ->where('status', 'submitted')
                ->with(['assessment.moduleWeek'])
                ->orderBy('submitted_at', 'desc')
                ->take(10)
                ->get();
        }

        return view('admin.learners.show', compact(
            'learner', 
            'enrollment', 
            'progressStats',
            'assessmentStats',
            'weeklyBreakdown',
            'recentAttempts'
        ));
    }

    /**
     * Get weekly assessment breakdown
     */
    private function getWeeklyAssessmentBreakdown($enrollment)
    {
        return WeekProgress::where('enrollment_id', $enrollment->id)
            ->whereHas('moduleWeek', function($q) {
                $q->where('has_assessment', true)
                  ->whereHas('assessment');
            })
            ->with(['moduleWeek.assessment', 'moduleWeek.programModule'])
            ->orderBy('created_at')
            ->get()
            ->map(function($weekProgress) use ($enrollment) {
                $week = $weekProgress->moduleWeek;
                $assessment = $week->assessment;
                
                // Get all attempts for this assessment
                $attempts = AssessmentAttempt::where('enrollment_id', $enrollment->id)
                    ->where('assessment_id', $assessment->id)
                    ->where('status', 'submitted')
                    ->orderBy('submitted_at')
                    ->get();
                
                return [
                    'week' => $week,
                    'module' => $week->programModule,
                    'assessment' => $assessment,
                    'week_progress' => $weekProgress,
                    'attempts' => $attempts,
                    'best_score' => $attempts->max('percentage'),
                    'latest_score' => $attempts->last()->percentage ?? null,
                    'attempts_count' => $attempts->count(),
                    'passed' => $weekProgress->assessment_passed,
                    'status' => $this->determineAssessmentStatus($weekProgress, $attempts, $assessment)
                ];
            });
    }

    /**
     * Calculate overall assessment statistics
     */
    private function calculateAssessmentStats($enrollment)
    {
        $allAttempts = AssessmentAttempt::where('enrollment_id', $enrollment->id)
            ->where('status', 'submitted')
            ->get();

        $weekProgress = WeekProgress::where('enrollment_id', $enrollment->id)
            ->whereHas('moduleWeek', function($q) {
                $q->where('has_assessment', true)->whereHas('assessment');
            })
            ->get();

        $totalAssessments = $weekProgress->count();
        $completedAssessments = $weekProgress->where('assessment_attempts', '>', 0)->count();
        $passedAssessments = $weekProgress->where('assessment_passed', true)->count();

        return [
            'total_assessments' => $totalAssessments,
            'completed_assessments' => $completedAssessments,
            'pending_assessments' => $totalAssessments - $completedAssessments,
            'passed_assessments' => $passedAssessments,
            'failed_assessments' => $completedAssessments - $passedAssessments,
            'total_attempts' => $allAttempts->count(),
            'average_score' => $enrollment->weekly_assessment_avg ?? 0,
            'highest_score' => $allAttempts->max('percentage') ?? 0,
            'lowest_score' => $allAttempts->min('percentage') ?? 0,
        ];
    }

    /**
     * Determine assessment status for display
     */
    private function determineAssessmentStatus($weekProgress, $attempts, $assessment)
    {
        if ($attempts->count() === 0) {
            return 'not_started';
        }
        
        if ($attempts->count() >= $assessment->max_attempts) {
            return 'attempts_exhausted';
        }
        
        if ($weekProgress->assessment_passed) {
            return 'passed';
        }
        
        return 'in_progress';
    }

    /**
     * Update learner status
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $learner = User::where('role', 'learner')->findOrFail($id);

            $request->validate([
                'status' => 'required|in:active,suspended,inactive'
            ]);

            $learner->update(['status' => $request->status]);

            return response()->json([
                'success' => true,
                'message' => 'Learner status updated successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status.'
            ], 500);
        }
    }

    /**
     * Show assessment details for specific attempt
     */
    public function showAssessmentAttempt($learnerId, $attemptId)
    {
        $learner = User::where('role', 'learner')->findOrFail($learnerId);
        
        $attempt = AssessmentAttempt::with([
            'assessment.questions',
            'assessment.moduleWeek',
            'enrollment'
        ])->findOrFail($attemptId);

        // Verify this attempt belongs to this learner
        if ($attempt->user_id !== $learner->id) {
            abort(404);
        }

        // Get scored answers
        $scoredAnswers = $attempt->scored_answers ?? [];

        return view('admin.learners.assessment-attempt', compact('learner', 'attempt', 'scoredAnswers'));
    }
}