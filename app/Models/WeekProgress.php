<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeekProgress extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'module_week_id',
        'enrollment_id',
        'is_unlocked',
        'is_completed',
        'progress_percentage',
        'total_contents',
        'contents_completed',
        'assessment_score',
        'assessment_passed',
        'assessment_attempts',
        'last_assessment_at',
        'unlocked_at',
        'completed_at',
    ];

    protected $casts = [
        'is_unlocked' => 'boolean',
        'is_completed' => 'boolean',
        'progress_percentage' => 'decimal:2',
        'assessment_score' => 'decimal:2',
        'assessment_passed' => 'boolean',
        'assessment_attempts' => 'integer',
        'unlocked_at' => 'datetime',
        'completed_at' => 'datetime',
        'last_assessment_at' => 'datetime',
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

    // Helper Methods

    /**
     * Check if week is fully complete (content + assessment if required)
     */
    public function isWeekFullyComplete(): bool
    {
        $week = $this->moduleWeek;
        
        // Check content completion
        if ($this->progress_percentage < 100) {
            return false;
        }
        
        // If week has assessment, check if taken (not necessarily passed)
        if ($week->has_assessment && $week->assessment) {
            return $this->assessment_attempts > 0; // Assessment taken (score recorded)
        }
        
        return true;
    }

    /**
     * Calculate and update week completion percentage
     */
    public function recalculateCompletion(): void
    {
        $week = $this->moduleWeek;
        $requiredContents = $week->contents()->where('is_required', true)->count();
        
        if ($requiredContents === 0) {
            $this->update(['progress_percentage' => 100]);
            return;
        }

        // Get completed required contents
        $completedContents = ContentProgress::where('user_id', $this->user_id)
            ->where('enrollment_id', $this->enrollment_id)
            ->where('is_completed', true)
            ->whereHas('weekContent', function($q) use ($week) {
                $q->where('module_week_id', $week->id)
                  ->where('is_required', true);
            })
            ->count();

        $percentage = ($completedContents / $requiredContents) * 100;
        
        $this->update([
            'contents_completed' => $completedContents,
            'total_contents' => $requiredContents,
            'progress_percentage' => round($percentage, 2),
        ]);
    }

    /**
     * Mark week as complete and unlock next week
     */
    public function markAsComplete(): void
    {
        if ($this->is_completed) {
            return; // Already completed
        }

        $this->update([
            'is_completed' => true,
            'completed_at' => now(),
        ]);

        // Unlock next week
        $this->unlockNextWeek();
        
        // Update enrollment grade averages
        $this->enrollment->recalculateGradeAverages();
    }

    /**
     * Unlock the next week in sequence
     */
    protected function unlockNextWeek(): void
    {
        $currentWeek = $this->moduleWeek;
        $program = $currentWeek->programModule->program;
        
        // Get all weeks in program ordered by module and week number
        $allWeeks = ModuleWeek::whereHas('programModule', function($q) use ($program) {
            $q->where('program_id', $program->id);
        })
        ->where('status', 'published')
        ->with('programModule')
        ->get()
        ->sortBy(function($week) {
            return [$week->programModule->order, $week->week_number];
        });

        // Find current week index
        $currentIndex = $allWeeks->search(function($week) use ($currentWeek) {
            return $week->id === $currentWeek->id;
        });

        // Get next week
        if ($currentIndex !== false && isset($allWeeks[$currentIndex + 1])) {
            $nextWeek = $allWeeks[$currentIndex + 1];
            
            // Create or update progress for next week
            WeekProgress::updateOrCreate(
                [
                    'user_id' => $this->user_id,
                    'module_week_id' => $nextWeek->id,
                    'enrollment_id' => $this->enrollment_id,
                ],
                [
                    'is_unlocked' => true,
                    'unlocked_at' => now(),
                    'total_contents' => $nextWeek->required_contents_count,
                ]
            );
        }
    }

    /**
     * Check if content is complete but assessment pending
     */
    public function isContentCompleteAssessmentPending(): bool
    {
        $week = $this->moduleWeek;
        
        return $this->progress_percentage >= 100 
            && $week->has_assessment 
            && $week->assessment
            && $this->assessment_attempts === 0;
    }

    /**
     * Check if assessment can be taken
     */
    public function canTakeAssessment(): bool
    {
        // Content must be 100% complete
        if ($this->progress_percentage < 100) {
            return false;
        }

        $week = $this->moduleWeek;
        
        // Week must have assessment
        if (!$week->has_assessment || !$week->assessment) {
            return false;
        }

        // Check if max attempts reached (though we still allow - just for UI)
        return $this->assessment_attempts < $week->assessment->max_attempts;
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
}