<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LiveSession extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'program_id', 'mentor_id', 'title', 'session_type',
        'start_time', 'end_time', 'duration_minutes',
        'meet_link', 'status', 'recording_link',
        'attendees', 'total_attendees', 'notes',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time'   => 'datetime',
        'attendees'  => 'array',
    ];

    public function program() { return $this->belongsTo(Program::class); }
    public function mentor()  { return $this->belongsTo(User::class, 'mentor_id'); }

    public function isUpcoming(): bool
    {
        return $this->status === 'scheduled' && $this->start_time->isFuture();
    }

    public function scopeUpcoming($query)
    {
        return $query->where('status', 'scheduled')->where('start_time', '>', now());
    }

    public function scopeForPrograms($query, array $programIds)
    {
        return $query->whereIn('program_id', $programIds);
    }
}