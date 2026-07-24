<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Concerns;

use App\Facades\Hook;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;

trait AuthorizesPermissions
{
    /**
     * @param string|array<int, string> $permissions
     */
    protected function authorizePermission(Request $request, string|array $permissions, bool $requireAll = false): void
    {
        $user = $request->user();

        if (!$user || !$this->userCan($user, $permissions, $requireAll)) {
            throw new AuthorizationException('This action is unauthorized.');
        }
    }

    /**
     * @param string|array<int, string> $permissions
     */
    protected function userCan(Authenticatable $user, string|array $permissions, bool $requireAll = false): bool
    {
        $permissions = is_array($permissions) ? $permissions : [$permissions];

        if ($permissions === []) {
            return true;
        }

        if (
            method_exists($user, 'hasAnyRole')
            && $user->hasAnyRole(['super-admin', 'admin'])
        ) {
            return (bool) Hook::applyFilters('auth.permission.can', true, $user, $permissions, $requireAll);
        }

        $result = $requireAll;

        foreach ($permissions as $permission) {
            $allowed = method_exists($user, 'can') && $user->can($permission);

            if ($allowed && !$requireAll) {
                $result = true;
                break;
            }

            if (!$allowed && $requireAll) {
                $result = false;
                break;
            }
        }

        return (bool) Hook::applyFilters('auth.permission.can', $result, $user, $permissions, $requireAll);
    }
}
