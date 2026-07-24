<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Facades\Hook;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class EditorPanelController extends Controller
{
    public function index(Request $request, string $type): JsonResponse
    {
        if ($response = $this->ensureEditorAccess($request, $type)) {
            return $response;
        }

        $type = strtolower($type);
        $panels = $this->getDefaultPanels($type);
        $panels = Hook::applyFilters('admin.editor.panels', $panels, $type, $request->user());
        $normalized = $this->normalizePanels($panels);
        $normalized = $this->applyUserPreferences($normalized, $type, (int) $request->user()->id);
        $preferences = $this->getUserPreferences($type, (int) $request->user()->id);

        return response()->json([
            'success' => true,
            'data' => $normalized,
            'preferences' => $preferences,
        ]);
    }

    public function update(Request $request, string $type): JsonResponse
    {
        $type = strtolower($type);
        if ($response = $this->ensureEditorAccess($request, $type)) {
            return $response;
        }

        $validated = $request->validate([
            'order' => ['nullable', 'array'],
            'order.main' => ['nullable', 'array'],
            'order.sidebar' => ['nullable', 'array'],
            'collapsed' => ['nullable', 'array'],
            'preferences' => ['nullable', 'array'],
        ]);

        $userId = (int) $request->user()->id;
        $existing = $this->getUserPreferences($type, $userId);
        
        $newPrefs = array_merge($existing, [
            'order' => [
                'main' => array_values(array_unique(array_map('strval', $validated['order']['main'] ?? $existing['order']['main']))),
                'sidebar' => array_values(array_unique(array_map('strval', $validated['order']['sidebar'] ?? $existing['order']['sidebar']))),
            ],
            'collapsed' => Arr::map($validated['collapsed'] ?? $existing['collapsed'], fn ($value) => (bool) $value),
        ], $validated['preferences'] ?? []);

        $this->storeUserPreferences($type, $userId, $newPrefs);

        return response()->json([
            'success' => true,
            'message' => 'Editor panels updated',
        ]);
    }

    /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    protected function normalizePanels(array $panels): array
    {
        $normalized = ['main' => [], 'sidebar' => []];
        foreach ($panels as $panel) {
            if (!is_array($panel)) {
                continue;
            }

            $key = $panel['key'] ?? null;
            if (!$key) {
                continue;
            }

            $area = $panel['area'] ?? 'main';
            if (!in_array($area, ['main', 'sidebar'], true)) {
                $area = 'main';
            }

            $normalized[$area][] = [
                'key' => (string) $key,
                'label' => $panel['label'] ?? ucfirst(str_replace('_', ' ', $key)),
                'description' => $panel['description'] ?? null,
                'order' => (int) ($panel['order'] ?? 100),
                'area' => $area,
                'component' => $panel['component'] ?? null,
                'props' => $panel['props'] ?? [],
                'icon' => $panel['icon'] ?? null,
                'collapsible' => $panel['collapsible'] ?? true,
                'collapsed' => (bool) ($panel['collapsed'] ?? false),
                'context' => $panel['context'] ?? null,
                'movable' => $panel['movable'] ?? true,
            ];
        }

        foreach ($normalized as $area => &$items) {
            usort($items, fn ($a, $b) => $a['order'] <=> $b['order']);
        }

        return $normalized;
    }

    protected function ensureEditorAccess(Request $request, string $type): ?JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        if ($user->hasRole(['admin', 'editor'])) {
            return null;
        }

        $type = strtolower($type);

        $hasPostAccess = $type === 'post' || $type === 'page' || $type === 'news';
        $hasProductAccess = $type === 'product';
        $hasLayoutAssetAccess = in_array($type, ['layout-part', 'layout-template'], true);

        if (
            $hasPostAccess &&
            ($user->hasRole('author') || $user->can('create post') || $user->can('update post'))
        ) {
            return null;
        }

        if (
            $hasProductAccess &&
            ($user->hasRole('author') || $user->can('create product') || $user->can('update product'))
        ) {
            return null;
        }

        if (
            $hasLayoutAssetAccess &&
            ($user->hasRole(['admin', 'editor']) || $user->can('view layout assets'))
        ) {
            return null;
        }

        return response()->json([
            'success' => false,
            'message' => 'Forbidden: insufficient permissions',
        ], 403);
    }

    protected function applyUserPreferences(array $panels, string $type, int $userId): array
    {
        $preferences = $this->getUserPreferences($type, $userId);
        $order = $preferences['order'];
        $collapsed = $preferences['collapsed'];

        // Flatten all available panels from both areas
        $allPanels = [];
        foreach (['main', 'sidebar'] as $area) {
            foreach ($panels[$area] as $panel) {
                $allPanels[$panel['key']] = $panel;
            }
        }

        $result = ['main' => [], 'sidebar' => []];
        $processedKeys = [];

        // Distribute panels according to saved order
        foreach (['main', 'sidebar'] as $area) {
            foreach ($order[$area] ?? [] as $key) {
                if (isset($allPanels[$key])) {
                    $panel = $allPanels[$key];
                    $panel['area'] = $area; // Update area to reflects user preference
                    $panel['collapsed'] = (bool) ($collapsed[$key] ?? $panel['collapsed'] ?? false);
                    $result[$area][] = $panel;
                    $processedKeys[] = $key;
                }
            }
        }

        // Add any remaining panels that weren't in the saved order to their default areas
        foreach ($allPanels as $key => $panel) {
            if (!in_array($key, $processedKeys, true)) {
                $area = $panel['area'];
                $panel['collapsed'] = (bool) ($collapsed[$key] ?? $panel['collapsed'] ?? false);
                $result[$area][] = $panel;
            }
        }

        return $result;
    }


    /**
     * @return array<int, array<string, mixed>>
     */
    protected function getDefaultPanels(string $type): array
    {
        return match ($type) {
            'product' => $this->getProductPanels(),
            'page' => $this->getPagePanels(),
            'layout-part' => $this->getLayoutPartPanels(),
            'layout-template' => $this->getLayoutTemplatePanels(),
            default => $this->getPostPanels(),
        };
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function getPostPanels(): array
    {
        return [
            [
                'key' => 'primary',
                'label' => 'Title & Content',
                'area' => 'main',
                'order' => 0,
                'collapsible' => false,
                'movable' => false,
            ],
            [
                'key' => 'excerpt',
                'label' => 'Excerpt',
                'area' => 'main',
                'order' => 30,
            ],
            [
                'key' => 'categories',
                'label' => 'Categories',
                'area' => 'sidebar',
                'order' => 15,
            ],
            [
                'key' => 'tags',
                'label' => 'Tags',
                'area' => 'sidebar',
                'order' => 20,
            ],
            [
                'key' => 'publish',
                'label' => 'Publish',
                'area' => 'sidebar',
                'order' => 5,
            ],
            [
                'key' => 'featured_image',
                'label' => 'Featured Image',
                'area' => 'sidebar',
                'order' => 10,
            ],
            [
                'key' => 'seo',
                'label' => 'SEO',
                'area' => 'main',
                'order' => 100,
            ],
            [
                'key' => 'theme_template',
                'label' => 'Theme Template',
                'area' => 'sidebar',
                'order' => 45,
            ],
            [
                'key' => 'template',
                'label' => 'Landing Template',
                'area' => 'sidebar',
                'order' => 50,
            ],
            [
                'key' => 'layout',
                'label' => 'Page Layout',
                'area' => 'sidebar',
                'order' => 55,
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function getPagePanels(): array
    {
        return [
            [
                'key' => 'primary',
                'label' => 'Title & Content',
                'area' => 'main',
                'order' => 0,
                'collapsible' => false,
                'movable' => false,
            ],
            [
                'key' => 'excerpt',
                'label' => 'Excerpt',
                'area' => 'main',
                'order' => 30,
            ],
            [
                'key' => 'publish',
                'label' => 'Publish',
                'area' => 'sidebar',
                'order' => 5,
            ],
            [
                'key' => 'featured_image',
                'label' => 'Featured Image',
                'area' => 'sidebar',
                'order' => 10,
            ],
            [
                'key' => 'seo',
                'label' => 'SEO',
                'area' => 'main',
                'order' => 100,
            ],
            [
                'key' => 'theme_template',
                'label' => 'Theme Template',
                'area' => 'sidebar',
                'order' => 35,
            ],
            [
                'key' => 'template',
                'label' => 'Landing Template',
                'area' => 'sidebar',
                'order' => 40,
            ],
            [
                'key' => 'layout',
                'label' => 'Page Layout',
                'area' => 'sidebar',
                'order' => 45,
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function getProductPanels(): array
    {
        return [
            [
                'key' => 'primary',
                'label' => 'Product Basics',
                'area' => 'main',
                'order' => 0,
                'collapsible' => false,
                'movable' => false,
            ],
            [
                'key' => 'publish',
                'label' => 'Publish',
                'area' => 'sidebar',
                'order' => 5,
            ],
            [
                'key' => 'media',
                'label' => 'Product Media',
                'area' => 'sidebar',
                'order' => 10,
            ],
            [
                'key' => 'pricing',
                'label' => 'Pricing & Inventory',
                'area' => 'sidebar',
                'order' => 15,
            ],
            [
                'key' => 'categories',
                'label' => 'Product Categories',
                'area' => 'sidebar',
                'order' => 20,
            ],
            [
                'key' => 'brands',
                'label' => 'Brands',
                'area' => 'sidebar',
                'order' => 25,
            ],
            [
                'key' => 'tags',
                'label' => 'Product Tags',
                'area' => 'sidebar',
                'order' => 30,
            ],
            [
                'key' => 'theme_template',
                'label' => 'Theme Template',
                'area' => 'sidebar',
                'order' => 55,
            ],
            [
                'key' => 'layout',
                'label' => 'Page Layout',
                'area' => 'sidebar',
                'order' => 60,
            ],
            [
                'key' => 'variants',
                'label' => 'Product Variants',
                'area' => 'main',
                'order' => 20,
                'collapsed' => true,
            ],
            [
                'key' => 'service',
                'label' => 'Service Configuration',
                'area' => 'main',
                'order' => 40,
            ],
            [
                'key' => 'seo',
                'label' => 'SEO',
                'area' => 'main',
                'order' => 100,
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function getLayoutPartPanels(): array
    {
        return [
            [
                'key' => 'primary',
                'label' => 'Part Editor',
                'area' => 'main',
                'order' => 0,
                'collapsible' => false,
                'movable' => false,
            ],
            [
                'key' => 'settings',
                'label' => 'Part Settings',
                'area' => 'sidebar',
                'order' => 10,
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function getLayoutTemplatePanels(): array
    {
        return [
            [
                'key' => 'primary',
                'label' => 'Template Editor',
                'area' => 'main',
                'order' => 0,
                'collapsible' => false,
                'movable' => false,
            ],
            [
                'key' => 'settings',
                'label' => 'Template Settings',
                'area' => 'sidebar',
                'order' => 10,
            ],
        ];
    }

    protected function getUserPreferences(string $type, int $userId): array
    {
        $setting = Setting::where('key', $this->getPreferenceKey($type, $userId))->first();
        if (!$setting || !$setting->value) {
            return [
                'order' => ['main' => [], 'sidebar' => []],
                'collapsed' => [],
            ];
        }

        $valueRaw = $setting->value;
        $value = is_array($valueRaw)
            ? $valueRaw
            : (json_decode((string) $valueRaw, true) ?: []);

        return array_merge([
            'order' => ['main' => [], 'sidebar' => []],
            'collapsed' => [],
        ], $value);
    }

    protected function storeUserPreferences(string $type, int $userId, array $preferences): void
    {
        Setting::updateOrCreate(
            ['key' => $this->getPreferenceKey($type, $userId)],
            [
                'value' => json_encode($preferences),
                'group' => 'editor_panels',
                'type' => 'json',
            ]
        );
    }

    protected function getPreferenceKey(string $type, int $userId): string
    {
        return "editor_panels_{$type}_user_{$userId}";
    }
}
