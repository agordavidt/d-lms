<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'enrollment_id',
        'program_id',
        'transaction_id',
        'reference',
        'amount',
        'discount_amount',
        'final_amount',
        'payment_method',
        'status',
        'payment_plan',
        'installment_number',
        'remaining_balance',
        'installment_status',
        'metadata',
        'flutterwave_response',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'remaining_balance' => 'decimal:2',
        'metadata' => 'array',
        'paid_at' => 'datetime',
    ];

    // Auto-generate transaction ID
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {
            if (empty($payment->transaction_id)) {
                $payment->transaction_id = 'TXN-' . strtoupper(uniqid());
            }
        });
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    // Status checks
    public function isSuccessful(): bool
    {
        return $this->status === 'successful';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    // Payment plan checks
    public function isOneTime(): bool
    {
        return $this->payment_plan === 'one-time';
    }

    public function isInstallment(): bool
    {
        return $this->payment_plan === 'installment';
    }

    // Scopes
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'successful');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}