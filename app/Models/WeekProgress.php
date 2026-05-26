<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeekProgress extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'module_week_id', 'enrollment_id',
        'is_unlocked', 'is_completed',
        'progress_percentage', 'total_contents', 'contents_completed',
        'assessment_passed', 'assessment_attempts', 'last_assessment_at',
        'unlocked_at', 'completed_at',
    ];

    protected $casts = [
        'is_unlocked'         => 'boolean',
        'is_completed'        => 'boolean',
        'progress_percentage' => 'decimal:2',
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

    // ── Core progression logic ────────────────────────────────────────────────

    /**
     * Recalculate content completion percentage from the database.
     * Called after any content item is marked complete.
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
            $completedCount = ContentProgress::where('user_id', $this->user_id)
                ->where('enrollment_id', $this->enrollment_id)
                ->where('is_completed', true)
                ->whereHas('weekContent', fn ($q) =>
                    $q->where('module_week_id', $week->id)->where('is_required', true)
                )
                ->count();

            $this->update([
                'contents_completed'  => $completedCount,
                'total_contents'      => $requiredContents,
                'progress_percentage' => round(($completedCount / $requiredContents) * 100, 2),
            ]);
        }

        $this->refresh();
        $this->checkAndCompleteWeek();
    }

    /**
     * Check whether all gates are met and mark the week complete if so.
     *
     * Gates:
     *   1. All required content consumed (progress_percentage = 100)
     *   2. If week has an assessment → assessment_passed must be true
     *      (weekly: score must be 100%; final: score must be ≥75% — enforced in scoring service)
     */
    public function checkAndCompleteWeek(): void
    {
        if ($this->is_completed) return;
        if ((float) $this->progress_percentage < 100) return;

        $week = $this->moduleWeek;

        if ($week->has_assessment && $week->assessment) {
            if (! $this->assessment_passed) return;
        }

        $this->markAsComplete();
    }

    /**
     * Mark this week complete and unlock the next sequential week.
     */
    public function markAsComplete(): void
    {
        if ($this->is_completed) return;

        $this->update([
            'is_completed' => true,
            'completed_at' => now(),
        ]);

        $this->unlockNextWeek();
    }

    /**
     * Unlock the next week in program order.
     * If no next week exists, all modules are done — the final exam becomes available.
     */
    protected function unlockNextWeek(): void
{
    $currentWeek = $this->moduleWeek;
    $program     = $currentWeek->programModule->program;

    $allWeeks = ModuleWeek::whereHas('programModule', fn ($q) =>
        $q->where('program_id', $program->id)
    )
    ->with('programModule')
    ->get()
    ->sortBy(fn ($w) => [
        (int) $w->is_final_week,          // final exam week always sorts last
        $w->programModule->order,
        $w->order,                         // use `order` (reorder-aware), not `week_number`
    ])
    ->values();

    $currentIndex = $allWeeks->search(fn ($w) => $w->id === $currentWeek->id);

    if ($currentIndex !== false && $currentIndex + 1 < $allWeeks->count()) {
        $nextWeek         = $allWeeks[$currentIndex + 1];
        $requiredContents = $nextWeek->contents()->where('is_required', true)->count();

        WeekProgress::updateOrCreate(
            [
                'user_id'        => $this->user_id,
                'module_week_id' => $nextWeek->id,
                'enrollment_id'  => $this->enrollment_id,
            ],
            [
                'is_unlocked'         => true,
                'unlocked_at'         => now(),
                'total_contents'      => $requiredContents,
                'progress_percentage' => $requiredContents === 0 ? 100 : 0,
            ]
        );
    }
}

    // ── Status helpers ────────────────────────────────────────────────────────

    /**
     * True only when both content and assessment gates are fully satisfied.
     */
    public function isFullyComplete(): bool
    {
        if ((float) $this->progress_percentage < 100) return false;

        $week = $this->moduleWeek;
        if ($week->has_assessment && $week->assessment) {
            return $this->assessment_passed;
        }

        return true;
    }

    /**
     * Content done but learner hasn't attempted the assessment yet.
     */
    public function isAwaitingAssessment(): bool
    {
        $week = $this->moduleWeek;
        return (float) $this->progress_percentage >= 100
            && $week->has_assessment
            && $week->assessment
            && $this->assessment_attempts === 0;
    }

    /**
     * Learner can take the assessment (content complete, not yet passed).
     * Weekly: unlimited retakes, no cooldown.
     */
    public function canTakeAssessment(): bool
    {
        if ((float) $this->progress_percentage < 100) return false;
        $week = $this->moduleWeek;
        return $week->has_assessment && $week->assessment && ! $this->assessment_passed;
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeUnlocked($query)   { return $query->where('is_unlocked', true); }
    public function scopeCompleted($query)  { return $query->where('is_completed', true); }
    public function scopeInProgress($query) { return $query->where('is_unlocked', true)->where('is_completed', false); }
}