<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

/** FR-M3-10 s.d. FR-M3-12 — batch reimbursement per periode per driver. */
enum ReimbursementBatchStatus: string
{
    use HasLabel;

    case Disusun = 'disusun';
    case DiserahkanFinance = 'diserahkan_finance';
    case Dibayar = 'dibayar';

    public function label(): string
    {
        return match ($this) {
            self::Disusun => 'Disusun',
            self::DiserahkanFinance => 'Diserahkan ke Finance',
            self::Dibayar => 'Dibayar',
        };
    }

    public function isOpen(): bool
    {
        return $this === self::Disusun;
    }
}
