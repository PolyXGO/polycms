<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\TopbarMenuService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
        
        // Set route params if provided
        if ($routeName && $routeParams) {
            $request->merge(['route' => $routeName, 'route_params' => $routeParams]);
        }
        
        $menuItems = $topbarService->getMenuItems($request);
        
        return response()->json([
            'data' => $menuItems,
        ]);
    }
}



