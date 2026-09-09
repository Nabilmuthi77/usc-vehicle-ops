<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/** FR-M4-22 — kontrak sewa kendaraan beserta pengingat berakhirnya kontrak. */
class RentalContract extends Model
{
    use LogsModelActivity, SoftDeletes;

    protected $fillable = [
        'contract_number',
        'vendor_id',
        'start_date',
        'end_date',
        'monthly_cost',
        'coverage',
        'pic_name',
        'pic_phone',
        'pic_email',
        'document_path',
        'reminder_days',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'monthly_cost' => 'decimal:2',
            'coverage' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    /** Sisa hari sampai kontrak berakhir; negatif berarti sudah lewat. */
    public function getDaysRemainingAttribute(): int
    {
        return (int) Carbon::today()->diffInDays($this->end_date, false);
    }

    public function isExpiringSoon(): bool
    {
        $days = $this->days_remaining;

        return $days >= 0 && $days <= $this->reminder_days;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * FR-M4-22 — kontrak yang memasuki masa pengingat (default H-60).
     *
     * Ambang pengingat berbeda per kontrak sehingga perbandingannya memakai
     * selisih hari; ekspresi disesuaikan per driver agar berjalan baik di
     * MySQL (produksi) maupun SQLite (pengembangan & pengujian).
     */
    public function scopeExpiringSoon(Builder $query): Builder
    {
        $today = Carbon::today()->toDateString();

        $expression = $query->getConnection()->getDriverName() === 'sqlite'
            ? 'julianday(end_date) - julianday(?) <= reminder_days'
            : 'DATEDIFF(end_date, ?) <= reminder_days';

        return $query->active()
            ->whereDate('end_date', '>=', $today)
            ->whereRaw($expression, [$today]);
    }
}
