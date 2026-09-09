<?php

namespace App\Policies;

use App\Models\ServiceRecord;
use App\Models\User;
use App\Support\Permissions;

/** PRD §5.2 — servis: Admin GA kelola, Viewer read-only. */
class ServiceRecordPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(Permissions::SERVICE_VIEW);
    }

    public function view(User $user, ServiceRecord $record): bool
    {
        return $user->can(Permissions::SERVICE_VIEW);
    }

    public function create(User $user): bool
    {
        return $user->can(Permissions::SERVICE_MANAGE);
    }

    public function update(User $user, ServiceRecord $record): bool
    {
        return $user->can(Permissions::SERVICE_MANAGE);
    }

    public function delete(User $user, ServiceRecord $record): bool
    {
        return $user->can(Permissions::SERVICE_MANAGE);
    }

    /**
     * BR-16 — mengubah penanggung biaya kendaraan sewa dari vendor ke
     * perusahaan hanya boleh Admin dan wajib beralasan.
     */
    public function overrideCostBorneBy(User $user, ServiceRecord $record): bool
    {
        return $user->can(Permissions::SETTING_MANAGE);
    }
}
