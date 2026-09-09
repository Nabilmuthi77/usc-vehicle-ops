<?php

namespace App\Policies;

use App\Models\FuelTransaction;
use App\Models\User;
use App\Support\Permissions;

/** PRD §5.2 — otorisasi klaim BBM (FR-M3-13 "Klaim Saya"). */
class FuelTransactionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission([
            Permissions::FUEL_VIEW_ALL,
            Permissions::FUEL_CLAIM_CREATE,
        ]);
    }

    public function view(User $user, FuelTransaction $transaction): bool
    {
        return $user->can(Permissions::FUEL_VIEW_ALL)
            || $transaction->claimant_id === $user->getKey();
    }

    public function create(User $user): bool
    {
        return $user->can(Permissions::FUEL_CLAIM_CREATE);
    }

    /** Klaim hanya dapat disunting pengajunya selagi draft atau ditolak. */
    public function update(User $user, FuelTransaction $transaction): bool
    {
        if (! $transaction->isEditableByClaimant()) {
            return $user->can(Permissions::FUEL_CLAIM_VERIFY);
        }

        return $transaction->claimant_id === $user->getKey()
            || $user->can(Permissions::FUEL_CLAIM_VERIFY);
    }

    public function submitClaim(User $user, FuelTransaction $transaction): bool
    {
        return $transaction->isEditableByClaimant()
            && ($transaction->claimant_id === $user->getKey()
                || $user->can(Permissions::FUEL_CLAIM_VERIFY));
    }

    /** FR-M3-09 — verifikasi hanya oleh Admin GA. */
    public function verify(User $user, FuelTransaction $transaction): bool
    {
        return $user->can(Permissions::FUEL_CLAIM_VERIFY) && $transaction->isVerifiable();
    }

    public function delete(User $user, FuelTransaction $transaction): bool
    {
        if ($user->can(Permissions::FUEL_CLAIM_VERIFY)) {
            return true;
        }

        return $transaction->claimant_id === $user->getKey()
            && $transaction->isEditableByClaimant();
    }
}
