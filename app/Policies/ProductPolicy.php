<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use App\Policies\Concerns\UsesAuthorizationHooks;

class ProductPolicy
{
    use UsesAuthorizationHooks;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Published products are public
        return $this->authorizeWithHooks($user, 'product.viewAny', true, Product::class);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Product $product): bool
    {
        // Published products are public
        $allowed = $product->status === 'published'
            || $user->can('view product')
            || $user->hasRole(['admin', 'editor'])
            || ($user->hasRole('author') && $product->user_id === $user->id);

        return $this->authorizeWithHooks($user, 'product.view', $allowed, $product);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $this->authorizeWithHooks($user, 'product.create', $user->can('create product') || $user->hasRole(['admin', 'editor', 'author']), Product::class);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Product $product): bool
    {
        $allowed = false;

        // Admins and editors can update any product
        if ($user->hasRole(['admin', 'editor'])) {
            $allowed = true;
        } elseif ($user->hasRole('author')) {
            $allowed = $product->user_id === $user->id;
        } else {
            $allowed = $user->can('update product');
        }

        return $this->authorizeWithHooks($user, 'product.update', $allowed, $product);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Product $product): bool
    {
        $allowed = false;

        // Admins can delete any product
        if ($user->hasRole('admin')) {
            $allowed = true;
        } elseif ($user->hasRole('editor')) {
            $allowed = $user->can('delete product');
        } elseif ($user->hasRole('author')) {
            $allowed = $product->user_id === $user->id && $user->can('delete product');
        } else {
            $allowed = $user->can('delete product');
        }

        return $this->authorizeWithHooks($user, 'product.delete', $allowed, $product);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Product $product): bool
    {
        return $this->authorizeWithHooks($user, 'product.restore', $user->hasRole(['admin', 'editor']) || $user->can('restore product'), $product);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Product $product): bool
    {
        return $this->authorizeWithHooks($user, 'product.forceDelete', $user->hasRole('admin') || $user->can('force-delete product'), $product);
    }
}
