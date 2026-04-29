<template>
    <div class="tag-selector w-full">
        <div class="tag-selector__input-group flex gap-3">
            <input
                v-model="tagInput"
                type="text"
                class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-indigo-500 focus:border-indigo-500"
                :placeholder="placeholder"
                @keydown.enter.prevent="addTag"
            />
            <button type="button" class="px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-semibold disabled:opacity-50 whitespace-nowrap" :disabled="adding" @click="addTag">
                {{ adding ? 'Adding…' : 'Add' }}
            </button>
        </div>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Separate tags with commas.</p>

        <div v-if="selectedTags.length" class="tag-selector__selected">
            <span
                v-for="tag in selectedTags"
                :key="tag.id"
                class="tag-selector__tag bg-indigo-100 dark:bg-indigo-900/50 text-indigo-800 dark:text-indigo-200 px-2.5 py-1 rounded-full text-sm cursor-pointer hover:bg-indigo-200 dark:hover:bg-indigo-900 transition-colors"
                @click="removeTag(tag.id)"
            >
                × {{ tag.name }}
            </span>
        </div>

        <div v-if="suggestions.length" class="tag-selector__suggestions mt-2">
            <span class="text-xs text-gray-500 dark:text-gray-400">Choose from the most used tags:</span>
            <button
                v-for="tag in suggestions"
                :key="tag.id"
                type="button"
                class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 text-sm font-medium p-1 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 rounded"
                @click="addExistingTag(tag)"
            >
                {{ tag.name }}
            </button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { onMounted, ref, watch } from 'vue';
import axios from 'axios';
import { useSlugify } from '../../../../composables/useSlugify';

export interface TagItem {
    id: number;
    name: string;
}

const props = defineProps<{
    type: 'post' | 'product';
    modelValue: TagItem[];
    placeholder?: string;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: TagItem[]): void;
}>();

const { slugify } = useSlugify();

const tagInput = ref('');
const adding = ref(false);
const suggestions = ref<TagItem[]>([]);
const selectedTags = ref<TagItem[]>(props.modelValue ?? []);

watch(
    () => props.modelValue,
    (value) => {
        selectedTags.value = value ?? [];
    }
);

const placeholder = props.placeholder ?? 'Add new tag';

const fetchSuggestions = async () => {
    const endpoint = props.type === 'product' ? '/api/v1/product-tags' : '/api/v1/post-tags';
    const response = await axios.get(endpoint, { params: { per_page: 20 } });
    const data = response.data?.data ?? [];
    suggestions.value = data.map((tag: any) => ({ id: tag.id, name: tag.name }));
};

const addExistingTag = (tag: TagItem) => {
    if (!selectedTags.value.find((item) => item.id === tag.id)) {
        selectedTags.value = [...selectedTags.value, tag];
        emit('update:modelValue', selectedTags.value);
    }
};

const addTag = async () => {
    if (!tagInput.value.trim()) {
        return;
    }

    const tagNames = tagInput.value.split(',').map((name) => name.trim()).filter(Boolean);
    tagInput.value = '';

    for (const name of tagNames) {
        const existing = findTagByName(name);
        if (existing) {
            addExistingTag(existing);
            continue;
        }
        await createTag(name);
    }
};

const createTag = async (name: string) => {
    adding.value = true;
    try {
        const endpoint = props.type === 'product' ? '/api/v1/product-tags' : '/api/v1/post-tags';
        const response = await axios.post(endpoint, { name, slug: slugify(name) });
        const tag = response.data?.data;
        if (tag) {
            const newTag = { id: tag.id, name: tag.name };
            selectedTags.value = [...selectedTags.value, newTag];
            emit('update:modelValue', selectedTags.value);
            suggestions.value = [...suggestions.value, newTag];
        }
    } finally {
        adding.value = false;
    }
};

const removeTag = (tagId: number) => {
    selectedTags.value = selectedTags.value.filter((tag) => tag.id !== tagId);
    emit('update:modelValue', selectedTags.value);
};

const findTagByName = (name: string): TagItem | undefined => {
    const lowered = name.toLowerCase();
    return (
        selectedTags.value.find((tag) => tag.name.toLowerCase() === lowered) ||
        suggestions.value.find((tag) => tag.name.toLowerCase() === lowered)
    );
};

onMounted(() => {
    void fetchSuggestions();
});
</script>

<style scoped>
.tag-selector {
    display: grid;
    gap: 0.75rem;
}

.tag-selector__input-group {
    display: flex;
    gap: 0.5rem;
}

.tag-selector__selected {
    display: inline-flex;
    flex-wrap: wrap;
    gap: 0.3rem;
}

.tag-selector__suggestions {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.5rem;
}
</style>

