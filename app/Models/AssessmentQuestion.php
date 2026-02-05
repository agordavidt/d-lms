<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssessmentQuestion extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'assessment_id',
        'question_type',
        'question_text',
        'question_image',
        'points',
        'order',
        'explanation',
        'options',
        'correct_answer',
    ];

    protected $casts = [
        'options' => 'array',
        'correct_answer' => 'array',
    ];

    // Relationships
    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }

    // Question type checks
    public function isMultipleChoice(): bool
    {
        return $this->question_type === 'multiple_choice';
    }

    public function isTrueFalse(): bool
    {
        return $this->question_type === 'true_false';
    }

    public function isMultipleSelect(): bool
    {
        return $this->question_type === 'multiple_select';
    }

    // Get options (randomized if assessment setting is on)
    public function getOptionsForDisplay()
    {
        $options = $this->options;

        if ($this->assessment->randomize_options && $this->isMultipleChoice()) {
            // Randomize while maintaining key-value pairs
            $keys = array_keys($options);
            shuffle($keys);
            $randomized = [];
            foreach ($keys as $key) {
                $randomized[$key] = $options[$key];
            }
            return $randomized;
        }

        return $options;
    }

    // Check if answer is correct
    public function checkAnswer($userAnswer): bool
    {
        switch ($this->question_type) {
            case 'multiple_choice':
            case 'true_false':
                return $userAnswer === $this->correct_answer['answer'];

            case 'multiple_select':
                $correctAnswers = $this->correct_answer['answers'] ?? [];
                sort($correctAnswers);
                sort($userAnswer);
                return $userAnswer === $correctAnswers;

            default:
                return false;
        }
    }

    // Calculate points earned (for partial credit in multiple_select)
    public function calculatePoints($userAnswer): float
    {
        if ($this->checkAnswer($userAnswer)) {
            return $this->points;
        }

        // Partial credit for multiple_select
        if ($this->question_type === 'multiple_select') {
            $correctAnswers = $this->correct_answer['answers'] ?? [];
            $correctCount = count(array_intersect($userAnswer, $correctAnswers));
            $incorrectCount = count(array_diff($userAnswer, $correctAnswers));
            
            // If they selected wrong answers, no partial credit
            if ($incorrectCount > 0) {
                return 0;
            }

            // Award partial credit based on correct selections
            return ($correctCount / count($correctAnswers)) * $this->points;
        }

        return 0;
    }

    // Get correct answer display
    public function getCorrectAnswerDisplay(): string
    {
        switch ($this->question_type) {
            case 'multiple_choice':
                $key = $this->correct_answer['answer'];
                return "{$key}: {$this->options[$key]}";

            case 'true_false':
                return ucfirst($this->correct_answer['answer']);

            case 'multiple_select':
                $answers = [];
                foreach ($this->correct_answer['answers'] as $key) {
                    $answers[] = "{$key}: {$this->options[$key]}";
                }
                return implode(', ', $answers);

            default:
                return '';
        }
    }

    // Scopes
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('question_type', $type);
    }
}