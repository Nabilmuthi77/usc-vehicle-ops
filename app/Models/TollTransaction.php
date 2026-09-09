<?php

namespace App\Models;

use App\Enums\TollTransactionSource;
use App\Enums\VehicleClass;
use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/** FR-M5-03 & FR-M5-04 — transaksi tol per kendaraan. */
class TollTransaction extends Model
{
    use LogsModelActivity, SoftDeletes;

    protected $fillable = [
        'toll_card_id',
        'vehicle_id',
        'booking_id',
        'transaction_datetime',
        'entry_gate',
        'exit_gate',
        'road_section',
        'vehicle_class',
        'amount',
        'source',
        'import_batch_id',
        'reference_number',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'transaction_datetime' => 'datetime',
            'vehicle_class' => VehicleClass::class,
            'source' => TollTransactionSource::class,
            'amount' => 'decimal:2',
        ];
    }

    public function tollCard(): BelongsTo
    {
        return $this->belongsTo(TollCard::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function importBatch(): BelongsTo
    {
        return $this->belongsTo(TollImportBatch::class, 'import_batch_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeBetweenDates(Builder $query, ?string $from, ?string $to): Builder
    {
        return $query
            ->when($from, fn (Builder $q) => $q->whereDate('transaction_datetime', '>=', $from))
            ->when($to, fn (Builder $q) => $q->whereDate('transaction_datetime', '<=', $to));
    }
}
