<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Policies\Concerns\UsesAuthorizationHooks;

class UserPolicy
{
    use UsesAuthorizationHooks;

    public function viewAny(User $user): bool
    {
        return $this->authorizeWithHooks($user, 'user.viewAny', $user->hasRole('admin') || $user->can('view users'), User::class);
    }

    public function view(User $user, User $model): bool
    {
        $allowed = false;

        if ($user->hasRole('admin') || $user->can('view users')) {
            $allowed = true;
        } else {
            $allowed = $user->id === $model->id;
        }

        return $this->authorizeWithHooks($user, 'user.view', $allowed, $model);
    }

    public function create(User $user): bool
    {
        return $this->authorizeWithHooks($user, 'user.create', $user->hasRole('admin') || $user->can('create users'), User::class);
    }

    public function update(User $user, User $model): bool
    {
        $allowed = false;

        if ($user->hasRole('admin')) {
            $allowed = true;
        } else {
            $allowed = $user->can('update users');
        }

        return $this->authorizeWithHooks($user, 'user.update', $allowed, $model);
    }

    public function delete(User $user, User $model): bool
    {
        if ($model->id === $user->id) {
            return false;
        }

        if ($model->hasRole('admin') && !$user->hasRole('admin')) {
            return false;
        }

        return $this->authorizeWithHooks($user, 'user.delete', $user->hasRole('admin') || $user->can('delete users'), $model);
    }

    public function manageRoles(User $user): bool
    {
        return $this->authorizeWithHooks($user, 'user.manageRoles', $user->hasRole('admin') || $user->can('manage roles'), User::class);
    }
}
