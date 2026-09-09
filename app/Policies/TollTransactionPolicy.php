<?php

namespace App\Policies;

use App\Models\TollTransaction;
use App\Models\User;
use App\Support\Permissions;

/** PRD §5.2 — input transaksi tol oleh Admin GA & Driver. */
class TollTransactionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(Permissions::TOLL_VIEW);
    }

    public function view(User $user, TollTransaction $transaction): bool
    {
        return $user->can(Permissions::TOLL_VIEW);
    }

    public function create(User $user): bool
    {
        return $user->can(Permissions::TOLL_MANAGE);
    }

    public function update(User $user, TollTransaction $transaction): bool
    {
        return $user->can(Permissions::TOLL_MANAGE);
    }

    public function delete(User $user, TollTransaction $transaction): bool
    {
        return $user->can(Permissions::TOLL_MANAGE);
    }
}
