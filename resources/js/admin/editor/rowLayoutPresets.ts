export interface RowLayoutPreset {
    id: string;
    label: string;
    widths: string[];
}

export interface RowBlockData {
    columns: number;
    gap: string;
    vertical_align: 'start' | 'center' | 'end';
    layout_preset: string;
    column_widths: string[];
    columns_data: Array<{ blocks: Array<Record<string, any>> }>;
    margin?: string;
    padding?: string;
}

export const ROW_LAYOUT_PRESETS: RowLayoutPreset[] = [
    { id: 'full', label: '1', widths: ['1'] },
    { id: 'halves', label: '1/2 + 1/2', widths: ['1/2', '1/2'] },
    { id: 'thirds', label: '1/3 + 1/3 + 1/3', widths: ['1/3', '1/3', '1/3'] },
    { id: 'quarter-half-quarter', label: '1/4 + 2/4 + 1/4', widths: ['1/4', '2/4', '1/4'] },
    { id: 'quarters', label: '1/4 + 1/4 + 1/4 + 1/4', widths: ['1/4', '1/4', '1/4', '1/4'] },
    { id: 'quarter-quarter-half', label: '1/4 + 1/4 + 1/2', widths: ['1/4', '1/4', '1/2'] },
    { id: 'half-quarter-quarter', label: '1/2 + 1/4 + 1/4', widths: ['1/2', '1/4', '1/4'] },
    { id: 'third-two-thirds', label: '1/3 + 2/3', widths: ['1/3', '2/3'] },
    { id: 'quarter-three-quarters', label: '1/4 + 3/4', widths: ['1/4', '3/4'] },
    { id: 'three-quarters-quarter', label: '3/4 + 1/4', widths: ['3/4', '1/4'] },
    { id: 'two-thirds-third', label: '2/3 + 1/3', widths: ['2/3', '1/3'] },
];

export const DEFAULT_ROW_LAYOUT_PRESET = 'halves';

export const ROW_GAP_OPTIONS = [
    {
        value: 'gap-0',
        label: 'No Gap',
        icon: '<svg viewBox="0 0 24 24" fill="currentColor"><rect x="6" y="5" width="5" height="14" /><rect x="13" y="5" width="5" height="14" /></svg>',
    },
    {
        value: 'gap-4',
        label: 'Small',
        icon: '<svg viewBox="0 0 24 24" fill="currentColor"><rect x="5" y="5" width="5" height="14" /><rect x="14" y="5" width="5" height="14" /></svg>',
    },
    {
        value: 'gap-8',
        label: 'Medium',
        icon: '<svg viewBox="0 0 24 24" fill="currentColor"><rect x="4" y="5" width="5" height="14" /><rect x="15" y="5" width="5" height="14" /></svg>',
    },
    {
        value: 'gap-16',
        label: 'Large',
        icon: '<svg viewBox="0 0 24 24" fill="currentColor"><rect x="2.5" y="5" width="5" height="14" /><rect x="16.5" y="5" width="5" height="14" /></svg>',
    },
];

export const ROW_VERTICAL_ALIGN_OPTIONS = [
    {
        value: 'start',
        label: 'Top',
        icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M4 4.5h16" stroke-linecap="round"/><rect x="6" y="7" width="4" height="7" fill="currentColor" stroke="none"/><rect x="14" y="7" width="4" height="7" fill="currentColor" stroke="none"/></svg>',
    },
    {
        value: 'center',
        label: 'Middle',
        icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M4 12h16" stroke-linecap="round"/><rect x="6" y="8.5" width="4" height="7" fill="currentColor" stroke="none"/><rect x="14" y="8.5" width="4" height="7" fill="currentColor" stroke="none"/></svg>',
    },
    {
        value: 'end',
        label: 'Bottom',
        icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M4 19.5h16" stroke-linecap="round"/><rect x="6" y="10" width="4" height="7" fill="currentColor" stroke="none"/><rect x="14" y="10" width="4" height="7" fill="currentColor" stroke="none"/></svg>',
    },
];

export function getRowLayoutPreset(presetId?: string | null): RowLayoutPreset | undefined {
    if (!presetId) {
        return undefined;
    }

    return ROW_LAYOUT_PRESETS.find((preset) => preset.id === presetId);
}

