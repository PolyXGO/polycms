<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Facades\Hook;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\V1\Concerns\EnsuresAdmin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WidgetController extends Controller
{
    use EnsuresAdmin;

    /**
     * List available widget types
     */
    public function types(Request $request): JsonResponse
    {
        if ($response = $this->ensureAdmin($request)) {
            return $response;
        }

        $widgetManager = app('widget');
        $widgets = $widgetManager->getWidgets();

        // Allow modules/themes to adjust widget definitions
        $widgets = Hook::applyFilters('widgets.types', $widgets);

        $categories = [];
        $transformedWidgets = [];

        foreach ($widgets as $widget) {
            $categoryKey = $widget['category'] ?? 'general';
            $categories[$categoryKey] = $categories[$categoryKey] ?? [
                'key' => $categoryKey,
                'label' => $this->formatCategoryLabel($categoryKey),
            ];

            $transformedWidgets[] = $this->transformWidgetDefinition($widget);
        }

        usort($transformedWidgets, fn($a, $b) => strcmp($a['label'], $b['label']));

        return response()->json([
            'success' => true,
            'data' => [
                'widgets' => $transformedWidgets,
                'categories' => array_values($categories),
            ],
        ]);
    }

    protected function transformWidgetDefinition(array $widget): array
    {
        $schema = $widget['config_schema'] ?? [];
        $defaultConfig = $widget['default_config'] ?? [];

        foreach ($schema as $key => $field) {
            if (is_array($field) && array_key_exists('default', $field) && !array_key_exists($key, $defaultConfig)) {
                $defaultConfig[$key] = $field['default'];
            }
        }

        return [
            'type' => $widget['type'],
            'label' => $widget['label'],
            'description' => $widget['description'] ?? '',
            'icon' => $widget['icon'] ?? null,
            'category' => $widget['category'] ?? 'general',
            'config_schema' => $schema,
            'default_config' => $defaultConfig,
        ];
    }

    protected function formatCategoryLabel(string $category): string
    {
        return ucwords(str_replace(['_', '-'], ' ', $category));
    }

    public function show(Request $request, string $type): JsonResponse
    {
        if ($response = $this->ensureAdmin($request)) {
            return $response;
        }

        $widgetManager = app('widget');
        $widget = $widgetManager->getWidget($type);

        if (!$widget) {
            return response()->json([
                'success' => false,
                'message' => 'Widget type not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->transformWidgetDefinition($widget),
        ]);
    }
}
