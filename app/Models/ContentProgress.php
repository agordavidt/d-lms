<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentProgress extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'week_content_id', 'enrollment_id',
        'is_completed', 'progress_percentage',
        'time_spent_seconds', 'view_count',
        'started_at', 'completed_at', 'last_accessed_at',
    ];

    protected $casts = [
        'is_completed'        => 'boolean',
        'progress_percentage' => 'integer',
        'time_spent_seconds'  => 'integer',
        'view_count'          => 'integer',
        'started_at'          => 'datetime',
        'completed_at'        => 'datetime',
        'last_accessed_at'    => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function user()       { return $this->belongsTo(User::class); }
    public function weekContent(){ return $this->belongsTo(WeekContent::class, 'week_content_id'); }
    public function enrollment() { return $this->belongsTo(Enrollment::class); }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeCompleted($query)  { return $query->where('is_completed', true); }
    public function scopeInProgress($query) { return $query->where('is_completed', false)->where('progress_percentage', '>', 0); }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function markAsCompleted(): void
    {
        if ($this->is_completed) return;

        $this->update([
            'is_completed'        => true,
            'progress_percentage' => 100,
            'completed_at'        => now(),
            'last_accessed_at'    => now(),
        ]);
    }

    public function updateProgress(int $percentage): void
    {
        $percentage = min(100, max(0, $percentage));

        $this->update([
            'progress_percentage' => $percentage,
            'last_accessed_at'    => now(),
        ]);

        if ($percentage >= 100) {
            $this->markAsCompleted();
        }
    }

    public function addTimeSpent(int $seconds): void
    {
        $this->increment('time_spent_seconds', $seconds);
        $this->increment('view_count');
        $this->update(['last_accessed_at' => now()]);
    }

    public function getFormattedTimeSpentAttribute(): string
    {
        $hours   = floor($this->time_spent_seconds / 3600);
        $minutes = floor(($this->time_spent_seconds % 3600) / 60);

        return $hours > 0
            ? sprintf('%dh %dm', $hours, $minutes)
            : sprintf('%dm', $minutes);
    }
}