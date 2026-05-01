<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Post;
use App\Models\Product;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class MenuService
{
    /**
     * Create a new menu
     */
    public function create(array $data): Menu
    {
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

        // Ensure unique slug
        $originalSlug = $data['slug'];
        $counter = 1;
        while (Menu::where('slug', $data['slug'])->exists()) {
            $data['slug'] = $originalSlug . '-' . $counter;
            $counter++;
        }

        return Menu::create($data);
    }

    /**
     * Update a menu
     */
    public function update(Menu $menu, array $data): Menu
    {
        if (isset($data['name']) && $data['name'] !== $menu->name) {
            // Update slug if name changes
            if (!isset($data['slug'])) {
                $data['slug'] = Str::slug($data['name']);

                // Ensure unique slug
                $originalSlug = $data['slug'];
                $counter = 1;
                while (Menu::where('slug', $data['slug'])->where('id', '!=', $menu->id)->exists()) {
                    $data['slug'] = $originalSlug . '-' . $counter;
                    $counter++;
                }
            }
        }

        $menu->update($data);
        return $menu->fresh();
    }

    /**
     * Delete a menu (soft delete)
     */
    public function delete(Menu $menu): bool
    {
        // Soft delete menu items first
        $menu->allItems()->delete();

        return $menu->delete();
    }

    /**
     * Assign menu to location
     */
    public function assignToLocation(Menu $menu, string $location): Menu
    {
        // Remove other menus from this location
        Menu::where('location', $location)
            ->where('id', '!=', $menu->id)
            ->update(['location' => null]);

        $menu->update(['location' => $location]);
        return $menu->fresh();
    }

    /**
     * Add menu item to menu
     */
    public function addMenuItem(Menu $menu, array $data): MenuItem
    {
        // Determine type and linkable
        $type = $data['type'] ?? 'custom';
        $linkableId = null;
        $linkableType = null;
        $url = $data['url'] ?? null;
        $title = $data['title'] ?? '';

        if ($type !== 'custom' && isset($data['linkable_id'])) {
            $linkableId = $data['linkable_id'];
            $linkableType = $this->getLinkableType($type);

            // Get title from linked entity if not provided
            if (empty($title)) {
                $entity = $linkableType::find($linkableId);
                if ($entity) {
                    $title = $entity->title ?? $entity->name ?? '';
                }
            }

            // For linkable items, don't store a static URL — it will be resolved
            // dynamically from the entity via getEffectiveUrlAttribute
            $url = null;
        }

        // Get max order for parent or root
        $parentId = $data['parent_id'] ?? null;
        $maxOrder = MenuItem::where('menu_id', $menu->id)
            ->where('parent_id', $parentId)
            ->max('order') ?? -1;

        $menuItem = MenuItem::create([
            'menu_id' => $menu->id,
            'parent_id' => $parentId,
            'title' => $title,
            'url' => $url,
            'type' => $type,
            'linkable_id' => $linkableId,
            'linkable_type' => $linkableType,
            'target' => $data['target'] ?? '_self',
            'icon' => $data['icon'] ?? null,
            'css_class' => $data['css_class'] ?? null,
            'order' => $maxOrder + 1,
            'active' => $data['active'] ?? true,
        ]);

        return $menuItem;
    }

    /**
     * Update menu item
     */
    public function updateMenuItem(MenuItem $menuItem, array $data): MenuItem
    {
        // If type changes, update linkable
        if (isset($data['type']) && $data['type'] !== $menuItem->type) {
            if ($data['type'] !== 'custom' && isset($data['linkable_id'])) {
                $data['linkable_id'] = $data['linkable_id'];
                $data['linkable_type'] = $this->getLinkableType($data['type']);
            } else {
                $data['linkable_id'] = null;
                $data['linkable_type'] = null;
            }
        }

        $menuItem->update($data);
        return $menuItem->fresh();
    }

    /**
     * Delete menu item (and children)
     */
    public function deleteMenuItem(MenuItem $menuItem): bool
    {
        // Delete children first
        $menuItem->children()->delete();

        return $menuItem->delete();
    }

    /**
     * Reorder menu items
     */
    public function reorderItems(Menu $menu, array $items): bool
    {
        DB::transaction(function () use ($menu, $items) {
            foreach ($items as $index => $itemData) {
                $itemId = is_array($itemData) ? $itemData['id'] : $itemData;
                // CRITICAL: parent_id can be null for root items
                // Use null coalescing to ensure null is preserved, not converted to default
                $parentId = is_array($itemData) && array_key_exists('parent_id', $itemData)
                    ? $itemData['parent_id']
                    : null;

                MenuItem::where('id', $itemId)
                    ->where('menu_id', $menu->id)
                    ->update([
                        'order' => $index,
                        'parent_id' => $parentId, // Can be null for root items
                    ]);

                // Recursively handle children
                if (is_array($itemData) && isset($itemData['children']) && is_array($itemData['children'])) {
                    $this->reorderChildren($menu, $itemId, $itemData['children']);
                }
            }
        });

        return true;
    }

    /**
     * Recursively reorder children
     */
    protected function reorderChildren(Menu $menu, int $parentId, array $children): void
    {
        foreach ($children as $index => $childData) {
            $childId = is_array($childData) ? $childData['id'] : $childData;

            MenuItem::where('id', $childId)
                ->where('menu_id', $menu->id)
                ->update([
                    'order' => $index,
                    'parent_id' => $parentId,
                ]);

            if (is_array($childData) && isset($childData['children']) && is_array($childData['children'])) {
                $this->reorderChildren($menu, $childId, $childData['children']);
            }
        }
    }

    /**
     * Get linkable type class from menu item type
     */
    protected function getLinkableType(string $type): ?string
    {
        return match ($type) {
            'post' => Post::class,
            'page' => Post::class, // Pages are also Post model with type='page'
            'product' => Product::class,
            'category' => Category::class,
            'tag' => Tag::class,
            default => null,
        };
    }

    /**
     * Get URL for entity
     */
    protected function getEntityUrl($entity, string $type): ?string
    {
        if (!$entity) {
            return null;
        }

        return match ($type) {
            'post' => $entity->frontend_url,
            'page' => route('pages.show', ['slug' => $entity->slug]),
            'product' => route('products.show', ['slug' => $entity->slug]),
            'category' => route('categories.show', ['slug' => $entity->slug]),
            'tag' => route('tags.show', ['slug' => $entity->slug]),
            default => null,
        };
    }

    /**
     * Validate hierarchy (prevent circular references)
     */
    public function validateHierarchy(MenuItem $menuItem, ?int $newParentId): bool
    {
        if (!$newParentId) {
            return true;
        }

        // Check if new parent is a descendant of this item
        $parent = MenuItem::find($newParentId);
        if (!$parent) {
            return true;
        }

        $currentParentId = $parent->parent_id;
        while ($currentParentId) {
            if ($currentParentId === $menuItem->id) {
                return false; // Circular reference detected
            }
            $currentParent = MenuItem::find($currentParentId);
            if (!$currentParent) {
                break;
            }
            $currentParentId = $currentParent->parent_id;
        }

        return true;
    }
}
