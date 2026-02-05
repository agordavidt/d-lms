<?php

namespace App\Services;

use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\AssessmentQuestion;
use App\Models\User;
use App\Models\Enrollment;
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
     * Submit and score an assessment attempt
     */
    public function submitAttempt(AssessmentAttempt $attempt, array $userAnswers, int $timeSpent): array
    {
        DB::beginTransaction();

        try {
            $assessment = $attempt->assessment;
            $questions = $assessment->questions;
            
            $totalPoints = 0;
            $earnedPoints = 0;
            $scoredAnswers = [];

            foreach ($questions as $question) {
                $questionKey = "question_{$question->id}";
                $userAnswer = $userAnswers[$questionKey] ?? null;

                // Handle different answer formats
                if ($question->isMultipleSelect() && is_string($userAnswer)) {
                    $userAnswer = json_decode($userAnswer, true) ?? [];
                }

                $isCorrect = $question->checkAnswer($userAnswer);
                $pointsEarned = $question->calculatePoints($userAnswer);

                $totalPoints += $question->points;
                $earnedPoints += $pointsEarned;

                $scoredAnswers[$questionKey] = [
                    'question_id' => $question->id,
                    'selected' => $userAnswer,
                    'correct_answer' => $question->correct_answer,
                    'is_correct' => $isCorrect,
                    'points_earned' => $pointsEarned,
                    'max_points' => $question->points,
                ];
            }

            $percentage = $totalPoints > 0 ? ($earnedPoints / $totalPoints) * 100 : 0;
            $passed = $percentage >= $assessment->pass_percentage;

            // Update attempt
            $attempt->update([
                'submitted_at' => now(),
                'time_spent_seconds' => $timeSpent,
                'score_earned' => $earnedPoints,
                'percentage' => round($percentage, 2),
                'passed' => $passed,
                'answers' => $scoredAnswers,
                'status' => 'submitted',
            ]);

            // Update week progress
            $this->updateWeekProgress($attempt, $percentage, $passed);

            DB::commit();

            return [
                'success' => true,
                'attempt' => $attempt,
                'scored_answers' => $scoredAnswers,
                'summary' => [
                    'total_questions' => count($questions),
                    'total_points' => $totalPoints,
                    'earned_points' => $earnedPoints,
                    'percentage' => round($percentage, 2),
                    'passed' => $passed,
                    'pass_percentage' => $assessment->pass_percentage,
                    'time_spent' => $attempt->getFormattedTimeSpent(),
                ],
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update week progress based on assessment result
     */
    protected function updateWeekProgress(AssessmentAttempt $attempt, float $percentage, bool $passed): void
    {
        $weekProgress = \App\Models\WeekProgress::where('user_id', $attempt->user_id)
            ->where('module_week_id', $attempt->assessment->module_week_id)
            ->first();

        if (!$weekProgress) {
            return;
        }

        // Update assessment fields
        $weekProgress->assessment_attempts += 1;
        $weekProgress->last_assessment_at = now();

        // Only update score if this is better than previous
        if (!$weekProgress->assessment_score || $percentage > $weekProgress->assessment_score) {
            $weekProgress->assessment_score = round($percentage, 2);
        }

        // Update passed status
        if ($passed && !$weekProgress->assessment_passed) {
            $weekProgress->assessment_passed = true;
            
            // Recalculate week completion
            $this->recalculateWeekCompletion($weekProgress);
        }

        $weekProgress->save();
    }

    /**
     * Recalculate week completion percentage
     */
    protected function recalculateWeekCompletion($weekProgress): void
    {
        $week = $weekProgress->moduleWeek;
        
        // Get content completion
        $contentProgress = $weekProgress->progress_percentage;

        // If week has assessment, factor it in
        if ($week->has_assessment) {
            // 70% content, 30% assessment (or adjust weights as needed)
            $assessmentWeight = 0.3;
            $contentWeight = 0.7;
            
            $assessmentScore = $weekProgress->assessment_passed ? 100 : 
                              ($weekProgress->assessment_score ?? 0);
            
            $totalProgress = ($contentProgress * $contentWeight) + ($assessmentScore * $assessmentWeight);
            $weekProgress->progress_percentage = round($totalProgress);
        }

        // Mark week as completed if all criteria met
        if ($weekProgress->progress_percentage >= 100 && 
            (!$week->has_assessment || $weekProgress->assessment_passed)) {
            $weekProgress->is_completed = true;
            if (!$weekProgress->completed_at) {
                $weekProgress->completed_at = now();
            }
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
            $questionKey = "question_{$question->id}";
            $answer = $attempt->answers[$questionKey] ?? null;

            $results[] = [
                'question' => $question,
                'user_answer' => $answer['selected'] ?? null,
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