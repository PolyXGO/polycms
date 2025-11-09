<template>
    <div class="space-y-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
    <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Widgets</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Drag widgets from the library into a sidebar or footer. Register new widget areas via code.
                </p>
            </div>
            <button
                type="button"
                @click="refreshAll"
                class="inline-flex items-center px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors"
                :disabled="widgetTypesLoading || widgetAreasLoading"
            >
                <svg
                    class="w-4 h-4 mr-2"
                    :class="{'animate-spin': widgetTypesLoading || widgetAreasLoading}"
                    viewBox="0 0 24 24"
                    fill="none"
                >
                    <path
                        d="M4 4v5h.582M4 4h5m11 11v5h-.581M20 20h-5"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                    <path
                        d="M5.346 14.652A7 7 0 0112 5a6.999 6.999 0 016.652 5.346M18.654 9.348A7 7 0 0112 19a6.999 6.999 0 01-6.652-5.346"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                </svg>
                Refresh
            </button>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
            <section class="xl:col-span-4 bg-white dark:bg-gray-800 rounded-lg shadow">
                <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Available Widgets</h2>
                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ widgetTypes.length }} types</span>
                </div>
                <div v-if="widgetTypesLoading" class="p-8 flex items-center justify-center">
                    <div class="h-8 w-8 border-2 border-indigo-500 border-t-transparent rounded-full animate-spin"></div>
                </div>
                <div v-else class="p-6 space-y-4 max-h-[70vh] overflow-y-auto">
                    <div
                        v-for="group in groupedWidgetTypes"
                        :key="group.key"
                        class="border border-gray-200 dark:border-gray-700 rounded-lg"
                    >
                        <div class="px-4 py-2 bg-gray-50 dark:bg-gray-900/40 border-b border-gray-200 dark:border-gray-700 text-sm font-semibold text-gray-700 dark:text-gray-200">
                            {{ group.label }}
                        </div>
                        <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                            <li
                                v-for="widget in group.widgets"
                                :key="widget.type"
                                class="px-4 py-3 cursor-move hover:bg-gray-50 dark:hover:bg-gray-900/40 transition-colors"
                                draggable="true"
                                @dragstart="onWidgetTypeDragStart(widget, $event)"
                                @dragend="resetDragState"
                            >
                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ widget.label }}
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    {{ widget.description || 'No description provided.' }}
                                </p>
                                <span class="inline-block mt-2 text-xs text-indigo-600 dark:text-indigo-300">
                                    {{ widget.type }}
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>
            </section>

            <section class="xl:col-span-5">
                <div v-if="widgetAreasLoading" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 flex items-center justify-center h-full">
                    <div class="h-8 w-8 border-2 border-indigo-500 border-t-transparent rounded-full animate-spin"></div>
                </div>
                <div v-else-if="!selectedArea" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 flex items-center justify-center text-sm text-gray-500 dark:text-gray-400">
                    No widget areas registered. Add areas via code.
                </div>
                <div
                    v-else
                    class="bg-white dark:bg-gray-800 rounded-lg shadow flex flex-col h-full"
                >
                    <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    {{ selectedArea.name }}
                                </h2>
                                <p v-if="selectedArea.description" class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                    {{ selectedArea.description }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                    Key: <span class="font-mono">{{ selectedArea.key }}</span>
                                </p>
                            </div>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium"
                                :class="selectedArea.locked ? 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300' : 'bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-200'">
                                {{ selectedArea.locked ? 'Core Area' : 'Custom Area' }}
                            </span>
                        </div>
                    </div>

                    <div
                        class="flex-1 overflow-y-auto"
                        :class="dragOverAreaId === selectedArea.id && draggingHasPayload ? 'bg-indigo-50/60 dark:bg-indigo-900/20' : ''"
                        @dragover="handleAreaDragOver(selectedArea, $event)"
                        @drop="handleAreaDrop(selectedArea, $event)"
                    >
                        <div
                            v-if="selectedArea.widgets.length === 0"
                            class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400"
                        >
                            Drag widgets here to activate them.
                        </div>
                        <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                            <li
                                v-for="widget in selectedArea.widgets"
                                :key="widget.id"
                                class="px-6 py-4"
                                :class="dragOverWidgetId === widget.id ? 'bg-indigo-50/70 dark:bg-indigo-900/30' : 'bg-transparent'"
                                draggable="true"
                                @dragstart="onWidgetInstanceDragStart(widget, $event)"
                                @dragend="resetDragState"
                                @dragover="handleWidgetDragOver(selectedArea, widget, $event)"
                                @drop="handleWidgetDrop(selectedArea, widget, $event)"
                            >
                                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                                                {{ getWidgetLabel(widget) }}
                                            </h3>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ widget.widget_type }}
                                            </span>
                                        </div>
                                        <p v-if="widget.definition?.description" class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            {{ widget.definition.description }}
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                            <input
                                                type="checkbox"
                                                v-model="widget.formActive"
                                                @change="updateWidgetActive(widget)"
                                                class="w-4 h-4 text-indigo-600 border-gray-300 dark:border-gray-600 rounded focus:ring-indigo-500"
                                            />
                                            Active
                                        </label>
                                        <button
                                            type="button"
                                            class="px-3 py-1.5 text-sm rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                                            @click="toggleWidgetExpanded(widget)"
                                        >
                                            {{ widget.isExpanded ? 'Collapse' : 'Configure' }}
                                        </button>
                                        <button
                                            type="button"
                                            class="px-3 py-1.5 text-sm rounded-lg border border-red-300 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30"
                                            @click="deleteWidget(selectedArea, widget)"
                                        >
                                            Remove
                                        </button>
                                    </div>
                                </div>

                                <div
                                    v-if="widget.isExpanded"
                                    class="mt-4 bg-gray-50 dark:bg-gray-900/20 border border-gray-200 dark:border-gray-700 rounded-lg p-4 space-y-4"
                                >
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Widget Title
                                        </label>
                                        <input
                                            v-model="widget.formTitle"
                                            type="text"
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                        />
                                    </div>

                                    <div v-if="widget.definition?.config_schema">
                                        <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-200 mb-3">Settings</h4>
                                        <div class="space-y-4">
                                            <div
                                                v-for="(field, key) in widget.definition.config_schema"
                                                :key="key"
                                                class="space-y-1"
                                            >
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    {{ field.label || key }}
                                                </label>

                                                <template v-if="field.type === 'textarea'">
                                                    <textarea
                                                        v-model="widget.formConfig[key]"
                                                        :rows="field.rows || 4"
                                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                                    ></textarea>
                                                </template>
                                                <template v-else-if="field.type === 'number'">
                                                    <input
                                                        type="number"
                                                        v-model.number="widget.formConfig[key]"
                                                        :min="field.min ?? undefined"
                                                        :max="field.max ?? undefined"
                                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                                    />
                                                </template>
                                                <template v-else-if="field.type === 'select'">
                                                    <select
                                                        v-model="widget.formConfig[key]"
                                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                                    >
                                                        <option
                                                            v-for="option in field.options || []"
                                                            :key="option.value"
                                                            :value="option.value"
                                                        >
                                                            {{ option.label }}
                                                        </option>
                                                    </select>
                                                </template>
                                                <template v-else-if="field.type === 'boolean'">
                                                    <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                                        <input
                                                            type="checkbox"
                                                            v-model="widget.formConfig[key]"
                                                            class="w-4 h-4 text-indigo-600 border-gray-300 dark:border-gray-600 rounded focus:ring-indigo-500"
                                                        />
                                                        Enable
                                                    </label>
                                                </template>
                                                <template v-else-if="field.type === 'tags'">
                                                    <input
                                                        type="text"
                                                        v-model="widget.formConfig[key]"
                                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                                        placeholder="Comma separated IDs (e.g. 1,2,3)"
                                                    />
                                                </template>
                                                <template v-else>
                                                    <input
                                                        type="text"
                                                        v-model="widget.formConfig[key]"
                                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                                    />
                                                </template>
                                                <p v-if="field.description" class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ field.description }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-end gap-2 pt-2">
                                        <button
                                            type="button"
                                            class="px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
                                            @click="revertWidgetChanges(widget)"
                                        >
                                            Reset
                                        </button>
                                        <button
                                            type="button"
                                            class="px-4 py-2 text-sm rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white disabled:opacity-60"
                                            :disabled="widget.isSaving"
                                            @click="saveWidget(widget)"
                                        >
                                            <svg
                                                v-if="widget.isSaving"
                                                class="w-4 h-4 mr-2 inline-block align-middle animate-spin"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                            >
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3-3-3-3v4a12 12 0 00-12 12h4z"></path>
                                            </svg>
                                            Save Widget
                                        </button>
                                    </div>
                                </div>
                            </li>
            </ul>
                    </div>
                </div>
            </section>

            <section class="xl:col-span-3 bg-white dark:bg-gray-800 rounded-lg shadow h-full">
                <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Widget Areas</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Click to manage widgets or drop to move items.</p>
                </div>
                <div class="max-h-[70vh] overflow-y-auto divide-y divide-gray-200 dark:divide-gray-700">
                    <button
                        v-for="area in widgetAreas"
                        :key="area.id"
                        type="button"
                        class="w-full text-left px-6 py-4 transition-colors"
                        :class="[
                            area.id === selectedAreaId ? 'bg-indigo-50 dark:bg-indigo-900/30' : 'hover:bg-gray-50 dark:hover:bg-gray-900/40',
                            dragOverAreaId === area.id && draggingHasPayload ? 'ring-2 ring-indigo-400 dark:ring-indigo-500' : ''
                        ]"
                        @click="selectArea(area.id)"
                        @dragover="handleAreaDragOver(area, $event)"
                        @drop="handleAreaDrop(area, $event)"
                    >
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ area.name }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ area.widgets.length }} widget{{ area.widgets.length === 1 ? '' : 's' }}
                                </p>
                            </div>
                            <span class="text-xs font-medium text-gray-400 dark:text-gray-500">
                                {{ area.key }}
                            </span>
                        </div>
                    </button>
                </div>
            </section>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, reactive } from 'vue';
