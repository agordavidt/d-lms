<?php

namespace App\Http\Controllers\Learner;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Services\AssessmentScoringService;
use Illuminate\Http\Request;

class AssessmentAttemptController extends Controller
{
    protected $scoringService;

    public function __construct(AssessmentScoringService $scoringService)
    {
        $this->scoringService = $scoringService;
    }

    /**
     * Show assessment start page (overview before taking)
     */
    public function start(Assessment $assessment)
    {
        $user = auth()->user();
        $enrollment = $user->enrollments()->where('status', 'active')->first();

        if (!$enrollment) {
            return redirect()->route('learner.programs.index')
                ->with(['message' => 'No active enrollment found.', 'alert-type' => 'error']);
        }

        $week = $assessment->moduleWeek;
        $weekProgress = $week->getProgressFor($user, $enrollment);

        // Check if content is complete
        if (!$weekProgress->canTakeAssessment()) {
            return redirect()->route('learner.learning.index')
                ->with(['message' => 'Please complete all content before taking the assessment.', 'alert-type' => 'warning']);
        }

        // Get user's attempts
        $attempts = $assessment->getUserAttempts($user);
        $attemptsUsed = $attempts->where('status', 'submitted')->count();
        $bestScore = $assessment->getUserBestScore($user);
        $remainingAttempts = $assessment->getRemainingAttempts($user);

        // Check for in-progress attempt
        $inProgressAttempt = $attempts->where('status', 'in_progress')->first();

        return view('learner.assessments.start', compact(
            'assessment',
            'week',
            'weekProgress',
            'attemptsUsed',
            'bestScore',
            'remainingAttempts',
            'inProgressAttempt'
        ));
    }

    /**
     * Create new attempt and redirect to taking page
     */
    public function createAttempt(Request $request, Assessment $assessment)
    {
        $user = auth()->user();
        $enrollment = $user->enrollments()->where('status', 'active')->first();

        if (!$enrollment) {
            return response()->json(['success' => false, 'message' => 'No active enrollment'], 400);
        }

        // Create attempt using service
        try {
            $attempt = $this->scoringService->createAttempt($assessment, $user, $enrollment);

            return response()->json([
                'success' => true,
                'redirect' => route('learner.attempts.show', $attempt->id)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Show assessment taking page (questions)
     */
    public function show(AssessmentAttempt $attempt)
    {
        $user = auth()->user();

        // Security check
        if ($attempt->user_id !== $user->id) {
            abort(403, 'Unauthorized');
        }

        // Check if already submitted
        if ($attempt->status === 'submitted') {
            return redirect()->route('learner.attempts.results', $attempt->id);
        }

        $assessment = $attempt->assessment;
        $questions = $assessment->getQuestionsForAttempt(); // Handles randomization

        // Check time limit
        if ($assessment->time_limit_minutes && $attempt->isTimeLimitExceeded()) {
            // Auto-submit if time exceeded
            $this->scoringService->submitAttempt($attempt, []);
            return redirect()->route('learner.attempts.results', $attempt->id)
                ->with(['message' => 'Time limit exceeded. Assessment auto-submitted.', 'alert-type' => 'warning']);
        }

        return view('learner.assessments.take', compact('attempt', 'assessment', 'questions'));
    }

    /**
     * Submit assessment answers
     */
    public function submit(Request $request, AssessmentAttempt $attempt)
    {
        $user = auth()->user();

        // Security check
        if ($attempt->user_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Check if already submitted
        if ($attempt->status === 'submitted') {
            return response()->json(['success' => false, 'message' => 'Already submitted'], 400);
        }

        $answers = $request->input('answers', []);

        try {
            // Use service to score and submit
            $result = $this->scoringService->submitAttempt($attempt, $answers);

            return response()->json([
                'success' => true,
                'redirect' => route('learner.attempts.results', $attempt->id)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit assessment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show assessment results
     */
    public function results(AssessmentAttempt $attempt)
    {
        $user = auth()->user();

        // Security check
        if ($attempt->user_id !== $user->id) {
            abort(403, 'Unauthorized');
        }

        // Must be submitted
        if ($attempt->status !== 'submitted') {
            return redirect()->route('learner.attempts.show', $attempt->id);
        }

        $assessment = $attempt->assessment;
        $week = $assessment->moduleWeek;
        $weekProgress = $week->getProgressFor($user, $attempt->enrollment);

        // Get detailed results
        $results = $this->scoringService->getAttemptResults($attempt);
        
        // Get all attempts for comparison
        $allAttempts = $assessment->getUserAttempts($user)
            ->where('status', 'submitted')
            ->sortByDesc('submitted_at');

        // Check if week is now complete
        $weekComplete = $weekProgress->isWeekFullyComplete();

        return view('learner.assessments.results', compact(
            'attempt',
            'assessment',
            'week',
            'weekProgress',
            'results',
            'allAttempts',
            'weekComplete'
        ));
    }
}