<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModuleWeek extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'program_module_id',
        'title',
        'description',
        'week_number',
        'order',
        'status',
        'learning_outcomes',
        'has_assessment',
        'assessment_pass_percentage',
    ];

    protected $casts = [
        'learning_outcomes' => 'array',
        'has_assessment' => 'boolean',
    ];

    // Relationships
    public function programModule()
    {
        return $this->belongsTo(ProgramModule::class);
    }

    public function contents()
    {
        return $this->hasMany(WeekContent::class)->orderBy('order');
    }

    public function publishedContents()
    {
        return $this->hasMany(WeekContent::class)->where('status', 'published')->orderBy('order');
    }

    public function weekProgress()
    {
        return $this->hasMany(WeekProgress::class);
    }

    public function liveSessions()
    {
        return $this->hasMany(LiveSession::class, 'week_id');
    }

    // Status checks
    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    // Helpers
    public function getRequiredContentsAttribute()
    {
        return $this->contents()->where('is_required', true)->get();
    }

    public function getTotalContentsCountAttribute(): int
    {
        return $this->contents()->where('status', 'published')->count();
    }

    public function getRequiredContentsCountAttribute(): int
    {
        return $this->contents()->where('status', 'published')->where('is_required', true)->count();
    }

    // Check if week is unlocked for a specific user
    public function isUnlockedFor(User $user, Enrollment $enrollment): bool
    {
        $progress = WeekProgress::where('user_id', $user->id)
            ->where('module_week_id', $this->id)
            ->where('enrollment_id', $enrollment->id)
            ->first();

        return $progress && $progress->is_unlocked;
    }

    // Check if week is completed for a specific user
    public function isCompletedBy(User $user): bool
    {
        $progress = WeekProgress::where('user_id', $user->id)
            ->where('module_week_id', $this->id)
            ->first();

        return $progress && $progress->is_completed;
    }

    // Get user's progress for this week
    public function getProgressFor(User $user, Enrollment $enrollment)
    {
        return WeekProgress::firstOrCreate([
            'user_id' => $user->id,
            'module_week_id' => $this->id,
            'enrollment_id' => $enrollment->id,
        ], [
            'is_unlocked' => false,
            'is_completed' => false,
            'progress_percentage' => 0,
            'total_contents' => $this->required_contents_count,
        ]);
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('week_number');
    }

    public function scopeByWeekNumber($query, $weekNumber)
    {
        return $query->where('week_number', $weekNumber);
    }


    // Add this to the ModuleWeek model relationships section

    public function assessment()
    {
        return $this->hasOne(Assessment::class);
    }

    // Add these helper methods to ModuleWeek model

    public function hasActiveAssessment(): bool
    {
        return $this->has_assessment && $this->assessment()->where('is_active', true)->exists();
    }

    public function getAssessmentStatusForUser(User $user): array
    {
        if (!$this->hasActiveAssessment()) {
            return [
                'has_assessment' => false,
            ];
        }

        $assessment = $this->assessment;
        $attempts = $assessment->getUserAttempts($user);
        $bestScore = $assessment->getUserBestScore($user);
        $passed = $assessment->hasUserPassed($user);
        $canTake = $assessment->canUserTakeAssessment($user);
        $remaining = $assessment->getRemainingAttempts($user);

        return [
            'has_assessment' => true,
            'assessment_id' => $assessment->id,
            'attempts_used' => $attempts->where('status', 'submitted')->count(),
            'max_attempts' => $assessment->max_attempts,
            'remaining_attempts' => $remaining,
            'best_score' => $bestScore,
            'passed' => $passed,
            'can_take' => $canTake,
            'pass_percentage' => $assessment->pass_percentage,
        ];
    }
}