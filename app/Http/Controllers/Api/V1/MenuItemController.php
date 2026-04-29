<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\EnsuresAdmin;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Services\MenuService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MenuItemController extends Controller
{
    use EnsuresAdmin;

    protected MenuService $menuService;

    public function __construct(MenuService $menuService)
    {
        $this->menuService = $menuService;
    }

    /**
     * Get all items for a menu
     */
    public function index(Request $request, Menu $menu): JsonResponse
    {
        if ($response = $this->ensureAdmin($request)) {
            return $response;
        }

        // Load all nested children recursively (up to 10 levels deep)
        $items = MenuItem::where('menu_id', $menu->id)
            ->whereNull('parent_id')
            ->orderBy('order')
            ->with($this->getNestedChildrenRelation($menu->id))
            ->get();

        return $this->successResponse($items);
    }

    /**
     * Get nested children relation for eager loading
     */
    private function getNestedChildrenRelation(int $menuId, int $depth = 0): array
    {
        if ($depth >= 10) {
            return []; // Prevent infinite recursion (max 10 levels)
        }

        return [
            'children' => function ($query) use ($menuId, $depth) {
                $query->where('menu_id', $menuId)
                    ->orderBy('order')
                    ->with($this->getNestedChildrenRelation($menuId, $depth + 1));
            }
        ];
    }

    /**
     * Add menu item
     */
    public function store(Request $request, Menu $menu): JsonResponse
    {
        if ($response = $this->ensureAdmin($request)) {
            return $response;
        }

        $validated = $request->validate([
            'type' => ['required', 'string', 'in:custom,post,page,category,product,tag'],
            'title' => ['nullable', 'string', 'max:255'],
            'url' => ['nullable', 'string', 'max:500'],
            'linkable_id' => ['nullable', 'integer'],
            'parent_id' => ['nullable', 'integer', 'exists:menu_items,id'],
            'target' => ['nullable', 'string', 'in:_self,_blank'],
            'icon' => ['nullable', 'string', 'max:255'],
            'css_class' => ['nullable', 'string', 'max:255'],
            'active' => ['nullable', 'boolean'],
        ]);

        // Validate parent belongs to same menu
        if (isset($validated['parent_id'])) {
            $parent = MenuItem::find($validated['parent_id']);
            if (!$parent || $parent->menu_id !== $menu->id) {
                return $this->errorResponse('Parent menu item must belong to the same menu', 'VALIDATION_ERROR', [], 422);
            }
        }

        $menuItem = $this->menuService->addMenuItem($menu, $validated);

        return $this->successResponse($menuItem, 'Menu item added successfully', 201);
    }

    /**
     * Update menu item
     */
    public function update(Request $request, Menu $menu, MenuItem $menuItem): JsonResponse
    {
        if ($response = $this->ensureAdmin($request)) {
            return $response;
        }

        // Verify menu item belongs to menu
        if ($menuItem->menu_id !== $menu->id) {
            return $this->notFoundResponse('Menu item');
        }

        $validated = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'url' => ['nullable', 'string', 'max:500'],
            'target' => ['nullable', 'string', 'in:_self,_blank'],
            'icon' => ['nullable', 'string', 'max:255'],
            'css_class' => ['nullable', 'string', 'max:255'],
            'active' => ['nullable', 'boolean'],
            'parent_id' => ['nullable', 'integer', 'exists:menu_items,id'],
        ]);

        // Validate parent belongs to same menu
        if (isset($validated['parent_id'])) {
            $parent = MenuItem::find($validated['parent_id']);
            if (!$parent || $parent->menu_id !== $menu->id) {
                return $this->errorResponse('Parent menu item must belong to the same menu', 'VALIDATION_ERROR', [], 422);
            }

            // Validate hierarchy (prevent circular references)
            if (!$this->menuService->validateHierarchy($menuItem, $validated['parent_id'])) {
                return $this->errorResponse('Cannot set parent: would create circular reference', 'VALIDATION_ERROR', [], 422);
            }
        }

        $menuItem = $this->menuService->updateMenuItem($menuItem, $validated);

        return $this->successResponse($menuItem, 'Menu item updated successfully');
    }

    /**
     * Delete menu item
     */
    public function destroy(Request $request, Menu $menu, MenuItem $menuItem): JsonResponse
    {
        if ($response = $this->ensureAdmin($request)) {
            return $response;
        }

        // Verify menu item belongs to menu
        if ($menuItem->menu_id !== $menu->id) {
            return $this->notFoundResponse('Menu item');
        }

        $this->menuService->deleteMenuItem($menuItem);

        return $this->successResponse(null, 'Menu item deleted successfully', 204);
    }

    /**
     * Reorder menu items
     */
    public function reorder(Request $request, Menu $menu): JsonResponse
    {
        if ($response = $this->ensureAdmin($request)) {
            return $response;
        }

        // Custom validation to allow parent_id to be null or integer
        $validated = $request->validate([
            'items' => ['required', 'array'],
            'items.*.id' => ['required', 'integer', 'exists:menu_items,id'],
            'items.*.parent_id' => ['nullable'], // Allow null for root items
            'items.*.children' => ['nullable', 'array'],
        ]);

        // Validate parent_id: if set, must be integer and exist
        foreach ($validated['items'] as $index => $item) {
            if (is_array($item) && isset($item['parent_id']) && $item['parent_id'] !== null) {
                if (!is_int($item['parent_id']) || !MenuItem::where('id', $item['parent_id'])->exists()) {
                    return $this->errorResponse(
                        "Invalid parent_id for item at index {$index}",
                        'VALIDATION_ERROR',
                        [],
                        422
                    );
                }
            }
            // Normalize: if parent_id is not set, set it to null (root level)
            if (is_array($item) && !isset($item['parent_id'])) {
                $validated['items'][$index]['parent_id'] = null;
            }
        }

        // Verify all items belong to menu
        $itemIds = $this->extractItemIds($validated['items']);
        $count = MenuItem::where('menu_id', $menu->id)
            ->whereIn('id', $itemIds)
            ->count();

        if ($count !== count($itemIds)) {
            return $this->errorResponse('Some menu items do not belong to this menu', 'VALIDATION_ERROR', [], 422);
        }

        $this->menuService->reorderItems($menu, $validated['items']);

        return $this->successResponse(null, 'Menu items reordered successfully');
    }

    /**
     * Extract all item IDs from nested structure
     */
    protected function extractItemIds(array $items): array
    {
        $ids = [];

        foreach ($items as $item) {
            if (is_array($item) && isset($item['id'])) {
                $ids[] = $item['id'];

                if (isset($item['children']) && is_array($item['children'])) {
                    $ids = array_merge($ids, $this->extractItemIds($item['children']));
                }
            }
        }

        return $ids;
    }
}
