<?php

namespace Database\Seeders;

use App\Enums\FuelType;
use App\Models\FuelPrice;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/** FR-M3-19 — harga BBM awal per jenis sebagai pembanding kewajaran klaim. */
class FuelPriceSeeder extends Seeder
{
    public function run(): void
    {
        $prices = [
            [FuelType::Pertalite, 10000],
            [FuelType::Pertamax, 13700],
            [FuelType::Solar, 6800],
            [FuelType::Dexlite, 14000],
        ];

        $effectiveDate = Carbon::today()->startOfYear()->toDateString();

        foreach ($prices as [$fuelType, $price]) {
            FuelPrice::updateOrCreate(
                ['fuel_type' => $fuelType, 'effective_date' => $effectiveDate],
                ['price_per_liter' => $price, 'notes' => 'Harga awal saat implementasi sistem.'],
            );
        }
    }
}
