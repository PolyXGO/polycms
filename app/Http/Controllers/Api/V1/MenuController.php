<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\EnsuresAdmin;
use App\Models\Menu;
use App\Services\MenuService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    use EnsuresAdmin;

    protected MenuService $menuService;

    public function __construct(MenuService $menuService)
    {
        $this->menuService = $menuService;
    }

    /**
     * List all menus
     */
    public function index(Request $request): JsonResponse
    {
        if ($response = $this->ensureAdmin($request)) {
            return $response;
        }

        $menus = Menu::withCount('allItems')
            ->orderBy('name')
            ->get();

        return $this->successResponse($menus);
    }

    /**
     * Create a new menu
     */
    public function store(Request $request): JsonResponse
    {
        if ($response = $this->ensureAdmin($request)) {
            return $response;
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $menu = $this->menuService->create($validated);

        return $this->successResponse($menu, 'Menu created successfully', 201);
    }

    /**
     * Get menu with items
     */
    public function show(Request $request, Menu $menu): JsonResponse
    {
        if ($response = $this->ensureAdmin($request)) {
            return $response;
        }

        $menu->load(['items' => function ($query) {
            $query->whereNull('parent_id')
                ->orderBy('order')
                ->with(['children' => function ($query) {
                    $query->orderBy('order');
                }]);
        }]);

        return $this->successResponse($menu);
    }

    /**
     * Update menu
     */
    public function update(Request $request, Menu $menu): JsonResponse
    {
        if ($response = $this->ensureAdmin($request)) {
            return $response;
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $menu = $this->menuService->update($menu, $validated);

        return $this->successResponse($menu, 'Menu updated successfully');
    }

    /**
     * Delete menu
     */
    public function destroy(Request $request, Menu $menu): JsonResponse
    {
        if ($response = $this->ensureAdmin($request)) {
            return $response;
        }

        $this->menuService->delete($menu);

        return $this->successResponse(null, 'Menu deleted successfully', 204);
    }

    /**
     * Assign menu to location
     */
    public function assign(Request $request, Menu $menu): JsonResponse
    {
        if ($response = $this->ensureAdmin($request)) {
            return $response;
        }

        $validated = $request->validate([
            'location' => ['required', 'string', 'max:255'],
        ]);

        $menu = $this->menuService->assignToLocation($menu, $validated['location']);

        return $this->successResponse($menu, 'Menu assigned to location successfully');
    }
}
