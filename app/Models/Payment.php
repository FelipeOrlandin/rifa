<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'rifa_id',
        'amount',
        'status',
        'payment_method',
        'transaction_id',
        'numbers_purchased',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'numbers_purchased' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function rifa(): BelongsTo
    {
        return $this->belongsTo(Rifa::class);
    }
}
