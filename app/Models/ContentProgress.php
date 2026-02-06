<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentProgress extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'week_content_id',
        'enrollment_id',
        'is_completed',
        'progress_percentage',
        'time_spent_seconds',
        'last_accessed_at',
        'completed_at',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'progress_percentage' => 'integer',
        'time_spent_seconds' => 'integer',
        'last_accessed_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // Relationships
    
    /**
     * The user who is making progress
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The week content being progressed through
     * Note: This is the correct relationship name based on the foreign key
     */
    public function weekContent()
    {
        return $this->belongsTo(WeekContent::class, 'week_content_id');
    }

    /**
     * Alias for weekContent to support both naming conventions
     */
    public function content()
    {
        return $this->weekContent();
    }

    /**
     * The enrollment this progress is associated with
     */
    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    // Scopes

    /**
     * Scope to get completed progress
     */
    public function scopeCompleted($query)
    {
        return $query->where('is_completed', true);
    }

    /**
     * Scope to get in-progress items
     */
    public function scopeInProgress($query)
    {
        return $query->where('is_completed', false)
                     ->where('progress_percentage', '>', 0);
    }

    /**
     * Scope to get not started items
     */
    public function scopeNotStarted($query)
    {
        return $query->where('progress_percentage', 0);
    }

    // Helper Methods

    /**
     * Mark content as completed
     */
    public function markAsCompleted()
    {
        $this->update([
            'is_completed' => true,
            'progress_percentage' => 100,
            'completed_at' => now(),
        ]);
    }

    /**
     * Update progress percentage
     */
    public function updateProgress(int $percentage)
    {
        $this->update([
            'progress_percentage' => min(100, max(0, $percentage)),
            'last_accessed_at' => now(),
        ]);

        if ($percentage >= 100) {
            $this->markAsCompleted();
        }
    }

    /**
     * Add time spent
     */
    public function addTimeSpent(int $seconds)
    {
        $this->increment('time_spent_seconds', $seconds);
        $this->update(['last_accessed_at' => now()]);
    }

    /**
     * Get formatted time spent
     */
    public function getFormattedTimeSpentAttribute()
    {
        $hours = floor($this->time_spent_seconds / 3600);
        $minutes = floor(($this->time_spent_seconds % 3600) / 60);
        
        if ($hours > 0) {
            return sprintf('%d hr %d min', $hours, $minutes);
        }
        
        return sprintf('%d min', $minutes);
    }

    /**
     * Check if content is in progress
     */
    public function isInProgress()
    {
        return !$this->is_completed && $this->progress_percentage > 0;
    }

    /**
     * Check if content is not started
     */
    public function isNotStarted()
    {
        return $this->progress_percentage === 0;
    }
}