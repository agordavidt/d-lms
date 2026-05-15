<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Assessment extends Model
{
    use HasFactory, SoftDeletes;

    const FINAL_COOLDOWN_HOURS = 48;

    protected $fillable = [
        'module_week_id', 'created_by', 'title',
        'time_limit_minutes', 'pass_percentage', 'randomize_questions', 'is_final',
    ];

    protected $casts = [
        'randomize_questions' => 'boolean',
        'is_final'            => 'boolean',
    ];

    public function moduleWeek() { return $this->belongsTo(ModuleWeek::class); }
    public function creator()    { return $this->belongsTo(User::class, 'created_by'); }

    public function questions()
    {
        return $this->hasMany(AssessmentQuestion::class)->orderBy('order');
    }

    public function attempts()
    {
        return $this->hasMany(AssessmentAttempt::class);
    }

    public function getTotalQuestionsAttribute(): int
    {
        return $this->questions()->count();
    }

    public function getTotalPointsAttribute(): int
    {
        return $this->questions()->sum('points');
    }

    public function getUserAttempts(User $user)
    {
        return $this->attempts()->where('user_id', $user->id)->orderByDesc('attempt_number')->get();
    }

    public function getUserBestScore(User $user): ?float
    {
        return $this->attempts()
            ->where('user_id', $user->id)
            ->where('status', 'submitted')
            ->max('percentage');
    }

    /**
     * Returns the latest submitted attempt for a user+enrollment,
     * or null if none exists.
     */
    public function getLatestAttempt(User $user, int $enrollmentId): ?AssessmentAttempt
    {
        return $this->attempts()
            ->where('user_id', $user->id)
            ->where('enrollment_id', $enrollmentId)
            ->where('status', 'submitted')
            ->latest()
            ->first();
    }

    /**
     * True if the learner must wait before retrying the final exam.
     */
    public function isOnCooldownFor(User $user, int $enrollmentId): bool
    {
        if (!$this->is_final) return false;

        $latest = $this->getLatestAttempt($user, $enrollmentId);

        return $latest && $latest->next_attempt_at && $latest->next_attempt_at->isFuture();
    }

    /**
     * Carbon datetime when cooldown expires, or null.
     */
    public function cooldownEndsAt(User $user, int $enrollmentId): ?\Carbon\Carbon
    {
        if (!$this->is_final) return null;

        $latest = $this->getLatestAttempt($user, $enrollmentId);

        return ($latest && $latest->next_attempt_at && $latest->next_attempt_at->isFuture())
            ? $latest->next_attempt_at
            : null;
    }

    public function getQuestionsForAttempt()
    {
        $q = $this->questions();
        return $this->randomize_questions ? $q->inRandomOrder()->get() : $q->get();
    }
}