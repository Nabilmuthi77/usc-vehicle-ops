<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

/** FR-M2-03 — pilihan durasi pemakaian kendaraan. */
enum DurationType: string
{
    use HasLabel;

    case AntarJemput = 'antar_jemput';
    case SeharianStandby = 'seharian_standby';

    public function label(): string
    {
        return match ($this) {
            self::AntarJemput => 'Antar-jemput',
            self::SeharianStandby => 'Seharian Standby',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::AntarJemput => 'Kendaraan mengantar lalu menjemput kembali, tidak menunggu di lokasi.',
            self::SeharianStandby => 'Kendaraan menemani pemohon sepanjang hari.',
        };
    }

    /** BR-02 — hanya seharian_standby yang mengunci unit sepanjang tanggal. */
    public function locksVehicleForWholeDay(): bool
    {
        return $this === self::SeharianStandby;
    }
}
