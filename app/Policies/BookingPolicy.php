<?php

namespace App\Policies;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\User;
use App\Support\Permissions;

/**
 * PRD §5.2 & FR-M6-06 — otorisasi peminjaman.
 *
 * Karyawan dan Driver hanya dapat melihat pengajuan miliknya sendiri;
 * persetujuan hanya oleh Admin GA (dengan Admin Sistem sebagai backup).
 */
class BookingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission([
            Permissions::BOOKING_VIEW_ALL,
            Permissions::BOOKING_CREATE,
        ]);
    }

    public function view(User $user, Booking $booking): bool
    {
        if ($user->can(Permissions::BOOKING_VIEW_ALL)) {
            return true;
        }

        // FR-M6-06 — pemohon dan driver yang ditugaskan boleh melihat.
        return $this->isOwnRecord($user, $booking);
    }

    public function create(User $user): bool
    {
        return $user->can(Permissions::BOOKING_CREATE);
    }

    /** FR-M2-08 & FR-M2-17 — approval satu level oleh Admin GA. */
    public function approve(User $user, Booking $booking): bool
    {
        return $user->can(Permissions::BOOKING_APPROVE)
            && $booking->status->is(BookingStatus::MenungguApproval, BookingStatus::Disetujui);
    }

    public function reject(User $user, Booking $booking): bool
    {
        return $user->can(Permissions::BOOKING_APPROVE)
            && $booking->status === BookingStatus::MenungguApproval;
    }

    /** FR-M2-07 — pembatalan oleh pemohon atau Admin GA. */
    public function cancel(User $user, Booking $booking): bool
    {
        if (! $booking->isCancellable()) {
            return false;
        }

        return $user->can(Permissions::BOOKING_APPROVE)
            || $booking->requester_id === $user->getKey();
    }

    /** FR-M2-20 — serah terima oleh Admin GA atau driver yang ditugaskan. */
    public function checkOut(User $user, Booking $booking): bool
    {
        if ($booking->status !== BookingStatus::Disetujui) {
            return false;
        }

        return $user->can(Permissions::BOOKING_HANDOVER)
            && ($user->can(Permissions::BOOKING_VIEW_ALL) || $this->isOwnRecord($user, $booking));
    }

    /** FR-M2-21 — pengembalian oleh Admin GA atau driver yang ditugaskan. */
    public function checkIn(User $user, Booking $booking): bool
    {
        if ($booking->status !== BookingStatus::SedangDigunakan) {
            return false;
        }

        return $user->can(Permissions::BOOKING_HANDOVER)
            && ($user->can(Permissions::BOOKING_VIEW_ALL) || $this->isOwnRecord($user, $booking));
    }

    /** BR-12 — peminjaman selesai hanya dapat diubah Admin dengan alasan. */
    public function update(User $user, Booking $booking): bool
    {
        if ($booking->status === BookingStatus::Selesai) {
            return $user->can(Permissions::BOOKING_CORRECT);
        }

        return $user->can(Permissions::BOOKING_APPROVE) && $booking->isAssignmentEditable();
    }

    public function delete(User $user, Booking $booking): bool
    {
        return $user->can(Permissions::BOOKING_CORRECT);
    }

    /** Pemohon sendiri atau driver yang ditugaskan pada peminjaman ini. */
    private function isOwnRecord(User $user, Booking $booking): bool
    {
        if ($booking->requester_id === $user->getKey()) {
            return true;
        }

        return $booking->driver_id !== null
            && $user->driver !== null
            && $booking->driver_id === $user->driver->getKey();
    }
}
