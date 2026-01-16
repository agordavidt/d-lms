<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Enrollment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'program_id',
        'cohort_id',
        'enrollment_number',
        'status',
        'enrolled_at',
        'completed_at',
        'progress_percentage',
    ];

    protected $casts = [
        'enrolled_at' => 'date',
        'completed_at' => 'date',
        'progress_percentage' => 'decimal:2',
    ];

    // Auto-generate enrollment number
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($enrollment) {
            if (empty($enrollment->enrollment_number)) {
                $enrollment->enrollment_number = 'ENR-' . strtoupper(uniqid());
            }
            if (empty($enrollment->enrolled_at)) {
                $enrollment->enrolled_at = now();
            }
        });
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function cohort()
    {
        return $this->belongsTo(Cohort::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    // Status checks
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    // Payment status
    public function isFullyPaid(): bool
    {
        $totalPaid = $this->payments()
            ->where('status', 'successful')
            ->sum('final_amount');

        return $totalPaid >= $this->program->price;
    }

    public function getRemainingBalanceAttribute(): float
    {
        $totalPaid = $this->payments()
            ->where('status', 'successful')
            ->sum('final_amount');

        return max(0, $this->program->price - $totalPaid);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}