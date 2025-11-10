import { ref, computed } from 'vue';
import axios from 'axios';

export interface EditorPanelDefinition {
    key: string;
    label: string;
    description?: string | null;
    order: number;
    area: 'main' | 'sidebar';
    component?: string | null;
    props?: Record<string, unknown>;
    icon?: string | null;
    collapsible?: boolean;
    collapsed?: boolean;
    context?: string | null;
    movable?: boolean;
}

interface EditorPanelState {
    main: EditorPanelDefinition[];
    sidebar: EditorPanelDefinition[];
}

interface EditorPanelPreferencesPayload {
    order: {
        main: string[];
        sidebar: string[];
    };
    collapsed: Record<string, boolean>;
}

export function useEditorPanels(initialType: string) {
    const panelType = ref<string>(initialType);
    const panels = ref<EditorPanelState>({ main: [], sidebar: [] });
    const collapsed = ref<Record<string, boolean>>({});
    const loading = ref<boolean>(false);
    const error = ref<string | null>(null);

    const panelOrderPayload = computed<EditorPanelPreferencesPayload>(() => ({
        order: {
            main: panels.value.main.map((panel) => panel.key),
            sidebar: panels.value.sidebar.map((panel) => panel.key),
        },
        collapsed: collapsed.value,
    }));

    const fetchPanels = async (typeOverride?: string) => {
        if (typeOverride) {
            panelType.value = typeOverride;
        }

        loading.value = true;
        error.value = null;

        try {
            const response = await axios.get(`/api/v1/editor-panels/${panelType.value}`);
            const data = response.data?.data ?? { main: [], sidebar: [] };

            panels.value = {
                main: normalizeArea(data.main ?? []),
                sidebar: normalizeArea(data.sidebar ?? []),
            };

            const collapsedState: Record<string, boolean> = {};
            for (const area of ['main', 'sidebar'] as const) {
                panels.value[area].forEach((panel) => {
                    collapsedState[panel.key] = !!panel.collapsed;
                });
            }
            collapsed.value = collapsedState;
        } catch (err: any) {
            error.value = err?.response?.data?.message || err?.message || 'Failed to load editor panels';
        } finally {
            loading.value = false;
        }
    };

    const persistPanels = async () => {
        try {
            await axios.put(`/api/v1/editor-panels/${panelType.value}`, panelOrderPayload.value);
        } catch (err: any) {
            // eslint-disable-next-line no-console
            console.error('Failed to persist editor panel preferences', err);
        }
    };

    const reorderPanel = (area: 'main' | 'sidebar', fromIndex: number, toIndex: number) => {
        const areaPanels = panels.value[area];
        if (!areaPanels[fromIndex]) {
            return;
        }

        const updated = [...areaPanels];
        const [moved] = updated.splice(fromIndex, 1);
        const insertAt = Math.max(0, Math.min(toIndex, updated.length));
        updated.splice(insertAt, 0, moved);

        panels.value = { ...panels.value, [area]: updated };
        void persistPanels();
    };

    const movePanelBetweenAreas = (
        sourceArea: 'main' | 'sidebar',
        targetArea: 'main' | 'sidebar',
        panelKey: string,
        targetIndex: number
    ) => {
        if (sourceArea === targetArea) {
            return;
        }

        const sourcePanels = panels.value[sourceArea];
        const targetPanels = panels.value[targetArea];
        const panelIndex = sourcePanels.findIndex((panel) => panel.key === panelKey);
        if (panelIndex === -1) {
            return;
        }

        const panel = { ...sourcePanels[panelIndex], area: targetArea };
        const newSource = [...sourcePanels];
        newSource.splice(panelIndex, 1);
        const newTarget = [...targetPanels];
        newTarget.splice(targetIndex, 0, panel);

        panels.value = {
            ...panels.value,
            [sourceArea]: newSource,
            [targetArea]: newTarget,
        };

        void persistPanels();
    };

    const toggleCollapsed = (panelKey: string) => {
        collapsed.value[panelKey] = !collapsed.value[panelKey];

        panels.value = {
            main: panels.value.main.map((panel) =>
                panel.key === panelKey ? { ...panel, collapsed: collapsed.value[panelKey] } : panel
            ),
            sidebar: panels.value.sidebar.map((panel) =>
                panel.key === panelKey ? { ...panel, collapsed: collapsed.value[panelKey] } : panel
            ),
        };

        void persistPanels();
    };

    const updatePanelProps = (panelKey: string, props: Record<string, unknown>) => {
        panels.value = {
            main: panels.value.main.map((panel) => (panel.key === panelKey ? { ...panel, props } : panel)),
            sidebar: panels.value.sidebar.map((panel) => (panel.key === panelKey ? { ...panel, props } : panel)),
        };
    };

    const setPanelType = (type: string) => {
        if (type && type !== panelType.value) {
            panelType.value = type;
        }
    };

    return {
        panels,
        loading,
        error,
        collapsed,
        panelType,
        fetchPanels,
        reorderPanel,
        movePanelBetweenAreas,
        toggleCollapsed,
        updatePanelProps,
        setPanelType,
    };
}

function normalizeArea(items: any[]): EditorPanelDefinition[] {
    return items.map((panel, index) => ({
        key: String(panel.key ?? `panel_${index}`),
        label: panel.label ?? panel.key ?? `Panel ${index + 1}`,
        description: panel.description ?? null,
        order: Number.isFinite(panel.order) ? Number(panel.order) : (index + 1) * 10,
        area: panel.area === 'sidebar' ? 'sidebar' : 'main',
        component: panel.component ?? null,
        props: panel.props ?? {},
        icon: panel.icon ?? null,
        collapsible: panel.collapsible ?? true,
        collapsed: panel.collapsed ?? false,
        context: panel.context ?? null,
        movable: panel.movable ?? true,
    }));
}

