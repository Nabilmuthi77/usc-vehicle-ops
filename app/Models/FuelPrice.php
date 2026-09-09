<?php

namespace App\Models;

use App\Enums\FuelType;
use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/** FR-M3-19 — master harga BBM per jenis dengan riwayat perubahan. */
class FuelPrice extends Model
{
    use LogsModelActivity;

    protected $fillable = [
        'fuel_type',
        'price_per_liter',
        'effective_date',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'fuel_type' => FuelType::class,
            'price_per_liter' => 'decimal:2',
            'effective_date' => 'date',
        ];
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Harga yang berlaku untuk sebuah jenis BBM pada tanggal tertentu. */
    public static function currentFor(FuelType $fuelType, ?Carbon $onDate = null): ?self
    {
        return self::query()
            ->where('fuel_type', $fuelType)
            ->whereDate('effective_date', '<=', $onDate ?? Carbon::today())
            ->orderByDesc('effective_date')
            ->first();
    }
}
