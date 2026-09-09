<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

/** FR-M2-20 & FR-M2-21 — jenis pemeriksaan kendaraan. */
enum InspectionType: string
{
    use HasLabel;

    case Checkout = 'checkout';
    case Checkin = 'checkin';

    public function label(): string
    {
        return match ($this) {
            self::Checkout => 'Serah Terima',
            self::Checkin => 'Pengembalian',
        };
    }

    public function odometerSource(): OdometerSource
    {
        return match ($this) {
            self::Checkout => OdometerSource::BookingCheckout,
            self::Checkin => OdometerSource::BookingCheckin,
        };
    }
}
