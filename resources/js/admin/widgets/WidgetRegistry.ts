import type { Component } from 'vue';

export interface WidgetTypeConfig {
    type: string;
    label: string;
    description?: string;
    icon?: string;
    category?: string;
    configSchema?: Record<string, any>;
    formComponent?: Component;
    previewComponent?: Component;
}

class WidgetRegistry {
    private widgets: Map<string, WidgetTypeConfig> = new Map();

    register(config: WidgetTypeConfig): void {
        this.widgets.set(config.type, config);
    }

    get(type: string): WidgetTypeConfig | undefined {
        return this.widgets.get(type);
    }

    getAll(): WidgetTypeConfig[] {
        return Array.from(this.widgets.values());
    }

    getByCategory(category: string): WidgetTypeConfig[] {
        return this.getAll().filter(widget => widget.category === category);
    }

    has(type: string): boolean {
        return this.widgets.has(type);
    }
}

export const widgetRegistry = new WidgetRegistry();

if (typeof window !== 'undefined') {
    (window as any).PolyCMSWidgets = {
        register: (config: WidgetTypeConfig) => widgetRegistry.register(config),
        get: (type: string) => widgetRegistry.get(type),
        getAll: () => widgetRegistry.getAll(),
    };
}
