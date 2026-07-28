<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Media;
use App\Models\User;
use App\Policies\Concerns\UsesAuthorizationHooks;

class MediaPolicy
{
    use UsesAuthorizationHooks;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $this->authorizeWithHooks($user, 'media.viewAny', $user->can('view-any media') || $user->hasRole(['admin', 'editor', 'author']), Media::class);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Media $media): bool
    {
        $allowed = false;

        // Users can view their own media
        if ($media->user_id === $user->id) {
            $allowed = true;
        } elseif ($user->hasRole(['admin', 'editor'])) {
            $allowed = true;
        } else {
            $allowed = $user->can('view media');
        }

        return $this->authorizeWithHooks($user, 'media.view', $allowed, $media);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $this->authorizeWithHooks($user, 'media.create', $user->can('create media') || $user->hasRole(['admin', 'editor', 'author']), Media::class);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Media $media): bool
    {
        $allowed = false;

        // Users can update their own media
        if ($media->user_id === $user->id) {
            $allowed = true;
        } elseif ($user->hasRole(['admin', 'editor'])) {
            $allowed = true;
        } else {
            $allowed = $user->can('update media');
        }

        return $this->authorizeWithHooks($user, 'media.update', $allowed, $media);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Media $media): bool
    {
        $allowed = false;

        // Users can delete their own media
        if ($media->user_id === $user->id) {
            $allowed = $user->can('delete media') || $user->hasRole(['admin', 'editor', 'author']);
        } elseif ($user->hasRole('admin')) {
            $allowed = true;
        } elseif ($user->hasRole('editor')) {
            $allowed = $user->can('delete media');
        } else {
            $allowed = $user->can('delete media');
        }

        return $this->authorizeWithHooks($user, 'media.delete', $allowed, $media);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Media $media): bool
    {
        return $this->authorizeWithHooks($user, 'media.restore', $user->hasRole(['admin', 'editor']) || $user->can('restore media'), $media);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Media $media): bool
    {
        return $this->authorizeWithHooks($user, 'media.forceDelete', $user->hasRole('admin') || $user->can('force-delete media'), $media);
    }
}
