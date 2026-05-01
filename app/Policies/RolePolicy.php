<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

class RolePolicy
{
    protected function canManage(User $user): bool
    {
        return $user->hasRole('admin') || $user->can('manage roles');
    }

    public function viewAny(User $user): bool
    {
        return $this->canManage($user);
    }

    public function view(User $user, Role $role): bool
    {
        return $this->canManage($user);
    }

    public function create(User $user): bool
    {
        return $this->canManage($user);
    }

    public function update(User $user, Role $role): bool
    {
        if ($role->is_system) {
            return false;
        }

        return $this->canManage($user);
    }

    public function delete(User $user, Role $role): bool
    {
        if ($role->is_system) {
            return false;
        }

        if ($role->users()->exists()) {
            return false;
        }

        return $this->canManage($user);
    }

    public function clone(User $user, Role $role): bool
    {
        return $this->canManage($user);
    }
}

