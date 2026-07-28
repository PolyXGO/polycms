<?php

declare(strict_types=1);

namespace App\Providers;

use App\Facades\Hook;
use App\Models\Category;
use App\Models\LayoutAsset;
use App\Models\Media;
use App\Models\Post;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use App\Policies\CategoryPolicy;
use App\Policies\LayoutAssetPolicy;
use App\Policies\MediaPolicy;
use App\Policies\PostPolicy;
use App\Policies\ProductPolicy;
use App\Policies\RolePolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Category::class => CategoryPolicy::class,
        LayoutAsset::class => LayoutAssetPolicy::class,
        Media::class => MediaPolicy::class,
        Post::class => PostPolicy::class,
        Product::class => ProductPolicy::class,
        Role::class => RolePolicy::class,
        User::class => UserPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        Hook::doAction('auth.register_policies', Gate::getFacadeRoot());

        Gate::before(function (User $user, string $ability, array $arguments = []) {
            $filtered = Hook::applyFilters('auth.gate.before', null, $user, $ability, $arguments);
            if ($filtered !== null) {
                return (bool) $filtered;
            }

            if (!$user->hasRole('super-admin')) {
                return null;
            }

            if ($this->hasHardPolicyDeny($user, $ability, $arguments)) {
                return null;
            }

            return true;
        });

        Gate::after(function (User $user, string $ability, ?bool $result, array $arguments = []) {
            return Hook::applyFilters('auth.can', $result, $user, $ability, $arguments);
        });
    }

    protected function hasHardPolicyDeny(User $user, string $ability, array $arguments): bool
    {
        $subject = $arguments[0] ?? null;

        if ($subject instanceof Role && in_array($ability, ['update', 'delete'], true)) {
            if ($subject->is_system) {
                return true;
            }

            if ($ability === 'delete' && $subject->users()->exists()) {
                return true;
            }
        }

        if ($subject instanceof LayoutAsset && in_array($ability, ['update', 'delete'], true)) {
            return (bool) $subject->is_system;
        }

        if ($subject instanceof User && $ability === 'delete') {
            return (int) $subject->id === (int) $user->id;
        }

        $denied = Hook::applyFilters('auth.gate.hard_denied', false, $user, $ability, $arguments);

        return (bool) $denied;
    }
}
