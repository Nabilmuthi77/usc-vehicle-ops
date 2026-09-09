<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

/** FR-M5-03 — golongan kendaraan pada gerbang tol. */
enum VehicleClass: string
{
    use HasLabel;

    case I = 'I';
    case II = 'II';
    case III = 'III';
    case IV = 'IV';
    case V = 'V';

    public function label(): string
    {
        return 'Golongan '.$this->value;
    }
}
