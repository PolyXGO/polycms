import { blockRegistry } from '@/admin/core/BlockRegistry';
import HeadingBlock from './types/HeadingBlock.vue';
import ParagraphBlock from './types/ParagraphBlock.vue';
import ImageBlock from './types/ImageBlock.vue';
import GridBlock from './types/GridBlock.vue';

/**
 * Register core block types
 */
export function registerCoreBlocks() {
    // Heading Block
    blockRegistry.register({
        type: 'heading',
        label: 'Heading',
        category: 'Text',
        component: HeadingBlock,
        defaultAttrs: {
            level: 2,
            text: '',
        },
    });

    // Paragraph Block
    blockRegistry.register({
        type: 'paragraph',
        label: 'Paragraph',
        category: 'Text',
        component: ParagraphBlock,
        defaultAttrs: {
            text: '',
        },
    });

    // Image Block
    blockRegistry.register({
        type: 'image',
        label: 'Image',
        category: 'Media',
        component: ImageBlock,
        defaultAttrs: {
            url: '',
            media_id: null,
            alt: '',
        },
    });

    // Grid Block
    blockRegistry.register({
        type: 'grid',
        label: 'Grid',
        category: 'Layout',
        component: GridBlock,
        defaultAttrs: {
            columns: 2,
            blocks: [],
        },
    });
}
