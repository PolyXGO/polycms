<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Tag;
use App\Models\User;
use App\Policies\Concerns\UsesAuthorizationHooks;

class TagPolicy
{
    use UsesAuthorizationHooks;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Tags are public
        return $this->authorizeWithHooks($user, 'tag.viewAny', true, Tag::class);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Tag $tag): bool
    {
        // Tags are public
        return $this->authorizeWithHooks($user, 'tag.view', true, $tag);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $this->authorizeWithHooks($user, 'tag.create', $user->can('create tag') || $user->hasRole(['admin', 'editor', 'author']), Tag::class);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Tag $tag): bool
    {
        return $this->authorizeWithHooks($user, 'tag.update', $user->can('update tag') || $user->hasRole(['admin', 'editor']), $tag);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Tag $tag): bool
    {
        return $this->authorizeWithHooks($user, 'tag.delete', $user->can('delete tag') || $user->hasRole(['admin', 'editor']), $tag);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Tag $tag): bool
    {
        return $this->authorizeWithHooks($user, 'tag.restore', $user->hasRole(['admin', 'editor']) || $user->can('restore tag'), $tag);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Tag $tag): bool
    {
        return $this->authorizeWithHooks($user, 'tag.forceDelete', $user->hasRole('admin') || $user->can('force-delete tag'), $tag);
    }
}
