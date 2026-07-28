export interface LayoutAssetSummary {
    id: number;
    kind: 'part' | 'template';
    name: string;
    slug: string;
    key?: string | null;
    category?: string | null;
    description?: string | null;
    layout?: string;
    content_raw?: any;
    content_html?: string | null;
    preview_url?: string | null;
    preview_image?: string | null;
    applies_to?: string[];
    is_system?: boolean;
    source_type?: string | null;
    source_name?: string | null;
}

export interface ReusablePartSelection {
    key: '__reusable_part__';
    label: string;
    isReusablePart: true;
    asset: LayoutAssetSummary;
    documentContent: any[];
    nestedBlocks: Array<{ type: string; data: Record<string, any> }>;
}

function cloneValue<T>(value: T): T {
    return JSON.parse(JSON.stringify(value));
}

export function extractLandingNodes(contentRaw: any): any[] {
    if (!contentRaw) {
        return [];
    }

    const nodes = Array.isArray(contentRaw)
        ? contentRaw
        : (contentRaw?.type === 'doc' && Array.isArray(contentRaw.content) ? contentRaw.content : []);

    return nodes.filter((node) => node?.type === 'landingBlock' && node?.attrs?.type);
}

export function extractNestedLandingBlocks(contentRaw: any): Array<{ type: string; data: Record<string, any> }> {
    return extractLandingNodes(contentRaw).map((node) => ({
        type: node.attrs.type,
        data: cloneValue(node.attrs.data || {}),
    }));
}

export function buildReusablePartSelection(asset: LayoutAssetSummary): ReusablePartSelection {
    return {
        key: '__reusable_part__',
        label: asset.name,
        isReusablePart: true,
        asset,
        documentContent: cloneValue(extractLandingNodes(asset.content_raw)),
        nestedBlocks: cloneValue(extractNestedLandingBlocks(asset.content_raw)),
    };
}
