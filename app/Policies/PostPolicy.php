<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Post;
use App\Models\User;
use App\Policies\Concerns\UsesAuthorizationHooks;

class PostPolicy
{
    use UsesAuthorizationHooks;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Anyone can view published posts, but all posts require permission
        return $this->authorizeWithHooks($user, 'post.viewAny', $user->can('view-any post') || $user->hasRole(['admin', 'editor', 'author']), Post::class);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Post $post): bool
    {
        // Published posts are public
        $allowed = $post->status === 'published'
            || $user->can('view post')
            || $user->hasRole(['admin', 'editor'])
            || ($user->hasRole('author') && $post->user_id === $user->id);

        return $this->authorizeWithHooks($user, 'post.view', $allowed, $post);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $this->authorizeWithHooks($user, 'post.create', $user->can('create post') || $user->hasRole(['admin', 'editor', 'author']), Post::class);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Post $post): bool
    {
        $allowed = false;

        // Admins and editors can update any post
        if ($user->hasRole(['admin', 'editor'])) {
            $allowed = true;
        } elseif ($user->hasRole('author')) {
            $allowed = $post->user_id === $user->id;
        } else {
            $allowed = $user->can('update post');
        }

        return $this->authorizeWithHooks($user, 'post.update', $allowed, $post);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Post $post): bool
    {
        $allowed = false;

        // Admins can delete any post
        if ($user->hasRole('admin')) {
            $allowed = true;
        } elseif ($user->hasRole('editor')) {
            $allowed = $user->can('delete post') || $post->user_id !== $user->id;
        } elseif ($user->hasRole('author')) {
            $allowed = $post->user_id === $user->id && $user->can('delete post');
        } else {
            $allowed = $user->can('delete post');
        }

        return $this->authorizeWithHooks($user, 'post.delete', $allowed, $post);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Post $post): bool
    {
        // Only admins and editors can restore
        return $this->authorizeWithHooks($user, 'post.restore', $user->hasRole(['admin', 'editor']) || $user->can('restore post'), $post);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Post $post): bool
    {
        // Only admins can permanently delete
        return $this->authorizeWithHooks($user, 'post.forceDelete', $user->hasRole('admin') || $user->can('force-delete post'), $post);
    }
}
