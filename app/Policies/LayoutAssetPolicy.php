<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\LayoutAsset;
use App\Models\User;

class LayoutAssetPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin', 'editor']) || $user->can('view layout assets');
    }

    public function view(User $user, LayoutAsset $layoutAsset): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['admin', 'editor']) || $user->can('create layout assets');
    }

    public function update(User $user, LayoutAsset $layoutAsset): bool
    {
        if ($layoutAsset->is_system) {
            return false;
        }

        return $user->hasRole(['admin', 'editor']) || $user->can('update layout assets');
    }

    public function delete(User $user, LayoutAsset $layoutAsset): bool
    {
        if ($layoutAsset->is_system) {
            return false;
        }

        return $user->hasRole(['admin']) || $user->can('delete layout assets');
    }
}
