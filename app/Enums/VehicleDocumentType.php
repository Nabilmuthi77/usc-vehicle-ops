<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

/** FR-M1-03 — dokumen kendaraan dengan pengingat kedaluwarsa. */
enum VehicleDocumentType: string
{
    use HasLabel;

    case Stnk = 'stnk';
    case PajakTahunan = 'pajak_tahunan';
    case Kir = 'kir';
    case Asuransi = 'asuransi';

    public function label(): string
    {
        return match ($this) {
            self::Stnk => 'STNK',
            self::PajakTahunan => 'Pajak Tahunan',
            self::Kir => 'KIR',
            self::Asuransi => 'Asuransi',
        };
    }
}
