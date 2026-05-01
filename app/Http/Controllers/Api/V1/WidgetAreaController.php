<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\EnsuresAdmin;
use App\Http\Controllers\Controller;
use App\Models\WidgetArea;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WidgetAreaController extends Controller
{
    use EnsuresAdmin;

    public function index(Request $request): JsonResponse
    {
        if ($response = $this->ensureAdmin($request)) {
            return $response;
        }

        $manager = app('widget');
        $areas = WidgetArea::orderBy('order')
            ->orderBy('name')
            ->get()
            ->map(function (WidgetArea $area) use ($manager) {
                $areaConfig = $manager->getArea($area->key) ?? [];
                $widgets = $area->allWidgets()
                    ->orderBy('order')
                    ->get()
                    ->map(function ($instance) use ($manager) {
                        $definition = $manager->getWidget($instance->widget_type);

                        return [
                            'id' => $instance->id,
                            'title' => $instance->title,
                            'widget_type' => $instance->widget_type,
                            'order' => $instance->order,
                            'active' => (bool) $instance->active,
                            'config' => $instance->config ?? [],
                            'definition' => $definition ? $this->transformWidgetDefinition($definition) : null,
                        ];
                    })
                    ->values();

                return [
                    'id' => $area->id,
                    'name' => $area->name,
                    'key' => $area->key,
                    'description' => $area->description,
                    'order' => $area->order,
                    'locked' => (bool) ($areaConfig['locked'] ?? false),
                    'widgets' => $widgets,
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'data' => $areas,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        if ($response = $this->ensureAdmin($request)) {
            return $response;
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'key' => ['nullable', 'string', 'max:150', 'regex:/^[A-Za-z0-9_\-]+$/', 'unique:widget_areas,key'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $name = $validated['name'];
        $key = $validated['key'] ?? $this->generateUniqueKey($name);
        $description = $validated['description'] ?? null;

        $order = (int) WidgetArea::max('order') + 10;

        $area = WidgetArea::create([
            'name' => $name,
            'key' => $key,
            'description' => $description,
            'order' => $order,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Widget area created successfully',
            'data' => [
                'id' => $area->id,
                'name' => $area->name,
                'key' => $area->key,
                'description' => $area->description,
                'order' => $area->order,
                'locked' => false,
                'widgets' => [],
            ],
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        if ($response = $this->ensureAdmin($request)) {
            return $response;
        }

        $area = WidgetArea::findOrFail($id);
        $manager = app('widget');
        $config = $manager->getArea($area->key) ?? [];
        $locked = (bool) ($config['locked'] ?? false);

        $rules = [
            'name' => ['sometimes', 'string', 'max:150'],
            'description' => ['sometimes', 'nullable', 'string', 'max:500'],
            'order' => ['sometimes', 'integer'],
        ];

        if (!$locked) {
            $rules['key'] = ['sometimes', 'string', 'max:150', 'regex:/^[A-Za-z0-9_\-]+$/', 'unique:widget_areas,key,' . $area->id];
        }

        $validated = $request->validate($rules);

        if ($locked && isset($validated['key']) && $validated['key'] !== $area->key) {
            return response()->json([
                'success' => false,
                'message' => 'This widget area cannot be renamed.',
            ], 403);
        }

        $area->fill($validated);
        $area->save();

        return response()->json([
            'success' => true,
            'message' => 'Widget area updated successfully',
            'data' => [
                'id' => $area->id,
                'name' => $area->name,
                'key' => $area->key,
                'description' => $area->description,
                'order' => $area->order,
                'locked' => $locked,
            ],
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        if ($response = $this->ensureAdmin($request)) {
            return $response;
        }

        $area = WidgetArea::findOrFail($id);
        $manager = app('widget');
        $config = $manager->getArea($area->key) ?? [];

        if (!empty($config['locked'])) {
            return response()->json([
                'success' => false,
                'message' => 'Core widget areas cannot be deleted.',
            ], 403);
        }

        $area->delete();

        return response()->json([
            'success' => true,
            'message' => 'Widget area deleted successfully',
        ]);
    }

    protected function generateUniqueKey(string $name): string
    {
        $base = Str::slug($name, '_');
        if ($base === '') {
            $base = 'widget_area';
        }

        $key = $base;
        $suffix = 1;

        while (WidgetArea::where('key', $key)->exists()) {
            $key = $base . '_' . $suffix;
            ++$suffix;
        }

        return $key;
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
