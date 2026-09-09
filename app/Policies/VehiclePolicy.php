<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vehicle;
use App\Support\Permissions;

/** PRD §5.2 — master data kendaraan: Admin Sistem CRUD, Admin GA R/U. */
class VehiclePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(Permissions::VEHICLE_VIEW);
    }

    public function view(User $user, Vehicle $vehicle): bool
    {
        return $user->can(Permissions::VEHICLE_VIEW);
    }

    public function create(User $user): bool
    {
        return $user->can(Permissions::VEHICLE_CREATE);
    }

    public function update(User $user, Vehicle $vehicle): bool
    {
        return $user->can(Permissions::VEHICLE_UPDATE);
    }

    public function delete(User $user, Vehicle $vehicle): bool
    {
        return $user->can(Permissions::VEHICLE_DELETE);
    }

    /** BR-03 — koreksi odometer mundur hanya oleh Admin. */
    public function correctOdometer(User $user, Vehicle $vehicle): bool
    {
        return $user->can(Permissions::VEHICLE_UPDATE);
    }
}
