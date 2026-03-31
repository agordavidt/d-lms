<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Enrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'program_id', 'cohort_id', 'enrollment_number',
        'status', 'graduation_status',
        'final_grade_avg', 'weekly_assessment_avg',
        'enrolled_at', 'completed_at',
        'graduation_requested_at', 'graduation_approved_at',
        'approved_by', 'certificate_key', 'certificate_issued_at',
    ];

    protected $casts = [
        'enrolled_at'             => 'datetime',
        'completed_at'            => 'datetime',
        'graduation_requested_at' => 'datetime',
        'graduation_approved_at'  => 'datetime',
        'certificate_issued_at'   => 'datetime',
        'final_grade_avg'         => 'decimal:2',
        'weekly_assessment_avg'   => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($enrollment) {
            if (empty($enrollment->enrollment_number)) {
                $enrollment->enrollment_number = self::generateEnrollmentNumber();
            }
        });
    }

    protected static function generateEnrollmentNumber(): string
    {
        do {
            $number = 'ENR-' . date('Y') . '-' . strtoupper(Str::random(6));
        } while (self::where('enrollment_number', $number)->exists());

        return $number;
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function user()        { return $this->belongsTo(User::class); }
    public function program()     { return $this->belongsTo(Program::class); }
    public function cohort()      { return $this->belongsTo(Cohort::class); }
    public function approver()    { return $this->belongsTo(User::class, 'approved_by'); }
    public function weekProgress(){ return $this->hasMany(WeekProgress::class); }
    public function assessmentAttempts() { return $this->hasMany(AssessmentAttempt::class); }
    public function payments()    { return $this->hasMany(Payment::class); }

    // ── Week progress initialisation ──────────────────────────────────────────

    /**
     * Create WeekProgress rows for every week in this program and unlock week 1.
     * Called by PaymentController::activateEnrollment() after payment is confirmed.
     *
     * CHANGED: removed ->where('status','published') filters on programModule,
     * moduleWeek, and weekContents. The new schema has NO status column on those
     * tables — program.status is the single visibility gate.
     */
    public function initializeWeekProgress(): void
    {
        $weeks = ModuleWeek::whereHas('programModule', function ($q) {
                $q->where('program_id', $this->program_id);
                // ← NO status filter: program_modules has no status column
            })
            ->with('programModule')
            ->get()
            ->sortBy(fn ($week) => [$week->programModule->order, $week->week_number])
            ->values();

        foreach ($weeks as $index => $week) {
            // Count required contents — no status filter (week_contents has no status column)
            $requiredContentsCount = $week->contents()
                ->where('is_required', true)
                ->count();

            WeekProgress::firstOrCreate(
                [
                    'user_id'        => $this->user_id,
                    'module_week_id' => $week->id,
                    'enrollment_id'  => $this->id,
                ],
                [
                    'is_unlocked'        => ($index === 0),
                    'unlocked_at'        => ($index === 0) ? now() : null,
                    'is_completed'       => false,
                    'progress_percentage'=> 0,
                    'total_contents'     => $requiredContentsCount,
                    'contents_completed' => 0,
                    'assessment_score'   => null,
                    'assessment_passed'  => false,
                    'assessment_attempts'=> 0,
                ]
            );
        }
    }

    // ── Grade calculation ─────────────────────────────────────────────────────

    public function recalculateGradeAverages(): void
    {
        $this->recalculateWeeklyAssessmentAverage();
        $this->recalculatePeriodicAssessmentAverage();
        $this->recalculateFinalGradeAverage();
        $this->checkGraduationEligibility();
    }

    protected function recalculateWeeklyAssessmentAverage(): void
    {
        $scores = WeekProgress::where('enrollment_id', $this->id)
            ->whereNotNull('assessment_score')
            ->where('assessment_attempts', '>', 0)
            ->pluck('assessment_score');

        $this->update([
            'weekly_assessment_avg' => $scores->isEmpty() ? null : round($scores->avg(), 2),
        ]);
    }

    protected function recalculatePeriodicAssessmentAverage(): void
    {
        // Periodic assessments not implemented — column removed from schema.
        // This is a no-op until periodic assessments are added.
    }

    protected function recalculateFinalGradeAverage(): void
    {
        $weekly   = $this->weekly_assessment_avg;
        $periodic = $this->periodic_assessment_avg;

        if ($weekly === null && $periodic === null) {
            $this->update(['final_grade_avg' => null]);
            return;
        }

        $this->update(['final_grade_avg' => round((float) ($weekly ?? 0), 2)]);
    }

    // ── Graduation workflow ───────────────────────────────────────────────────

    public function checkGraduationEligibility(): void
    {
        if ($this->graduation_status !== 'active') return;

        if ($this->isEligibleForGraduation()) {
            $this->update(['graduation_status' => 'pending_review']);
        }
    }

    public function isEligibleForGraduation(): bool
    {
        return $this->hasCompletedAllContent()
            && $this->hasPassedAllAssessments()
            && $this->meetsMinimumGradeRequirement();
    }

    /**
     * All weeks must be completed.
     *
     * CHANGED: removed ->where('status','published') — module_weeks has no status column.
     */
    public function hasCompletedAllContent(): bool
    {
        $totalWeeks = ModuleWeek::whereHas('programModule', function ($q) {
            $q->where('program_id', $this->program_id);
            // ← NO status filter
        })->count();

        $completedWeeks = WeekProgress::where('enrollment_id', $this->id)
            ->where('is_completed', true)
            ->count();

        return $totalWeeks > 0 && $completedWeeks >= $totalWeeks;
    }

    /**
     * All weeks that have an assessment must be passed.
     *
     * CHANGED: removed ->where('status','published') — module_weeks has no status column.
     */
    public function hasPassedAllAssessments(): bool
    {
        $weeksWithAssessments = ModuleWeek::whereHas('programModule', function ($q) {
            $q->where('program_id', $this->program_id);
            // ← NO status filter
        })
        ->where('has_assessment', true)
        ->whereHas('assessment')
        ->pluck('id');

        if ($weeksWithAssessments->isEmpty()) {
            return true; // No assessments required
        }

        $passedCount = WeekProgress::where('enrollment_id', $this->id)
            ->whereIn('module_week_id', $weeksWithAssessments)
            ->where('assessment_passed', true)
            ->count();

        return $passedCount >= $weeksWithAssessments->count();
    }

    public function meetsMinimumGradeRequirement(): bool
    {
        if ($this->final_grade_avg === null) return false;

        $minimum = $this->program->min_passing_average ?? null;
        if ($minimum === null) return true;

        return (float) $this->final_grade_avg >= (float) $minimum;
    }

    public function requestGraduation(): bool
    {
        if (!$this->isEligibleForGraduation()) return false;

        $this->update([
            'graduation_status'       => 'pending_review',
            'graduation_requested_at' => now(),
        ]);

        return true;
    }

    public function approveGraduation(User $approver): void
    {
        $certificateKey = $this->generateCertificateKey();

        $this->update([
            'graduation_status'      => 'graduated',
            'status'                 => 'completed',
            'graduation_approved_at' => now(),
            'approved_by'            => $approver->id,
            'completed_at'           => now(),
            'certificate_key'        => $certificateKey,
            'certificate_issued_at'  => now(),
        ]);
    }

    protected function generateCertificateKey(): string
    {
        do {
            $key = 'CERT-' . strtoupper(Str::random(12));
        } while (self::where('certificate_key', $key)->exists());

        return $key;
    }

    public function getCertificateVerificationUrl(): ?string
    {
        if (!$this->certificate_key) return null;
        return route('certificate.verify', $this->certificate_key);
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive($query)           { return $query->where('status', 'active'); }
    public function scopePendingGraduation($query){ return $query->where('graduation_status', 'pending_review'); }
    public function scopeGraduated($query)        { return $query->where('graduation_status', 'graduated'); }
}