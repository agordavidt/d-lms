<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WeekContent extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'module_week_id', 'created_by', 'title', 'content_type', 'order',
        'video_url', 'video_duration_minutes', 'file_path',
        'external_url', 'text_content', 'is_required', 'is_downloadable',
    ];

    protected $casts = [
        'is_required'     => 'boolean',
        'is_downloadable' => 'boolean',
    ];

    public function moduleWeek()     { return $this->belongsTo(ModuleWeek::class); }
    public function creator()        { return $this->belongsTo(User::class, 'created_by'); }
    public function contentProgress(){ return $this->hasMany(ContentProgress::class); }

    public function scopePublished($query) { return $query; } // no status — always visible

    public function getFileUrlAttribute(): ?string
    {
        return $this->file_path ? asset('storage/' . $this->file_path) : null;
    }
}