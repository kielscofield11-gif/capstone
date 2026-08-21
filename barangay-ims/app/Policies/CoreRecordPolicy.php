<?php

namespace App\Policies;

use App\Models\User;

abstract class CoreRecordPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(User::ROLES);
    }

    public function view(User $user, mixed $model): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(User::CORE_CREATORS);
    }

    public function update(User $user, mixed $model): bool
    {
        return $user->hasAnyRole(User::CORE_EDITORS);
    }

    public function delete(User $user, mixed $model): bool
    {
        return $user->isAdmin();
    }
}
