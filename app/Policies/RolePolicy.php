<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use App\Policies\Concerns\UsesAuthorizationHooks;

class RolePolicy
{
    use UsesAuthorizationHooks;

    protected function canManage(User $user): bool
    {
        return $user->hasRole('admin') || $user->can('manage roles');
    }

    public function viewAny(User $user): bool
    {
        return $this->authorizeWithHooks($user, 'role.viewAny', $this->canManage($user), Role::class);
    }

    public function view(User $user, Role $role): bool
    {
        return $this->authorizeWithHooks($user, 'role.view', $this->canManage($user), $role);
    }

    public function create(User $user): bool
    {
        return $this->authorizeWithHooks($user, 'role.create', $this->canManage($user), Role::class);
    }

    public function update(User $user, Role $role): bool
    {
        if ($role->is_system) {
            return false;
        }

        return $this->authorizeWithHooks($user, 'role.update', $this->canManage($user), $role);
    }

    public function delete(User $user, Role $role): bool
    {
        if ($role->is_system) {
            return false;
        }

        if ($role->users()->exists()) {
            return false;
        }

        return $this->authorizeWithHooks($user, 'role.delete', $this->canManage($user), $role);
    }

    public function clone(User $user, Role $role): bool
    {
        return $this->authorizeWithHooks($user, 'role.clone', $this->canManage($user), $role);
    }
}
