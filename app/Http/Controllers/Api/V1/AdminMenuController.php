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
    public function index(): JsonResponse
    {
        // Clear registry before building
        $this->menuRegistry->clear();

        // Register core menu items first
        $this->coreMenuService->registerCoreMenus();

        // Get enabled modules and only allow them to register menu items
        $enabledModules = $this->moduleManager->getEnabledModules();
        
        // Filter hook to only execute for enabled modules
        // We'll wrap the hook execution to check module status
        if (!empty($enabledModules)) {
            // Allow modules to register menu items via action hook
            // The hook callbacks will only execute for enabled modules since their service providers are only registered when enabled
            Hook::doAction('admin.menu.build');
        }

        $menuItems = $this->menuRegistry->all();

        // Sort children by order and translate labels
        foreach ($menuItems as &$item) {
            // Translate menu item label
            $this->translateMenuItem($item);
            
            if (!empty($item['children'])) {
                usort($item['children'], function ($a, $b) {
                    $orderA = $a['order'] ?? 999;
                    $orderB = $b['order'] ?? 999;
                    return $orderA <=> $orderB;
                });
            }
        }

        return response()->json([
            'success' => true,
            'data' => array_values($menuItems),
        ]);
    }
}
