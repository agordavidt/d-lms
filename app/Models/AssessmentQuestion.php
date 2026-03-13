<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssessmentQuestion extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'assessment_id', 'question_type', 'question_text',
        'options', 'correct_answer', 'explanation', 'points', 'order',
    ];

    protected $casts = [
        'options'        => 'array',
        'correct_answer' => 'array',
    ];

    public function assessment() { return $this->belongsTo(Assessment::class); }

    /**
     * Score a given answer against this question.
     * Returns points earned (0 or full points — no partial credit).
     */
    public function calculatePoints($userAnswer): int|float
    {
        if ($userAnswer === null) return 0;

        $correct = $this->correct_answer; // always an array e.g. ["Option A"]

        return match ($this->question_type) {
            'multiple_choice', 'true_false' => $this->scoreExact($userAnswer, $correct),
            'multiple_select'               => $this->scoreMultiSelect($userAnswer, $correct),
            default                         => 0,
        };
    }

    /** Readable display of the correct answer(s) for results screen */
    public function getCorrectAnswerDisplay(): string
    {
        return implode(', ', $this->correct_answer ?? []);
    }

    /** Check if a given answer is fully correct (used in results builder) */
    public function checkAnswer($userAnswer): bool
    {
        return $this->calculatePoints($userAnswer) > 0;
    }

    private function scoreExact($userAnswer, array $correct): int|float
    {
        $given = is_array($userAnswer) ? ($userAnswer[0] ?? null) : $userAnswer;
        return in_array($given, $correct, true) ? $this->points : 0;
    }

    private function scoreMultiSelect($userAnswer, array $correct): int|float
    {
        $given = is_array($userAnswer) ? $userAnswer : [$userAnswer];
        sort($given);
        sort($correct);
        return $given === $correct ? $this->points : 0;
    }
}