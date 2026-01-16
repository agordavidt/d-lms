<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Program extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'overview',
        'duration',
        'price',
        'discount_percentage',
        'image',
        'status',
        'features',
        'requirements',
        'max_students',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'features' => 'array',
        'requirements' => 'array',
    ];

    // Automatically generate slug
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($program) {
            if (empty($program->slug)) {
                $program->slug = Str::slug($program->name);
            }
        });
    }

    // Relationships
    public function cohorts()
    {
        return $this->hasMany(Cohort::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    // Accessors
    public function getDiscountedPriceAttribute(): float
    {
        if ($this->discount_percentage > 0) {
            $discount = ($this->price * $this->discount_percentage) / 100;
            return $this->price - $discount;
        }
        return $this->price;
    }

    public function getInstallmentAmountAttribute(): float
    {
        return $this->price / 2;
    }

    public function getImageUrlAttribute(): string
    {
        if ($this->image) {
            return asset('storage/' . $this->image);
        }
        return asset('images/default-program.png');
    }

    // Status checks
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function hasAvailableCohorts(): bool
    {
        return $this->cohorts()
            ->whereIn('status', ['upcoming', 'ongoing'])
            ->exists();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePublished($query)
    {
        return $query->whereIn('status', ['active']);
    }
}