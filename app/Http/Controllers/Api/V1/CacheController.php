<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\CacheService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CacheController extends Controller
{
    public function __construct(
        private readonly CacheService $cacheService,
    ) {}

    /**
     * GET /api/v1/system/cache/status
     *
     * Return current cache status for all registered cache types.
     */
    public function status(): JsonResponse
    {
        return response()->json([
            'data' => $this->cacheService->getStatus(),
        ]);
    }

    /**
     * POST /api/v1/system/cache/clear
     *
     * Clear one or more cache types.
     * Body: { "types": ["all"] }  or  { "types": ["view", "config", "theme"] }
     */
    public function clear(Request $request): JsonResponse
    {
        $request->validate([
            'types'       => 'required|array|min:1',
            'types.*'     => 'string',
            'current_url' => 'nullable|string',
        ]);

        $currentUrl = $request->input('current_url');
        $results = $this->cacheService->clear($request->input('types'), $currentUrl);

        $allSuccess = !in_array('failed', $results, true);

        return response()->json([
            'success' => $allSuccess,
            'results' => $results,
            'message' => $allSuccess
                ? 'All selected caches cleared successfully.'
                : 'Some caches failed to clear. Check the results for details.',
        ], $allSuccess ? 200 : 207);
    }

    /**
     * POST /api/v1/system/cache/fix-permissions
     *
     * Attempt to recursively fix storage and bootstrap/cache permissions.
     */
    public function fixPermissions(): JsonResponse
    {
        $result = $this->cacheService->fixPermissions();

        return response()->json([
            'success' => $result['success'],
            'data' => $result,
            'message' => $result['success']
                ? 'Permissions fixed successfully.'
                : 'Some files/directories could not be fixed. Please run chown/chmod commands manually via SSH.',
        ], $result['success'] ? 200 : 207);
    }

    /**
     * GET /api/v1/system/cache/detail/{type}
     *
     * Return detailed diagnostic info for a specific cache type.
     */
    public function detail(string $type): JsonResponse
    {
        return response()->json([
            'data' => $this->cacheService->getTypeDetail($type),
        ]);
    }

    /**
     * GET /api/v1/system/cache/routes
     *
     * Return registered route groups for cache allowlist selection (includes core + active theme/module registered routes).
     */
    public function routes(): JsonResponse
    {
        return response()->json([
            'data' => [
                'groups' => $this->cacheService->getRegisteredRouteGroups(),
            ],
        ]);
    }
}
