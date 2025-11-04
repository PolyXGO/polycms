<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Anyone can view published posts, but all posts require permission
        return $user->can('view-any post') || $user->hasRole(['admin', 'editor', 'author']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Post $post): bool
    {
        // Published posts are public
        if ($post->status === 'published') {
            return true;
        }

        // Draft/archived posts require permission
        return $user->can('view post') || 
               $user->hasRole(['admin', 'editor']) ||
               ($user->hasRole('author') && $post->user_id === $user->id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create post') || $user->hasRole(['admin', 'editor', 'author']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Post $post): bool
    {
        // Admins and editors can update any post
        if ($user->hasRole(['admin', 'editor'])) {
            return true;
        }

        // Authors can only update their own posts
        if ($user->hasRole('author')) {
            return $post->user_id === $user->id;
        }

        return $user->can('update post');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Post $post): bool
    {
        // Admins can delete any post
        if ($user->hasRole('admin')) {
            return true;
        }

        // Editors can delete any post except their own (or with permission)
        if ($user->hasRole('editor')) {
            return $user->can('delete post') || $post->user_id !== $user->id;
        }

        // Authors can only delete their own posts
        if ($user->hasRole('author')) {
            return $post->user_id === $user->id && $user->can('delete post');
        }

        return $user->can('delete post');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Post $post): bool
    {
        // Only admins and editors can restore
        return $user->hasRole(['admin', 'editor']) || $user->can('restore post');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Post $post): bool
    {
        // Only admins can permanently delete
        return $user->hasRole('admin') || $user->can('force-delete post');
    }
}
