<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Program extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'mentor_id',
        'name',
        'slug',
        'description',
        'cover_image',
        'duration',
        'price',
        'discount_percentage',
        'min_passing_average',
        'status',
        'submitted_at',
        'reviewed_at',
        'reviewed_by',
        'review_notes',
    ];

    protected $casts = [
        'price'               => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'min_passing_average' => 'decimal:2',
        'submitted_at'        => 'datetime',
        'reviewed_at'         => 'datetime',
    ];

    // ── Boot ──────────────────────────────────────────────────────────────────

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($program) {
            if (empty($program->slug)) {
                $program->slug = Str::slug($program->name);
            }
        });
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function mentor()
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function cohorts()
    {
        return $this->hasMany(Cohort::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function modules()
    {
        return $this->hasMany(ProgramModule::class)->orderBy('order');
    }

    public function liveSessions()
    {
        return $this->hasMany(LiveSession::class)->orderBy('start_time');
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function getCoverImageUrlAttribute(): string
    {
        if ($this->cover_image) {
            return asset('storage/' . $this->cover_image);
        }
        return asset('images/default-program.png');
    }

    public function getDiscountedPriceAttribute(): float
    {
        if ($this->discount_percentage > 0) {
            return $this->price - ($this->price * $this->discount_percentage / 100);
        }
        return (float) $this->price;
    }

    public function getInstallmentAmountAttribute(): float
    {
        return $this->price / 2;
    }

    // ── Status helpers ────────────────────────────────────────────────────────

    public function isDraft(): bool        { return $this->status === 'draft'; }
    public function isUnderReview(): bool  { return $this->status === 'under_review'; }
    public function isActive(): bool       { return $this->status === 'active'; }
    public function isInactive(): bool     { return $this->status === 'inactive'; }

    /** Learners can discover and enroll */
    public function isEnrollable(): bool   { return $this->status === 'active'; }

    // ── Content helpers ───────────────────────────────────────────────────────

    /**
     * All published weeks, ordered by module order then week number.
     * Used by LearningController and Enrollment::initializeWeekProgress().
     */
    public function getPublishedWeeks()
    {
        return ModuleWeek::whereHas('programModule', function ($q) {
            $q->where('program_id', $this->id);
        })
        ->with('programModule')
        ->get()
        ->sortBy(fn ($w) => [$w->programModule->order, $w->week_number])
        ->values();
    }

    /**
     * Alias used by LearningController — returns the same collection.
     */
    public function getAllWeeks()
    {
        return $this->getPublishedWeeks();
    }

    // ── Default cohort ────────────────────────────────────────────────────────

    /**
     * Find or create the single default cohort used for auto-enrollment.
     * Mentors never see or interact with cohorts — this is internal plumbing.
     */
    public function getOrCreateDefaultCohort(): Cohort
    {
        return $this->cohorts()->firstOrCreate(
            ['code' => 'DEFAULT-' . $this->id],
            [
                'name'   => $this->name . ' — Default',
                'status' => 'ongoing',
            ]
        );
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByMentor($query, $mentorId)
    {
        return $query->where('mentor_id', $mentorId);
    }

    public function scopePendingReview($query)
    {
        return $query->where('status', 'under_review');
    }
}