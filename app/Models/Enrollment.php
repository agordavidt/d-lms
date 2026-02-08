<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Enrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'program_id',
        'cohort_id',
        'enrollment_number',
        'status',
        'graduation_status',
        'final_grade_avg',
        'weekly_assessment_avg',
        'periodic_assessment_avg',
        'enrolled_at',
        'completed_at',
        'graduation_requested_at',
        'graduation_approved_at',
        'approved_by',
        'certificate_key',
        'certificate_issued_at',
    ];

    protected $casts = [
        'enrolled_at' => 'datetime',
        'completed_at' => 'datetime',
        'graduation_requested_at' => 'datetime',
        'graduation_approved_at' => 'datetime',
        'certificate_issued_at' => 'datetime',
        'final_grade_avg' => 'decimal:2',
        'weekly_assessment_avg' => 'decimal:2',
        'periodic_assessment_avg' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($enrollment) {
            // Auto-generate enrollment number if not provided
            if (empty($enrollment->enrollment_number)) {
                $enrollment->enrollment_number = self::generateEnrollmentNumber();
            }
        });
    }

    /**
     * Generate unique enrollment number
     */
    protected static function generateEnrollmentNumber(): string
    {
        do {
            $number = 'ENR-' . date('Y') . '-' . strtoupper(Str::random(6));
        } while (self::where('enrollment_number', $number)->exists());

        return $number;
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function cohort()
    {
        return $this->belongsTo(Cohort::class);
    }

    public function weekProgress()
    {
        return $this->hasMany(WeekProgress::class);
    }

    public function assessmentAttempts()
    {
        return $this->hasMany(AssessmentAttempt::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Grade Calculation Methods

    /**
     * Recalculate all grade averages
     */
    public function recalculateGradeAverages(): void
    {
        $this->recalculateWeeklyAssessmentAverage();
        $this->recalculatePeriodicAssessmentAverage();
        $this->recalculateFinalGradeAverage();
        
        // Check if ready for graduation
        $this->checkGraduationEligibility();
    }

    /**
     * Initialize week progress for all published weeks in the program
     * Called after enrollment becomes active (payment confirmed)
     */
    public function initializeWeekProgress(): void
    {
        // Get all published weeks for this program, ordered correctly
        $weeks = ModuleWeek::whereHas('programModule', function($q) {
                $q->where('program_id', $this->program_id)
                ->where('status', 'published');
            })
            ->where('status', 'published')
            ->with('programModule')
            ->get()
            ->sortBy(function($week) {
                return [$week->programModule->order, $week->week_number];
            })
            ->values(); // Re-index array

        foreach ($weeks as $index => $week) {
            // Count required contents for this week
            $requiredContentsCount = $week->contents()
                ->where('is_required', true)
                ->where('status', 'published')
                ->count();

            WeekProgress::firstOrCreate([
                'user_id' => $this->user_id,
                'module_week_id' => $week->id,
                'enrollment_id' => $this->id,
            ], [
                // Only unlock the first week
                'is_unlocked' => ($index === 0),
                'unlocked_at' => ($index === 0) ? now() : null,
                
                // Progress tracking
                'is_completed' => false,
                'progress_percentage' => 0,
                'total_contents' => $requiredContentsCount,
                'contents_completed' => 0,
                
                // Assessment tracking
                'assessment_score' => null,
                'assessment_passed' => false,
                'assessment_attempts' => 0,
            ]);
        }
    }

    /**
     * Calculate average of all weekly assessments
     */
    protected function recalculateWeeklyAssessmentAverage(): void
    {
        $weeklyScores = WeekProgress::where('enrollment_id', $this->id)
            ->whereNotNull('assessment_score')
            ->where('assessment_attempts', '>', 0)
            ->pluck('assessment_score');

        if ($weeklyScores->isEmpty()) {
            $this->update(['weekly_assessment_avg' => null]);
            return;
        }

        $average = $weeklyScores->avg();
        $this->update(['weekly_assessment_avg' => round($average, 2)]);
    }

    /**
     * Calculate average of periodic/event assessments (future feature)
     */
    protected function recalculatePeriodicAssessmentAverage(): void
    {
        // Placeholder for periodic assessments
        // This will be implemented when you add scheduled/event-based assessments
        $this->update(['periodic_assessment_avg' => null]);
    }

    /**
     * Calculate overall final grade average
     */
    protected function recalculateFinalGradeAverage(): void
    {
        $weekly = $this->weekly_assessment_avg;
        $periodic = $this->periodic_assessment_avg;

        // If no assessments taken yet
        if ($weekly === null && $periodic === null) {
            $this->update(['final_grade_avg' => null]);
            return;
        }

        // For now, final grade = weekly average
        // When periodic assessments are added, you can weight them
        // e.g., final = (weekly * 0.7) + (periodic * 0.3)
        $final = $weekly ?? 0;

        $this->update(['final_grade_avg' => round($final, 2)]);
    }

    // Graduation Workflow Methods

    /**
     * Check if learner is eligible for graduation
     */
    public function checkGraduationEligibility(): void
    {
        if ($this->graduation_status !== 'active') {
            return; // Already in graduation process or graduated
        }

        if ($this->isEligibleForGraduation()) {
            $this->update(['graduation_status' => 'pending_review']);
        }
    }

    /**
     * Determine if enrollment meets graduation criteria
     */
    public function isEligibleForGraduation(): bool
    {
        // 1. All required content must be completed
        if (!$this->hasCompletedAllContent()) {
            return false;
        }

        // 2. All weekly assessments must be attempted
        if (!$this->hasAttemptedAllAssessments()) {
            return false;
        }

        // 3. Assessment average must meet minimum threshold
        if (!$this->meetsMinimumGradeRequirement()) {
            return false;
        }

        // 4. Periodic assessments (future) - placeholder
        // if (!$this->hasCompletedPeriodicAssessments()) {
        //     return false;
        // }

        return true;
    }

    /**
     * Check if all required content completed
     */
    public function hasCompletedAllContent(): bool
    {
        $program = $this->program;
        $totalRequiredWeeks = ModuleWeek::whereHas('programModule', function($q) use ($program) {
            $q->where('program_id', $program->id);
        })
        ->where('status', 'published')
        ->count();

        $completedWeeks = WeekProgress::where('enrollment_id', $this->id)
            ->where('is_completed', true)
            ->count();

        return $completedWeeks >= $totalRequiredWeeks;
    }

    /**
     * Check if all weekly assessments attempted
     */
    public function hasAttemptedAllAssessments(): bool
    {
        $program = $this->program;
        
        // Get weeks that have assessments
        $weeksWithAssessments = ModuleWeek::whereHas('programModule', function($q) use ($program) {
            $q->where('program_id', $program->id);
        })
        ->where('status', 'published')
        ->where('has_assessment', true)
        ->whereHas('assessment')
        ->pluck('id');

        if ($weeksWithAssessments->isEmpty()) {
            return true; // No assessments required
        }

        // Check if all have been attempted
        $attemptedCount = WeekProgress::where('enrollment_id', $this->id)
            ->whereIn('module_week_id', $weeksWithAssessments)
            ->where('assessment_attempts', '>', 0)
            ->count();

        return $attemptedCount >= $weeksWithAssessments->count();
    }

    /**
     * Check if meets minimum grade requirement
     */
    public function meetsMinimumGradeRequirement(): bool
    {
        if ($this->final_grade_avg === null) {
            return false;
        }

        return $this->final_grade_avg >= $this->program->min_passing_average;
    }

    /**
     * Learner requests graduation
     */
    public function requestGraduation(): bool
    {
        if (!$this->isEligibleForGraduation()) {
            return false;
        }

        $this->update([
            'graduation_status' => 'pending_review',
            'graduation_requested_at' => now(),
        ]);

        return true;
    }

    /**
     * Admin/Mentor approves graduation
     */
    public function approveGraduation(User $approver): void
    {
        $certificateKey = $this->generateCertificateKey();

        $this->update([
            'graduation_status' => 'graduated',
            'status' => 'completed',
            'graduation_approved_at' => now(),
            'approved_by' => $approver->id,
            'completed_at' => now(),
            'certificate_key' => $certificateKey,
            'certificate_issued_at' => now(),
        ]);

        // TODO: Queue certificate generation job
        // GenerateCertificate::dispatch($this);
    }

    /**
     * Generate unique certificate key
     */
    protected function generateCertificateKey(): string
    {
        do {
            $key = 'CERT-' . strtoupper(Str::random(12));
        } while (self::where('certificate_key', $key)->exists());

        return $key;
    }

    /**
     * Get certificate verification URL
     */
    public function getCertificateVerificationUrl(): ?string
    {
        if (!$this->certificate_key) {
            return null;
        }

        return route('certificate.verify', $this->certificate_key);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePendingGraduation($query)
    {
        return $query->where('graduation_status', 'pending_review');
    }

    public function scopeGraduated($query)
    {
        return $query->where('graduation_status', 'graduated');
    }
}