import axios from 'axios';
import { useDialog } from '../../composables/useDialog';

interface WidgetFieldOption {
    value: string | number;
    label: string;
}

interface WidgetField {
    type: string;
    label: string;
    description?: string;
    rows?: number;
    options?: WidgetFieldOption[];
    default?: unknown;
    min?: number;
    max?: number;
}

interface WidgetDefinition {
    type: string;
    label: string;
    description?: string;
    icon?: string | null;
    category: string;
    config_schema: Record<string, WidgetField>;
    default_config: Record<string, unknown>;
}

interface ApiWidgetInstance {
    id: number;
    widget_type: string;
    title: string | null;
    config: Record<string, unknown>;
    order: number;
    active: boolean;
    definition: WidgetDefinition | null;
}

interface WidgetInstance extends ApiWidgetInstance {
    definition: WidgetDefinition | null;
    isExpanded: boolean;
    isSaving: boolean;
    formTitle: string;
    formConfig: Record<string, any>;
    formActive: boolean;
}

interface ApiWidgetArea {
    id: number;
    name: string;
    key: string;
    description: string | null;
    order: number;
    locked: boolean;
    widgets: ApiWidgetInstance[];
}

interface WidgetArea extends ApiWidgetArea {
    widgets: WidgetInstance[];
}

