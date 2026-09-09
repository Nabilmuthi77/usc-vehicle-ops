<?php

namespace App\Policies;

use App\Models\ReimbursementBatch;
use App\Models\User;
use App\Support\Permissions;

/** FR-M3-10 s.d. FR-M3-12 — pengelolaan batch reimbursement. */
class ReimbursementBatchPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission([
            Permissions::REIMBURSEMENT_MANAGE,
            Permissions::REIMBURSEMENT_PAY,
            Permissions::FUEL_CLAIM_CREATE,
        ]);
    }

    public function view(User $user, ReimbursementBatch $batch): bool
    {
        return $user->hasAnyPermission([
            Permissions::REIMBURSEMENT_MANAGE,
            Permissions::REIMBURSEMENT_PAY,
        ]) || $batch->claimant_id === $user->getKey();
    }

    public function create(User $user): bool
    {
        return $user->can(Permissions::REIMBURSEMENT_MANAGE);
    }

    public function update(User $user, ReimbursementBatch $batch): bool
    {
        return $user->can(Permissions::REIMBURSEMENT_MANAGE) && $batch->isOpen();
    }

    /** FR-M3-12 — penandaan pembayaran oleh Finance (Viewer) atau Admin GA. */
    public function markAsPaid(User $user, ReimbursementBatch $batch): bool
    {
        return $user->can(Permissions::REIMBURSEMENT_PAY)
            && $batch->status === \App\Enums\ReimbursementBatchStatus::DiserahkanFinance;
    }

    public function delete(User $user, ReimbursementBatch $batch): bool
    {
        return $user->can(Permissions::REIMBURSEMENT_MANAGE) && $batch->isOpen();
    }
}
