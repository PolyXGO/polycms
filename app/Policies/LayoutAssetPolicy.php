<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\LayoutAsset;
use App\Models\User;
use App\Policies\Concerns\UsesAuthorizationHooks;

class LayoutAssetPolicy
{
    use UsesAuthorizationHooks;

    public function viewAny(User $user): bool
    {
        return $this->authorizeWithHooks($user, 'layoutAsset.viewAny', $user->hasRole(['admin', 'editor']) || $user->can('view layout assets'), LayoutAsset::class);
    }

    public function view(User $user, LayoutAsset $layoutAsset): bool
    {
        return $this->authorizeWithHooks($user, 'layoutAsset.view', $this->viewAny($user), $layoutAsset);
    }

    public function create(User $user): bool
    {
        return $this->authorizeWithHooks($user, 'layoutAsset.create', $user->hasRole(['admin', 'editor']) || $user->can('create layout assets'), LayoutAsset::class);
    }

    public function update(User $user, LayoutAsset $layoutAsset): bool
    {
        if ($layoutAsset->is_system) {
            return false;
        }

        return $this->authorizeWithHooks($user, 'layoutAsset.update', $user->hasRole(['admin', 'editor']) || $user->can('update layout assets'), $layoutAsset);
    }

    public function delete(User $user, LayoutAsset $layoutAsset): bool
    {
        if ($layoutAsset->is_system) {
            return false;
        }

        return $this->authorizeWithHooks($user, 'layoutAsset.delete', $user->hasRole(['admin']) || $user->can('delete layout assets'), $layoutAsset);
    }
}
