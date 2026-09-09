<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

/** FR-M1-06 & FR-M4-22 — jenis mitra/vendor. */
enum VendorType: string
{
    use HasLabel;

    case Bengkel = 'bengkel';
    case Spbu = 'spbu';
    case Leasing = 'leasing';

    public function label(): string
    {
        return match ($this) {
            self::Bengkel => 'Bengkel',
            self::Spbu => 'SPBU',
            self::Leasing => 'Sewa / Leasing',
        };
    }
}
