<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeekProgress extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'module_week_id', 'enrollment_id',
        'is_unlocked', 'is_completed', 'progress_percentage',
        'total_contents', 'contents_completed',
        'assessment_score', 'assessment_passed', 'assessment_attempts',
        'last_assessment_at', 'unlocked_at', 'completed_at',
    ];

    protected $casts = [
        'is_unlocked'         => 'boolean',
        'is_completed'        => 'boolean',
        'progress_percentage' => 'decimal:2',
        'assessment_score'    => 'decimal:2',
        'assessment_passed'   => 'boolean',
        'assessment_attempts' => 'integer',
        'unlocked_at'         => 'datetime',
        'completed_at'        => 'datetime',
        'last_assessment_at'  => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function user()       { return $this->belongsTo(User::class); }
    public function moduleWeek() { return $this->belongsTo(ModuleWeek::class); }
    public function enrollment() { return $this->belongsTo(Enrollment::class); }

    // ── Core logic ────────────────────────────────────────────────────────────

    /**
     * Recalculate content completion percentage, then check if week can be marked done.
     * Called after any content item is completed.
     */
    public function recalculateCompletion(): void
    {
        $week             = $this->moduleWeek;
        $requiredContents = $week->contents()->where('is_required', true)->count();

        if ($requiredContents === 0) {
            $this->update([
                'progress_percentage' => 100,
                'contents_completed'  => 0,
                'total_contents'      => 0,
            ]);
        } else {
            $completedContents = ContentProgress::where('user_id', $this->user_id)
                ->where('enrollment_id', $this->enrollment_id)
                ->where('is_completed', true)
                ->whereHas('weekContent', fn ($q) => $q
                    ->where('module_week_id', $week->id)
                    ->where('is_required', true)
                )
                ->count();

            $this->update([
                'contents_completed'  => $completedContents,
                'total_contents'      => $requiredContents,
                'progress_percentage' => round(($completedContents / $requiredContents) * 100, 2),
            ]);
        }

        // Reload to get latest values, then check if week is now completable
        $this->refresh();
        $this->checkAndCompleteWeek();
    }

    /**
     * Check conditions and mark week complete if met.
     * Called after content completion AND after assessment scoring.
     */
    public function checkAndCompleteWeek(): void
    {
        if ($this->is_completed) return;
        if ($this->progress_percentage < 100) return;

        $week = $this->moduleWeek;

        // If week has an assessment, it must be PASSED (not merely attempted)
        if ($week->has_assessment && $week->assessment) {
            if (!$this->assessment_passed) return;
        }

        $this->markAsComplete();
    }

    /**
     * Mark week complete and unlock the next one.
     */
    public function markAsComplete(): void
    {
        if ($this->is_completed) return;

        $this->update([
            'is_completed' => true,
            'completed_at' => now(),
        ]);

        $this->unlockNextWeek();

        // Recalculate enrollment grade averages (triggers graduation check)
        $this->enrollment->recalculateGradeAverages();
    }

    /**
     * Unlock the next sequential week in this program.
     * FIXED: removed ->where('status','published') — no status column in new schema.
     */
    protected function unlockNextWeek(): void
    {
        $currentWeek = $this->moduleWeek;
        $program     = $currentWeek->programModule->program;

        $allWeeks = ModuleWeek::whereHas('programModule', fn ($q) =>
            $q->where('program_id', $program->id)
            // ← NO status filter: program_modules has no status column
        )
        ->with('programModule')
        ->get()
        ->sortBy(fn ($w) => [$w->programModule->order, $w->week_number])
        ->values();

        $currentIndex = $allWeeks->search(fn ($w) => $w->id === $currentWeek->id);

        if ($currentIndex !== false && $currentIndex + 1 < $allWeeks->count()) {
            $nextWeek = $allWeeks[$currentIndex + 1];

            WeekProgress::updateOrCreate(
                [
                    'user_id'        => $this->user_id,
                    'module_week_id' => $nextWeek->id,
                    'enrollment_id'  => $this->enrollment_id,
                ],
                [
                    'is_unlocked'   => true,
                    'unlocked_at'   => now(),
                    'total_contents' => $nextWeek->contents()
                                          ->where('is_required', true)->count(),
                ]
            );
        }
    }

    // ── Status helpers ────────────────────────────────────────────────────────

    /**
     * FIXED: assessment must be PASSED (was checking attempts > 0).
     */
    public function isWeekFullyComplete(): bool
    {
        if ($this->progress_percentage < 100) return false;

        $week = $this->moduleWeek;
        if ($week->has_assessment && $week->assessment) {
            return $this->assessment_passed;
        }

        return true;
    }

    public function isContentCompleteAssessmentPending(): bool
    {
        $week = $this->moduleWeek;
        return $this->progress_percentage >= 100
            && $week->has_assessment
            && $week->assessment
            && $this->assessment_attempts === 0;
    }

    public function canTakeAssessment(): bool
    {
        if ($this->progress_percentage < 100) return false;
        $week = $this->moduleWeek;
        return $week->has_assessment && $week->assessment;
        // Unlimited attempts — no max check
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeUnlocked($query)  { return $query->where('is_unlocked', true); }
    public function scopeCompleted($query) { return $query->where('is_completed', true); }
    public function scopeInProgress($query)
    {
        return $query->where('is_unlocked', true)->where('is_completed', false);
    }
}