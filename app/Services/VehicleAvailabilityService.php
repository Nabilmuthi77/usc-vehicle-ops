<?php

namespace App\Services;

use App\Enums\DurationType;
use App\Enums\VehicleStatus;
use App\Models\Booking;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Services\Data\VehicleCandidate;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * FR-M2-11 s.d. FR-M2-14 — penentuan kendaraan & driver yang boleh
 * ditugaskan Admin GA pada layar approval.
 *
 * Menerapkan BR-01 (status unit), BR-02 (bentrok berbasis durasi),
 * BR-18 (ganjil–genap), dan BR-21 (bentrok driver).
 */
class VehicleAvailabilityService
{
    /**
     * Daftar kandidat kendaraan untuk sebuah pengajuan.
     *
     * Seluruh unit dikembalikan — termasuk yang tidak dapat dipilih —
     * agar Admin GA melihat alasan penolakannya (FR-M2-11).
     *
     * @return Collection<int, VehicleCandidate>
     */
    public function candidatesFor(Booking $booking): Collection
    {
        $date = $booking->booking_date;

        $vehicles = Vehicle::query()
            ->with(['serviceSchedules', 'documents'])
            ->orderBy('plate_number')
            ->get();

        // Alokasi yang sudah ada pada tanggal tersebut, dikelompokkan per unit.
        $existing = Booking::query()
            ->activeOn($date)
            ->when($booking->exists, fn ($q) => $q->whereKeyNot($booking->getKey()))
            ->get(['id', 'vehicle_id', 'duration_type', 'booking_number'])
            ->groupBy('vehicle_id');

        return $vehicles->map(function (Vehicle $vehicle) use ($booking, $date, $existing) {
            return $this->evaluate(
                $vehicle,
                $booking,
                $date,
                $existing->get($vehicle->getKey()) ?? collect(),
            );
        });
    }

    /** Menilai satu unit terhadap sebuah pengajuan. */
    private function evaluate(
        Vehicle $vehicle,
        Booking $booking,
        Carbon $date,
        Collection $existingBookings,
    ): VehicleCandidate {
        $blocking = [];
        $warnings = [];

        // BR-01 & FR-M2-12 — unit servis/nonaktif tidak dapat dipilih.
        if (! $vehicle->isBookable()) {
            $blocking[] = match ($vehicle->status) {
                VehicleStatus::Servis => 'Kendaraan sedang dalam masa servis.',
                VehicleStatus::Nonaktif => 'Kendaraan berstatus nonaktif.',
                default => 'Kendaraan tidak tersedia.',
            };
        }

        // BR-18 & FR-M2-11 — penyaringan ganjil–genap.
        $matchesOddEven = $vehicle->matchesOddEvenRule($date);

        if ($booking->odd_even_zone && ! $matchesOddEven) {
            $blocking[] = sprintf(
                'Pelat berakhiran %s tidak sesuai aturan ganjil–genap untuk tanggal %s.',
                $vehicle->plate_parity ? strtolower($vehicle->plate_parity) : 'tanpa angka',
                $date->format('d-m-Y'),
            );
        }

        // BR-02 & FR-M2-14 — validasi bentrok berbasis durasi.
        $conflict = $this->evaluateDurationConflict($booking->duration_type, $existingBookings);
        $blocking = array_merge($blocking, $conflict['blocking']);
        $warnings = array_merge($warnings, $conflict['warnings']);

        // FR-M2-12 — indikator kesiapan tambahan (tidak menghalangi pemilihan).
        $serviceStatus = $vehicle->service_status;

        if ($serviceStatus->needsAttention()) {
            $warnings[] = 'Status servis: '.$serviceStatus->label().'.';
        }

        foreach ($vehicle->documents as $document) {
            if ($document->isExpired()) {
                $warnings[] = $document->document_type->label().' sudah kedaluwarsa.';
            } elseif ($document->isExpiringSoon()) {
                $warnings[] = sprintf(
                    '%s berakhir dalam %d hari.',
                    $document->document_type->label(),
                    $document->days_remaining,
                );
            }
        }

        return new VehicleCandidate(
            vehicle: $vehicle,
            selectable: $blocking === [],
            blockingReasons: $blocking,
            warnings: $warnings,
            matchesOddEven: $matchesOddEven,
            serviceStatus: $serviceStatus,
            conflictingBookings: $existingBookings->count(),
        );
    }

    /**
     * BR-02 — unit `seharian_standby` terkunci sepanjang tanggal tersebut;
     * unit `antar_jemput` boleh dialokasikan lebih dari sekali disertai
     * peringatan bentrok kepada Admin GA.
     *
     * @return array{blocking: array<int, string>, warnings: array<int, string>}
     */
    private function evaluateDurationConflict(
        DurationType $requested,
        Collection $existingBookings,
    ): array {
        if ($existingBookings->isEmpty()) {
            return ['blocking' => [], 'warnings' => []];
        }

        $standby = $existingBookings->firstWhere(
            'duration_type',
            DurationType::SeharianStandby,
        );

        if ($standby !== null) {
            return [
                'blocking' => [sprintf(
                    'Sudah dialokasikan seharian standby pada pengajuan %s.',
                    $standby->booking_number,
                )],
                'warnings' => [],
            ];
        }

        // Sisa alokasi pasti bertipe antar-jemput.
        if ($requested->locksVehicleForWholeDay()) {
            return [
                'blocking' => [sprintf(
                    'Sudah dialokasikan pada %d pengajuan antar-jemput; '
                    .'penugasan seharian standby membutuhkan unit yang kosong penuh.',
                    $existingBookings->count(),
                )],
                'warnings' => [],
            ];
        }

        return [
            'blocking' => [],
            'warnings' => [sprintf(
                'Unit sudah dialokasikan pada %d pengajuan antar-jemput lain di tanggal yang sama (%s). '
                .'Pastikan jadwalnya tidak berbenturan.',
                $existingBookings->count(),
                $existingBookings->pluck('booking_number')->implode(', '),
            )],
        ];
    }

    /**
     * FR-M2-13 & BR-21 — driver yang boleh ditugaskan: SIM masih berlaku
     * dan belum terikat penugasan yang beririsan.
     *
     * @return Collection<int, Driver>
     */
    public function availableDrivers(Booking $booking): Collection
    {
        return Driver::query()
            ->active()
            ->withValidLicense($booking->booking_date)
            ->availableOn(
                $booking->booking_date,
                $booking->duration_type,
                $booking->exists ? $booking->getKey() : null,
            )
            ->orderBy('name')
            ->get();
    }

    /**
     * Periksa apakah sebuah unit boleh ditetapkan ke pengajuan tertentu.
     *
     * @return array{allowed: bool, reasons: array<int, string>, warnings: array<int, string>}
     */
    public function check(Booking $booking, Vehicle $vehicle): array
    {
        $candidate = $this->candidatesFor($booking)
            ->first(fn (VehicleCandidate $c) => $c->vehicle->is($vehicle));

        if ($candidate === null) {
            return ['allowed' => false, 'reasons' => ['Kendaraan tidak ditemukan.'], 'warnings' => []];
        }

        return [
            'allowed' => $candidate->selectable,
            'reasons' => $candidate->blockingReasons,
            'warnings' => $candidate->warnings,
        ];
    }
}
