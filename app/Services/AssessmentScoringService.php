<?php

namespace App\Services;

use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\Enrollment;
use App\Models\User;
use App\Models\WeekProgress;
use Illuminate\Support\Facades\DB;

class AssessmentScoringService
{
    // ── Create attempt ────────────────────────────────────────────────────────

    /**
     * Create a new attempt after validating all pre-conditions.
     *
     * Weekly assessment:
     *   - Content for the week must be 100% complete
     *   - No cooldown, unlimited retakes until 100% score
     *
     * Final exam:
     *   - ALL course weeks (content + assessments) must be complete
     *   - Must not be on a 48-hour cooldown from a previous failed attempt
     */
    public function createAttempt(Assessment $assessment, User $user, Enrollment $enrollment): AssessmentAttempt
    {
        if ($assessment->is_final) {
            $this->assertFinalExamEligible($assessment, $user, $enrollment);
        } else {
            $this->assertWeekContentComplete($assessment, $user, $enrollment);
        }

        $attemptNumber = $assessment->attempts()
            ->where('user_id', $user->id)
            ->where('enrollment_id', $enrollment->id)
            ->max('attempt_number') + 1;

        return AssessmentAttempt::create([
            'assessment_id'   => $assessment->id,
            'user_id'         => $user->id,
            'enrollment_id'   => $enrollment->id,
            'attempt_number'  => $attemptNumber,
            'started_at'      => now(),
            'total_questions' => $assessment->total_questions,
            'total_points'    => $assessment->total_points,
            'status'          => 'in_progress',
        ]);
    }

    // ── Score and persist ─────────────────────────────────────────────────────

