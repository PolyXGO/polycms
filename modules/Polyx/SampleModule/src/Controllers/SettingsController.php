<?php

declare(strict_types=1);

namespace Modules\Polyx\SampleModule\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /**
     * Get module settings
     */
    public function index(): JsonResponse
    {
        // In a real module, this would fetch from database
        // For now, return default settings
        $settings = [
            'enabled' => true,
            'additional_content' => 'This content was added by Sample Module after the post title.',
            'style' => 'padding: 10px; background: #f0f0f0; border-left: 3px solid #3b82f6; margin: 20px 0;',
        ];

        return response()->json([
            'success' => true,
            'data' => $settings,
        ]);
    }

    /**
     * Save module settings
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'enabled' => ['boolean'],
            'additional_content' => ['nullable', 'string', 'max:1000'],
            'style' => ['nullable', 'string', 'max:500'],
        ]);

        // In a real module, this would save to database
        // For demonstration, we'll just return success

        return response()->json([
            'success' => true,
            'message' => 'Settings saved successfully',
            'data' => $validated,
        ]);
    }
}
