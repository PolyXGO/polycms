<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\EnsuresAdmin;
use App\Http\Controllers\Controller;
use App\Models\WidgetArea;
use App\Models\WidgetInstance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WidgetInstanceController extends Controller
{
    use EnsuresAdmin;

    public function store(Request $request): JsonResponse
    {
        if ($response = $this->ensureAdmin($request)) {
            return $response;
        }

        $validated = $request->validate([
            'widget_area_id' => ['required', 'integer', 'exists:widget_areas,id'],
            'widget_type' => ['required', 'string'],
            'title' => ['nullable', 'string', 'max:150'],
            'config' => ['nullable', 'array'],
            'active' => ['nullable', 'boolean'],
        ]);

        $manager = app('widget');
        $definition = $manager->getWidget($validated['widget_type']);

        if (!$definition) {
            return response()->json([
                'success' => false,
                'message' => 'Unknown widget type: ' . $validated['widget_type'],
            ], 422);
        }

        $config = $this->normalizeConfig($validated['config'] ?? [], $definition);
        $order = (int) WidgetInstance::where('widget_area_id', $validated['widget_area_id'])->max('order') + 10;

        $instance = WidgetInstance::create([
            'widget_area_id' => $validated['widget_area_id'],
            'widget_type' => $validated['widget_type'],
            'title' => $validated['title'] ?? null,
            'config' => $config,
            'order' => $order,
            'active' => $validated['active'] ?? true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Widget added successfully',
            'data' => [
                'id' => $instance->id,
                'widget_area_id' => $instance->widget_area_id,
                'widget_type' => $instance->widget_type,
                'title' => $instance->title,
                'config' => $instance->config ?? [],
                'order' => $instance->order,
                'active' => $instance->active,
                'definition' => $this->transformWidgetDefinition($definition),
            ],
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        if ($response = $this->ensureAdmin($request)) {
            return $response;
        }

        $instance = WidgetInstance::findOrFail($id);
        $manager = app('widget');
        $definition = $manager->getWidget($instance->widget_type);

        $validated = $request->validate([
            'title' => ['sometimes', 'nullable', 'string', 'max:150'],
            'config' => ['sometimes', 'array'],
            'active' => ['sometimes', 'boolean'],
            'order' => ['sometimes', 'integer', 'min:0'],
            'widget_area_id' => ['sometimes', 'integer', 'exists:widget_areas,id'],
        ]);

        if (isset($validated['config'])) {
            if (!$definition) {
                return response()->json([
                    'success' => false,
                    'message' => 'Widget type not registered.',
                ], 422);
            }

            $instance->config = $this->normalizeConfig($validated['config'], $definition);
        }

        if (isset($validated['widget_area_id']) && $validated['widget_area_id'] !== $instance->widget_area_id) {
            $instance->widget_area_id = $validated['widget_area_id'];
            $instance->order = (int) WidgetInstance::where('widget_area_id', $instance->widget_area_id)->max('order') + 10;
        }

        if (isset($validated['title'])) {
            $instance->title = $validated['title'];
        }

        if (array_key_exists('active', $validated)) {
            $instance->active = (bool) $validated['active'];
        }

        if (isset($validated['order'])) {
            $this->reorderWidget($instance, (int) $validated['order']);
        } else {
            $instance->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Widget updated successfully',
            'data' => [
                'id' => $instance->id,
                'widget_area_id' => $instance->widget_area_id,
                'widget_type' => $instance->widget_type,
                'title' => $instance->title,
                'config' => $instance->config ?? [],
                'order' => $instance->order,
                'active' => $instance->active,
            ],
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        if ($response = $this->ensureAdmin($request)) {
            return $response;
        }

        $instance = WidgetInstance::findOrFail($id);
        $instance->delete();

        return response()->json([
            'success' => true,
            'message' => 'Widget removed successfully',
        ]);
    }

    public function reorder(Request $request, WidgetArea $widgetArea): JsonResponse
    {
        if ($response = $this->ensureAdmin($request)) {
            return $response;
        }

        $validated = $request->validate([
            'widget_ids' => ['required', 'array'],
            'widget_ids.*' => ['integer'],
        ]);

        $ids = $validated['widget_ids'];

        $widgets = WidgetInstance::whereIn('id', $ids)
            ->where('widget_area_id', $widgetArea->id)
            ->orderBy('order')
            ->get()
            ->keyBy('id');

        if ($widgets->count() !== count($ids)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid widget IDs provided.',
            ], 422);
        }

        foreach (array_values($ids) as $index => $widgetId) {
            /** @var WidgetInstance $widget */
            $widget = $widgets[$widgetId];
            $widget->order = ($index + 1) * 10;
            $widget->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Widget order updated successfully',
        ]);
    }

    /**
     * @param array<string, mixed> $config
     * @param array<string, mixed> $definition
     * @return array<string, mixed>
     */
    protected function normalizeConfig(array $config, array $definition): array
    {
        $schema = $definition['config_schema'] ?? [];
        $defaults = $definition['default_config'] ?? [];

        foreach ($schema as $key => $field) {
            if (is_array($field) && array_key_exists('default', $field) && !array_key_exists($key, $defaults)) {
                $defaults[$key] = $field['default'];
            }
        }

        return array_replace_recursive($defaults, $config);
    }

    protected function reorderWidget(WidgetInstance $instance, int $targetPosition): void
    {
        $widgets = WidgetInstance::where('widget_area_id', $instance->widget_area_id)
            ->orderBy('order')
            ->get()
            ->filter(fn(WidgetInstance $widget) => $widget->id !== $instance->id)
            ->values()
            ->all();

        $targetPosition = max(0, $targetPosition);
        array_splice($widgets, $targetPosition, 0, [$instance]);

        foreach ($widgets as $index => $widget) {
            $widget->order = ($index + 1) * 10;
            $widget->save();
        }
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
}

