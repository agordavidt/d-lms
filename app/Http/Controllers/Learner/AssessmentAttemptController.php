<?php

namespace App\Http\Controllers\Learner;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Services\AssessmentScoringService;
use Illuminate\Http\Request;

class AssessmentAttemptController extends Controller
{
    public function __construct(protected AssessmentScoringService $scoringService) {}

    // ── Create attempt ────────────────────────────────────────────────────────

    /**
     * AJAX — start a new attempt.
     * Pre-conditions checked in AssessmentScoringService::createAttempt().
     */
    public function createAttempt(Request $request, Assessment $assessment)
    {
        $user       = auth()->user();
        $enrollment = $this->resolveEnrollment($user, $request->input('enrollment_id'), $assessment);

        if (! $enrollment) {
            return response()->json(['success' => false, 'message' => 'No active enrollment found.'], 400);
        }

        try {
            $attempt = $this->scoringService->createAttempt($assessment, $user, $enrollment);
            return response()->json(['success' => true, 'attempt_id' => $attempt->id]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    // ── Show attempt (full-page take view) ────────────────────────────────────

    public function show(AssessmentAttempt $attempt)
    {
        $user = auth()->user();
        if ($attempt->user_id !== $user->id) abort(403);

        if ($attempt->status === 'submitted') {
            return $attempt->assessment->is_final
                ? redirect()->route('learner.attempts.final-result', $attempt->id)
                : redirect()->route('learner.attempts.results', $attempt->id);
        }

        $assessment = $attempt->assessment;

        // Auto-submit if time limit exceeded
        if ($assessment->time_limit_minutes && $attempt->isTimeLimitExceeded()) {
            $this->scoringService->submitAttempt($attempt, []);
            $attempt->refresh();
            return $assessment->is_final
                ? redirect()->route('learner.attempts.final-result', $attempt->id)
                    ->with(['message' => 'Time limit exceeded. Auto-submitted.', 'alert-type' => 'warning'])
                : redirect()->route('learner.attempts.results', $attempt->id)
                    ->with(['message' => 'Time limit exceeded. Auto-submitted.', 'alert-type' => 'warning']);
        }

        $questions = $assessment->getQuestionsForAttempt();

        return view('learner.assessments.take', compact('attempt', 'assessment', 'questions'));
    }

    // ── Submit ────────────────────────────────────────────────────────────────

    /**
     * AJAX — submit answers.
     *
     * Both weekly and final return score + passed only.
     * No question breakdown is ever returned (prevents answer-guessing on retry).
     *
     * Weekly fail  → immediate retry, no cooldown
     * Final fail   → next_attempt_at returned so UI can show countdown
     * Final pass   → graduation workflow triggered inside scoring service
     */
    public function submit(Request $request, AssessmentAttempt $attempt)
    {
        $user = auth()->user();

        if ($attempt->user_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        if ($attempt->status === 'submitted') {
            return response()->json(['success' => false, 'message' => 'Already submitted.'], 400);
        }

        // Normalise JS array [{question_id, answer}] → keyed ['question_N' => answer]
        $answers = [];
        foreach ($request->input('answers', []) as $item) {
            if (! empty($item['question_id'])) {
                $answers['question_' . $item['question_id']] = $item['answer'] ?? null;
            }
        }

        try {
            $result = $this->scoringService->submitAttempt($attempt, $answers);
            $attempt->refresh();

            // Score only — no question breakdown regardless of assessment type
            return response()->json([
                'success'         => true,
                'score'           => (float) $attempt->percentage,
                'passed'          => (bool)  $attempt->passed,
                'is_final'        => $attempt->assessment->is_final,
                'next_attempt_at' => $result['next_attempt_at'] ?? null,
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ── Results pages ─────────────────────────────────────────────────────────

    /**
     * Weekly assessment results — shows score and pass/fail.
     * No question breakdown shown (prevents guessing on retake).
     */
    public function results(AssessmentAttempt $attempt)
    {
        $user = auth()->user();
        if ($attempt->user_id !== $user->id) abort(403);
        if ($attempt->assessment->is_final) abort(404); // final exam has its own page

        if ($attempt->status !== 'submitted') {
            return redirect()->route('learner.attempts.show', $attempt->id);
        }

        $assessment   = $attempt->assessment;
        $week         = $assessment->moduleWeek;
        $enrollment   = $attempt->enrollment;
        $weekProgress = $week->getProgressFor($user, $enrollment);
        $allAttempts  = $assessment->getUserAttempts($user)
                            ->where('status', 'submitted')
                            ->sortByDesc('submitted_at');

        return view('learner.assessments.results', compact(
            'attempt', 'assessment', 'week', 'enrollment',
            'weekProgress', 'allAttempts'
        ));
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function resolveEnrollment($user, $enrollmentId, Assessment $assessment)
    {
        // Try the provided enrollment ID first
        if ($enrollmentId) {
            $enrollment = $user->enrollments()
                ->where('id', $enrollmentId)
                ->whereIn('status', ['active', 'completed'])
                ->first();
            if ($enrollment) return $enrollment;
        }

        // Fall back to active enrollment for the program this assessment belongs to
        $programId = $assessment->moduleWeek->programModule->program_id;

        return $user->enrollments()
            ->where('program_id', $programId)
            ->whereIn('status', ['active', 'completed'])
            ->first();
    }
}