<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\UscVehicleOpsNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification as NotificationFacade;

/**
 * Modul M7 — titik masuk tunggal pengiriman notifikasi.
 *
 * Menjamin preferensi kanal per pengguna termuat sebelum notifikasi dikirim
 * (FR-M7-04) dan pengguna nonaktif tidak ikut dikirimi.
 */
class NotificationDispatcher
{
    public function send(?User $user, UscVehicleOpsNotification $notification): void
    {
        if ($user === null || ! $user->is_active) {
            return;
        }

        $user->loadMissing('notificationPreferences');

        NotificationFacade::send([$user], $notification);
    }

    /** @param  iterable<int, User>  $users */
    public function sendMany(iterable $users, UscVehicleOpsNotification $notification): void
    {
        $recipients = Collection::make($users)
            ->filter(fn (User $user) => $user->is_active)
            ->unique(fn (User $user) => $user->getKey());

        if ($recipients->isEmpty()) {
            return;
        }

        $recipients->each->loadMissing('notificationPreferences');

        NotificationFacade::send($recipients, $notification);
    }

    /**
     * FR-M2-08 & FR-M2-17 — kirim ke antrean approval Admin GA
     * beserta Admin Sistem sebagai backup approver.
     */
    public function sendToApprovers(UscVehicleOpsNotification $notification): void
    {
        $this->sendMany($this->approvers(), $notification);
    }

    /** @return Collection<int, User> */
    public function approvers(): Collection
    {
        return User::query()
            ->with('notificationPreferences')
            ->approvers()
            ->get();
    }

    /**
     * Kirim ke seluruh pengguna dengan peran tertentu.
     *
     * @param  array<int, string>|string  $roles
     */
    public function sendToRoles(array|string $roles, UscVehicleOpsNotification $notification): void
    {
        $users = User::query()
            ->with('notificationPreferences')
            ->active()
            ->role($roles)
            ->get();

        $this->sendMany($users, $notification);
    }
}