export function getDefaultRowWidths(columns: number): string[] {
    switch (columns) {
        case 1:
            return ['1'];
        case 2:
            return ['1/2', '1/2'];
        case 3:
            return ['1/3', '1/3', '1/3'];
        case 4:
            return ['1/4', '1/4', '1/4', '1/4'];
        default:
            return getRowLayoutPreset(DEFAULT_ROW_LAYOUT_PRESET)?.widths ?? ['1/2', '1/2'];
    }
}

export function buildRowTemplateColumns(widths: string[]): string {
    return widths
        .map((width) => `${fractionToNumber(width)}fr`)
        .join(' ');
}

export function normalizeRowData(source?: Record<string, any>): RowBlockData {
    const preset = getRowLayoutPreset(source?.layout_preset) ?? resolvePresetFromWidths(source?.column_widths);
    const fallbackColumns = Number(source?.columns) > 0 ? Number(source?.columns) : 2;
    const columnWidths = normalizeColumnWidths(source?.column_widths, preset?.widths, fallbackColumns);

    return {
        columns: columnWidths.length,
        gap: source?.gap || 'gap-8',
        vertical_align: normalizeVerticalAlign(source?.vertical_align),
        layout_preset: resolvePresetFromWidths(columnWidths)?.id ?? preset?.id ?? DEFAULT_ROW_LAYOUT_PRESET,
        column_widths: [...columnWidths],
        columns_data: normalizeColumnsData(source?.columns_data, columnWidths.length),
        margin: source?.margin || '',
        padding: source?.padding || '',
    };
}

export function applyRowLayout(source: Record<string, any>, presetId: string): RowBlockData {
    const preset = getRowLayoutPreset(presetId) ?? getRowLayoutPreset(DEFAULT_ROW_LAYOUT_PRESET)!;
    const current = normalizeRowData(source);

    return {
        ...current,
        columns: preset.widths.length,
        layout_preset: preset.id,
        column_widths: [...preset.widths],
        columns_data: normalizeColumnsData(current.columns_data, preset.widths.length),
    };
}

function normalizeColumnWidths(
    widths: unknown,
    presetWidths: string[] | undefined,
    fallbackColumns: number,
): string[] {
    if (Array.isArray(widths) && widths.length > 0) {
        return widths.map((width) => String(width));
    }

    if (presetWidths?.length) {
        return [...presetWidths];
    }

    return getDefaultRowWidths(fallbackColumns);
}

function normalizeColumnsData(columnsData: unknown, targetColumns: number): Array<{ blocks: Array<Record<string, any>> }> {
    const source = Array.isArray(columnsData) ? columnsData : [];
    const normalized = Array.from({ length: targetColumns }, (_, index) => ({
        blocks: cloneBlocks((source[index] as { blocks?: Array<Record<string, any>> } | undefined)?.blocks),
    }));

    if (source.length > targetColumns) {
        source.slice(targetColumns).forEach((column) => {
            const overflowBlocks = cloneBlocks((column as { blocks?: Array<Record<string, any>> } | undefined)?.blocks);
            normalized[targetColumns - 1].blocks.push(...overflowBlocks);
        });
    }

    return normalized;
}

function cloneBlocks(blocks: Array<Record<string, any>> | undefined): Array<Record<string, any>> {
    if (!Array.isArray(blocks)) {
        return [];
    }

    return blocks.map((block) => JSON.parse(JSON.stringify(block)));
}

function normalizeVerticalAlign(value: unknown): 'start' | 'center' | 'end' {
    if (value === 'center' || value === 'end') {
        return value;
    }

    return 'start';
}

function resolvePresetFromWidths(widths: unknown): RowLayoutPreset | undefined {
    if (!Array.isArray(widths) || widths.length === 0) {
        return undefined;
    }

    const signature = widths.map((width) => String(width)).join('|');
    return ROW_LAYOUT_PRESETS.find((preset) => preset.widths.join('|') === signature);
}

function fractionToNumber(value: string): number {
    if (value === '1') {
        return 1;
    }

    const [numerator, denominator] = value.split('/').map((part) => Number.parseFloat(part));
    if (!Number.isFinite(numerator) || !Number.isFinite(denominator) || denominator === 0) {
        return 1;
    }

    return Number((numerator / denominator).toFixed(4));
}
