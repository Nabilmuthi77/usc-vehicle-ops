<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

/** FR-M5-06 — asal data transaksi tol. */
enum TollTransactionSource: string
{
    use HasLabel;

    case Manual = 'manual';
    case Import = 'import';

    public function label(): string
    {
        return match ($this) {
            self::Manual => 'Input Manual',
            self::Import => 'Import Mutasi',
        };
    }
}
