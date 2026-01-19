<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeekProgress extends Model
{
    use HasFactory;

    protected $table = 'week_progress';

    protected $fillable = [
        'user_id',
        'module_week_id',
        'enrollment_id',
        'is_unlocked',
        'is_completed',
        'progress_percentage',
        'contents_completed',
        'total_contents',
        'unlocked_at',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'is_unlocked' => 'boolean',
        'is_completed' => 'boolean',
        'unlocked_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function moduleWeek()
    {
        return $this->belongsTo(ModuleWeek::class);
    }

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    // Helper methods
    public function unlock(): void
    {
        if (!$this->is_unlocked) {
            $this->update([
                'is_unlocked' => true,
                'unlocked_at' => now(),
            ]);
        }
    }

    public function markAsStarted(): void
    {
        if (!$this->started_at) {
            $this->update([
                'started_at' => now(),
            ]);
        }
    }

    public function recalculateProgress(): void
    {
        $week = $this->moduleWeek;
        
        // Get all required published contents for this week
        $requiredContents = $week->contents()
            ->where('status', 'published')
            ->where('is_required', true)
            ->pluck('id');

        $totalRequired = $requiredContents->count();
        
        if ($totalRequired === 0) {
            // No required content, mark as complete
            $this->update([
                'is_completed' => true,
                'progress_percentage' => 100,
                'contents_completed' => 0,
                'total_contents' => 0,
                'completed_at' => now(),
            ]);
            return;
        }

        // Count completed required contents
        $completedCount = ContentProgress::where('user_id', $this->user_id)
            ->where('enrollment_id', $this->enrollment_id)
            ->whereIn('week_content_id', $requiredContents)
            ->where('is_completed', true)
            ->count();

        $progressPercentage = ($completedCount / $totalRequired) * 100;
        $isCompleted = $completedCount >= $totalRequired;

        $this->update([
            'contents_completed' => $completedCount,
            'total_contents' => $totalRequired,
            'progress_percentage' => round($progressPercentage, 2),
            'is_completed' => $isCompleted,
            'completed_at' => $isCompleted ? ($this->completed_at ?? now()) : null,
        ]);

        // If week is completed, unlock next week
        if ($isCompleted) {
            $this->unlockNextWeek();
        }
    }

    private function unlockNextWeek(): void
    {
        $currentWeek = $this->moduleWeek;
        $program = $currentWeek->programModule->program;
        
        // Get next week by week_number
        $nextWeek = ModuleWeek::whereHas('programModule', function($query) use ($program) {
            $query->where('program_id', $program->id);
        })
        ->where('week_number', $currentWeek->week_number + 1)
        ->where('status', 'published')
        ->first();

        if ($nextWeek) {
            // Check cohort start date restriction
            $enrollment = $this->enrollment;
            $cohortStartDate = $enrollment->cohort->start_date;
            $weeksSinceStart = now()->diffInWeeks($cohortStartDate);

            // Only unlock if cohort has reached that week
            if ($weeksSinceStart >= $nextWeek->week_number - 1) {
                $nextWeekProgress = WeekProgress::firstOrCreate([
                    'user_id' => $this->user_id,
                    'module_week_id' => $nextWeek->id,
                    'enrollment_id' => $this->enrollment_id,
                ], [
                    'total_contents' => $nextWeek->required_contents_count,
                ]);

                $nextWeekProgress->unlock();
            }
        }
    }

    // Scopes
    public function scopeUnlocked($query)
    {
        return $query->where('is_unlocked', true);
    }

    public function scopeCompleted($query)
    {
        return $query->where('is_completed', true);
    }

    public function scopeInProgress($query)
    {
        return $query->where('is_unlocked', true)
            ->where('is_completed', false);
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