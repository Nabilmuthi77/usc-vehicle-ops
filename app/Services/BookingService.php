<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\InspectionType;
use App\Enums\OdometerSource;
use App\Enums\VehicleStatus;
use App\Exceptions\BusinessRuleException;
use App\Models\Booking;
use App\Models\BookingInspection;
use App\Models\Driver;
use App\Models\User;
use App\Models\Vehicle;
use App\Notifications\BookingApproved;
use App\Notifications\BookingRejected;
use App\Notifications\BookingSubmitted;
use App\Notifications\DriverAssigned;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Modul M2 — siklus peminjaman kendaraan (PRD §6.1).
 *
 * Pengajuan → approval & penugasan unit → serah terima → pengembalian.
 */
class BookingService
{
    public function __construct(
        private readonly DocumentNumberService $numbers,
        private readonly VehicleAvailabilityService $availability,
        private readonly OdometerService $odometer,
        private readonly SettingService $settings,
        private readonly NotificationDispatcher $notifier,
    ) {}

    /**
     * FR-M2-01 & FR-M2-02 — pengajuan oleh pemohon.
     *
     * Pemohon hanya mengisi 5 field; kendaraan & driver tidak diisi di sini
     * karena menjadi kewenangan Admin GA (BR-19).
     *
     * @param  array<string, mixed>  $data
     */
    public function submit(array $data, User $requester): Booking
    {
        return DB::transaction(function () use ($data, $requester) {
            $booking = new Booking($data);

            $booking->fill([
                'booking_number' => $this->numbers->generate(
                    'document_prefix_booking',
                    'bookings',
                    'booking_number',
                ),
                'requester_id' => $requester->getKey(),
                'department_id' => $data['department_id'] ?? $requester->department_id,
                'status' => BookingStatus::MenungguApproval,
                'created_by' => $requester->getKey(),
            ]);

            $booking->save();

            // FR-M7-02 — pengajuan baru diberitahukan ke antrean approval.
            $this->notifier->sendToApprovers(new BookingSubmitted($booking));

            return $booking;
        });
    }

    /**
     * FR-M2-09 s.d. FR-M2-14 — persetujuan sekaligus penugasan unit.
     *
     * @throws BusinessRuleException bila kendaraan tidak memenuhi syarat
     */
    public function approve(
        Booking $booking,
        Vehicle $vehicle,
        ?Driver $driver,
        User $approver,
        ?string $assignmentNote = null,
    ): Booking {
        if (! $booking->status->is(BookingStatus::MenungguApproval, BookingStatus::Disetujui)) {
            throw BusinessRuleException::rule(
                'BR-13',
                'Hanya pengajuan berstatus menunggu approval atau disetujui yang dapat diproses.',
            );
        }

        // FR-M2-11, FR-M2-12, FR-M2-14 — validasi ulang di sisi server.
        $check = $this->availability->check($booking, $vehicle);

        if (! $check['allowed']) {
            throw new BusinessRuleException(
                'Kendaraan tidak dapat dipilih: '.implode(' ', $check['reasons']),
            );
        }

        if ($driver !== null) {
            $this->assertDriverAssignable($booking, $driver);
        }

        return DB::transaction(function () use ($booking, $vehicle, $driver, $approver, $assignmentNote) {
            $booking->fill([
                'vehicle_id' => $vehicle->getKey(),
                'driver_id' => $driver?->getKey(),
                // BR-20 — tanpa driver berarti pemohon mengemudi sendiri.
                'self_drive' => $driver === null,
                'approved_by' => $approver->getKey(),
                'approved_at' => Carbon::now(),
                'assignment_note' => $assignmentNote,
                'rejection_reason' => null,
                'status' => BookingStatus::Disetujui,
            ])->save();

            // FR-M2-18 — pemohon dan driver menerima detail penugasan.
            $this->notifier->send($booking->requester, new BookingApproved($booking));

            if ($driver?->user !== null) {
                $this->notifier->send($driver->user, new DriverAssigned($booking));
            }

            return $booking->refresh();
        });
    }

