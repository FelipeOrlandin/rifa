<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Rifa extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'prize_image',
        'number_price',
        'total_numbers',
        'prize_value',
        'status',
        'draw_date',
    ];

    protected $casts = [
        'number_price' => 'decimal:2',
        'prize_value' => 'decimal:2',
        'total_numbers' => 'integer',
        'draw_date' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();
        
        static::creating(function ($rifa) {
            if (empty($rifa->slug)) {
                $rifa->slug = Str::slug($rifa->title);
            }
        });
    }

    public function numbers(): HasMany
    {
        return $this->hasMany(RifaNumber::class);
    }

    public function winner(): HasOne
    {
        return $this->hasOne(Winner::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function getAvailableNumbersCountAttribute(): int
    {
        return $this->numbers()->where('status', 'available')->count();
    }

    public function getSoldNumbersCountAttribute(): int
    {
        return $this->numbers()->where('status', 'paid')->count();
    }

    public function getProgressPercentageAttribute(): float
    {
        return $this->total_numbers > 0 
            ? ($this->sold_numbers_count / $this->total_numbers) * 100 
            : 0;
    }
}
