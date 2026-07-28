<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Category;
use App\Models\User;
use App\Policies\Concerns\UsesAuthorizationHooks;

class CategoryPolicy
{
    use UsesAuthorizationHooks;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Categories are public
        return $this->authorizeWithHooks($user, 'category.viewAny', true, Category::class);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Category $category): bool
    {
        // Categories are public
        return $this->authorizeWithHooks($user, 'category.view', true, $category);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $this->authorizeWithHooks($user, 'category.create', $user->can('create category') || $user->hasRole(['admin', 'editor']), Category::class);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Category $category): bool
    {
        return $this->authorizeWithHooks($user, 'category.update', $user->can('update category') || $user->hasRole(['admin', 'editor']), $category);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Category $category): bool
    {
        return $this->authorizeWithHooks($user, 'category.delete', $user->can('delete category') || $user->hasRole('admin'), $category);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Category $category): bool
    {
        return $this->authorizeWithHooks($user, 'category.restore', $user->hasRole(['admin', 'editor']) || $user->can('restore category'), $category);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Category $category): bool
    {
        return $this->authorizeWithHooks($user, 'category.forceDelete', $user->hasRole('admin') || $user->can('force-delete category'), $category);
    }
}