interface WidgetCategoryGroup {
    key: string;
    label: string;
    widgets: WidgetDefinition[];
}

const dialog = useDialog();

const widgetTypesLoading = ref(false);
const widgetAreasLoading = ref(false);

const widgetTypes = ref<WidgetDefinition[]>([]);
const widgetCategoryLabels = ref<Record<string, string>>({});
const widgetAreas = ref<WidgetArea[]>([]);
const selectedAreaId = ref<number | null>(null);

const draggingState = reactive<{
    type: WidgetDefinition | null;
    widgetId: number | null;
}>({
    type: null,
    widgetId: null,
});

const dragOverAreaId = ref<number | null>(null);
const dragOverWidgetId = ref<number | null>(null);

const groupedWidgetTypes = computed<WidgetCategoryGroup[]>(() => {
    const groups: Record<string, WidgetCategoryGroup> = {};

    widgetTypes.value.forEach((widget) => {
        const key = widget.category || 'general';
        if (!groups[key]) {
            groups[key] = {
                key,
                label: widgetCategoryLabels.value[key] || formatCategoryLabel(key),
                widgets: [],
            };
        }
        groups[key].widgets.push(widget);
    });

    return Object.values(groups)
        .map((group) => ({
            ...group,
            widgets: group.widgets.sort((a, b) => a.label.localeCompare(b.label)),
        }))
        .sort((a, b) => a.label.localeCompare(b.label));
});

