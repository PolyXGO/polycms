<template>
    <div>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ isEdit ? 'Edit Product Tag' : 'Create New Product Tag' }}
            </h1>
            <button
                @click="router.back()"
                class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300"
            >
                Cancel
            </button>
        </div>

        <form @submit.prevent="saveTag" class="space-y-6">
            <!-- Basic Information -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold mb-4 dark:text-white">Tag Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name *</label>
                        <input
                            v-model="form.name"
                            type="text"
                            required
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500"
                            placeholder="Tag name"
                            @input="generateSlug"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Slug *</label>
                        <input
                            v-model="form.slug"
                            type="text"
                            required
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500"
                            placeholder="tag-slug"
                            @input="onSlugInput"
                        />
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('Description') }}</label>
                        <textarea
                            v-model="form.description"
                            rows="4"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500"
                            placeholder="Tag description..."
                        />
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end space-x-3">
                <button
                    type="button"
                    @click="router.back()"
                    class="px-6 py-2 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300"
                >
                    {{ $t('Cancel') }}
                </button>
                <button
                    type="submit"
                    :disabled="loading"
                    class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50"
                >
                    {{ loading ? $t('Saving') : (isEdit ? $t('Update Tag') : $t('Create Tag')) }}
                </button>
            </div>
        </form>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, getCurrentInstance } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import axios from 'axios';
import { useSlugify } from '../../../composables/useSlugify';
import { useDialog } from '../../../composables/useDialog';
import { useTranslation } from '../../../composables/useTranslation';

const { t } = useTranslation();
const instance = getCurrentInstance();
const $t = instance?.appContext.config.globalProperties.$t || t;

const router = useRouter();
const route = useRoute();
const { slugify } = useSlugify();
const dialog = useDialog();

const isEdit = computed(() => !!route.params.id);
const loading = ref(false);
const slugManuallyEdited = ref(false);

const form = ref({
    name: '',
    slug: '',
    description: '',
});

const generateSlug = () => {
    // Only auto-generate if slug is empty or hasn't been manually edited
    if (!slugManuallyEdited.value && form.value.name) {
        form.value.slug = slugify(form.value.name);
    }
};

const onSlugInput = () => {
    // Mark slug as manually edited when user types in slug field
    slugManuallyEdited.value = true;
};

const loadTag = async () => {
    if (!isEdit.value) return;

    try {
        const response = await axios.get(`/api/v1/product-tags/${route.params.id}`);
        const tag = response.data.data;
        form.value = {
            name: tag.name,
            slug: tag.slug,
            description: tag.description || '',
        };
    } catch (error: any) {
        console.error('Error loading tag:', error);
        const message = error.response?.data?.message || 'Failed to load tag';
        dialog.error(message);
        router.back();
    }
};

const saveTag = async () => {
    loading.value = true;
    try {
        if (isEdit.value) {
            await axios.put(`/api/v1/product-tags/${route.params.id}`, form.value);
            // Reload tag data to reflect changes, but stay on edit page
            await loadTag();
            dialog.success($t('Tag updated successfully') || 'Tag updated successfully');
        } else {
            const response = await axios.post('/api/v1/product-tags', form.value);
            // Redirect to edit page after creating
            dialog.success($t('Tag created successfully') || 'Tag created successfully');
            router.push({ 
                name: 'admin.product-tags.edit', 
                params: { id: response.data.data.id }
            });
        }
    } catch (error: any) {
        console.error('Error saving tag:', error);
        const message = error.response?.data?.message || ($t('Failed to save tag') || 'Failed to save tag');
        dialog.error(message);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    if (isEdit.value) {
        loadTag();
    }
});
</script>
