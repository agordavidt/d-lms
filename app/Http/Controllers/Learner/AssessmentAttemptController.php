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
     * AJAX — Create a new attempt.
     * Blocked if the learner is on a final-exam cooldown.
     */
    public function createAttempt(Request $request, Assessment $assessment)
    {
        $user         = auth()->user();
        $enrollmentId = $request->input('enrollment_id');

        $enrollment = $user->enrollments()
            ->where('id', $enrollmentId)
            ->whereIn('status', ['active', 'completed'])
            ->first()
            ?? $user->enrollments()->where('status', 'active')->first();

        if (!$enrollment) {
            return response()->json(['success' => false, 'message' => 'No active enrollment found.'], 400);
        }

        try {
            $attempt = $this->scoringService->createAttempt($assessment, $user, $enrollment);

            return response()->json(['success' => true, 'attempt_id' => $attempt->id]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * Show the assessment taking page (non-inline/full-page flow).
     */
   public function show(AssessmentAttempt $attempt)
    {
        $user = auth()->user();
        if ($attempt->user_id !== $user->id) abort(403);
    
        if ($attempt->status === 'submitted') {
            // Final exam goes to score-only result page
            if ($attempt->assessment->is_final) {
                return redirect()->route('learner.attempts.final-result', $attempt->id);
            }
            return redirect()->route('learner.attempts.results', $attempt->id);
        }
    
        $assessment = $attempt->assessment;
        $questions  = $assessment->getQuestionsForAttempt();
    
        if ($assessment->time_limit_minutes && $attempt->isTimeLimitExceeded()) {
            $this->scoringService->submitAttempt($attempt, []);
            $attempt->refresh();
            $route = $assessment->is_final ? 'learner.attempts.final-result' : 'learner.attempts.results';
            return redirect()->route($route, $attempt->id)
                ->with(['message' => 'Time limit exceeded. Auto-submitted.', 'alert-type' => 'warning']);
        }
    
        return view('learner.assessments.take', compact('attempt', 'assessment', 'questions'));
    }

    /**
     * AJAX — Submit answers.
     *
     * Final exam: returns only { success, score, passed, next_attempt_at } — no question breakdown.
     * Weekly:     returns full { success, score, passed, question_results }.
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

        // Convert JS array [{question_id, answer}] to keyed format { "question_1": "answer" }
        $rawAnswers = $request->input('answers', []);
        $answers    = [];
        foreach ($rawAnswers as $item) {
            if (!empty($item['question_id'])) {
                $answers['question_' . $item['question_id']] = $item['answer'] ?? null;
            }
        }

        try {
            $result = $this->scoringService->submitAttempt($attempt, $answers);
            $attempt->refresh();

            $assessment = $attempt->assessment->load('questions');

            // ── Final exam: score only, no question breakdown ─────────────────
            if ($assessment->is_final) {
                return response()->json([
                    'success'         => true,
                    'score'           => (float) $attempt->percentage,
                    'passed'          => (bool)  $attempt->passed,
                    'next_attempt_at' => $result['next_attempt_at'],
                ]);
            }

            // ── Weekly assessment: full question breakdown ─────────────────────
            $questionResults = $assessment->questions->map(function ($question) use ($attempt) {
                $key             = 'question_' . $question->id;
                $submittedAnswer = $attempt->answers[$key] ?? null;
                $isCorrect       = $question->checkAnswer($submittedAnswer['answer'] ?? $submittedAnswer);

                return [
                    'question_id'    => $question->id,
                    'question_text'  => $question->question_text,
                    'is_correct'     => $isCorrect,
                    'correct_answer' => $isCorrect ? null : $question->getCorrectAnswerDisplay(),
                ];
            })->values()->all();

            return response()->json([
                'success'          => true,
                'score'            => (float) $attempt->percentage,
                'passed'           => (bool)  $attempt->passed,
                'score_earned'     => $attempt->score_earned,
                'total_points'     => $attempt->total_points,
                'question_results' => $questionResults,
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Full-page results view (weekly assessments).
     */
    public function results(AssessmentAttempt $attempt)
    {
        $user = auth()->user();
        if ($attempt->user_id !== $user->id) abort(403);

        if ($attempt->status !== 'submitted') {
            return redirect()->route('learner.attempts.show', $attempt->id);
        }

        $assessment   = $attempt->assessment;
        $week         = $assessment->moduleWeek;
        $enrollment   = $attempt->enrollment;
        $weekProgress = $week->getProgressFor($user, $enrollment);
        $results      = $this->scoringService->getAttemptResults($attempt);
        $allAttempts  = $assessment->getUserAttempts($user)->where('status', 'submitted')->sortByDesc('submitted_at');
        $weekComplete = $weekProgress->isWeekFullyComplete();

        return view('learner.assessments.results', compact(
            'attempt', 'assessment', 'week', 'enrollment',
            'weekProgress', 'results', 'allAttempts', 'weekComplete'
        ));
    }
}