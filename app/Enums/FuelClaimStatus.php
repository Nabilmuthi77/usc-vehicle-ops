<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

/** FR-M3-06 — alur reimbursement klaim BBM. */
enum FuelClaimStatus: string
{
    use HasLabel;

    case Draft = 'draft';
    case Diajukan = 'diajukan';
    case Terverifikasi = 'terverifikasi';
    case Ditolak = 'ditolak';
    case Dibayar = 'dibayar';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Diajukan => 'Diajukan',
            self::Terverifikasi => 'Terverifikasi',
            self::Ditolak => 'Ditolak',
            self::Dibayar => 'Dibayar',
        };
    }

    /** BR-14 — hanya klaim terverifikasi/dibayar yang masuk perhitungan biaya. */
    public function countsAsCost(): bool
    {
        return $this->is(self::Terverifikasi, self::Dibayar);
    }

    /** BR-14 — kewajiban perusahaan yang belum dibayar. */
    public function isOutstanding(): bool
    {
        return $this === self::Terverifikasi;
    }

    public function isEditableByClaimant(): bool
    {
        return $this->is(self::Draft, self::Ditolak);
    }
}
