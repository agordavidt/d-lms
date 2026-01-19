<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentProgress extends Model
{
    use HasFactory;

    protected $table = 'content_progress';

    protected $fillable = [
        'user_id',
        'week_content_id',
        'enrollment_id',
        'is_completed',
        'progress_percentage',
        'time_spent_seconds',
        'started_at',
        'completed_at',
        'view_count',
        'last_accessed_at',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'last_accessed_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function weekContent()
    {
        return $this->belongsTo(WeekContent::class);
    }

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    // Helper methods
    public function markAsStarted(): void
    {
        if (!$this->started_at) {
            $this->update([
                'started_at' => now(),
                'view_count' => $this->view_count + 1,
                'last_accessed_at' => now(),
            ]);
        } else {
            $this->update([
                'view_count' => $this->view_count + 1,
                'last_accessed_at' => now(),
            ]);
        }
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'is_completed' => true,
            'progress_percentage' => 100,
            'completed_at' => now(),
            'last_accessed_at' => now(),
        ]);

        // Update week progress
        $this->updateWeekProgress();
    }

    public function updateProgress(int $percentage): void
    {
        $this->update([
            'progress_percentage' => min(100, max(0, $percentage)),
            'last_accessed_at' => now(),
        ]);

        // Auto-complete if 100%
        if ($percentage >= 100 && !$this->is_completed) {
            $this->markAsCompleted();
        }
    }

    public function addTimeSpent(int $seconds): void
    {
        $this->increment('time_spent_seconds', $seconds);
        $this->update(['last_accessed_at' => now()]);
    }

    // Update the week progress when content is completed
    private function updateWeekProgress(): void
    {
        $weekProgress = WeekProgress::where('user_id', $this->user_id)
            ->where('module_week_id', $this->weekContent->module_week_id)
            ->where('enrollment_id', $this->enrollment_id)
            ->first();

        if ($weekProgress) {
            $weekProgress->recalculateProgress();
        }
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('is_completed', true);
    }

    public function scopeInProgress($query)
    {
        return $query->where('is_completed', false)
            ->where('progress_percentage', '>', 0);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForEnrollment($query, $enrollmentId)
    {
        return $query->where('enrollment_id', $enrollmentId);
    }
}