<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Facades\Hook;
use App\Http\Controllers\Controller;
use App\Services\CoreMenuService;
use App\Services\MenuRegistry;
use App\Services\ModuleManager;
use App\Helpers\LanguageHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\Auth\Authenticatable;

class AdminMenuController extends Controller
{
    public function __construct(
        protected MenuRegistry $menuRegistry,
        protected CoreMenuService $coreMenuService,
        protected ModuleManager $moduleManager
    ) {
        // Ensure LanguageHelper is initialized
        LanguageHelper::init();
    }

    /**
     * Translate menu item labels recursively
     */
    protected function translateMenuItem(array &$item): void
    {
        if (isset($item['label'])) {
            $item['label'] = _l($item['label']);
        }
        
        if (isset($item['children']) && is_array($item['children'])) {
            foreach ($item['children'] as &$child) {
                $this->translateMenuItem($child);
            }
        }
    }

    /**
     * Get admin menu items from registry
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $this->resolveUserId($request);
        $locale = app()->getLocale();
        $menuVersion = Cache::get('polycms.admin_menu.version', 1);
        $permissionSignature = $this->resolvePermissionSignature($request);
        $cacheKey = sprintf('polycms.admin_menu.v2.user.%d.permissions.%s.locale.%s.v%s', $userId, $permissionSignature, $locale, $menuVersion);

        if ($request->boolean('refresh')) {
            Cache::forget($cacheKey);
        }

        // Try cache first
        $menuItems = Cache::get($cacheKey);

        if (!$menuItems) {
            // Build menu fresh
            $this->menuRegistry->clear();
            $this->coreMenuService->registerCoreMenus();

            $enabledModules = $this->moduleManager->getEnabledModules();
            if (!empty($enabledModules)) {
                Hook::doAction('admin.menu.build');
            }

            $menuItems = $this->menuRegistry->all();
            $menuItems = $this->filterMenuItemsForUser($menuItems, $this->resolveUser($request));

            // Sort children by order and translate labels
            foreach ($menuItems as &$item) {
                $this->translateMenuItem($item);
                if (!empty($item['children'])) {
                    usort($item['children'], function ($a, $b) {
                        return ($a['order'] ?? 999) <=> ($b['order'] ?? 999);
                    });
                }
            }

            $menuItems = array_values($menuItems);

            // Only cache if menu is not empty (prevent caching failed builds)
            if (!empty($menuItems)) {
                Cache::put($cacheKey, $menuItems, now()->addMinutes(5));
            }
        }

        return response()->json([
            'success' => true,
            'data' => $menuItems,
        ]);
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

    protected function resolveUser(Request $request): ?Authenticatable
    {
        return $request->user()
            ?? Auth::guard('web')->user()
            ?? Auth::guard('sanctum')->user()
            ?? Auth::user();
    }

    protected function resolvePermissionSignature(Request $request): string
    {
        $user = $this->resolveUser($request);

        if (!$user || !method_exists($user, 'getRoleNames') || !method_exists($user, 'getAllPermissions')) {
            return 'guest';
        }

        $roles = $user->getRoleNames()->sort()->values()->all();
        $permissions = $user->getAllPermissions()->pluck('name')->sort()->values()->all();

        return md5(json_encode([$roles, $permissions]));
    }

    /**
     * @param array<string, array<string, mixed>> $menuItems
     * @return array<string, array<string, mixed>>
     */
    protected function filterMenuItemsForUser(array $menuItems, ?Authenticatable $user): array
    {
        $filtered = [];

        foreach ($menuItems as $key => $item) {
            $children = $item['children'] ?? [];

            if (is_array($children) && $children !== []) {
                $children = array_map(function (array $child) use ($item): array {
                    if (!isset($child['permission']) && !isset($child['permissions'])) {
                        if (isset($item['permission'])) {
                            $child['permission'] = $item['permission'];
                        } elseif (isset($item['permissions'])) {
                            $child['permissions'] = $item['permissions'];
                        }
                    }

                    if (!isset($child['permission_mode']) && isset($item['permission_mode'])) {
                        $child['permission_mode'] = $item['permission_mode'];
                    }

                    return $child;
                }, $children);

                $item['children'] = array_values($this->filterMenuItemsForUser($children, $user));
            }

            $hasVisibleChildren = !empty($item['children']);

            if ($this->canViewMenuItem($item, $user) || $hasVisibleChildren) {
                $filtered[$key] = $item;
            }
        }

        return $filtered;
    }

    /**
     * @param array<string, mixed> $item
     */
    protected function canViewMenuItem(array $item, ?Authenticatable $user): bool
    {
        $permissions = $item['permissions'] ?? ($item['permission'] ?? null);

        if (!$permissions) {
            return true;
        }

        if (!$user || !method_exists($user, 'can')) {
            return false;
        }

        if (
            method_exists($user, 'hasAnyRole')
            && $user->hasAnyRole(['super-admin', 'admin'])
        ) {
            return true;
        }

        $permissions = is_array($permissions) ? $permissions : [$permissions];
        $mode = $item['permission_mode'] ?? 'any';

        foreach ($permissions as $permission) {
            $allowed = $user->can($permission);

            if ($allowed && $mode !== 'all') {
                return true;
            }

            if (!$allowed && $mode === 'all') {
                return false;
            }
        }

        return $mode === 'all';
    }
}
