<template>
    <div class="space-y-6">
        <header class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Theme Options</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Control typography, colors, and layout tokens used by the active theme.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <button
                    type="button"
                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                    :disabled="loading || saving"
                    @click="resetAll"
                >
                    Reset to Defaults
                </button>
                <button
                    type="button"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors disabled:opacity-60 disabled:cursor-not-allowed"
                    :disabled="loading || saving || !isDirty"
                    @click="saveOptions"
                >
                    <span v-if="saving" class="flex items-center">
                        <svg class="w-4 h-4 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Saving...
                    </span>
                    <span v-else>Save Changes</span>
                </button>
            </div>
        </header>

        <div v-if="loading" class="bg-white dark:bg-gray-800 rounded-lg shadow p-12 text-center">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
            <p class="mt-3 text-gray-600 dark:text-gray-400 text-sm">Loading theme options...</p>
        </div>

        <div v-else-if="sections.length === 0" class="bg-white dark:bg-gray-800 rounded-lg shadow p-12 text-center">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">No Theme Options Registered</h2>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                The current theme does not expose customizable options. Developers can add settings via the <code>settings.defaults</code> filter.
            </p>
        </div>

        <template v-else>
            <section
                v-for="section in sections"
                :key="section.key"
                class="bg-white dark:bg-gray-800 rounded-lg shadow"
            >
                <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ section.label }}</h2>
                        <p v-if="section.description" class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ section.description }}</p>
                    </div>
                </div>

                <div class="p-6 space-y-8">
                    <div
                        v-for="category in section.categories"
                        :key="category.key"
                        class="space-y-4"
                    >
                        <div v-if="category.label" class="flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide">{{ category.label }}</h3>
                        </div>
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <div
                                v-for="setting in category.items"
                                :key="setting.key"
                                class="bg-gray-50 dark:bg-gray-900/30 border border-gray-200 dark:border-gray-700 rounded-lg p-5 space-y-3"
                            >
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <label class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ setting.label }}</label>
                                        <p v-if="setting.description" class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ setting.description }}</p>
                                    </div>
                                    <button
                                        type="button"
                                        class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline"
                                        :disabled="!canReset(setting)"
                                        @click="resetSetting(setting)"
                                    >
                                        Reset
                                    </button>
                                </div>

                                <div>
                                    <template v-if="setting.type === 'color'">
                                        <div class="flex items-center gap-3">
                                            <input
                                                type="color"
                                                v-model="setting.value"
                                                class="h-10 w-16 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800"
                                            />
                                            <input
                                                type="text"
                                                v-model="setting.value"
                                                class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/50 shadow-sm"
                                            />
                                        </div>
                                    </template>

                                    <template v-else-if="setting.type === 'select'">
                                        <select
                                            v-model="setting.value"
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/50 shadow-sm appearance-none"
                                        >
                                            <option
                                                v-for="option in setting.options"
                                                :key="option.value"
                                                :value="option.value"
                                                class="bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
                                            >
                                                {{ option.label }}
                                            </option>
                                        </select>
                                    </template>

                                    <template v-else-if="setting.type === 'number'">
                                        <div class="flex items-center gap-3">
                                            <input
                                                type="number"
                                                v-model.number="setting.value"
                                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/50 shadow-sm"
                                                :min="setting.input_props?.min"
                                                :max="setting.input_props?.max"
                                                :step="setting.input_props?.step || 'any'"
                                            />
                                            <span
                                                v-if="setting.input_props?.suffix"
                                                class="text-sm text-gray-500 dark:text-gray-400"
                                            >
                                                {{ setting.input_props.suffix }}
                                            </span>
                                        </div>
                                    </template>

                                    <template v-else-if="setting.type === 'textarea'">
                                        <textarea
                                            v-model="setting.value"
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/50 shadow-sm"
                                            rows="3"
                                        ></textarea>
                                    </template>

                                    <template v-else-if="setting.type === 'toggle' || setting.type === 'boolean'">
                                        <FormToggle
                                            :name="setting.key"
                                            :modelValue="['true', '1', true, 1].includes(setting.value)"
                                            @update:modelValue="setting.value = $event"
                                        />
                                    </template>

                                    <template v-else>
                                        <input
                                            type="text"
                                            v-model="setting.value"
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/50 shadow-sm"
                                        />
                                    </template>

                                    <p class="text-xs text-gray-400 dark:text-gray-500" v-if="setting.type === 'number' && setting.input_props?.step">
                                        Step: {{ setting.input_props.step }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </template>

        <!-- Floating Actions -->
        <div class="editor-floating-actions" style="right: 32px">
            <button 
                type="button" 
                class="editor-floating-actions__primary" 
                :disabled="loading || saving || !isDirty" 
                @click="saveOptions"
                :title="saving ? 'Saving...' : 'Save Changes'"
            >
                <svg v-if="!saving" class="w-6 h-6" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M19 21H5C4.44772 21 4 20.5523 4 20V4C4 3.44772 4.44772 3 5 3H16L20 7V20C20 20.5523 19.5523 21 19 21Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                    <path d="M17 21V13H7V21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                    <path d="M7 3V8H15V3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
                <svg v-else class="animate-spin h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import axios from 'axios';
import { useDialog } from '../../composables/useDialog';
import FormToggle from '../../components/forms/FormToggle.vue';

interface ThemeSettingOption {
    label: string;
    value: any;
}

interface ThemeSetting {
    key: string;
    label: string;
    description?: string;
    type: string;
    value: any;
    default: any;
    options?: ThemeSettingOption[];
    section?: string | null;
    section_label?: string | null;
    section_description?: string | null;
    section_order?: number | null;
    category?: string | null;
    category_label?: string | null;
    category_order?: number | null;
    order?: number | null;
    input_props?: Record<string, any> | null;
}

interface ThemeCategoryGroup {
    key: string;
    label: string | null;
    order: number;
    items: ThemeSetting[];
}

interface ThemeSectionGroup {
    key: string;
    label: string;
    description?: string | null;
    order: number;
    categories: ThemeCategoryGroup[];
}

const dialog = useDialog();
const loading = ref(true);
const saving = ref(false);
const settings = ref<ThemeSetting[]>([]);
const initialValues = ref<Record<string, any>>({});

const formatLabel = (value: string): string => {
    return value
        .replace(/[-_]/g, ' ')
        .replace(/\s+/g, ' ')
        .replace(/(^|\s)\w/g, (match) => match.toUpperCase());
};

const parseSettingValue = (setting: ThemeSetting, rawValue: any): any => {
    if (setting.type === 'number') {
        const numeric = Number(rawValue);
        return Number.isNaN(numeric) ? null : numeric;
    }

    let parsedValue = rawValue ?? (setting.type === 'color' ? '#000000' : '');

    // If it's a select field and the value is empty or not in options, fallback to default
    if (setting.type === 'select' && setting.options) {
        const isValidOption = setting.options.some(opt => String(opt.value) === String(parsedValue));
        if (!isValidOption) {
            parsedValue = setting.default ?? '';
        }
    }

    return parsedValue;
};

const canReset = (setting: ThemeSetting): boolean => {
    const defaultValue = parseSettingValue(setting, setting.default);
    return !isEqual(setting.value, defaultValue);
};

const isEqual = (a: any, b: any): boolean => {
    if (a === undefined && b === undefined) {
        return true;
    }

    if (typeof a === 'number' || typeof b === 'number') {
        return Number(a) === Number(b);
    }

    return String(a ?? '') === String(b ?? '');
};

const sections = computed<ThemeSectionGroup[]>(() => {
    const sectionMap = new Map<string, ThemeSectionGroup>();

    settings.value.forEach((setting) => {
        const sectionKey = setting.section ?? 'general';
        if (!sectionMap.has(sectionKey)) {
            sectionMap.set(sectionKey, {
                key: sectionKey,
                label: setting.section_label ?? formatLabel(sectionKey),
                description: setting.section_description ?? null,
                order: setting.section_order ?? 100,
                categories: [],
            });
        }

        const section = sectionMap.get(sectionKey)!;
        const categoryKey = setting.category ?? '__default__';
        let category = section.categories.find((item) => item.key === categoryKey);

        if (!category) {
            category = {
                key: categoryKey,
                label: setting.category_label ?? (setting.category ? formatLabel(setting.category) : null),
                order: setting.category_order ?? 100,
                items: [],
            };
            section.categories.push(category);
        }

        category.items.push(setting);
    });

    return Array.from(sectionMap.values())
        .map((section) => ({
            ...section,
            categories: section.categories
                .sort((a, b) => a.order - b.order)
                .map((category) => ({
                    ...category,
                    items: category.items.sort((a, b) => (a.order ?? 100) - (b.order ?? 100)),
                })),
        }))
        .sort((a, b) => a.order - b.order);
});

const isDirty = computed(() =>
    settings.value.some((setting) => !isEqual(setting.value, initialValues.value[setting.key]))
);

const loadOptions = async () => {
    loading.value = true;

    try {
        const response = await axios.get('/api/v1/settings/group/theme_options');
        const data = response.data?.data ?? {};

        const loadedSettings: ThemeSetting[] = Object.values(data).map((item: any) => {
            let normalizedOptions: ThemeSettingOption[] = [];
            if (item.options) {
                if (Array.isArray(item.options)) {
                    normalizedOptions = item.options.map((opt: any) => {
                        if (typeof opt === 'object' && opt !== null) {
                            return { label: opt.label ?? opt.value, value: opt.value };
                        }
                        return { label: String(opt), value: opt };
                    });
                } else if (typeof item.options === 'object') {
                    normalizedOptions = Object.entries(item.options).map(([key, val]) => ({
                        value: key,
                        label: String(val)
                    }));
                }
            }

            const normalized: ThemeSetting = {
                key: item.key,
                label: item.label ?? formatLabel(item.key),
                description: item.description ?? '',
                type: item.type ?? 'string',
                value: item.value,
                default: item.default ?? item.value ?? null,
                options: normalizedOptions,
                section: item.section ?? null,
                section_label: item.section_label ?? null,
                section_description: item.section_description ?? null,
                section_order: item.section_order ?? null,
                category: item.category ?? null,
                category_label: item.category_label ?? null,
                category_order: item.category_order ?? null,
                order: item.order ?? null,
                input_props: item.input_props ?? {},
            };

            normalized.value = parseSettingValue(normalized, normalized.value ?? normalized.default);
            normalized.default = parseSettingValue(normalized, normalized.default);

            return normalized;
        });

        settings.value = loadedSettings;
        initialValues.value = loadedSettings.reduce<Record<string, any>>((acc, setting) => {
            acc[setting.key] = setting.value;
            return acc;
        }, {});
    } catch (error: any) {
        console.error('Failed to load theme options:', error);
        dialog.error(error?.response?.data?.message ?? 'Unable to load theme options.');
    } finally {
        loading.value = false;
    }
};

const resetSetting = (setting: ThemeSetting) => {
    setting.value = parseSettingValue(setting, setting.default);
};

const resetAll = async () => {
    const confirmed = await dialog.confirm({
        title: 'Reset Theme Options',
        message: 'Restore all theme settings to their default values?',
        confirmText: 'Reset',
        type: 'danger',
    });

    if (!confirmed) {
        return;
    }

    settings.value.forEach((setting) => {
        setting.value = parseSettingValue(setting, setting.default);
    });
};

const saveOptions = async () => {
    saving.value = true;

    try {
        const payload: Record<string, any> = {};

        settings.value.forEach((setting) => {
            let value = setting.value;

            if (setting.type === 'number') {
                value = value === null || value === '' ? null : Number(value);
            }

            payload[setting.key] = {
                value,
                type: setting.type,
            };
        });

        await axios.put('/api/v1/settings/group/theme_options', {
            settings: payload,
        });

        initialValues.value = settings.value.reduce<Record<string, any>>((acc, setting) => {
            acc[setting.key] = setting.value;
            return acc;
        }, {});

        dialog.success('Theme options updated.');
    } catch (error: any) {
        console.error('Failed to save theme options:', error);
        dialog.error(error?.response?.data?.message ?? 'Unable to save theme options.');
    } finally {
        saving.value = false;
    }
};

onMounted(() => {
    loadOptions();
});
</script>

<style scoped>
.editor-floating-actions {
    position: fixed;
    bottom: 32px;
    right: 32px;
    z-index: 50;
    display: flex;
    gap: 12px;
    align-items: center;
    transition: right 0.3s ease;
}

.editor-floating-actions__primary {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background-color: var(--color-indigo-600, #4f46e5);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 10px 25px -5px rgba(79, 70, 229, 0.4), 0 8px 10px -6px rgba(79, 70, 229, 0.3);
    border: none;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.editor-floating-actions__primary:hover {
    transform: translateY(-4px) scale(1.05);
    box-shadow: 0 14px 28px -5px rgba(79, 70, 229, 0.5), 0 10px 10px -6px rgba(79, 70, 229, 0.4);
    background-color: var(--color-indigo-700, #4338ca);
}

.editor-floating-actions__primary:active {
    transform: translateY(0) scale(0.95);
    box-shadow: 0 6px 16px -5px rgba(79, 70, 229, 0.4);
}

.editor-floating-actions__primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}
</style>
