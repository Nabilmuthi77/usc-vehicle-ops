<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

/** FR-M5-01 — status kartu e-toll. */
enum TollCardStatus: string
{
    use HasLabel;

    case Aktif = 'aktif';
    case Nonaktif = 'nonaktif';
    case Hilang = 'hilang';

    public function label(): string
    {
        return match ($this) {
            self::Aktif => 'Aktif',
            self::Nonaktif => 'Nonaktif',
            self::Hilang => 'Hilang',
        };
    }

    public function isUsable(): bool
    {
        return $this === self::Aktif;
    }
}