    /**
     * FR-M2-13 & BR-21 — pastikan driver layak ditugaskan.
     *
     * @throws BusinessRuleException
     */
    private function assertDriverAssignable(Booking $booking, Driver $driver): void
    {
        if (! $driver->is_active) {
            throw new BusinessRuleException('Driver berstatus nonaktif.');
        }

        if (! $driver->hasValidLicense($booking->booking_date)) {
            throw BusinessRuleException::rule('FR-M2-13', sprintf(
                'SIM driver %s sudah tidak berlaku pada tanggal pemakaian.',
                $driver->name,
            ));
        }

        $available = $this->availability->availableDrivers($booking)
            ->contains(fn (Driver $candidate) => $candidate->is($driver));

        if (! $available) {
            throw BusinessRuleException::rule('BR-21', sprintf(
                'Driver %s sudah ditugaskan seharian standby pada tanggal tersebut.',
                $driver->name,
            ));
        }
    }

    /** FR-M2-15 — penolakan dengan alasan wajib. */
    public function reject(Booking $booking, string $reason, User $approver): Booking
    {
        if ($booking->status !== BookingStatus::MenungguApproval) {
            throw new BusinessRuleException('Hanya pengajuan yang menunggu approval dapat ditolak.');
        }

        return DB::transaction(function () use ($booking, $reason, $approver) {
            $booking->fill([
                'status' => BookingStatus::Ditolak,
                'rejection_reason' => $reason,
                'approved_by' => $approver->getKey(),
                'approved_at' => Carbon::now(),
            ])->save();

            $this->notifier->send($booking->requester, new BookingRejected($booking));

            return $booking;
        });
    }

    /** FR-M2-07 — pembatalan oleh pemohon sebelum serah terima. */
    public function cancel(Booking $booking, ?string $reason, User $actor): Booking
    {
        if (! $booking->isCancellable()) {
            throw new BusinessRuleException(
                'Peminjaman tidak dapat dibatalkan setelah serah terima dilakukan.',
            );
        }

        $booking->fill([
            'status' => BookingStatus::Dibatalkan,
            'cancelled_at' => Carbon::now(),
            'cancellation_reason' => $reason,
        ])->save();

        activity('Booking')
            ->performedOn($booking)
            ->causedBy($actor)
            ->log('Peminjaman dibatalkan');

        $this->notifier->sendToApprovers(new \App\Notifications\BookingCancelled($booking));

        return $booking;
    }

    /**
     * FR-M2-20 & FR-M2-22 — serah terima kendaraan (check-out).
     *
     * @param  array<string, mixed>  $data
     */
    public function checkOut(Booking $booking, array $data, User $actor): Booking
    {
        if ($booking->status !== BookingStatus::Disetujui) {
            throw new BusinessRuleException(
                'Serah terima hanya dapat dilakukan pada peminjaman berstatus disetujui.',
            );
        }

        if (! $booking->hasVehicleAssigned()) {
            throw BusinessRuleException::rule('BR-20', 'Peminjaman belum memiliki kendaraan yang ditetapkan.');
        }

        return DB::transaction(function () use ($booking, $data, $actor) {
            $vehicle = $booking->vehicle;
            $odometer = (int) $data['odometer'];

            $this->odometer->assertNotBackward($vehicle, $odometer);

            $inspection = $this->recordInspection($booking, InspectionType::Checkout, $data, $actor);

            $booking->fill([
                'status' => BookingStatus::SedangDigunakan,
                'actual_start_datetime' => $data['actual_start_datetime'] ?? Carbon::now(),
                'odometer_start' => $odometer,
                'fuel_level_start' => $data['fuel_level'] ?? null,
            ])->save();

            $vehicle->forceFill(['status' => VehicleStatus::Dipinjam])->save();

            $this->odometer->record(
                vehicle: $vehicle,
                odometer: $odometer,
                source: OdometerSource::BookingCheckout,
                recordedAt: $booking->actual_start_datetime,
                sourceable: $booking,
                recordedBy: $actor,
                notes: 'Serah terima '.$booking->booking_number,
            );

            return $booking->refresh()->setRelation('checkoutInspection', $inspection);
        });
    }

