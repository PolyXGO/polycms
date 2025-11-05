<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\ModuleManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    public function __construct(
        protected ModuleManager $moduleManager
    ) {}

    /**
     * Get all discovered modules
     */
    public function index(): JsonResponse
    {
        $modules = $this->moduleManager->discoverModules();

        // Convert to array for JSON response
        $modulesArray = [];
        foreach ($modules as $key => $module) {
            $modulesArray[] = [
                'key' => $key,
                'name' => $module['name'],
                'vendor' => $module['vendor'],
                'module' => $module['module'],
                'version' => $module['version'],
                'description' => $module['description'],
                'enabled' => $module['enabled'],
                'has_provider' => !empty($module['provider']),
            ];
        }

        return response()->json([
            'success' => true,
            'data' => array_values($modulesArray),
        ]);
    }

    /**
     * Enable a module
     */
    public function enable(Request $request, string $moduleKey): JsonResponse
    {
        $success = $this->moduleManager->enableModule($moduleKey);

        if (!$success) {
            return response()->json([
                'success' => false,
                'message' => 'Module not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Module enabled successfully',
        ]);
    }

    /**
     * Disable a module
     */
    public function disable(Request $request, string $moduleKey): JsonResponse
    {
        $success = $this->moduleManager->disableModule($moduleKey);

        if (!$success) {
            return response()->json([
                'success' => false,
                'message' => 'Module not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Module disabled successfully',
        ]);
    }
}
