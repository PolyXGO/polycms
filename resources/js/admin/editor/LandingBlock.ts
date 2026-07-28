import { Node, mergeAttributes, InputRule, PasteRule } from '@tiptap/core';
import { VueNodeViewRenderer } from '@tiptap/vue-3';
import LandingBlockNodeView from '../components/editor/LandingBlockNodeView.vue';
import { landingBlockRegistry } from './landingBlockRegistry';

export interface LandingBlockOptions {
    HTMLAttributes: Record<string, any>;
}

declare module '@tiptap/core' {
    interface Commands<ReturnType> {
        landingBlock: {
            /**
             * Add a landing block
             */
            setLandingBlock: (attributes: { type: string; [key: string]: any }) => ReturnType;
        };
    }
}

export const LandingBlock = Node.create<LandingBlockOptions>({
    name: 'landingBlock',

    group: 'block',

    atom: true,

    draggable: false,
    selectable: true,

    addAttributes() {
        return {
            id: {
                default: null,
                parseHTML: element => element.getAttribute('data-id'),
                renderHTML: attributes => {
                    return {
                        'data-id': attributes.id,
                    };
                },
            },
            type: {
                default: null,
                parseHTML: element => element.getAttribute('data-block-type'),
                renderHTML: attributes => {
                    return {
                        'data-block-type': attributes.type,
                    };
                },
            },
            data: {
                default: {},
                parseHTML: element => {
                    const dataAttr = element.getAttribute('data-block-data');
                    try {
                        return dataAttr ? JSON.parse(dataAttr) : {};
                    } catch (e) {
                        console.error('Failed to parse block data:', dataAttr);
                        return {};
                    }
                },
                renderHTML: attributes => {
                    return {
                        'data-block-data': JSON.stringify(attributes.data),
                    };
                },
            },
        };
    },

    parseHTML() {
        return [
            {
                tag: 'div[data-type="landing-block"]',
            },
            {
                tag: 'section.pricing',
                getAttrs: (element) => {
                    const el = element as HTMLElement;
                    const idAttr = el.getAttribute('id');
                    const productId = idAttr?.match(/pricing-matrix-(\d+)/)?.[1];
                    
                    // Detect style
                    let style = 'cards';
                    if (el.querySelector('.pricing-table-container')) style = 'table';
                    else if (el.querySelector('.pricing-list')) style = 'list';
                    
                    return {
                        type: 'pricing_matrix',
                        data: {
                            style,
                            product_id: productId ? parseInt(productId) : null
                        }
                    };
                }
            }
        ];
    },

    renderHTML({ HTMLAttributes }) {
        return ['div', mergeAttributes(this.options.HTMLAttributes, HTMLAttributes, { 'data-type': 'landing-block' })];
    },

    addNodeView() {
        return VueNodeViewRenderer(LandingBlockNodeView);
    },

    addInputRules() {
        return [
            new InputRule({
                find: /\[landing_block\s+type="([^"]+)"\s*(.*?)\]$/,
                handler: ({ state, range, match }) => {
                    const { tr } = state;
                    const type = match[1];
                    const attrsString = match[2];
                    const data: Record<string, any> = {};
                    
                    const attrRegex = /(\w+)="([^"]*)"/g;
                    let m;
                    while ((m = attrRegex.exec(attrsString)) !== null) {
                        data[m[1]] = m[2];
                    }

                    if (range.from !== range.to) {
                        tr.delete(range.from, range.to);
                    }

                    tr.replaceWith(range.from, range.to, this.type.create({
                        id: crypto.randomUUID(),
                        type,
                        data: {
                            ...(landingBlockRegistry.get(type)?.defaultAttrs || {}),
                            ...data,
                        },
                    }));
                },
            }),
        ];
    },

    addPasteRules() {
        return [
            new PasteRule({
                find: /\[landing_block\s+type="([^"]+)"\s*(.*?)\]/g,
                handler: ({ state, range, match }) => {
                    const { tr } = state;
                    const type = match[1];
                    const attrsString = match[2];
                    const data: Record<string, any> = {};
                    
                    const attrRegex = /(\w+)="([^"]*)"/g;
                    let m;
                    while ((m = attrRegex.exec(attrsString)) !== null) {
                        data[m[1]] = m[2];
                    }

                    tr.replaceWith(range.from, range.to, this.type.create({
                        id: crypto.randomUUID(),
                        type,
                        data: {
                            ...(landingBlockRegistry.get(type)?.defaultAttrs || {}),
                            ...data,
                        },
                    }));
                },
            }),
        ];
    },

    addCommands() {
        return {
            setLandingBlock: (attributes: { type: string; data?: any; attrs?: any }) => ({ commands }) => {
                return commands.insertContent({
                    type: this.name,
                    attrs: {
                        id: crypto.randomUUID(),
                        type: attributes.type,
                        data: attributes.data || attributes.attrs || landingBlockRegistry.get(attributes.type)?.defaultAttrs || {},
                    },
                });
            },
        };
    },
});
