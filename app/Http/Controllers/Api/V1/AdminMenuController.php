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
        $cacheKey = sprintf('polycms.admin_menu.v1.user.%d.locale.%s.v%s', $userId, $locale, $menuVersion);

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
}
