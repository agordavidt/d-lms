<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LiveSession extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'program_id',
        'cohort_id',
        'mentor_id',
        'week_id',
        'title',
        'description',
        'session_type',
        'meet_link',
        'meet_id',
        'start_time',
        'end_time',
        'duration_minutes',
        'status',
        'recording_link',
        'attendees',
        'total_attendees',
        'notes',
        'resources',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'attendees' => 'array',
        'resources' => 'array',
    ];

    // Auto-calculate duration
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($session) {
            if ($session->start_time && $session->end_time) {
                $session->duration_minutes = $session->start_time->diffInMinutes($session->end_time);
            }
        });

        static::updating(function ($session) {
            if ($session->start_time && $session->end_time) {
                $session->duration_minutes = $session->start_time->diffInMinutes($session->end_time);
            }
        });
    }

    // Relationships
    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function cohort()
    {
        return $this->belongsTo(Cohort::class);
    }

    public function mentor()
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }

    // Status checks
    public function isScheduled(): bool
    {
        return $this->status === 'scheduled';
    }

    public function isOngoing(): bool
    {
        return $this->status === 'ongoing';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    // Time checks
    public function isUpcoming(): bool
    {
        return $this->start_time->isFuture() && $this->status === 'scheduled';
    }

    public function isPast(): bool
    {
        return $this->end_time->isPast();
    }

    public function isToday(): bool
    {
        return $this->start_time->isToday();
    }

    // Attendance
    public function markAttendance($userId): void
    {
        $attendees = $this->attendees ?? [];
        
        if (!in_array($userId, $attendees)) {
            $attendees[] = $userId;
            $this->update([
                'attendees' => $attendees,
                'total_attendees' => count($attendees)
            ]);
        }
    }

    public function hasAttended($userId): bool
    {
        return in_array($userId, $this->attendees ?? []);
    }

    // Calendar formatting
    public function getCalendarEventAttribute(): array
    {
        $color = match($this->session_type) {
            'live_class' => '#7571f9',
            'workshop' => '#4d7cff',
            'q&a' => '#6fd96f',
            'assessment' => '#f29d56',
            default => '#9097c4'
        };

        return [
            'id' => $this->id,
            'title' => $this->title,
            'start' => $this->start_time->toIso8601String(),
            'end' => $this->end_time->toIso8601String(),
            'color' => $color,
            'description' => $this->description,
            'meet_link' => $this->meet_link,
            'mentor' => $this->mentor ? $this->mentor->name : 'TBA',
        ];
    }

    // Scopes
    public function scopeUpcoming($query)
    {
        return $query->where('start_time', '>', now())
            ->where('status', 'scheduled')
            ->orderBy('start_time');
    }

    public function scopeForCohort($query, $cohortId)
    {
        return $query->where('cohort_id', $cohortId);
    }

    public function scopeForMentor($query, $mentorId)
    {
        return $query->where('mentor_id', $mentorId);
    }
    // Add relationship to week
    public function week()
    {
        return $this->belongsTo(ModuleWeek::class, 'week_id');
    }
}