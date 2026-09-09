<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

/** FR-M4-16 — status permintaan servis ke vendor. */
enum ServiceRequestStatus: string
{
    use HasLabel;

    case Dibuat = 'dibuat';
    case Dikirim = 'dikirim';
    case Dijadwalkan = 'dijadwalkan';
    case Dikerjakan = 'dikerjakan';
    case Selesai = 'selesai';
    case Dibatalkan = 'dibatalkan';

    public function label(): string
    {
        return match ($this) {
            self::Dibuat => 'Dibuat',
            self::Dikirim => 'Dikirim ke Vendor',
            self::Dijadwalkan => 'Dijadwalkan Vendor',
            self::Dikerjakan => 'Sedang Dikerjakan',
            self::Selesai => 'Selesai',
            self::Dibatalkan => 'Dibatalkan',
        };
    }

    public function isClosed(): bool
    {
        return $this->is(self::Selesai, self::Dibatalkan);
    }

    /** FR-M4-20 — hanya permintaan terkirim yang dihitung untuk eskalasi. */
    public function awaitsVendorResponse(): bool
    {
        return $this === self::Dikirim;
    }

    /** @return array<int, self> transisi status yang diizinkan. */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Dibuat => [self::Dikirim, self::Dibatalkan],
            self::Dikirim => [self::Dijadwalkan, self::Dikerjakan, self::Dibatalkan],
            self::Dijadwalkan => [self::Dikerjakan, self::Dibatalkan],
            self::Dikerjakan => [self::Selesai, self::Dibatalkan],
            self::Selesai, self::Dibatalkan => [],
        };
    }
}
