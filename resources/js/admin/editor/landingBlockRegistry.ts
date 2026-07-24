import type { Component } from 'vue';

export interface LandingBlockDefinition {
    key: string;
    label: string;
    icon?: string;
    category?: 'atomic' | 'layout' | 'general' | 'marketing' | 'ecommerce' | 'custom' | 'patterns';
    component: any; // Using any for Vue component to avoid strict type issues in registry
    /** Optional dedicated preview component for the editor. Falls back to `component` with mode="preview". */
    previewComponent?: any;
    /** Optional dedicated settings component for the sidebar. Falls back to `component` with mode="settings". */
    settingsComponent?: any;
    defaultAttrs?: Record<string, any>;
    /** If true, this block is treated as a reusable pattern (predefined group of blocks) */
    isPattern?: boolean;
}

class BlockRegistry {
    private blocks = new Map<string, LandingBlockDefinition>();

    register(definition: LandingBlockDefinition) {
        this.blocks.set(definition.key, definition);
    }

    get(key: string): LandingBlockDefinition | undefined {
        const direct = this.blocks.get(key);
        if (direct) return direct;

        // Fallback: strip theme prefix (e.g., fw_hero_section → hero_section)
        // Theme-prefixed blocks reuse core editor components for preview
        const prefixMatch = key.match(/^[a-z]{2,4}_(.+)$/);
        if (prefixMatch) {
            return this.blocks.get(prefixMatch[1]);
        }

        return undefined;
    }

    getAll(): LandingBlockDefinition[] {
        return Array.from(this.blocks.values());
    }

    getCategorized(): Record<string, LandingBlockDefinition[]> {
        const categories: Record<string, LandingBlockDefinition[]> = {
            atomic: [],
            layout: [],
            general: [],
            marketing: [],
            ecommerce: [],
            patterns: [],
            custom: [],
        };

        this.blocks.forEach((block) => {
            const cat = block.category || 'custom';
            if (!categories[cat]) {
                categories[cat] = [];
            }
            categories[cat].push(block);
        });

        // Filter out empty categories
        return Object.fromEntries(
            Object.entries(categories).filter(([_, blocks]) => blocks.length > 0)
        );
    }
}

export const landingBlockRegistry = new BlockRegistry();
