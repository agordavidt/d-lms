<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'assessment_id',
        'user_id',
        'enrollment_id',
        'attempt_number',
        'started_at',
        'submitted_at',
        'time_spent_seconds',
        'total_questions',
        'total_points',
        'score_earned',
        'percentage',
        'passed',
        'answers',
        'status',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
        'passed' => 'boolean',
        'answers' => 'array',
        'score_earned' => 'decimal:2',
        'percentage' => 'decimal:2',
    ];

    // Relationships
    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    // Status checks
    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }

    public function isAbandoned(): bool
    {
        return $this->status === 'abandoned';
    }

    // Get formatted time spent
    public function getFormattedTimeSpent(): string
    {
        $minutes = floor($this->time_spent_seconds / 60);
        $seconds = $this->time_spent_seconds % 60;
        
        if ($minutes > 0) {
            return "{$minutes} min {$seconds} sec";
        }
        return "{$seconds} sec";
    }

    // Get formatted score
    public function getFormattedScore(): string
    {
        return "{$this->score_earned}/{$this->total_points} ({$this->percentage}%)";
    }

    // Get pass/fail badge
    public function getStatusBadge(): string
    {
        if ($this->passed) {
            return '<span class="badge badge-success">PASSED</span>';
        }
        return '<span class="badge badge-danger">FAILED</span>';
    }

    // Get answer for specific question
    public function getAnswerForQuestion($questionId)
    {
        $questionKey = "question_{$questionId}";
        return $this->answers[$questionKey] ?? null;
    }

    // Check if time limit exceeded
    public function isTimeLimitExceeded(): bool
    {
        if (!$this->assessment->time_limit_minutes) {
            return false;
        }

        $timeLimit = $this->assessment->time_limit_minutes * 60; // Convert to seconds
        return $this->time_spent_seconds > $timeLimit;
    }

    // Scopes
    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopePassed($query)
    {
        return $query->where('passed', true);
    }

    public function scopeFailed($query)
    {
        return $query->where('passed', false)->where('status', 'submitted');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}