<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Assessment extends Model
{
    use HasFactory, SoftDeletes;

    // Weekly assessments: always 100% (all correct) — not stored, enforced in scoring service
    // Final exam: configurable, defaults to this
    const FINAL_PASS_PERCENTAGE  = 75;
    const FINAL_COOLDOWN_HOURS   = 48;

    protected $fillable = [
        'module_week_id', 'created_by', 'title',
        'time_limit_minutes', 'randomize_questions', 'is_final', 'pass_percentage',
    ];

    protected $casts = [
        'randomize_questions' => 'boolean',
        'is_final'            => 'boolean',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function moduleWeek() { return $this->belongsTo(ModuleWeek::class); }
    public function creator()    { return $this->belongsTo(User::class, 'created_by'); }
    public function questions()  { return $this->hasMany(AssessmentQuestion::class)->orderBy('order'); }
    public function attempts()   { return $this->hasMany(AssessmentAttempt::class); }

    // ── Computed ──────────────────────────────────────────────────────────────

    public function getTotalQuestionsAttribute(): int { return $this->questions()->count(); }
    public function getTotalPointsAttribute(): int    { return $this->questions()->sum('points'); }

    /**
     * The pass threshold actually used during scoring.
     * Weekly → always 100. Final → stored pass_percentage (default 75).
     */
    public function getEffectivePassPercentage(): int
    {
        return $this->is_final ? (int) $this->pass_percentage : 100;
    }

    // ── Attempt helpers ───────────────────────────────────────────────────────

    public function getLatestAttempt(User $user, int $enrollmentId): ?AssessmentAttempt
    {
        return $this->attempts()
            ->where('user_id', $user->id)
            ->where('enrollment_id', $enrollmentId)
            ->where('status', 'submitted')
            ->latest()
            ->first();
    }

    public function getUserAttempts(User $user)
    {
        return $this->attempts()
            ->where('user_id', $user->id)
            ->orderByDesc('attempt_number')
            ->get();
    }

    /**
     * True when a failed final exam is still within the 48-hour cooldown window.
     */
    public function isOnCooldownFor(User $user, int $enrollmentId): bool
    {
        if (! $this->is_final) return false;

        $latest = $this->getLatestAttempt($user, $enrollmentId);

        return $latest
            && $latest->next_attempt_at
            && $latest->next_attempt_at->isFuture();
    }

    public function cooldownEndsAt(User $user, int $enrollmentId): ?\Carbon\Carbon
    {
        if (! $this->is_final) return null;

        $latest = $this->getLatestAttempt($user, $enrollmentId);

        return ($latest && $latest->next_attempt_at && $latest->next_attempt_at->isFuture())
            ? $latest->next_attempt_at
            : null;
    }

    /**
     * Questions in display order, randomised if enabled.
     */
    public function getQuestionsForAttempt()
    {
        $q = $this->questions();
        return $this->randomize_questions ? $q->inRandomOrder()->get() : $q->get();
    }
}