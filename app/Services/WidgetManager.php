<?php

declare(strict_types=1);

namespace App\Services;

use App\Facades\Hook;
use App\Models\WidgetArea;
use App\Models\WidgetInstance;
use Illuminate\Support\Str;

class WidgetManager
{
    protected array $widgets = [];
    protected array $widgetAreas = [];

    public function register(string $type, string $class, array $config = []): void
    {
        $this->widgets[$type] = [
            'type' => $type,
            'class' => $class,
            'label' => $config['label'] ?? ucfirst(str_replace('_', ' ', $type)),
            'description' => $config['description'] ?? '',
            'icon' => $config['icon'] ?? null,
            'category' => $config['category'] ?? 'general',
            'config_schema' => $config['config_schema'] ?? [],
            'default_config' => $config['default_config'] ?? [],
        ];
    }

    public function getWidget(string $type): ?array
    {
        return $this->widgets[$type] ?? null;
    }

    public function getWidgets(): array
    {
        return $this->widgets;
    }

    public function getWidgetsByCategory(string $category): array
    {
        return array_filter($this->widgets, fn($widget) => $widget['category'] === $category);
    }

    public function render(WidgetInstance $instance): string
    {
        $widget = $this->getWidget($instance->widget_type);

        if (!$widget) {
            return '<!-- Widget type not found: ' . $instance->widget_type . ' -->';
        }

        $instance = Hook::applyFilters('widget.render.instance', $instance);
        $widget = Hook::applyFilters("widget.render.{$instance->widget_type}", $widget);

        $widgetClass = $widget['class'];

        if (!class_exists($widgetClass)) {
            return '<!-- Widget class not found: ' . $widgetClass . ' -->';
        }

        $widgetObject = new $widgetClass();
        
        if (method_exists($widgetObject, 'render')) {
            return $widgetObject->render($instance);
        }

        return '<!-- Widget class does not implement render method -->';
    }

    public function renderArea(string $areaKey): string
    {
        $area = WidgetArea::where('key', $areaKey)->first();

        if (!$area) {
            return '';
        }

        $instances = $area->widgets()->get();
        $instances = Hook::applyFilters('widget.area.render.instances', $instances, $area);

        $html = '';

        foreach ($instances as $instance) {
            $widgetHtml = $this->render($instance);
            $html .= Hook::applyFilters('widget.render.output', $widgetHtml, $instance);
        }

        return Hook::applyFilters('widget.area.render.output', $html, $area);
    }

    public function has(string $type): bool
    {
        return isset($this->widgets[$type]);
    }

    /**
     * Register a widget area (sidebar)
     *
     * @param string $key Unique key for the area, e.g. sidebar_primary
     * @param array<string, mixed> $config
     */
    public function registerArea(string $key, array $config = []): WidgetArea
    {
        $defaults = [
            'name' => Str::headline(str_replace(['-', '_'], ' ', $key)),
            'description' => '',
            'order' => count($this->widgetAreas) + 1,
            'locked' => false,
        ];

        $config = array_merge($defaults, $config);
        $config['key'] = $key;

        $this->widgetAreas[$key] = $config;

        return $this->ensureAreaRecord($config);
    }

    /**
     * Get registered area definition
     *
     * @return array<string, mixed>|null
     */
    public function getArea(string $key): ?array
    {
        return $this->widgetAreas[$key] ?? null;
    }

    /**
     * Get all registered areas
     *
     * @return array<string, array<string, mixed>>
     */
    public function getAreas(): array
    {
        return $this->widgetAreas;
    }

    /**
     * Check if area exists
     */
    public function hasArea(string $key): bool
    {
        return isset($this->widgetAreas[$key]);
    }

    /**
     * Ensure database contains registered widget areas
     */
    public function syncRegisteredAreas(): void
    {
        foreach ($this->widgetAreas as $config) {
            $this->ensureAreaRecord($config);
        }
    }

    /**
     * Normalize config array with defaults
     *
     * @param array<string, mixed> $config
     * @param array<string, mixed> $defaults
     * @return array<string, mixed>
     */
    public function mergeWidgetConfig(array $config, array $defaults = []): array
    {
        return array_replace_recursive($defaults, $config);
    }

    /**
     * Ensure widget area model exists and matches config
     *
     * @param array<string, mixed> $config
     */
    protected function ensureAreaRecord(array $config): WidgetArea
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('widget_areas')) {
            return new WidgetArea(['key' => $config['key']]);
        }
        $area = WidgetArea::withTrashed()->where('key', $config['key'])->first();

        if (!$area) {
            $area = new WidgetArea();
            $area->key = $config['key'];
        } elseif ($area->trashed()) {
            $area->restore();
        }

        $area->name = (string) ($config['name'] ?? $area->name);
        $area->description = (string) ($config['description'] ?? $area->description);
        if (isset($config['order'])) {
            $area->order = (int) $config['order'];
        }

        if ($area->isDirty()) {
            $area->save();
        } elseif (!$area->exists) {
            $area->save();
        }

        return $area;
    }
}
