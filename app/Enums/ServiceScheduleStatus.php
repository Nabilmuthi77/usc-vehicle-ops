<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

/** FR-M4-04 — status jadwal servis berbasis sisa KM. */
enum ServiceScheduleStatus: string
{
    use HasLabel;

    case Aman = 'aman';
    case Segera = 'segera';
    case JatuhTempo = 'jatuh_tempo';

    public function label(): string
    {
        return match ($this) {
            self::Aman => 'Aman',
            self::Segera => 'Segera Servis',
            self::JatuhTempo => 'Jatuh Tempo',
        };
    }

    public function needsAttention(): bool
    {
        return $this->is(self::Segera, self::JatuhTempo);
    }

    /** Urutan tingkat urgensi untuk pengurutan dashboard (FR-M4-06). */
    public function urgency(): int
    {
        return match ($this) {
            self::JatuhTempo => 0,
            self::Segera => 1,
            self::Aman => 2,
        };
    }
}