const selectedArea = computed<WidgetArea | null>(() => {
    if (widgetAreas.value.length === 0) {
        return null;
    }
    if (selectedAreaId.value === null) {
        return widgetAreas.value[0];
    }
    return widgetAreas.value.find((area) => area.id === selectedAreaId.value) ?? widgetAreas.value[0];
});

const draggingHasPayload = computed(() => draggingState.type !== null || draggingState.widgetId !== null);

onMounted(async () => {
    await refreshAll();
});

async function refreshAll() {
    await Promise.all([loadWidgetTypes(), loadWidgetAreas(selectedAreaId.value ?? undefined)]);
}

function formatCategoryLabel(category: string): string {
    return category
        .split(/[-_]/)
        .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
        .join(' ');
}

async function loadWidgetTypes(): Promise<void> {
    widgetTypesLoading.value = true;
    try {
        const response = await axios.get('/api/v1/widgets/types');
        const data = response.data?.data;
        widgetTypes.value = data?.widgets ?? [];
        const categories = data?.categories ?? [];
        const labelMap: Record<string, string> = {};
        categories.forEach((cat: { key: string; label: string }) => {
            labelMap[cat.key] = cat.label;
        });
        widgetCategoryLabels.value = labelMap;

        widgetAreas.value.forEach((area) => {
            area.widgets.forEach((widget) => {
                widget.definition =
                    widget.definition ?? findWidgetDefinition(widget.widget_type) ?? null;
                widget.formConfig = prepareConfigForForm(widget.definition, widget.config ?? {});
            });
        });
    } catch (error) {
        console.error('Failed to load widget types:', error);
        dialog.error('Unable to load widget types. Please try again.');
    } finally {
        widgetTypesLoading.value = false;
    }
}

async function loadWidgetAreas(preserveSelectionId?: number): Promise<void> {
    widgetAreasLoading.value = true;
    try {
        const response = await axios.get('/api/v1/widget-areas');
        const areas: ApiWidgetArea[] = response.data?.data ?? [];
        widgetAreas.value = areas.map((area) => transformArea(area));

        if (widgetAreas.value.length === 0) {
            selectedAreaId.value = null;
        } else if (preserveSelectionId) {
            const exists = widgetAreas.value.some((area) => area.id === preserveSelectionId);
            selectedAreaId.value = exists ? preserveSelectionId : widgetAreas.value[0].id;
        } else if (!selectedAreaId.value) {
            selectedAreaId.value = widgetAreas.value[0].id;
        }
    } catch (error) {
        console.error('Failed to load widget areas:', error);
        dialog.error('Unable to load widget areas. Please try again.');
    } finally {
        widgetAreasLoading.value = false;
    }
}

function transformArea(area: ApiWidgetArea): WidgetArea {
    return {
        ...area,
        widgets: (area.widgets ?? []).map((widget) => transformWidget(widget)),
    };
}

