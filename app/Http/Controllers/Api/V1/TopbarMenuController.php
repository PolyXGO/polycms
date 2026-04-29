<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\TopbarMenuService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class TopbarMenuController extends Controller
{
    /**
     * Get topbar menu items
     */
    public function index(Request $request): JsonResponse
    {
        $topbarService = app(TopbarMenuService::class);

        // Get route info from request
        $routeName = $request->get('route');
        $routeParams = $request->get('route_params');

        // If route params are provided as JSON string, decode them
        if (is_string($routeParams)) {
            $routeParams = json_decode($routeParams, true) ?? [];
        }

        // Ensure route_params is an array
        if (!is_array($routeParams)) {
            $routeParams = [];
        }

        // Set route params if provided
        if ($routeName) {
            $request->merge(['route' => $routeName]);
        }
        if (!empty($routeParams)) {
            $request->merge(['route_params' => $routeParams]);
        }

        $normalizedRouteParams = $this->normalizeRecursive($routeParams);
        $userId = $this->resolveUserId($request);
        $locale = app()->getLocale();
        $routeHash = md5((string) ($routeName ?? ''));
        $paramsHash = md5((string) json_encode($normalizedRouteParams));
        $cacheKey = sprintf(
            'polycms.topbar_menu.v1.user.%d.locale.%s.route.%s.params.%s',
            $userId,
            $locale,
            $routeHash,
            $paramsHash
        );

        if ($request->boolean('refresh')) {
            Cache::forget($cacheKey);
        }

        $menuItems = Cache::remember($cacheKey, now()->addSeconds(30), fn (): array => $topbarService->getMenuItems($request));

        return response()->json([
            'data' => $menuItems,
        ]);
    }

    /**
     * @param array<mixed> $value
     * @return array<mixed>
     */
    protected function normalizeRecursive(array $value): array
    {
        foreach ($value as $key => $item) {
            if (is_array($item)) {
                $value[$key] = $this->normalizeRecursive($item);
            }
        }

        if ($this->isAssocArray($value)) {
            ksort($value);
        }

        return $value;
    }

    /**
     * @param array<mixed> $value
     */
    protected function isAssocArray(array $value): bool
    {
        if ($value === []) {
            return false;
        }

        return array_keys($value) !== range(0, count($value) - 1);
    }

    protected function resolveUserId(Request $request): int
    {
        return (int) (
            $request->user()?->id
            ?? Auth::guard('web')->id()
            ?? Auth::guard('sanctum')->id()
            ?? Auth::id()
            ?? 0
        );
    }
}
