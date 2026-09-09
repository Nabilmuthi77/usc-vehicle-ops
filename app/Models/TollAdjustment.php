<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** FR-M5-08 & BR-08 — penyesuaian saldo hasil rekonsiliasi kartu. */
class TollAdjustment extends Model
{
    use LogsModelActivity;

    protected $fillable = [
        'toll_card_id',
        'adjustment_date',
        'system_balance',
        'actual_balance',
        'amount',
        'reason',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'adjustment_date' => 'date',
            'system_balance' => 'decimal:2',
            'actual_balance' => 'decimal:2',
            'amount' => 'decimal:2',
        ];
    }

    public function tollCard(): BelongsTo
    {
        return $this->belongsTo(TollCard::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
