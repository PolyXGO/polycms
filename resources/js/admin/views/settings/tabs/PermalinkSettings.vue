<template>
    <form @submit.prevent="$emit('save')" class="space-y-8">
        <div
            v-for="section in orderedSections"
            :key="section.id"
            class="space-y-4"
        >
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ $t(section.label) }}
                    </h2>
                    <p v-if="section.description" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {{ $t(section.description) }}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div
                    v-for="setting in section.items"
                    :key="setting.key"
                    class="space-y-2"
                >
                    <label :for="setting.key" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ $t(setting.label) }}
                    </label>
                    <div class="flex h-11 items-stretch overflow-hidden rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 focus-within:ring-2 focus-within:ring-indigo-500 focus-within:border-indigo-500">
                        <span class="flex items-center px-3 text-sm text-gray-500 dark:text-gray-400">
                            {{ buildBasePrefix(setting.key) }}
                        </span>
                        <input
                            :id="setting.key"
                            :value="getSettingValue(setting.key)"
                            @input="handleInput(setting, $event)"
                            type="text"
                            class="flex-1 border-0 bg-transparent px-3 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-0"
                            :placeholder="setting.input_props?.placeholder || ''"
                        />
                    </div>
                    <p v-if="setting.description" class="text-sm text-gray-500 dark:text-gray-400">
                        {{ $t(setting.description) }}
                    </p>
                    <p class="text-xs text-gray-400 dark:text-gray-500">
                        {{ $t('Preview:') }} {{ buildPreview(setting.key) }}
                    </p>
                </div>
            </div>
        </div>

        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
            <button
                type="submit"
                :disabled="saving"
                class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-60"
            >
                <svg
                    v-if="saving"
                    class="mr-2 h-4 w-4 animate-spin text-white"
                    fill="none"
                    viewBox="0 0 24 24"
                >
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
                {{ saving ? $t('Saving...') : $t('Save Changes') }}
            </button>
        </div>
    </form>
</template>

<script setup lang="ts">
import { computed, getCurrentInstance } from 'vue';
import { useSlugify } from '../../../composables/useSlugify';
import { useTranslation } from '../../../composables/useTranslation';

interface SettingDefinition {
    key: string;
    value: string;
    label: string;
    description: string;
    section?: string | null;
    section_label?: string | null;
    section_description?: string | null;
    section_order?: number;
    order?: number;
    allow_empty?: boolean;
    input_props?: Record<string, any>;
}

interface SectionGroup {
    id: string;
    label: string;
    description?: string | null;
    order: number;
    items: SettingDefinition[];
}

const props = defineProps<{
    settings: Record<string, SettingDefinition>;
    saving: boolean;
}>();

const emit = defineEmits<{
    (e: 'update', group: string, key: string, value: string): void;
    (e: 'save'): void;
}>();

const { slugify } = useSlugify();
const { t } = useTranslation();
const instance = getCurrentInstance();
const $t = instance?.appContext.config.globalProperties.$t || t;
const baseUrl = window.location.origin;

const orderedSections = computed<SectionGroup[]>(() => {
    const sectionMap = new Map<string, SectionGroup>();

    Object.values(props.settings || {}).forEach((setting) => {
        const sectionId = setting.section || 'general';
        if (!sectionMap.has(sectionId)) {
            sectionMap.set(sectionId, {
                id: sectionId,
                label: setting.section_label || capitalize(sectionId),
                description: setting.section_description || null,
                order: setting.section_order ?? 100,
                items: [],
            });
        }

        sectionMap.get(sectionId)!.items.push(setting);
    });

    const sections = Array.from(sectionMap.values());

    sections.forEach((section) => {
        section.items.sort((a, b) => (a.order ?? 100) - (b.order ?? 100));
    });

    return sections.sort((a, b) => a.order - b.order);
});

const getSettingValue = (key: string): string => {
    return props.settings?.[key]?.value ?? '';
};

const sanitizeSegment = (value: string | null | undefined, setting: SettingDefinition): string => {
    const allowEmpty = Boolean(setting.allow_empty);
    const trimmed = (value ?? '').trim().replace(/^\/+/, '').replace(/\/+$/, '');

    if (!trimmed) {
        return allowEmpty ? '' : '';
    }

    const segments = trimmed
        .split('/')
        .filter(Boolean)
        .map((segment) => slugify(segment));

    const sanitized = segments.filter(Boolean).join('/');

    if (!sanitized) {
        return allowEmpty ? '' : '';
    }

    return sanitized;
};

const onInput = (setting: SettingDefinition, rawValue: string) => {
    const sanitized = sanitizeSegment(rawValue, setting);
    emit('update', 'permalinks', setting.key, sanitized);
};

const handleInput = (setting: SettingDefinition, event: Event) => {
    const target = event.target as HTMLInputElement;
    onInput(setting, target.value);
};

const buildBasePrefix = (key: string): string => {
    switch (key) {
        case 'permalink_posts_archive':
        case 'permalink_posts_single':
        case 'permalink_products_archive':
        case 'permalink_products_single':
        case 'permalink_category_base':
        case 'permalink_post_tag_base':
        case 'permalink_product_tag_base':
        case 'permalink_user_base':
            return `${baseUrl}/`;
        case 'permalink_pages_single':
            return props.settings?.[key]?.allow_empty ? `${baseUrl}/` : `${baseUrl}/`;
        default:
            return `${baseUrl}/`;
    }
};

const buildPreview = (key: string): string => {
    const segment = getSettingValue(key);
    const slug = segment ? `/${segment}` : '';

    switch (key) {
        case 'permalink_posts_archive':
            return `${baseUrl}${slug || '/posts'}`;
        case 'permalink_posts_single':
            return segment ? `${baseUrl}/${segment}/sample-post` : `${baseUrl}/sample-post`;
        case 'permalink_pages_single':
            return segment ? `${baseUrl}/${segment}/about-us` : `${baseUrl}/about-us`;
        case 'permalink_products_archive':
            return `${baseUrl}${slug || '/products'}`;
        case 'permalink_products_single':
            return `${baseUrl}${slug || '/products'}/sample-product`;
        case 'permalink_category_base':
            return `${baseUrl}${slug || '/categories'}/sample-category`;
        case 'permalink_post_tag_base':
            return `${baseUrl}${slug || '/tags'}/sample-tag`;
        case 'permalink_product_tag_base':
            return `${baseUrl}${slug || '/product-tags'}/sample-tag`;
        case 'permalink_user_base':
            return `${baseUrl}${slug || '/author'}/john-doe`;
        default:
            return `${baseUrl}${slug}`;
    }
};

function capitalize(text: string): string {
    if (!text) {
        return '';
    }
    return text.charAt(0).toUpperCase() + text.slice(1);
}
</script>

