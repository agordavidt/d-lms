<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProgramModule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'program_id',
        'title',
        'description',
        'order',
        'duration_weeks',
        'status',
        'learning_objectives',
    ];

    protected $casts = [
        'learning_objectives' => 'array',
    ];

    // Relationships
    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function weeks()
    {
        return $this->hasMany(ModuleWeek::class)->orderBy('order');
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

    public function isArchived(): bool
    {
        return $this->status === 'archived';
    }

    // Helpers
    public function getTotalWeeksAttribute(): int
    {
        return $this->weeks()->count();
    }

    public function getTotalContentsAttribute(): int
    {
        return WeekContent::whereHas('moduleWeek', function($query) {
            $query->where('program_module_id', $this->id);
        })->count();
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    public function scopeForProgram($query, $programId)
    {
        return $query->where('program_id', $programId);
    }
}