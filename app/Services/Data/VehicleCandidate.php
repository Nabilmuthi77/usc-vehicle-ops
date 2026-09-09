<?php

namespace App\Services\Data;

use App\Enums\ServiceScheduleStatus;
use App\Models\Vehicle;

/**
 * FR-M2-12 — satu baris pilihan kendaraan pada layar approval.
 *
 * Unit yang tidak memenuhi syarat tetap ditampilkan (`selectable = false`)
 * lengkap dengan alasannya, sesuai FR-M2-11.
 */
class VehicleCandidate
{
    /**
     * @param  array<int, string>  $blockingReasons  alasan unit tidak dapat dipilih
     * @param  array<int, string>  $warnings  peringatan yang tidak menghalangi pemilihan
     */
    public function __construct(
        public readonly Vehicle $vehicle,
        public readonly bool $selectable,
        public readonly array $blockingReasons = [],
        public readonly array $warnings = [],
        public readonly bool $matchesOddEven = true,
        public readonly ServiceScheduleStatus $serviceStatus = ServiceScheduleStatus::Aman,
        public readonly int $conflictingBookings = 0,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->vehicle->getKey(),
            'plate_number' => $this->vehicle->plate_number,
            'name' => $this->vehicle->full_name,
            'status' => $this->vehicle->status->value,
            'parity' => $this->vehicle->plate_parity,
            'selectable' => $this->selectable,
            'blocking_reasons' => $this->blockingReasons,
            'warnings' => $this->warnings,
            'matches_odd_even' => $this->matchesOddEven,
            'service_status' => $this->serviceStatus->value,
            'conflicting_bookings' => $this->conflictingBookings,
        ];
    }
}