    /**
     * Score a submitted attempt.
     *
     * Pass threshold:
     *   Weekly  → 100% (every question must be correct)
     *   Final   → assessment->pass_percentage (default 75%)
     *
     * On weekly fail  → score returned, no cooldown, retake immediately
     * On final fail   → next_attempt_at set to +48 hours
     * On final pass   → enrollment.recordFinalExamPass() triggers graduation workflow
     */
    public function submitAttempt(AssessmentAttempt $attempt, array $answers): array
    {
        DB::beginTransaction();

        try {
            $assessment = $attempt->assessment;
            $questions  = $assessment->questions;

            $totalPoints  = 0;
            $earnedPoints = 0;
            $scored       = [];

            foreach ($questions as $question) {
                $totalPoints += $question->points;
                $key          = 'question_' . $question->id;
                $userAnswer   = $answers[$key] ?? null;
                $points       = $question->calculatePoints($userAnswer);
                $earnedPoints += $points;

                $scored[$key] = [
                    'answer'          => $userAnswer,
                    'points_earned'   => $points,
                    'points_possible' => $question->points,
                    'is_correct'      => $points > 0 && $points === (float) $question->points,
                ];
            }

            $percentage = $totalPoints > 0
                ? round(($earnedPoints / $totalPoints) * 100, 2)
                : 0;

            // Use the correct threshold for this assessment type
            $passed = $percentage >= $assessment->getEffectivePassPercentage();

            // Only final exam failed attempts get a cooldown
            $nextAttemptAt = null;
            if ($assessment->is_final && ! $passed) {
                $nextAttemptAt = now()->addHours(Assessment::FINAL_COOLDOWN_HOURS);
            }

            $attempt->update([
                'submitted_at'       => now(),
                'next_attempt_at'    => $nextAttemptAt,
                'time_spent_seconds' => now()->diffInSeconds($attempt->started_at),
                'total_questions'    => $questions->count(),
                'total_points'       => $totalPoints,
                'score_earned'       => $earnedPoints,
                'percentage'         => $percentage,
                'passed'             => $passed,
                'answers'            => $scored,
                'status'             => 'submitted',
            ]);

            // Update week progress and trigger downstream side-effects
            $this->handlePostSubmission($attempt, $percentage, $passed);

            DB::commit();

            return [
                'percentage'      => $percentage,
                'score_earned'    => $earnedPoints,
                'total_points'    => $totalPoints,
                'passed'          => $passed,
                'next_attempt_at' => $nextAttemptAt?->toIso8601String(),
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // ── Post-submission side-effects ──────────────────────────────────────────

    protected function handlePostSubmission(AssessmentAttempt $attempt, float $percentage, bool $passed): void
    {
        $assessment   = $attempt->assessment;
        $week         = $assessment->moduleWeek;
        $weekProgress = $week->getProgressFor($attempt->user, $attempt->enrollment);

        // Always increment attempt count and record time
        $weekProgress->increment('assessment_attempts');
        $weekProgress->update(['last_assessment_at' => now()]);

        if ($passed) {
            $weekProgress->update(['assessment_passed' => true]);
            $weekProgress->refresh();

            // This will mark the week complete if content is also done,
            // then unlock the next week automatically
            $weekProgress->checkAndCompleteWeek();

            // If this was the final exam, trigger graduation workflow
            if ($assessment->is_final) {
                $attempt->enrollment->recordFinalExamPass($percentage);
            }

            // Recalculate overall enrollment progress percentage
            $attempt->enrollment->recalculateProgress();
        }
        // On fail: nothing changes — week stays locked, learner retries
    }

    // ── Pre-condition assertions ──────────────────────────────────────────────

    protected function assertWeekContentComplete(Assessment $assessment, User $user, Enrollment $enrollment): void
    {
        $week         = $assessment->moduleWeek;
        $weekProgress = $week->getProgressFor($user, $enrollment);

        if ((float) $weekProgress->progress_percentage < 100) {
            throw new \Exception('You must complete all required content for this week before taking the assessment.');
        }
    }

    protected function assertFinalExamEligible(Assessment $assessment, User $user, Enrollment $enrollment): void
    {
        // Gate 1: all REGULAR (non-final) weeks must be complete.
        // We deliberately exclude the final exam week's own WeekProgress from this
        // check — it can never be complete before the exam is taken.
        $regularWeekIds = \App\Models\ModuleWeek::whereHas('programModule', fn ($q) =>
                $q->where('program_id', $enrollment->program_id)
            )
            ->whereDoesntHave('assessment', fn ($q) => $q->where('is_final', true))
            ->pluck('id');

        $allRegularWeeksComplete = $regularWeekIds->isNotEmpty()
            && \App\Models\WeekProgress::where('enrollment_id', $enrollment->id)
                ->whereIn('module_week_id', $regularWeekIds)
                ->where('is_completed', false)
                ->doesntExist();

        if (! $allRegularWeeksComplete) {
            throw new \Exception('You must complete all course modules before taking the final examination.');
        }

        // Gate 2: 48-hour cooldown on a previous failed attempt
        $lastAttempt = $assessment->attempts()
            ->where('user_id', $user->id)
            ->where('enrollment_id', $enrollment->id)
            ->where('status', 'submitted')
            ->latest()
            ->first();

        if ($lastAttempt && $lastAttempt->next_attempt_at && $lastAttempt->next_attempt_at->isFuture()) {
            throw new \Exception(
                'You must wait until ' .
                $lastAttempt->next_attempt_at->format('M d, Y \a\t g:i A') .
                ' before retrying the final examination.'
            );
        }
    }

    // ── Results helper ────────────────────────────────────────────────────────

    /**
     * Build a detailed results array for weekly assessment result pages.
     * NOT used for final exam (score only shown there).
     */
    public function getAttemptResults(AssessmentAttempt $attempt): array
    {
        $results = [];

        foreach ($attempt->assessment->questions as $question) {
            $key    = 'question_' . $question->id;
            $answer = $attempt->answers[$key] ?? null;

            $results[] = [
                'question'       => $question,
                'user_answer'    => $answer['answer'] ?? null,
                'correct_answer' => $question->correct_answer,
                'is_correct'     => $answer['is_correct'] ?? false,
                'points_earned'  => $answer['points_earned'] ?? 0,
                'max_points'     => $question->points,
                'explanation'    => $question->explanation,
            ];
        }

        return $results;
    }

    public function abandonAttempt(AssessmentAttempt $attempt): void
    {
        if ($attempt->status === 'in_progress') {
            $attempt->update(['status' => 'abandoned', 'submitted_at' => now()]);
        }
    }
}