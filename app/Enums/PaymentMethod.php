<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

/** FR-M3-01 — metode pembayaran pengisian BBM. */
enum PaymentMethod: string
{
    use HasLabel;

    case Tunai = 'tunai';
    case Voucher = 'voucher';
    case Kartu = 'kartu';

    public function label(): string
    {
        return match ($this) {
            self::Tunai => 'Tunai',
            self::Voucher => 'Voucher',
            self::Kartu => 'Kartu',
        };
    }
}
