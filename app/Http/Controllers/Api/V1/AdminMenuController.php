<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Facades\Hook;
use App\Http\Controllers\Controller;
use App\Services\MenuRegistry;
use Illuminate\Http\JsonResponse;

class AdminMenuController extends Controller
{
    public function __construct(
        protected MenuRegistry $menuRegistry
    ) {}

    /**
     * Get admin menu items from registry
     */
    public function index(): JsonResponse
    {
        // Allow modules to register menu items via action hook
        Hook::doAction('admin.menu.build');

        $menuItems = $this->menuRegistry->all();

        return response()->json([
            'success' => true,
            'data' => array_values($menuItems),
        ]);
    }
}
