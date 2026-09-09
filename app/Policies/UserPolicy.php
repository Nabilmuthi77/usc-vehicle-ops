<?php

namespace App\Policies;

use App\Models\User;
use App\Support\Permissions;

/** PRD §5.2 — manajemen user & peran hanya oleh Admin Sistem. */
class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(Permissions::USER_MANAGE);
    }

    public function view(User $user, User $target): bool
    {
        return $user->can(Permissions::USER_MANAGE) || $user->is($target);
    }

    public function create(User $user): bool
    {
        return $user->can(Permissions::USER_MANAGE);
    }

    public function update(User $user, User $target): bool
    {
        return $user->can(Permissions::USER_MANAGE);
    }

    public function delete(User $user, User $target): bool
    {
        // Akun sendiri tidak boleh dihapus agar tidak mengunci diri sendiri.
        return $user->can(Permissions::USER_MANAGE) && ! $user->is($target);
    }
}
