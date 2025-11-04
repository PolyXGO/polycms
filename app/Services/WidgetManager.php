<?php

declare(strict_types=1);

namespace App\Services;

use App\Facades\Hook;
use App\Models\WidgetArea;
use App\Models\WidgetInstance;

class WidgetManager
{
    protected array $widgets = [];

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
}
