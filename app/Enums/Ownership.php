<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

/** FR-M1-02 & FR-M4-13 — kepemilikan kendaraan. */
enum Ownership: string
{
    use HasLabel;

    case Milik = 'milik';
    case Sewa = 'sewa';
    case Leasing = 'leasing';

    public function label(): string
    {
        return match ($this) {
            self::Milik => 'Milik',
            self::Sewa => 'Sewa',
            self::Leasing => 'Leasing',
        };
    }

    /** BR-16 — sewa & leasing wajib terikat kontrak vendor. */
    public function requiresRentalContract(): bool
    {
        return $this->is(self::Sewa, self::Leasing);
    }

    /** BR-16 — penanggung biaya servis default menurut kepemilikan. */
    public function defaultCostBorneBy(): CostBorneBy
    {
        return $this->requiresRentalContract()
            ? CostBorneBy::Vendor
            : CostBorneBy::Perusahaan;
    }
}
