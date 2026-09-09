<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

/** FR-M4-13 & BR-16 — penanggung biaya servis. */
enum CostBorneBy: string
{
    use HasLabel;

    case Perusahaan = 'perusahaan';
    case Vendor = 'vendor';

    public function label(): string
    {
        return match ($this) {
            self::Perusahaan => 'Perusahaan',
            self::Vendor => 'Vendor',
        };
    }

    /** BR-09 — biaya vendor tidak masuk perhitungan biaya operasional perusahaan. */
    public function countsAsCompanyCost(): bool
    {
        return $this === self::Perusahaan;
    }
}
