<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Media;
use App\Models\User;

class MediaPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-any media') || $user->hasRole(['admin', 'editor', 'author']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Media $media): bool
    {
        // Users can view their own media
        if ($media->user_id === $user->id) {
            return true;
        }

        // Admins and editors can view all media
        if ($user->hasRole(['admin', 'editor'])) {
            return true;
        }

        return $user->can('view media');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create media') || $user->hasRole(['admin', 'editor', 'author']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Media $media): bool
    {
        // Users can update their own media
        if ($media->user_id === $user->id) {
            return true;
        }

        // Admins and editors can update any media
        if ($user->hasRole(['admin', 'editor'])) {
            return true;
        }

        return $user->can('update media');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Media $media): bool
    {
        // Users can delete their own media
        if ($media->user_id === $user->id) {
            return $user->can('delete media') || $user->hasRole(['admin', 'editor', 'author']);
        }

        // Admins can delete any media
        if ($user->hasRole('admin')) {
            return true;
        }

        // Editors can delete any media
        if ($user->hasRole('editor')) {
            return $user->can('delete media');
        }

        return $user->can('delete media');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Media $media): bool
    {
        return $user->hasRole(['admin', 'editor']) || $user->can('restore media');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Media $media): bool
    {
        return $user->hasRole('admin') || $user->can('force-delete media');
    }
}
