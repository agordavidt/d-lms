<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Assessment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'module_week_id', 'created_by', 'title',
        'time_limit_minutes', 'pass_percentage', 'randomize_questions',
    ];

    protected $casts = ['randomize_questions' => 'boolean'];

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
}