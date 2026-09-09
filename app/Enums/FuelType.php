<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

/** FR-M1-02 — jenis BBM kendaraan. */
enum FuelType: string
{
    use HasLabel;

    case Pertalite = 'pertalite';
    case Pertamax = 'pertamax';
    case Solar = 'solar';
    case Dexlite = 'dexlite';

    public function label(): string
    {
        return match ($this) {
            self::Pertalite => 'Pertalite',
            self::Pertamax => 'Pertamax',
            self::Solar => 'Solar',
            self::Dexlite => 'Dexlite',
        };
    }
}
