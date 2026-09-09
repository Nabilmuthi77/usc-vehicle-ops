<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

/** FR-M4-11 — kategori realisasi servis. */
enum ServiceCategory: string
{
    use HasLabel;

    case Berkala = 'berkala';
    case Insidental = 'insidental';

    public function label(): string
    {
        return match ($this) {
            self::Berkala => 'Servis Berkala',
            self::Insidental => 'Servis Insidental / Perbaikan',
        };
    }

    /** FR-M4-09 — hanya servis berkala yang men-generate jadwal berikutnya. */
    public function regeneratesSchedule(): bool
    {
        return $this === self::Berkala;
    }
}
