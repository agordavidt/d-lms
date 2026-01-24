<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cohort extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'program_id',
        'name',
        'code',
        'start_date',
        'end_date',
        'status',
        'max_students',
        'enrolled_count',
        'whatsapp_link',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    // Relationships
    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'enrollments')
            ->withPivot('status', 'enrolled_at')
            ->withTimestamps();
    }

    // Status checks
    public function isUpcoming(): bool
    {
        return $this->status === 'upcoming';
    }

    public function isOngoing(): bool
    {
        return $this->status === 'ongoing';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFull(): bool
    {
        return $this->enrolled_count >= $this->max_students;
    }

    public function canEnroll(): bool
    {
        return in_array($this->status, ['upcoming', 'ongoing']) && !$this->isFull();
    }

    // Helpers
    public function incrementEnrollment()
    {
        $this->increment('enrolled_count');
    }

    public function decrementEnrollment()
    {
        if ($this->enrolled_count > 0) {
            $this->decrement('enrolled_count');
        }
    }

    public function getSpotsRemainingAttribute(): int
    {
        return max(0, $this->max_students - $this->enrolled_count);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['upcoming', 'ongoing']);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('status', 'upcoming');
    }

    public function mentor()
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }
}