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
     * Create a new attempt.
     * Rejects if the learner is on a final-exam cooldown.
     */
    public function createAttempt(Assessment $assessment, User $user, Enrollment $enrollment): AssessmentAttempt
    {
        // Block new final-exam attempts during cooldown
        if ($assessment->is_final) {
            $lastAttempt = $assessment->attempts()
                ->where('user_id', $user->id)
                ->where('enrollment_id', $enrollment->id)
                ->where('status', 'submitted')
                ->latest()
                ->first();

            if ($lastAttempt && $lastAttempt->isOnCooldown()) {
                throw new \Exception(
                    'You must wait until ' .
                    $lastAttempt->next_attempt_at->format('M d, Y \a\t g:i A') .
                    ' before retrying the final examination.'
                );
            }
        }

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
     * Score and persist a submitted attempt.
     *
     * Final exam differences:
     *   - Fail: sets next_attempt_at = +48 hours on the attempt record
     *   - Pass: graduation eligibility check fires automatically via
     *            WeekProgress::markAsComplete() → recalculateGradeAverages()
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

            $percentage = $totalPoints > 0 ? ($earnedPoints / $totalPoints) * 100 : 0;
            $passed     = $percentage >= $assessment->pass_percentage;

            // Set cooldown on failed final exam attempts
            $nextAttemptAt = null;
            if ($assessment->is_final && !$passed) {
                $nextAttemptAt = now()->addHours(Assessment::FINAL_COOLDOWN_HOURS);
            }

            $attempt->update([
                'submitted_at'       => now(),
                'next_attempt_at'    => $nextAttemptAt,
                'time_spent_seconds' => now()->diffInSeconds($attempt->started_at),
                'total_questions'    => $questions->count(),
                'total_points'       => $totalPoints,
                'score_earned'       => $earnedPoints,
                'percentage'         => round($percentage, 2),
                'passed'             => $passed,
                'answers'            => $scored,
                'status'             => 'submitted',
            ]);

            $this->updateWeekProgress($attempt, $percentage, $passed);

            DB::commit();

            return [
                'percentage'    => round($percentage, 2),
                'score_earned'  => $earnedPoints,
                'total_points'  => $totalPoints,
                'passed'        => $passed,
                'next_attempt_at' => $nextAttemptAt?->toIso8601String(),
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

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

        // This triggers checkGraduationEligibility() → pending_review if all gates pass
        $attempt->enrollment->recalculateGradeAverages();
    }

    protected function recalculateWeekCompletion(WeekProgress $weekProgress): void
    {
        if ($weekProgress->is_completed) return;
        if ($weekProgress->progress_percentage < 100) return;

        $week = $weekProgress->moduleWeek;
        if ($week->has_assessment && $week->assessment) {
            if (!$weekProgress->assessment_passed) return;
        }

        $weekProgress->markAsComplete();
    }

    public function abandonAttempt(AssessmentAttempt $attempt): void
    {
        if ($attempt->isInProgress()) {
            $attempt->update(['status' => 'abandoned', 'submitted_at' => now()]);
        }
    }

    public function getAttemptResults(AssessmentAttempt $attempt): array
    {
        $results = [];

        foreach ($attempt->assessment->questions as $question) {
            $key    = 'question_' . $question->id;
            $answer = $attempt->answers[$key] ?? null;

            $results[] = [
                'question'      => $question,
                'user_answer'   => $answer['answer'] ?? null,
                'correct_answer'=> $question->correct_answer,
                'is_correct'    => $answer['is_correct'] ?? false,
                'points_earned' => $answer['points_earned'] ?? 0,
                'max_points'    => $question->points,
                'explanation'   => $question->explanation,
            ];
        }

        return $results;
    }
}