function transformWidget(widget: ApiWidgetInstance): WidgetInstance {
    const definition = widget.definition ?? findWidgetDefinition(widget.widget_type) ?? null;
    return {
        ...widget,
        definition,
        isExpanded: false,
        isSaving: false,
        formTitle: widget.title ?? '',
        formConfig: prepareConfigForForm(definition, widget.config ?? {}),
        formActive: widget.active,
    };
}

function findWidgetDefinition(type: string): WidgetDefinition | undefined {
    return widgetTypes.value.find((widget) => widget.type === type);
}

function prepareConfigForForm(definition: WidgetDefinition | null, config: Record<string, unknown>): Record<string, any> {
    const formConfig: Record<string, any> = {};
    if (!definition) {
        return { ...config };
    }

    const schema = definition.config_schema || {};
    const defaults = definition.default_config || {};

    Object.entries(schema).forEach(([key, field]) => {
        const value = config[key] ?? defaults[key] ?? field.default ?? null;

        switch (field.type) {
            case 'boolean':
                formConfig[key] = Boolean(value);
                break;
            case 'number':
                formConfig[key] = value !== null && value !== undefined ? Number(value) : null;
                break;
            case 'tags':
                if (Array.isArray(value)) {
                    formConfig[key] = value.join(', ');
                } else if (typeof value === 'string') {
                    formConfig[key] = value;
                } else {
                    formConfig[key] = '';
                }
                break;
            default:
                formConfig[key] = value ?? '';
        }
    });

    return formConfig;
}

function prepareConfigForSave(widget: WidgetInstance): Record<string, unknown> {
    const definition = widget.definition;
    if (!definition) {
        return widget.formConfig;
    }

    const schema = definition.config_schema || {};
    const payload: Record<string, unknown> = {};

    Object.entries(schema).forEach(([key, field]) => {
        let value = widget.formConfig[key];

        switch (field.type) {
            case 'boolean':
                value = Boolean(value);
                break;
            case 'number':
                if (value === '' || value === null || value === undefined) {
                    value = null;
                } else {
                    value = Number(value);
                }
                break;
            case 'tags':
                if (Array.isArray(value)) {
                    value = value;
                } else if (typeof value === 'string') {
                    const items = value
                        .split(',')
                        .map((part) => part.trim())
                        .filter((part) => part.length > 0)
                        .map((part) => {
                            const numeric = Number(part);
                            return Number.isNaN(numeric) ? part : numeric;
                        });
                    value = items;
                } else {
                    value = [];
                }
                break;
            default:
                value = value ?? null;
        }

        payload[key] = value;
    });

    return payload;
}

function selectArea(areaId: number): void {
    selectedAreaId.value = areaId;
}

function onWidgetTypeDragStart(widget: WidgetDefinition, event: DragEvent): void {
    draggingState.type = widget;
    draggingState.widgetId = null;
    dragOverAreaId.value = null;
    dragOverWidgetId.value = null;
    event.dataTransfer?.setData('text/plain', widget.type);
    event.dataTransfer?.setDragImage(createDragImage(widget.label), 0, 0);
}

function onWidgetInstanceDragStart(widget: WidgetInstance, event: DragEvent): void {
    draggingState.type = null;
    draggingState.widgetId = widget.id;
    dragOverAreaId.value = null;
    dragOverWidgetId.value = null;
    event.dataTransfer?.setData('text/plain', String(widget.id));
    event.dataTransfer?.setDragImage(createDragImage(getWidgetLabel(widget)), 0, 0);
}

function handleAreaDragOver(area: WidgetArea, event: DragEvent): void {
    if (!draggingHasPayload.value) return;
    event.preventDefault();
    event.stopPropagation();
    dragOverAreaId.value = area.id;
    dragOverWidgetId.value = null;
    if (event.dataTransfer) {
        event.dataTransfer.dropEffect = 'move';
    }
}

