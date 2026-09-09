<?php

namespace App\Models;

use App\Enums\VehicleDocumentType;
use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/** FR-M1-03 — dokumen kendaraan dengan pengingat H-30. */
class VehicleDocument extends Model
{
    use LogsModelActivity, SoftDeletes;

    protected $fillable = [
        'vehicle_id',
        'document_type',
        'document_number',
        'issued_date',
        'expiry_date',
        'file_path',
        'reminder_days',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'document_type' => VehicleDocumentType::class,
            'issued_date' => 'date',
            'expiry_date' => 'date',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function getDaysRemainingAttribute(): int
    {
        return (int) Carbon::today()->diffInDays($this->expiry_date, false);
    }

    public function isExpired(): bool
    {
        return $this->days_remaining < 0;
    }

    public function isExpiringSoon(): bool
    {
        $days = $this->days_remaining;

        return $days >= 0 && $days <= $this->reminder_days;
    }

    /** Dokumen yang memasuki masa pengingat (default H-30). */
    public function scopeExpiringSoon(Builder $query): Builder
    {
        $today = Carbon::today()->toDateString();

        $expression = $query->getConnection()->getDriverName() === 'sqlite'
            ? 'julianday(expiry_date) - julianday(?) <= reminder_days'
            : 'DATEDIFF(expiry_date, ?) <= reminder_days';

        return $query->whereDate('expiry_date', '>=', $today)
            ->whereRaw($expression, [$today]);
    }
}
