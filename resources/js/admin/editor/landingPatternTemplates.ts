import { applyRowLayout } from '@/admin/editor/rowLayoutPresets';

export const DEFAULT_DEMO_SHOWCASE_IMAGE = 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=1000&q=80';
export const DEFAULT_DEMO_SHOWCASE_VIDEO = 'https://www.youtube.com/watch?v=C_QAOi0_qpg';

function cloneValue<T>(value: T): T {
    return JSON.parse(JSON.stringify(value));
}

function createButtonRow() {
    return {
        ...applyRowLayout({
            columns: 2,
            layout_preset: 'halves',
            column_widths: ['1/2', '1/2'],
            gap: 'gap-4',
            vertical_align: 'center',
            columns_data: [
                {
                    blocks: [
                        {
                            type: 'button',
                            data: {
                                label: 'Start Now',
                                url: '#',
                                style: 'primary',
                                size: 'px-6 py-3 text-base',
                                alignment: 'full',
                            },
                        },
                    ],
                },
                {
                    blocks: [
                        {
                            type: 'button',
                            data: {
                                label: 'Learn More',
                                url: '#',
                                style: 'secondary',
                                size: 'px-6 py-3 text-base',
                                alignment: 'full',
                            },
                        },
                    ],
                },
            ],
        }, 'halves'),
    };
}

function createStepBlocks() {
    return [
        {
            type: 'heading',
            data: {
                text: 'Branding & Setup',
                level: 4,
                alignment: 'left',
                font_weight: 'font-bold',
                color: '',
            },
        },
        {
            type: 'text',
            data: {
                content: 'We customize everything with your logo and colors.',
                font_size: 'text-base',
                alignment: 'left',
                color: '#6b7280',
            },
        },
        {
            type: 'spacer',
            data: {
                height: 20,
            },
        },
        {
            type: 'heading',
            data: {
                text: 'Training & Handover',
                level: 4,
                alignment: 'left',
                font_weight: 'font-bold',
                color: '',
            },
        },
        {
            type: 'text',
            data: {
                content: 'We walk you through the admin panel.',
                font_size: 'text-base',
                alignment: 'left',
                color: '#6b7280',
            },
        },
    ];
}

export function createDemoShowcasePatternBlock() {
    return {
        key: 'row',
        defaultAttrs: {
            ...cloneValue(applyRowLayout({
                columns: 2,
                layout_preset: 'halves',
                column_widths: ['1/2', '1/2'],
                gap: 'gap-8',
                vertical_align: 'center',
                columns_data: [
                    {
                        blocks: [
                            {
                                type: 'video',
                                data: {
                                    url: DEFAULT_DEMO_SHOWCASE_VIDEO,
                                    preview_image: DEFAULT_DEMO_SHOWCASE_IMAGE,
                                    aspect_ratio: '16/10',
                                    caption: '',
                                },
                            },
                        ],
                    },
                    {
                        blocks: [
                            {
                                type: 'heading',
                                data: {
                                    text: 'From Zero to SaaS in 10 Days',
                                    level: 2,
                                    alignment: 'left',
                                    font_weight: 'font-bold',
                                    color: '',
                                },
                            },
                            {
                                type: 'spacer',
                                data: {
                                    height: 24,
                                },
                            },
                            {
                                type: 'row',
                                data: createButtonRow(),
                            },
                            {
                                type: 'spacer',
                                data: {
                                    height: 28,
                                },
                            },
                            ...createStepBlocks(),
                        ],
                    },
                ],
            }, 'halves')),
        },
    };
}
