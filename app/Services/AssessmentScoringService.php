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
     * Create a new assessment attempt
     */
    public function createAttempt(Assessment $assessment, User $user, Enrollment $enrollment): AssessmentAttempt
    {
        // Get next attempt number
        $attemptNumber = $assessment->attempts()
            ->where('user_id', $user->id)
            ->max('attempt_number') + 1;

        return AssessmentAttempt::create([
            'assessment_id' => $assessment->id,
            'user_id' => $user->id,
            'enrollment_id' => $enrollment->id,
            'attempt_number' => $attemptNumber,
            'started_at' => now(),
            'total_questions' => $assessment->total_questions,
            'total_points' => $assessment->total_points,
            'status' => 'in_progress',
        ]);
    }

    /**
     * Submit and score an attempt (NON-BLOCKING - records score, allows progression)
     */
    public function submitAttempt(AssessmentAttempt $attempt, array $answers): array
    {
        DB::beginTransaction();

        try {
            $assessment = $attempt->assessment;
            $questions = $assessment->questions;

            $totalPoints = 0;
            $earnedPoints = 0;
            $scoredAnswers = [];

            foreach ($questions as $question) {
                $totalPoints += $question->points;
                $userAnswer = $answers[$question->id] ?? null;

                $points = $question->calculatePoints($userAnswer);
                $earnedPoints += $points;

                $scoredAnswers[$question->id] = [
                    'answer' => $userAnswer,
                    'points_earned' => $points,
                    'points_possible' => $question->points,
                    'is_correct' => $points === $question->points,
                ];
            }

            $percentage = $totalPoints > 0 ? ($earnedPoints / $totalPoints) * 100 : 0;
            $passed = $percentage >= $assessment->pass_percentage; // For reference only

            // Update attempt
            $attempt->update([
                'submitted_at' => now(),
                'time_spent_seconds' => now()->diffInSeconds($attempt->started_at),
                'total_questions' => $questions->count(),
                'total_points' => $totalPoints,
                'score_earned' => $earnedPoints,
                'percentage' => round($percentage, 2),
                'passed' => $passed, // Recorded but doesn't block progression
                'answers' => $scoredAnswers,
                'status' => 'submitted',
            ]);

            // Update week progress - record score but DON'T block progression
            $this->updateWeekProgress($attempt, $percentage, $passed);

            DB::commit();

            return [
                'percentage' => round($percentage, 2),
                'score_earned' => $earnedPoints,
                'total_points' => $totalPoints,
                'passed' => $passed,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update week progress with assessment score (non-blocking)
     */
    protected function updateWeekProgress(AssessmentAttempt $attempt, float $percentage, bool $passed): void
    {
        $week = $attempt->assessment->moduleWeek;
        $weekProgress = $week->getProgressFor($attempt->user, $attempt->enrollment);

        // Update assessment fields
        $updates = [
            'assessment_score' => max($weekProgress->assessment_score ?? 0, $percentage), // Keep best score
            'assessment_attempts' => $weekProgress->assessment_attempts + 1,
            'last_assessment_at' => now(),
        ];

        // Only update passed status if this attempt passed
        if ($passed) {
            $updates['assessment_passed'] = true;
        }

        $weekProgress->update($updates);

        // Mark week as complete (assessment taken counts as complete)
        $this->recalculateWeekCompletion($weekProgress);

        // Update enrollment grade averages
        $attempt->enrollment->recalculateGradeAverages();
    }

    /**
     * Recalculate week completion (content done + assessment taken = complete)
     */
    protected function recalculateWeekCompletion(WeekProgress $weekProgress): void
    {
        $week = $weekProgress->moduleWeek;

        // Content must be 100% complete
        if ($weekProgress->progress_percentage < 100) {
            return;
        }

        // If week has assessment, check if taken (at least once)
        if ($week->has_assessment && $week->assessment) {
            if ($weekProgress->assessment_attempts === 0) {
                return; // Assessment not taken yet
            }
        }

        // Mark week as complete
        if (!$weekProgress->is_completed) {
            $weekProgress->markAsComplete(); // This also unlocks next week
        }
    }

    /**
     * Abandon an in-progress attempt
     */
    public function abandonAttempt(AssessmentAttempt $attempt): void
    {
        if ($attempt->isInProgress()) {
            $attempt->update([
                'status' => 'abandoned',
                'submitted_at' => now(),
            ]);
        }
    }

    /**
     * Get detailed results for an attempt
     */
    public function getAttemptResults(AssessmentAttempt $attempt): array
    {
        $questions = $attempt->assessment->questions;
        $results = [];

        foreach ($questions as $question) {
            $answer = $attempt->answers[$question->id] ?? null;

            $results[] = [
                'question' => $question,
                'user_answer' => $answer['answer'] ?? null,
                'correct_answer' => $question->correct_answer,
                'is_correct' => $answer['is_correct'] ?? false,
                'points_earned' => $answer['points_earned'] ?? 0,
                'max_points' => $question->points,
                'explanation' => $question->explanation,
            ];
        }

        return $results;
    }
}