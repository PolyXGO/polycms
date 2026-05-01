<template>
    <div>
        <form @submit.prevent="$emit('save')" class="space-y-8">
            <div class="mb-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                    {{ $t('Template Defaults') }}
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ $t('Select the default template for each entity type. These templates will be used if an entity does not have a specific template assigned.') }}
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Posts Show -->
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ getSettingLabel('template_default_posts_show') }}
                    </label>
                    <TemplateSelector
                        view-type="posts.show"
                        :model-value="getSettingValue('template_default_posts_show')"
                        @update:model-value="updateValue('template_default_posts_show', $event)"
                    />
                    <p class="text-xs text-gray-500">{{ getSettingDescription('template_default_posts_show') }}</p>
                </div>

                <!-- Posts Index -->
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ getSettingLabel('template_default_posts_index') }}
                    </label>
                    <TemplateSelector
                        view-type="posts.index"
                        :model-value="getSettingValue('template_default_posts_index')"
                        @update:model-value="updateValue('template_default_posts_index', $event)"
                    />
                    <p class="text-xs text-gray-500">{{ getSettingDescription('template_default_posts_index') }}</p>
                </div>

                <!-- Pages Show -->
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ getSettingLabel('template_default_pages_show') }}
                    </label>
                    <TemplateSelector
                        view-type="pages.show"
                        :model-value="getSettingValue('template_default_pages_show')"
                        @update:model-value="updateValue('template_default_pages_show', $event)"
                    />
                    <p class="text-xs text-gray-500">{{ getSettingDescription('template_default_pages_show') }}</p>
                </div>

                <!-- Products Show -->
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ getSettingLabel('template_default_products_show') }}
                    </label>
                    <TemplateSelector
                        view-type="products.show"
                        :model-value="getSettingValue('template_default_products_show')"
                        @update:model-value="updateValue('template_default_products_show', $event)"
                    />
                    <p class="text-xs text-gray-500">{{ getSettingDescription('template_default_products_show') }}</p>
                </div>

                <!-- Products Index -->
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ getSettingLabel('template_default_products_index') }}
                    </label>
                    <TemplateSelector
                        view-type="products.index"
                        :model-value="getSettingValue('template_default_products_index')"
                        @update:model-value="updateValue('template_default_products_index', $event)"
                    />
                    <p class="text-xs text-gray-500">{{ getSettingDescription('template_default_products_index') }}</p>
                </div>

                <!-- Categories Show -->
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ getSettingLabel('template_default_categories_show') }}
                    </label>
                    <TemplateSelector
                        view-type="categories.show"
                        :model-value="getSettingValue('template_default_categories_show')"
                        @update:model-value="updateValue('template_default_categories_show', $event)"
                    />
                    <p class="text-xs text-gray-500">{{ getSettingDescription('template_default_categories_show') }}</p>
                </div>

                <!-- Product Categories Show -->
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ getSettingLabel('template_default_product_categories_show') }}
                    </label>
                    <TemplateSelector
                        view-type="product-categories.show"
                        :model-value="getSettingValue('template_default_product_categories_show')"
                        @update:model-value="updateValue('template_default_product_categories_show', $event)"
                    />
                    <p class="text-xs text-gray-500">{{ getSettingDescription('template_default_product_categories_show') }}</p>
                </div>

                <!-- Home -->
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ getSettingLabel('template_default_home') }}
                    </label>
                    <TemplateSelector
                        view-type="home"
                        :model-value="getSettingValue('template_default_home')"
                        @update:model-value="updateValue('template_default_home', $event)"
                    />
                    <p class="text-xs text-gray-500">{{ getSettingDescription('template_default_home') }}</p>
                </div>
            </div>

            <!-- Save Button -->
            <div class="flex justify-end pt-6 border-t border-gray-200 dark:border-gray-700 mt-8">
                <button
                    type="submit"
                    :disabled="saving"
                    class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                     <span v-if="saving" class="flex items-center">
                        <div class="h-4 w-4 border-2 border-white border-t-transparent rounded-full animate-spin mr-2"></div>
                        {{ $t('Saving...') }}
                    </span>
                    <span v-else>{{ $t('Save Changes') }}</span>
                </button>
            </div>
        </form>
    </div>
</template>

<script setup lang="ts">
import { useTranslation } from '../../../composables/useTranslation';
import TemplateSelector from '../../../components/TemplateSelector.vue';

interface Setting {
    key: string;
    value: any;
    type: string;
    label: string;
    description: string;
}

interface Props {
    settings: {
        [key: string]: Setting;
    };
    saving: boolean;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    (e: 'update', group: string, key: string, value: any): void;
    (e: 'save'): void;
}>();

const { t } = useTranslation();
const $t = t;

const getSettingValue = (key: string): any => {
    return props.settings[key]?.value || null;
};

const getSettingLabel = (key: string): string => {
    return props.settings[key]?.label ?? key;
};

const getSettingDescription = (key: string): string => {
    return props.settings[key]?.description ?? '';
};

const updateValue = (key: string, value: any) => {
    emit('update', 'template_defaults', key, value);
};
</script>
