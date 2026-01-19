<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class WeekContent extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'module_week_id',
        'created_by',
        'title',
        'description',
        'content_type',
        'order',
        'video_url',
        'video_duration_minutes',
        'file_path',
        'external_url',
        'text_content',
        'is_required',
        'is_downloadable',
        'status',
        'metadata',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_downloadable' => 'boolean',
        'metadata' => 'array',
    ];

    // Relationships
    public function moduleWeek()
    {
        return $this->belongsTo(ModuleWeek::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function contentProgress()
    {
        return $this->hasMany(ContentProgress::class);
    }

    // Status checks
    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    // Content type checks
    public function isVideo(): bool
    {
        return $this->content_type === 'video';
    }

    public function isPdf(): bool
    {
        return $this->content_type === 'pdf';
    }

    public function isLink(): bool
    {
        return $this->content_type === 'link';
    }

    public function isText(): bool
    {
        return $this->content_type === 'text';
    }

    // Accessors
    public function getFileUrlAttribute(): ?string
    {
        if ($this->file_path) {
            return Storage::url($this->file_path);
        }
        return null;
    }

    public function getFileSizeAttribute(): ?string
    {
        if ($this->file_path && Storage::exists($this->file_path)) {
            $bytes = Storage::size($this->file_path);
            return $this->formatBytes($bytes);
        }
        return null;
    }

    public function getContentUrlAttribute(): ?string
    {
        return match($this->content_type) {
            'video' => $this->video_url,
            'pdf' => $this->file_url,
            'link' => $this->external_url,
            'text' => null,
            default => null,
        };
    }

    public function getIconAttribute(): string
    {
        return match($this->content_type) {
            'video' => '📹',
            'pdf' => '📄',
            'link' => '🔗',
            'text' => '📝',
            default => '📄',
        };
    }

    public function getTypeDisplayAttribute(): string
    {
        return match($this->content_type) {
            'video' => 'Video',
            'pdf' => 'PDF Document',
            'link' => 'External Resource',
            'text' => 'Article',
            default => 'Content',
        };
    }

    // Helper methods
    private function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    // Check if content is completed by user
    public function isCompletedBy(User $user): bool
    {
        $progress = ContentProgress::where('user_id', $user->id)
            ->where('week_content_id', $this->id)
            ->first();

        return $progress && $progress->is_completed;
    }

    // Get user's progress for this content
    public function getProgressFor(User $user, Enrollment $enrollment)
    {
        return ContentProgress::firstOrCreate([
            'user_id' => $user->id,
            'week_content_id' => $this->id,
            'enrollment_id' => $enrollment->id,
        ], [
            'is_completed' => false,
            'progress_percentage' => 0,
            'time_spent_seconds' => 0,
        ]);
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('content_type', $type);
    }
}