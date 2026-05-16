<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WeekContent extends Model
{
    use HasFactory, SoftDeletes;

    // Valid content types — 'article' is the canonical text type throughout the stack
    const TYPES = ['video', 'pdf', 'link', 'article'];

    protected $fillable = [
        'module_week_id', 'created_by', 'title', 'content_type', 'order',
        'video_url', 'video_duration_minutes', 'file_path',
        'external_url', 'text_content',
        'is_required', 'is_downloadable',
    ];

    protected $casts = [
        'is_required'     => 'boolean',
        'is_downloadable' => 'boolean',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function moduleWeek()      { return $this->belongsTo(ModuleWeek::class); }
    public function creator()         { return $this->belongsTo(User::class, 'created_by'); }
    public function contentProgress() { return $this->hasMany(ContentProgress::class, 'week_content_id'); }

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function getFileUrlAttribute(): ?string
    {
        return $this->file_path ? asset('storage/' . $this->file_path) : null;
    }

    // ── Progress helper ───────────────────────────────────────────────────────

    /**
     * Get or initialise a content progress record for a learner.
     */
    public function getProgressFor(User $user, Enrollment $enrollment): ContentProgress
    {
        return ContentProgress::firstOrCreate(
            [
                'user_id'         => $user->id,
                'week_content_id' => $this->id,
                'enrollment_id'   => $enrollment->id,
            ],
            [
                'is_completed'        => false,
                'progress_percentage' => 0,
                'time_spent_seconds'  => 0,
                'view_count'          => 0,
            ]
        );
    }
}