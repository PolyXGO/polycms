<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Published products are public
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Product $product): bool
    {
        // Published products are public
        if ($product->status === 'published') {
            return true;
        }

        // Draft/archived products require permission
        return $user->can('view product') ||
               $user->hasRole(['admin', 'editor']) ||
               ($user->hasRole('author') && $product->user_id === $user->id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create product') || $user->hasRole(['admin', 'editor', 'author']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Product $product): bool
    {
        // Admins and editors can update any product
        if ($user->hasRole(['admin', 'editor'])) {
            return true;
        }

        // Authors can only update their own products
        if ($user->hasRole('author')) {
            return $product->user_id === $user->id;
        }

        return $user->can('update product');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Product $product): bool
    {
        // Admins can delete any product
        if ($user->hasRole('admin')) {
            return true;
        }

        // Editors can delete products
        if ($user->hasRole('editor')) {
            return $user->can('delete product');
        }

        // Authors can only delete their own products
        if ($user->hasRole('author')) {
            return $product->user_id === $user->id && $user->can('delete product');
        }

        return $user->can('delete product');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Product $product): bool
    {
        return $user->hasRole(['admin', 'editor']) || $user->can('restore product');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Product $product): bool
    {
        return $user->hasRole('admin') || $user->can('force-delete product');
    }
}
