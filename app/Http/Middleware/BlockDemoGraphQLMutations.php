<?php

namespace App\Http\Middleware;

use App\Services\SettingsService;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class BlockDemoGraphQLMutations
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (!$this->isMutationRequest($request) || !$this->isRestrictedDemoUser($request)) {
            return $next($request);
        }

        return new JsonResponse($this->restrictionPayload($request), 403);
    }

    private function isMutationRequest(Request $request): bool
    {
        foreach ($this->graphQLPayloads($request) as $payload) {
            $query = (string) ($payload['query'] ?? '');
            $operationName = (string) ($payload['operationName'] ?? '');

            if (preg_match('/\bmutation\b/i', $query)) {
                return true;
            }

            if ($operationName !== '' && preg_match('/^(create|update|delete|revoke|regenerate|sync|attach|detach)/i', $operationName)) {
                return true;
            }
        }

        return false;
    }

    private function graphQLPayloads(Request $request): array
    {
        $json = $request->json()->all();

        if (is_array($json) && array_is_list($json)) {
            return array_values(array_filter($json, static fn ($payload) => is_array($payload)));
        }

        if (is_array($json) && (array_key_exists('query', $json) || array_key_exists('operationName', $json))) {
            return [$json];
        }

        return [[
            'query' => $request->input('query', ''),
            'operationName' => $request->input('operationName', ''),
        ]];
    }

    private function isRestrictedDemoUser(Request $request): bool
    {
        $user = $request->user();
        if (!$user) {
            return false;
        }

        try {
            if (!Schema::hasTable('demo_builder_accounts')) {
                return false;
            }

            $settings = app(SettingsService::class);
            if (!(bool) $settings->get('demo_builder_restrictions_enabled', true)) {
                return false;
            }

            if ($this->isAllowedAdmin($user, $settings)) {
                return false;
            }

            return \Illuminate\Support\Facades\DB::table('demo_builder_accounts')
                ->where('user_id', $user->id)
                ->exists();
        } catch (\Throwable) {
            return false;
        }
    }

    private function isAllowedAdmin($user, SettingsService $settings): bool
    {
        if ((int) $user->id === 1) {
            return true;
        }

        $allowed = $settings->get('demo_builder_allowed_admins', []);
        if (is_string($allowed)) {
            $allowed = json_decode($allowed, true) ?? [];
        }

        if (!is_array($allowed)) {
            return false;
        }

        return in_array((string) $user->id, $allowed, true)
            || in_array($user->email, $allowed, true);
    }

    private function restrictionPayload(Request $request): array
    {
        if (class_exists(\Modules\Polyx\DemoBuilder\Services\DemoRestrictionContent::class)) {
            try {
                $payload = array_merge(
                    ['success' => false, 'is_demo_restriction' => true],
                    app(\Modules\Polyx\DemoBuilder\Services\DemoRestrictionContent::class)
                        ->payload($request, $request->user())
                );

                return $this->graphQLRestrictionPayload($payload);
            } catch (\Throwable) {
                // Fall through to static fallback.
            }
        }

        return $this->graphQLRestrictionPayload([
            'success' => false,
            'is_demo_restriction' => true,
            'title' => 'Demo Actions Restricted',
            'message' => 'Create, update, and delete actions are restricted in this demo environment.',
            'confirm_text' => 'Close',
        ]);
    }

    private function graphQLRestrictionPayload(array $payload): array
    {
        return array_merge($payload, [
            'data' => null,
            'errors' => [
                [
                    'message' => $payload['title'] ?? 'Demo Actions Restricted',
                    'extensions' => [
                        'category' => 'demo_restriction',
                        'is_demo_restriction' => true,
                        'restriction' => $payload,
                    ],
                ],
            ],
        ]);
    }
}
