<?php

namespace App\Models;

use App\Enums\TollCardStatus;
use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/** FR-M5-01 — master kartu e-toll beserta saldo berjalan. */
class TollCard extends Model
{
    use LogsModelActivity, SoftDeletes;

    protected $fillable = [
        'card_number',
        'issuer',
        'vehicle_id',
        'balance',
        'min_balance_alert',
        'status',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'balance' => 'decimal:2',
            'min_balance_alert' => 'decimal:2',
            'status' => TollCardStatus::class,
            'is_active' => 'boolean',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function topups(): HasMany
    {
        return $this->hasMany(TollTopup::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(TollTransaction::class);
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(TollAdjustment::class);
    }

    /** FR-M5-07 — saldo di bawah ambang minimum. */
    public function isBelowMinimum(): bool
    {
        return (float) $this->balance < (float) $this->min_balance_alert;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->where('status', TollCardStatus::Aktif);
    }

    public function scopeBelowMinimum(Builder $query): Builder
    {
        return $query->active()->whereColumn('balance', '<', 'min_balance_alert');
    }
}
