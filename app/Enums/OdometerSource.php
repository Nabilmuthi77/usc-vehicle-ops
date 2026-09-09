<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

/** FR-M4-03 — sumber pembaruan odometer kendaraan. */
enum OdometerSource: string
{
    use HasLabel;

    case BookingCheckout = 'booking_checkout';
    case BookingCheckin = 'booking_checkin';
    case Fuel = 'fuel';
    case Service = 'service';
    case Manual = 'manual';

    public function label(): string
    {
        return match ($this) {
            self::BookingCheckout => 'Serah Terima Peminjaman',
            self::BookingCheckin => 'Pengembalian Peminjaman',
            self::Fuel => 'Pengisian BBM',
            self::Service => 'Realisasi Servis',
            self::Manual => 'Input Manual',
        };
    }
}
