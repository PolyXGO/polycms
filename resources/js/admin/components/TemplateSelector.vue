<template>
    <div class="template-selector">
        <label v-if="label" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            {{ label }}
        </label>
        <select
            v-model="computedValue"
            :disabled="loading"
            class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 disabled:opacity-50"
        >
            <option value="">Default (Main Theme)</option>
            <optgroup
                v-for="group in themeGroups"
                :key="group.theme_slug"
                :label="group.theme_name + (group.theme_role === 'main' ? ' (Main Theme)' : ' (Sub Theme)')"
            >
                <option
                    v-for="tpl in group.templates"
                    :key="tpl.template_id"
                    :value="tpl.template_id"
                >
                    {{ tpl.template_name }}
                </option>
            </optgroup>
        </select>
        <p v-if="help" class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ help }}</p>
        <p v-if="loading" class="mt-1 text-xs text-gray-400 dark:text-gray-500 flex items-center">
            <span class="inline-block h-3 w-3 border-2 border-gray-400 border-t-transparent rounded-full animate-spin mr-1"></span>
            Loading templates...
        </p>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch, computed } from 'vue';
import axios from 'axios';

interface TemplateEntry {
    theme_slug: string;
    theme_name: string;
    theme_role: 'main' | 'sub' | null;
    template_name: string;
    template_id?: string;
    is_group?: boolean;
    description: string | null;
    screenshot_url: string | null;
}

interface ThemeGroup {
    theme_slug: string;
    theme_name: string;
    theme_role: 'main' | 'sub' | null;
    templates: { template_id: string; template_name: string }[];
}

const props = defineProps<{
    modelValue: string | null;
    viewType: string;
    label?: string;
    help?: string;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: string | null): void;
}>();

const loading = ref(false);
const rawTemplates = ref<TemplateEntry[]>([]);

const computedValue = computed({
    get: () => {
        if (props.modelValue === null || props.modelValue === undefined || props.modelValue === '' || props.modelValue === 'null') {
            return '';
        }
        return props.modelValue;
    },
    set: (val) => {
        const newValue = (val === '' || val === null || val === 'null') ? null : val;
        emit('update:modelValue', newValue);
    }
});

/** Group flat template entries by theme_slug, preserving order */
const themeGroups = computed<ThemeGroup[]>(() => {
    const groupMap = new Map<string, ThemeGroup>();

    for (const entry of rawTemplates.value) {
        let group = groupMap.get(entry.theme_slug);
        if (!group) {
            group = {
                theme_slug: entry.theme_slug,
                theme_name: entry.theme_name,
                theme_role: entry.theme_role,
                templates: [],
            };
            groupMap.set(entry.theme_slug, group);
        }
        
        // Remove the theme name prefix (e.g., "FlexiDocs — ") from the template name if it's there
        const prefixRegex = new RegExp(`^${entry.theme_name}\\s*[-—|:]*\\s*`, 'i');
        const trimmedName = entry.template_name.replace(prefixRegex, '');
        
        group.templates.push({
            template_id: entry.template_id || entry.theme_slug,
            template_name: trimmedName,
        });
    }

    return Array.from(groupMap.values());
});

const fetchTemplates = async () => {
    if (!props.viewType) return;

    loading.value = true;
    try {
        const response = await axios.get('/api/v1/themes/templates', {
            params: { view_type: props.viewType },
        });
        rawTemplates.value = response.data.data || [];
    } catch (error) {
        console.error('Failed to load templates:', error);
        rawTemplates.value = [];
    } finally {
        loading.value = false;
    }
};

// Fetch on mount and when viewType changes
onMounted(fetchTemplates);
watch(() => props.viewType, fetchTemplates);
</script>
