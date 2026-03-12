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

        if (!$weekProgress->canTakeAssessment()) {
            return redirect()->route('learner.learning.index')
                ->with(['message' => 'Please complete all content before taking the assessment.', 'alert-type' => 'warning']);
        }

        $attempts = $assessment->getUserAttempts($user);
        $attemptsUsed = $attempts->where('status', 'submitted')->count();
        $bestScore = $assessment->getUserBestScore($user);
        $remainingAttempts = $assessment->getRemainingAttempts($user);
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
     * AJAX — Create a new attempt and return its ID for the inline player.
     *
     * Route: POST /learner/assessments/{assessment}/attempt
     * Body:  { enrollment_id: X }
     *
     * FIX #2: Was returning { redirect: url } — now returns { attempt_id: X }
     * FIX #5: Now uses the enrollment_id from the request body instead of
     *         blindly grabbing the first active enrollment.
     */
    public function createAttempt(Request $request, Assessment $assessment)
    {
        $user = auth()->user();

        // FIX #5: Resolve the specific enrollment sent by the inline player.
        $enrollmentId = $request->input('enrollment_id');

        $enrollment = $user->enrollments()
            ->where('id', $enrollmentId)
            ->whereIn('status', ['active', 'completed'])
            ->first();

        // Fallback: if no enrollment_id supplied, find the first active one
        // (preserves backward-compat with any other callers).
        if (!$enrollment) {
            $enrollment = $user->enrollments()
                ->where('status', 'active')
                ->first();
        }

        if (!$enrollment) {
            return response()->json([
                'success' => false,
                'message' => 'No active enrollment found.',
            ], 400);
        }

        try {
            $attempt = $this->scoringService->createAttempt($assessment, $user, $enrollment);

            // FIX #2: Return attempt_id so the inline JS can store it.
            return response()->json([
                'success'    => true,
                'attempt_id' => $attempt->id,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Show assessment taking page (questions) — used by the traditional
     * full-page flow; not called by the inline player.
     */
    public function show(AssessmentAttempt $attempt)
    {
        $user = auth()->user();

        if ($attempt->user_id !== $user->id) {
            abort(403, 'Unauthorized');
        }

        if ($attempt->status === 'submitted') {
            return redirect()->route('learner.attempts.results', $attempt->id);
        }

        $assessment = $attempt->assessment;
        $questions  = $assessment->getQuestionsForAttempt();

        if ($assessment->time_limit_minutes && $attempt->isTimeLimitExceeded()) {
            $this->scoringService->submitAttempt($attempt, []);
            return redirect()->route('learner.attempts.results', $attempt->id)
                ->with(['message' => 'Time limit exceeded. Assessment auto-submitted.', 'alert-type' => 'warning']);
        }

        return view('learner.assessments.take', compact('attempt', 'assessment', 'questions'));
    }

    /**
     * AJAX — Submit answers from the inline player and return scored results.
     *
     * Route: POST /learner/attempts/{attempt}/submit
     * Body:  { answers: [{ question_id: X, answer: "..." }, ...] }
     *
     * FIX #3: Was returning { redirect: url } — now returns
     *         { success, score, passed, question_results } for inline rendering.
     * FIX #6: Converts the JS array format to the keyed format the model
     *         expects before passing to the scoring service.
     */
    public function submit(Request $request, AssessmentAttempt $attempt)
    {
        $user = auth()->user();

        if ($attempt->user_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        if ($attempt->status === 'submitted') {
            return response()->json(['success' => false, 'message' => 'Already submitted'], 400);
        }

        // FIX #6: JS sends [{question_id, answer}, ...].
        // Convert to the keyed format { "question_1": "answer" } that the
        // scoring service and model helpers expect.
        $rawAnswers = $request->input('answers', []);
        $answers    = [];

        foreach ($rawAnswers as $item) {
            if (!empty($item['question_id'])) {
                $answers['question_' . $item['question_id']] = $item['answer'] ?? null;
            }
        }

        try {
            $this->scoringService->submitAttempt($attempt, $answers);

            // Reload the attempt with fresh scored data.
            $attempt->refresh();
            $attempt->load('assessment.questions');

            $assessment = $attempt->assessment;

            // Build per-question result breakdown for the results screen.
            $questionResults = $assessment->questions->map(function ($question) use ($attempt) {
                $key            = 'question_' . $question->id;
                $submittedAnswer = $attempt->answers[$key] ?? null;
                $isCorrect      = $question->checkAnswer($submittedAnswer);

                return [
                    'question_id'    => $question->id,
                    'question_text'  => $question->question_text,
                    'is_correct'     => $isCorrect,
                    'correct_answer' => $isCorrect ? null : $question->getCorrectAnswerDisplay(),
                ];
            })->values()->all();

            // FIX #3: Return inline result data instead of a redirect URL.
            return response()->json([
                'success'          => true,
                'score'            => (float) $attempt->percentage,
                'passed'           => (bool)  $attempt->passed,
                'score_earned'     => $attempt->score_earned,
                'total_points'     => $attempt->total_points,
                'question_results' => $questionResults,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit assessment: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show assessment results page — traditional full-page flow.
     */
    public function results(AssessmentAttempt $attempt)
    {
        $user = auth()->user();

        if ($attempt->user_id !== $user->id) {
            abort(403, 'Unauthorized');
        }

        if ($attempt->status !== 'submitted') {
            return redirect()->route('learner.attempts.show', $attempt->id);
        }

        $assessment  = $attempt->assessment;
        $week        = $assessment->moduleWeek;
        $weekProgress = $week->getProgressFor($user, $attempt->enrollment);
        $results     = $this->scoringService->getAttemptResults($attempt);

        $allAttempts = $assessment->getUserAttempts($user)
            ->where('status', 'submitted')
            ->sortByDesc('submitted_at');

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