async function handleAreaDrop(area: WidgetArea, event: DragEvent): Promise<void> {
    if (!draggingHasPayload.value) return;
    event.preventDefault();
    event.stopPropagation();
    await commitDrop(area, area.widgets.length);
    resetDragState();
}

function handleWidgetDragOver(area: WidgetArea, widget: WidgetInstance, event: DragEvent): void {
    if (!draggingHasPayload.value) return;
    event.preventDefault();
    event.stopPropagation();
    dragOverAreaId.value = area.id;
    dragOverWidgetId.value = widget.id;
    if (event.dataTransfer) {
        event.dataTransfer.dropEffect = 'move';
    }
}

async function handleWidgetDrop(area: WidgetArea, widget: WidgetInstance, event: DragEvent): Promise<void> {
    if (!draggingHasPayload.value) return;
    event.preventDefault();
    event.stopPropagation();
    const targetIndex = Math.max(area.widgets.findIndex((item) => item.id === widget.id), 0);
    await commitDrop(area, targetIndex);
    resetDragState();
}

async function commitDrop(targetArea: WidgetArea, targetIndex: number): Promise<void> {
    const areaRef = widgetAreas.value.find((area) => area.id === targetArea.id);
    if (!areaRef) return;

    if (draggingState.type) {
        await addWidgetByType(areaRef, draggingState.type, targetIndex);
    } else if (draggingState.widgetId !== null) {
        await moveWidgetById(draggingState.widgetId, areaRef, targetIndex);
    }
}

async function addWidgetByType(area: WidgetArea, definition: WidgetDefinition, targetIndex: number): Promise<void> {
    try {
        const response = await axios.post('/api/v1/widget-instances', {
            widget_area_id: area.id,
            widget_type: definition.type,
        });

        const newWidget = transformWidget(response.data?.data as ApiWidgetInstance);
        const areaRef = widgetAreas.value.find((item) => item.id === area.id);
        if (!areaRef) return;

        const insertIndex = Math.min(Math.max(targetIndex, 0), areaRef.widgets.length);
        areaRef.widgets.splice(insertIndex, 0, newWidget);
        await persistAreaOrder(areaRef);
        selectedAreaId.value = area.id;
        dialog.success('Widget added.');
    } catch (error: any) {
        console.error('Failed to add widget:', error);
        const message = error?.response?.data?.message ?? 'Unable to add widget.';
        dialog.error(message);
        await loadWidgetAreas(selectedAreaId.value ?? undefined);
    }
}

async function moveWidgetById(widgetId: number, targetArea: WidgetArea, targetIndex: number): Promise<void> {
    const sourceArea = widgetAreas.value.find((area) =>
        area.widgets.some((widget) => widget.id === widgetId)
    );

    if (!sourceArea) return;

    const sourceIndex = sourceArea.widgets.findIndex((widget) => widget.id === widgetId);
    if (sourceIndex === -1) return;

    const [widget] = sourceArea.widgets.splice(sourceIndex, 1);
    const targetAreaRef = widgetAreas.value.find((area) => area.id === targetArea.id);

    if (!targetAreaRef) return;

    if (sourceArea.id !== targetAreaRef.id) {
        try {
            await axios.put(`/api/v1/widget-instances/${widgetId}`, {
                widget_area_id: targetAreaRef.id,
            });
        } catch (error: any) {
            console.error('Failed to move widget:', error);
            const message = error?.response?.data?.message ?? 'Unable to move widget.';
            dialog.error(message);
            await loadWidgetAreas(selectedAreaId.value ?? undefined);
            return;
        }
    }

    const insertIndex = Math.min(Math.max(targetIndex, 0), targetAreaRef.widgets.length);
    targetAreaRef.widgets.splice(insertIndex, 0, widget);

    if (targetAreaRef.id !== sourceArea.id) {
        await persistAreaOrder(sourceArea);
    }
    await persistAreaOrder(targetAreaRef);
    selectedAreaId.value = targetAreaRef.id;
}

