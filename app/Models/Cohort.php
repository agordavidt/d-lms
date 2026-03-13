<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cohort extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'program_id', 'name', 'code', 'status', 'enrolled_count',
    ];

    public function program()   { return $this->belongsTo(Program::class); }
    public function enrollments() { return $this->hasMany(Enrollment::class); }

    public function incrementEnrollment() { $this->increment('enrolled_count'); }
    public function decrementEnrollment() { if ($this->enrolled_count > 0) $this->decrement('enrolled_count'); }
}