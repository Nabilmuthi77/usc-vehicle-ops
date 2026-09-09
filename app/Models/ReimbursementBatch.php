<?php

namespace App\Models;

use App\Enums\ReimbursementBatchStatus;
use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/** FR-M3-10 s.d. FR-M3-12 — batch reimbursement klaim BBM. */
class ReimbursementBatch extends Model
{
    use LogsModelActivity, SoftDeletes;

    protected $fillable = [
        'batch_number',
        'claimant_id',
        'period_start',
        'period_end',
        'total_amount',
        'item_count',
        'status',
        'submitted_at',
        'paid_at',
        'payment_proof_path',
        'payment_note',
        'paid_by',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'total_amount' => 'decimal:2',
            'item_count' => 'integer',
            'status' => ReimbursementBatchStatus::class,
            'submitted_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function claimant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'claimant_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(FuelTransaction::class, 'reimbursement_batch_id');
    }

    public function paidBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Hitung ulang total & jumlah item dari klaim yang tergabung. */
    public function recalculateTotals(): void
    {
        $items = $this->items()->get();

        $this->forceFill([
            'item_count' => $items->count(),
            'total_amount' => $items->sum(fn (FuelTransaction $item) => $item->claimable_amount),
        ])->save();
    }

    public function isOpen(): bool
    {
        return $this->status->isOpen();
    }

    public function scopeUnpaid(Builder $query): Builder
    {
        return $query->whereIn('status', [
            ReimbursementBatchStatus::Disusun,
            ReimbursementBatchStatus::DiserahkanFinance,
        ]);
    }
}
