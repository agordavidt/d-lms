<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProgramModule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['program_id', 'title', 'order', 'duration_weeks'];

    public function program() { return $this->belongsTo(Program::class); }

    public function weeks()
    {
        return $this->hasMany(ModuleWeek::class)->orderBy('order');
    }

    public function scopeForProgram($query, $programId)
    {
        return $query->where('program_id', $programId)->orderBy('order');
    }
}