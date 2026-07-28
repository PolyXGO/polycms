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

        $locale = $request->input('locale') ?? app()->getLocale();
        $manager = app('widget');
        $areas = WidgetArea::orderBy('order')
            ->orderBy('name')
            ->get()
            ->map(function (WidgetArea $area) use ($manager, $locale) {
                $areaConfig = $manager->getArea($area->key) ?? [];
                $widgets = $area->allWidgets()
                    ->where('locale', $locale)
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

                $layout = app(\App\Services\SettingsService::class)->get("widget_area_layout_{$area->key}", '1-col');
                $colCount = 1;
                if ($layout === '2-col' || $layout === 'split-left' || $layout === 'split-right') {
                    $colCount = 2;
                } elseif ($layout === '3-col') {
                    $colCount = 3;
                } elseif ($layout === '4-col') {
                    $colCount = 4;
                } elseif ($layout === '5-col') {
                    $colCount = 5;
                }
                
                $columnAlignments = [];
                for ($i = 0; $i < $colCount; $i++) {
                    $columnAlignments["col_{$i}"] = app(\App\Services\SettingsService::class)->get("widget_area_align_{$area->key}_col_{$i}", 'left');
                }

                return [
                    'id' => $area->id,
                    'name' => $area->name,
                    'key' => $area->key,
                    'description' => $area->description,
                    'order' => $area->order,
                    'locked' => (bool) ($areaConfig['locked'] ?? false),
                    'layout' => $layout,
                    'alignment' => app(\App\Services\SettingsService::class)->get("widget_area_align_{$area->key}", 'left'),
                    'column_alignments' => $columnAlignments,
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
            'layout' => ['sometimes', 'nullable', 'string', 'max:50'],
            'alignment' => ['sometimes', 'nullable', 'string', 'max:50'],
            'column_alignments' => ['sometimes', 'nullable', 'array'],
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

        if (array_key_exists('layout', $validated)) {
            app(\App\Services\SettingsService::class)->set("widget_area_layout_{$area->key}", $validated['layout']);
        }

        if (array_key_exists('alignment', $validated)) {
            app(\App\Services\SettingsService::class)->set("widget_area_align_{$area->key}", $validated['alignment']);
        }

        if ($request->has('column_alignments')) {
            $colAligns = $request->input('column_alignments');
            if (is_array($colAligns)) {
                foreach ($colAligns as $colKey => $alignValue) {
                    if (in_array($alignValue, ['left', 'right'])) {
                        app(\App\Services\SettingsService::class)->set("widget_area_align_{$area->key}_{$colKey}", $alignValue);
                    }
                }
            }
        }

        $layout = app(\App\Services\SettingsService::class)->get("widget_area_layout_{$area->key}", '1-col');
        $colCount = 1;
        if ($layout === '2-col' || $layout === 'split-left' || $layout === 'split-right') {
            $colCount = 2;
        } elseif ($layout === '3-col') {
            $colCount = 3;
        } elseif ($layout === '4-col') {
            $colCount = 4;
        } elseif ($layout === '5-col') {
            $colCount = 5;
        }
        
        $columnAlignments = [];
        for ($i = 0; $i < $colCount; $i++) {
            $columnAlignments["col_{$i}"] = app(\App\Services\SettingsService::class)->get("widget_area_align_{$area->key}_col_{$i}", 'left');
        }

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
                'layout' => $layout,
                'alignment' => app(\App\Services\SettingsService::class)->get("widget_area_align_{$area->key}", 'left'),
                'column_alignments' => $columnAlignments,
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
