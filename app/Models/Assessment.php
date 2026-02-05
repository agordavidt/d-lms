<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Assessment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'module_week_id',
        'title',
        'description',
        'time_limit_minutes',
        'max_attempts',
        'pass_percentage',
        'randomize_questions',
        'randomize_options',
        'show_correct_answers',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'randomize_questions' => 'boolean',
        'randomize_options' => 'boolean',
        'show_correct_answers' => 'boolean',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function moduleWeek()
    {
        return $this->belongsTo(ModuleWeek::class);
    }

    public function questions()
    {
        return $this->hasMany(AssessmentQuestion::class)->orderBy('order');
    }

    public function attempts()
    {
        return $this->hasMany(AssessmentAttempt::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Helper methods
    public function getTotalPointsAttribute(): int
    {
        return $this->questions()->sum('points');
    }

    public function getTotalQuestionsAttribute(): int
    {
        return $this->questions()->count();
    }

    // Get user's attempts for this assessment
    public function getUserAttempts(User $user)
    {
        return $this->attempts()
            ->where('user_id', $user->id)
            ->orderBy('attempt_number')
            ->get();
    }

    // Get user's best score
    public function getUserBestScore(User $user): ?float
    {
        $bestAttempt = $this->attempts()
            ->where('user_id', $user->id)
            ->where('status', 'submitted')
            ->orderBy('percentage', 'desc')
            ->first();

        return $bestAttempt ? $bestAttempt->percentage : null;
    }

    // Check if user can take assessment
    public function canUserTakeAssessment(User $user): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $attemptsCount = $this->attempts()
            ->where('user_id', $user->id)
            ->where('status', 'submitted')
            ->count();

        return $attemptsCount < $this->max_attempts;
    }

    // Get remaining attempts for user
    public function getRemainingAttempts(User $user): int
    {
        $used = $this->attempts()
            ->where('user_id', $user->id)
            ->where('status', 'submitted')
            ->count();

        return max(0, $this->max_attempts - $used);
    }

    // Check if user passed
    public function hasUserPassed(User $user): bool
    {
        $bestScore = $this->getUserBestScore($user);
        return $bestScore && $bestScore >= $this->pass_percentage;
    }

    // Get questions (randomized if needed)
    public function getQuestionsForAttempt()
    {
        $query = $this->questions();

        if ($this->randomize_questions) {
            return $query->inRandomOrder()->get();
        }

        return $query->get();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForWeek($query, $weekId)
    {
        return $query->where('module_week_id', $weekId);
    }
}