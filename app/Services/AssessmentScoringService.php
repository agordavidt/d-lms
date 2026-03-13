<?php

namespace App\Services;

use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\User;
use App\Models\Enrollment;
use App\Models\WeekProgress;
use Illuminate\Support\Facades\DB;

class AssessmentScoringService
{
    /**
     * Create a new assessment attempt.
     */
    public function createAttempt(Assessment $assessment, User $user, Enrollment $enrollment): AssessmentAttempt
    {
        $attemptNumber = $assessment->attempts()
            ->where('user_id', $user->id)
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

    /**
     * Submit and score an attempt.
     *
     * PASS REQUIRED — a learner must achieve >= pass_percentage to have
     * assessment_passed = true on their WeekProgress, which is the gate
     * that allows the next week to unlock. Unlimited attempts are allowed;
     * the learner retakes until they pass.
     *
     * KEY FORMAT: $answers keyed as "question_{id}" => value
     * (AssessmentAttemptController::submit() converts JS array to this format)
     */
    public function submitAttempt(AssessmentAttempt $attempt, array $answers): array
    {
        DB::beginTransaction();

        try {
            $assessment  = $attempt->assessment;
            $questions   = $assessment->questions;

            $totalPoints   = 0;
            $earnedPoints  = 0;
            $scoredAnswers = [];

            foreach ($questions as $question) {
                $totalPoints  += $question->points;
                $key           = 'question_' . $question->id;
                $userAnswer    = $answers[$key] ?? null;
                $points        = $question->calculatePoints($userAnswer);
                $earnedPoints += $points;

                $scoredAnswers[$key] = [
                    'answer'          => $userAnswer,
                    'points_earned'   => $points,
                    'points_possible' => $question->points,
                    'is_correct'      => $points > 0 && $points === (float) $question->points,
                ];
            }

            $percentage = $totalPoints > 0 ? ($earnedPoints / $totalPoints) * 100 : 0;
            $passed     = $percentage >= $assessment->pass_percentage;

            $attempt->update([
                'submitted_at'       => now(),
                'time_spent_seconds' => now()->diffInSeconds($attempt->started_at),
                'total_questions'    => $questions->count(),
                'total_points'       => $totalPoints,
                'score_earned'       => $earnedPoints,
                'percentage'         => round($percentage, 2),
                'passed'             => $passed,
                'answers'            => $scoredAnswers,
                'status'             => 'submitted',
            ]);

            $this->updateWeekProgress($attempt, $percentage, $passed);

            DB::commit();

            return [
                'percentage'   => round($percentage, 2),
                'score_earned' => $earnedPoints,
                'total_points' => $totalPoints,
                'passed'       => $passed,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update WeekProgress after an attempt.
     *
     * - assessment_score    best score across all attempts (never lowered)
     * - assessment_passed   set true when this attempt passes; never reverted
     * - assessment_attempts incremented every attempt regardless of result
     *
     * Week completion (and next-week unlock) only fires when
     * assessment_passed becomes true — see recalculateWeekCompletion().
     */
    protected function updateWeekProgress(AssessmentAttempt $attempt, float $percentage, bool $passed): void
    {
        $week         = $attempt->assessment->moduleWeek;
        $weekProgress = $week->getProgressFor($attempt->user, $attempt->enrollment);

        $updates = [
            'assessment_score'    => max($weekProgress->assessment_score ?? 0, $percentage),
            'assessment_attempts' => $weekProgress->assessment_attempts + 1,
            'last_assessment_at'  => now(),
        ];

        if ($passed) {
            $updates['assessment_passed'] = true;
        }

        $weekProgress->update($updates);
        $weekProgress->refresh();

        $this->recalculateWeekCompletion($weekProgress);

        $attempt->enrollment->recalculateGradeAverages();
    }

    /**
     * Mark week complete when ALL content is done AND assessment is PASSED.
     *
     * BLOCKING GATE: weeks with assessments require assessment_passed = true.
     * A learner who fails cannot complete the week — the next week stays locked.
     * They must revisit content, then retake until they pass.
     *
     * Weeks without assessments complete on content consumption alone.
     */
    protected function recalculateWeekCompletion(WeekProgress $weekProgress): void
    {
        if ($weekProgress->is_completed) {
            return;
        }

        if ($weekProgress->progress_percentage < 100) {
            return;
        }

        $week = $weekProgress->moduleWeek;
        if ($week->has_assessment && $week->assessment) {
            if (!$weekProgress->assessment_passed) {
                return; // Not yet passed — stay locked
            }
        }

        $weekProgress->markAsComplete(); // Unlocks next week
    }

    /**
     * Abandon an in-progress attempt (e.g. timer ran out server-side).
     */
    public function abandonAttempt(AssessmentAttempt $attempt): void
    {
        if ($attempt->isInProgress()) {
            $attempt->update([
                'status'       => 'abandoned',
                'submitted_at' => now(),
            ]);
        }
    }

    /**
     * Build per-question results for display.
     */
    public function getAttemptResults(AssessmentAttempt $attempt): array
    {
        $questions = $attempt->assessment->questions;
        $results   = [];

        foreach ($questions as $question) {
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
}