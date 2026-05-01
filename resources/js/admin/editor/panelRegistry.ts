import type { Component } from 'vue';

const registry = new Map<string, Component>();

// Note: LandingBlockOptionsPanel is NOT registered here
// It's a standalone panel rendered via Teleport directly in editor views

export function registerEditorPanelComponent(name: string, component: Component): void {
    registry.set(name, component);
}

export function getEditorPanelComponent(name: string): Component | undefined {
    return registry.get(name);
}

export function getRegisteredPanelNames(): string[] {
    return Array.from(registry.keys());
}
