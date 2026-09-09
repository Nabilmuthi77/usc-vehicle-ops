<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

enum BookingStatus: string
{
    use HasLabel;

    case Draft = 'draft';
    case MenungguApproval = 'menunggu_approval';
    case Disetujui = 'disetujui';
    case Ditolak = 'ditolak';
    case SedangDigunakan = 'sedang_digunakan';
    case Selesai = 'selesai';
    case Dibatalkan = 'dibatalkan';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::MenungguApproval => 'Menunggu Approval',
            self::Disetujui => 'Disetujui',
            self::Ditolak => 'Ditolak',
            self::SedangDigunakan => 'Sedang Digunakan',
            self::Selesai => 'Selesai',
            self::Dibatalkan => 'Dibatalkan',
        };
    }

    /** FR-M2-07 — pemohon dapat membatalkan sebelum serah terima. */
    public function isCancellable(): bool
    {
        return $this->is(self::Draft, self::MenungguApproval, self::Disetujui);
    }

    /** Status yang masih menahan alokasi unit pada tanggal pemakaian. */
    public function holdsVehicle(): bool
    {
        return $this->is(self::Disetujui, self::SedangDigunakan);
    }
}
