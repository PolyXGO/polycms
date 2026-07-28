import type { Component } from 'vue';

export interface BlockTypeConfig {
    type: string;
    label: string;
    icon?: string;
    component: Component;
    category?: string;
    defaultAttrs?: Record<string, any>;
}

class BlockRegistry {
    private blocks: Map<string, BlockTypeConfig> = new Map();

    register(config: BlockTypeConfig): void {
        this.blocks.set(config.type, config);
    }

    get(type: string): BlockTypeConfig | undefined {
        return this.blocks.get(type);
    }

    getAll(): BlockTypeConfig[] {
        return Array.from(this.blocks.values());
    }

    getByCategory(category: string): BlockTypeConfig[] {
        return this.getAll().filter(block => block.category === category);
    }

    has(type: string): boolean {
        return this.blocks.has(type);
    }

    createBlock(type: string, attrs?: Record<string, any>): any {
        const config = this.get(type);
        if (!config) {
            throw new Error(`Block type "${type}" is not registered`);
        }

        return {
            type,
            attrs: { ...config.defaultAttrs, ...attrs },
        };
    }
}

export const blockRegistry = new BlockRegistry();

if (typeof window !== 'undefined') {
    (window as any).PolyCMSBlocks = {
        register: (config: BlockTypeConfig) => blockRegistry.register(config),
        get: (type: string) => blockRegistry.get(type),
        getAll: () => blockRegistry.getAll(),
    };
}
