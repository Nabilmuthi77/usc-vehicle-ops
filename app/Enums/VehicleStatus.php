<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

enum VehicleStatus: string
{
    use HasLabel;

    case Tersedia = 'tersedia';
    case Dipinjam = 'dipinjam';
    case Servis = 'servis';
    case Nonaktif = 'nonaktif';

    public function label(): string
    {
        return match ($this) {
            self::Tersedia => 'Tersedia',
            self::Dipinjam => 'Dipinjam',
            self::Servis => 'Servis',
            self::Nonaktif => 'Nonaktif',
        };
    }

    /** BR-01 — kendaraan servis/nonaktif tidak dapat dipinjam. */
    public function isBookable(): bool
    {
        return $this->is(self::Tersedia, self::Dipinjam);
    }
}
