<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin') || $user->can('view users');
    }

    public function view(User $user, User $model): bool
    {
        if ($user->hasRole('admin') || $user->can('view users')) {
            return true;
        }

        return $user->id === $model->id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin') || $user->can('create users');
    }

    public function update(User $user, User $model): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->can('update users');
    }

    public function delete(User $user, User $model): bool
    {
        if ($model->id === $user->id) {
            return false;
        }

        if ($model->hasRole('admin') && !$user->hasRole('admin')) {
            return false;
        }

        return $user->hasRole('admin') || $user->can('delete users');
    }

    public function manageRoles(User $user): bool
    {
        return $user->hasRole('admin') || $user->can('manage roles');
    }
}