    /**
     * FR-M2-21 s.d. FR-M2-23 — pengembalian kendaraan (check-in).
     *
     * @param  array<string, mixed>  $data
     */
    public function checkIn(Booking $booking, array $data, User $actor): Booking
    {
        if ($booking->status !== BookingStatus::SedangDigunakan) {
            throw new BusinessRuleException(
                'Pengembalian hanya dapat dilakukan pada peminjaman yang sedang digunakan.',
            );
        }

        $odometerEnd = (int) $data['odometer'];

        // FR-M2-23 — odometer akhir tidak boleh lebih kecil dari odometer awal.
        if ($odometerEnd < (int) $booking->odometer_start) {
            throw BusinessRuleException::rule('FR-M2-23', sprintf(
                'Odometer akhir (%s km) tidak boleh lebih kecil dari odometer awal (%s km).',
                number_format($odometerEnd, 0, ',', '.'),
                number_format((int) $booking->odometer_start, 0, ',', '.'),
            ));
        }

        return DB::transaction(function () use ($booking, $data, $actor, $odometerEnd) {
            $vehicle = $booking->vehicle;
            $endedAt = $data['actual_end_datetime'] ?? Carbon::now();

            $inspection = $this->recordInspection($booking, InspectionType::Checkin, $data, $actor);

            $booking->fill([
                'status' => BookingStatus::Selesai,
                'actual_end_datetime' => $endedAt,
                'odometer_end' => $odometerEnd,
                // BR-04 — jarak tempuh peminjaman.
                'distance_traveled' => $odometerEnd - (int) $booking->odometer_start,
                'fuel_level_end' => $data['fuel_level'] ?? null,
            ])->save();

            // Unit kembali tersedia kecuali sedang ditandai servis/nonaktif.
            if ($vehicle->status === VehicleStatus::Dipinjam) {
                $vehicle->forceFill(['status' => VehicleStatus::Tersedia])->save();
            }

            $this->odometer->record(
                vehicle: $vehicle,
                odometer: $odometerEnd,
                source: OdometerSource::BookingCheckin,
                recordedAt: $endedAt,
                sourceable: $booking,
                recordedBy: $actor,
                notes: 'Pengembalian '.$booking->booking_number,
            );

            return $booking->refresh()->setRelation('checkinInspection', $inspection);
        });
    }

    /**
     * FR-M2-23 — peringatan bila jarak tempuh per hari tidak wajar.
     * Tidak memblokir penyimpanan, hanya dikembalikan sebagai peringatan.
     */
    public function unusualDistanceWarning(Booking $booking): ?string
    {
        $distance = $booking->calculateDistance();

        if ($distance === null) {
            return null;
        }

        $threshold = $this->settings->integer('daily_distance_threshold_km', 1000);

        if ($distance <= $threshold) {
            return null;
        }

        return sprintf(
            'Jarak tempuh %s km melebihi ambang kewajaran %s km/hari. Mohon diperiksa kembali.',
            number_format($distance, 0, ',', '.'),
            number_format($threshold, 0, ',', '.'),
        );
    }

    /**
     * Simpan hasil pemeriksaan beserta foto kondisi.
     *
     * @param  array<string, mixed>  $data
     */
    private function recordInspection(
        Booking $booking,
        InspectionType $type,
        array $data,
        User $actor,
    ): BookingInspection {
        $inspection = BookingInspection::updateOrCreate(
            ['booking_id' => $booking->getKey(), 'type' => $type],
            [
                'checklist' => $data['checklist'] ?? [],
                'odometer' => $data['odometer'] ?? null,
                'fuel_level' => $data['fuel_level'] ?? null,
                'condition_notes' => $data['condition_notes'] ?? null,
                'damage_found' => (bool) ($data['damage_found'] ?? false),
                'damage_notes' => $data['damage_notes'] ?? null,
                'confirmed' => (bool) ($data['confirmed'] ?? false),
                'inspected_by' => $actor->getKey(),
                'inspected_at' => Carbon::now(),
            ],
        );

        foreach ($data['photos'] ?? [] as $position => $path) {
            $inspection->photos()->create([
                'photo_path' => $path,
                'position' => $position,
            ]);
        }

        return $inspection;
    }

    /**
     * FR-M2-19 — papan penugasan harian untuk dicetak/ditampilkan di ruang GA.
     */
    public function dailyAssignmentBoard(Carbon|string $date)
    {
        return Booking::query()
            ->with(['vehicle', 'driver', 'requester', 'department'])
            ->activeOn($date)
            ->orderBy('duration_type')
            ->orderBy('booking_number')
            ->get();
    }
}
