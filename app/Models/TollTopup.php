<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/** FR-M5-02 — top-up saldo kartu e-toll. */
class TollTopup extends Model
{
    use LogsModelActivity, SoftDeletes;

    protected $fillable = [
        'toll_card_id',
        'topup_date',
        'amount',
        'method',
        'reference_number',
        'proof_path',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'topup_date' => 'date',
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
