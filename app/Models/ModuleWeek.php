<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModuleWeek extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'program_module_id', 'title', 'week_number', 'order',
        'has_assessment', 'assessment_pass_percentage',
    ];

    protected $casts = ['has_assessment' => 'boolean'];

    public function programModule() { return $this->belongsTo(ProgramModule::class); }
    public function contents()      { return $this->hasMany(WeekContent::class)->orderBy('order'); }
    public function assessment()    { return $this->hasOne(Assessment::class); }

    public function weekProgress()
    {
        return $this->hasMany(WeekProgress::class);
    }

    public function getProgressFor(User $user, Enrollment $enrollment): WeekProgress
    {
        return WeekProgress::firstOrCreate(
            [
                'user_id'        => $user->id,
                'module_week_id' => $this->id,
                'enrollment_id'  => $enrollment->id,
            ],
            [
                'is_unlocked'     => false,
                'is_completed'    => false,
                'progress_percentage' => 0,
                'total_contents'  => $this->contents()->where('is_required', true)->count(),
                'contents_completed' => 0,
            ]
        );
    }

    public function scopePublished($query) { return $query; } 
}