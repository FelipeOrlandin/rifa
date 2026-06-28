<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class RifaNumber extends Model
{
    use HasFactory;

    protected $fillable = [
        'rifa_id',
        'number',
        'status',
        'user_id',
        'reserved_at',
        'reserved_until',
    ];

    protected $casts = [
        'number' => 'integer',
        'reserved_at' => 'datetime',
        'reserved_until' => 'datetime',
    ];

    public function rifa(): BelongsTo
    {
        return $this->belongsTo(Rifa::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: retorna números disponíveis (available) e reservas expiradas (reserved com reserved_until < now)
     * Também limpa automaticamente as reservas expiradas no ato da query
     */
    public function scopeDisponiveis(Builder $query): Builder
    {
        // Primeiro, liberar reservas expiradas
        static::where('status', 'reserved')
            ->whereNotNull('reserved_until')
            ->where('reserved_until', '<', now())
            ->update([
                'status' => 'available',
                'user_id' => null,
                'reserved_at' => null,
                'reserved_until' => null,
            ]);

        // Retornar números available + reserved expirados (que acabaram de ser liberados)
        return $query->where(function ($q) {
            $q->where('status', 'available')
              ->orWhere(function ($q2) {
                  $q2->where('status', 'reserved')
                     ->whereNotNull('reserved_until')
                     ->where('reserved_until', '<', now());
              });
        });
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeReserved($query)
    {
        return $query->where('status', 'reserved');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Verifica se a reserva deste número expirou
     */
    public function isReservationExpired(): bool
    {
        return $this->status === 'reserved' 
            && $this->reserved_until !== null 
            && $this->reserved_until->isPast();
    }
}
