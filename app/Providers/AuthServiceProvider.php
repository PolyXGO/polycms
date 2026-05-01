<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\LayoutAsset;
use App\Models\Role;
use App\Models\User;
use App\Policies\LayoutAssetPolicy;
use App\Policies\RolePolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        LayoutAsset::class => LayoutAssetPolicy::class,
        Role::class => RolePolicy::class,
        User::class => UserPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
