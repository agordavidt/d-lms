<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Enrollment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'program_id', 'cohort_id', 'enrollment_number',
        'status', 'enrolled_at', 'completed_at', 'progress_percentage',
        'graduation_status', 'final_exam_score',
        'graduation_requested_at', 'graduation_approved_at', 'approved_by',
        'certificate_key', 'certificate_issued_at',
    ];

    protected $casts = [
        'enrolled_at'              => 'date',
        'completed_at'             => 'date',
        'progress_percentage'      => 'decimal:2',
        'final_exam_score'         => 'decimal:2',
        'graduation_requested_at'  => 'datetime',
        'graduation_approved_at'   => 'datetime',
        'certificate_issued_at'    => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function user()        { return $this->belongsTo(User::class); }
    public function program()     { return $this->belongsTo(Program::class); }
    public function cohort()      { return $this->belongsTo(Cohort::class); }
    public function approvedBy()  { return $this->belongsTo(User::class, 'approved_by'); }
    public function payments()    { return $this->hasMany(Payment::class); }
    public function weekProgress(){ return $this->hasMany(WeekProgress::class); }

    // ── Boot ──────────────────────────────────────────────────────────────────

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($enrollment) {
            if (empty($enrollment->enrollment_number)) {
                $enrollment->enrollment_number = strtoupper(
                    'ENR-' . now()->format('Ym') . '-' . strtoupper(Str::random(6))
                );
            }
        });
    }

    // ── Progress ──────────────────────────────────────────────────────────────

    /**
     * Recalculate overall enrollment progress percentage based on completed weeks.
     * Called after any week is marked complete.
     */
    public function recalculateProgress(): void
    {
        $totalWeeks = $this->program->getPublishedWeeks()->count();

        if ($totalWeeks === 0) return;

        $completedWeeks = WeekProgress::where('enrollment_id', $this->id)
            ->where('is_completed', true)
            ->count();

        $this->update([
            'progress_percentage' => round(($completedWeeks / $totalWeeks) * 100, 2),
        ]);
    }

    /**
     * Check whether the learner has completed all course weeks.
     * Used as the gate before the final exam can be started.
     */
    public function hasCompletedAllWeeks(): bool
    {
        $courseWeeks = $this->program->getCourseWeeks();
        if ($courseWeeks->isEmpty()) return false;

        $completed = WeekProgress::where('enrollment_id', $this->id)
            ->whereIn('module_week_id', $courseWeeks->pluck('id'))
            ->where('is_completed', true)
            ->count();

        return $completed === $courseWeeks->count();
    }

    /**
     * Record a passed final exam score and move graduation status to pending_review.
     * Called by AssessmentScoringService after a successful final exam submission.
     */
    public function recordFinalExamPass(float $score): void
    {
        $this->update([
            'final_exam_score'          => $score,
            'graduation_status'         => 'pending_review',
            'graduation_requested_at'   => now(),
            'status'                    => 'completed',
            'completed_at'              => now(),
        ]);
    }

    // ── Graduation helpers ────────────────────────────────────────────────────

    public function isGraduated(): bool       { return $this->graduation_status === 'graduated'; }
    public function isPendingReview(): bool   { return $this->graduation_status === 'pending_review'; }
    public function isActive(): bool          { return $this->status === 'active'; }

    // ── Initialisation ────────────────────────────────────────────────────────

    /**
     * Create week progress records and unlock the first week.
     * Called once when an enrollment is activated after payment.
     */
    public function initializeWeekProgress(): void
    {
        $weeks = $this->program->getPublishedWeeks();

        if ($weeks->isEmpty()) return;

        foreach ($weeks as $index => $week) {
            WeekProgress::firstOrCreate(
                [
                    'user_id'        => $this->user_id,
                    'module_week_id' => $week->id,
                    'enrollment_id'  => $this->id,
                ],
                [
                    'is_unlocked'         => $index === 0, // only first week unlocked
                    'is_completed'        => false,
                    'progress_percentage' => 0,
                    'total_contents'      => $week->contents()->where('is_required', true)->count(),
                    'contents_completed'  => 0,
                    'unlocked_at'         => $index === 0 ? now() : null,
                ]
            );
        }
    }
}