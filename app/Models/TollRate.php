<?php

namespace App\Models;

use App\Enums\VehicleClass;
use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/** FR-M5-05 — master ruas & tarif tol. */
class TollRate extends Model
{
    use LogsModelActivity;

    protected $fillable = [
        'road_section',
        'entry_gate',
        'exit_gate',
        'vehicle_class',
        'amount',
        'effective_date',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'vehicle_class' => VehicleClass::class,
            'amount' => 'decimal:2',
            'effective_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    /** Tarif berlaku untuk kombinasi gerbang & golongan pada tanggal tertentu. */
    public static function lookup(
        string $entryGate,
        string $exitGate,
        VehicleClass $vehicleClass,
        ?Carbon $onDate = null,
    ): ?self {
        return self::query()
            ->where('is_active', true)
            ->where('entry_gate', $entryGate)
            ->where('exit_gate', $exitGate)
            ->where('vehicle_class', $vehicleClass)
            ->whereDate('effective_date', '<=', $onDate ?? Carbon::today())
            ->orderByDesc('effective_date')
            ->first();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