async function persistAreaOrder(area: WidgetArea): Promise<void> {
    if (area.widgets.length === 0) {
        return;
    }

    const ids = area.widgets.map((widget) => widget.id);
    try {
        await axios.post(`/api/v1/widget-areas/${area.id}/reorder`, {
            widget_ids: ids,
        });
        area.widgets.forEach((widget, index) => {
            widget.order = (index + 1) * 10;
        });
    } catch (error) {
        console.error('Failed to persist widget order:', error);
        dialog.error('Unable to reorder widgets. The list will be refreshed.');
        await loadWidgetAreas(selectedAreaId.value ?? undefined);
    }
}

async function saveWidget(widget: WidgetInstance): Promise<void> {
    widget.isSaving = true;
    try {
        const payload = {
            title: widget.formTitle,
            config: prepareConfigForSave(widget),
        };
        await axios.put(`/api/v1/widget-instances/${widget.id}`, payload);
        widget.title = widget.formTitle;
        widget.config = payload.config;
        dialog.success('Widget saved.');
    } catch (error: any) {
        console.error('Failed to save widget:', error);
        const message = error?.response?.data?.message ?? 'Unable to save widget.';
        dialog.error(message);
    } finally {
        widget.isSaving = false;
    }
}

async function updateWidgetActive(widget: WidgetInstance): Promise<void> {
    try {
        await axios.put(`/api/v1/widget-instances/${widget.id}`, {
            active: widget.formActive,
        });
        widget.active = widget.formActive;
    } catch (error: any) {
        console.error('Failed to update widget status:', error);
        widget.formActive = widget.active;
        const message = error?.response?.data?.message ?? 'Unable to update widget status.';
        dialog.error(message);
    }
}

async function deleteWidget(area: WidgetArea, widget: WidgetInstance): Promise<void> {
    const confirmed = await dialog.confirm({
        title: 'Remove Widget',
        message: `Remove "${getWidgetLabel(widget)}" from this area?`,
        type: 'danger',
        confirmText: 'Remove',
    });

    if (!confirmed) {
        return;
    }

    widget.isSaving = true;
    try {
        await axios.delete(`/api/v1/widget-instances/${widget.id}`);
        const areaRef = widgetAreas.value.find((item) => item.id === area.id);
        if (areaRef) {
            areaRef.widgets = areaRef.widgets.filter((item) => item.id !== widget.id);
            await persistAreaOrder(areaRef);
        }
        dialog.success('Widget removed.');
    } catch (error: any) {
        console.error('Failed to delete widget:', error);
        const message = error?.response?.data?.message ?? 'Unable to remove widget.';
        dialog.error(message);
        await loadWidgetAreas(selectedAreaId.value ?? undefined);
    } finally {
        widget.isSaving = false;
    }
}

function revertWidgetChanges(widget: WidgetInstance): void {
    widget.formTitle = widget.title ?? '';
    widget.formConfig = prepareConfigForForm(widget.definition, widget.config ?? {});
    widget.formActive = widget.active;
}

function toggleWidgetExpanded(widget: WidgetInstance): void {
    widget.isExpanded = !widget.isExpanded;
}

function getWidgetLabel(widget: WidgetInstance): string {
    return widget.definition?.label ?? widget.widget_type;
}

function resetDragState(): void {
    draggingState.type = null;
    draggingState.widgetId = null;
    dragOverAreaId.value = null;
    dragOverWidgetId.value = null;
}

function createDragImage(label: string): HTMLElement {
    const el = document.createElement('div');
    el.style.position = 'absolute';
    el.style.top = '-9999px';
    el.style.left = '-9999px';
    el.style.padding = '6px 10px';
    el.style.borderRadius = '6px';
    el.style.background = '#4f46e5';
    el.style.color = '#fff';
    el.style.fontSize = '12px';
    el.style.boxShadow = '0 4px 10px rgba(79,70,229,0.3)';
    el.textContent = label;
    document.body.appendChild(el);
    setTimeout(() => document.body.removeChild(el), 0);
    return el;
}
</script>
