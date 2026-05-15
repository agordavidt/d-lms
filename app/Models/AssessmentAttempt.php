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
        'next_attempt_at',
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
        'started_at'      => 'datetime',
        'submitted_at'    => 'datetime',
        'next_attempt_at' => 'datetime',
        'passed'          => 'boolean',
        'answers'         => 'array',
        'score_earned'    => 'decimal:2',
        'percentage'      => 'decimal:2',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function assessment() { return $this->belongsTo(Assessment::class); }
    public function user()       { return $this->belongsTo(User::class); }
    public function enrollment() { return $this->belongsTo(Enrollment::class); }

    // ── Status checks ─────────────────────────────────────────────────────────

    public function isInProgress(): bool { return $this->status === 'in_progress'; }
    public function isSubmitted(): bool  { return $this->status === 'submitted'; }
    public function isAbandoned(): bool  { return $this->status === 'abandoned'; }

    /**
     * True if this attempt enforces a retry cooldown that hasn't expired yet.
     * Only set on failed final exam attempts.
     */
    public function isOnCooldown(): bool
    {
        return $this->next_attempt_at !== null && $this->next_attempt_at->isFuture();
    }

    /**
     * Human-readable time remaining on the cooldown (e.g. "46 hours").
     */
    public function cooldownRemainingHuman(): string
    {
        if (!$this->isOnCooldown()) return '';
        return $this->next_attempt_at->diffForHumans(now(), ['parts' => 2]);
    }

    // ── Formatters ────────────────────────────────────────────────────────────

    public function getFormattedTimeSpent(): string
    {
        $minutes = floor($this->time_spent_seconds / 60);
        $seconds = $this->time_spent_seconds % 60;

        return $minutes > 0
            ? "{$minutes} min {$seconds} sec"
            : "{$seconds} sec";
    }

    public function getFormattedScore(): string
    {
        return "{$this->score_earned}/{$this->total_points} ({$this->percentage}%)";
    }

    public function getAnswerForQuestion($questionId)
    {
        return $this->answers['question_' . $questionId] ?? null;
    }

    public function isTimeLimitExceeded(): bool
    {
        if (!$this->assessment->time_limit_minutes) return false;
        return $this->time_spent_seconds > ($this->assessment->time_limit_minutes * 60);
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeSubmitted($query)  { return $query->where('status', 'submitted'); }
    public function scopeInProgress($query) { return $query->where('status', 'in_progress'); }
    public function scopePassed($query)     { return $query->where('passed', true); }
    public function scopeFailed($query)     { return $query->where('passed', false)->where('status', 'submitted'); }
    public function scopeForUser($query, $userId) { return $query->where('user_id', $userId); }
}