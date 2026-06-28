<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Winner extends Model
{
    use HasFactory;

    protected $fillable = [
        'rifa_id',
        'user_id',
        'rifa_number_id',
        'winning_number',
        'is_manual',
        'notes',
        'sorteado_em',
    ];

    protected $casts = [
        'winning_number' => 'integer',
        'is_manual' => 'boolean',
        'sorteado_em' => 'datetime',
    ];

    public function rifa(): BelongsTo
    {
        return $this->belongsTo(Rifa::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function rifaNumber(): BelongsTo
    {
        return $this->belongsTo(RifaNumber::class);
    }
}
