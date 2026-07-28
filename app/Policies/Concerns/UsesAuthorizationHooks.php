<?php

declare(strict_types=1);

namespace App\Policies\Concerns;

use App\Facades\Hook;
use App\Models\User;

trait UsesAuthorizationHooks
{
    /**
     * Let modules/themes adjust policy decisions without replacing core policies.
     *
     * The generic filter runs first, then a specific filter such as
     * `auth.policy.post.update` receives the already-filtered result.
     */
    protected function authorizeWithHooks(
        User $user,
        string $ability,
        bool $allowed,
        mixed $subject = null,
        array $context = []
    ): bool {
        $result = Hook::applyFilters('auth.policy.can', $allowed, $user, $ability, $subject, $context);
        $result = Hook::applyFilters("auth.policy.{$ability}", $result, $user, $subject, $context);

        return (bool) $result;
    }
}
