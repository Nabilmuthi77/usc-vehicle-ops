<?php

namespace App\Policies;

use App\Models\ServiceRequest;
use App\Models\User;
use App\Support\Permissions;

/** FR-M4-15 s.d. FR-M4-20 — permintaan servis ke vendor. */
class ServiceRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(Permissions::SERVICE_VIEW);
    }

    public function view(User $user, ServiceRequest $request): bool
    {
        return $user->can(Permissions::SERVICE_VIEW);
    }

    public function create(User $user): bool
    {
        return $user->can(Permissions::SERVICE_REQUEST_MANAGE);
    }

    public function update(User $user, ServiceRequest $request): bool
    {
        return $user->can(Permissions::SERVICE_REQUEST_MANAGE) && ! $request->status->isClosed();
    }

    public function send(User $user, ServiceRequest $request): bool
    {
        return $user->can(Permissions::SERVICE_REQUEST_MANAGE)
            && $request->canTransitionTo(\App\Enums\ServiceRequestStatus::Dikirim);
    }

    public function complete(User $user, ServiceRequest $request): bool
    {
        return $user->can(Permissions::SERVICE_REQUEST_MANAGE)
            && $request->canTransitionTo(\App\Enums\ServiceRequestStatus::Selesai);
    }

    public function delete(User $user, ServiceRequest $request): bool
    {
        return $user->can(Permissions::SERVICE_REQUEST_MANAGE);
    }
}
