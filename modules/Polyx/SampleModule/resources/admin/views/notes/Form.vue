<!--
  Notes/Form.vue — Create & Edit Form
  =====================================
  This component handles BOTH create and edit operations.
  It detects the mode by checking if route params contain an 'id'.

  Pattern: Single form component for create + edit
  - No id param → Create mode (POST /api/v1/sample-module/notes)
  - Has id param → Edit mode (PUT /api/v1/sample-module/notes/{id})
-->
<template>
    <div>
        <!-- Header -->
        <div class="flex items-center mb-6">
            <router-link
                :to="{ name: 'admin.sample-module.notes' }"
                class="mr-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
            >
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </router-link>
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ isEditMode ? 'Edit Note' : 'Create Note' }}
                </h1>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    {{ isEditMode ? 'Update an existing note' : 'Add a new note' }}
                </p>
            </div>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
        </div>

        <!-- Form -->
        <form v-else @submit.prevent="handleSubmit" class="space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 space-y-5">
                <!-- Title -->
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Title <span class="text-red-500">*</span>
                    </label>
                    <input
                        id="title"
                        v-model="form.title"
                        type="text"
                        required
                        maxlength="255"
                        class="w-full px-3 py-2 border rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-indigo-500 focus:border-indigo-500"
                        :class="errors.title ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
                        placeholder="Enter note title..."
                    />
                    <p v-if="errors.title" class="mt-1 text-sm text-red-500">{{ errors.title[0] }}</p>
                </div>

                <!-- Content -->
                <div>
                    <label for="content" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Content
                    </label>
                    <textarea
                        id="content"
                        v-model="form.content"
                        rows="8"
                        maxlength="10000"
                        class="w-full px-3 py-2 border rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-indigo-500 focus:border-indigo-500"
                        :class="errors.content ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
                        placeholder="Write your note content..."
                    ></textarea>
                    <p v-if="errors.content" class="mt-1 text-sm text-red-500">{{ errors.content[0] }}</p>
                    <p class="mt-1 text-xs text-gray-400">{{ (form.content || '').length }} / 10,000 characters</p>
                </div>

                <!-- Color + Pin Row -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <!-- Color Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Color Label
                        </label>
                        <div class="flex flex-wrap gap-2">
                            <button
                                v-for="c in colors"
                                :key="c"
                                type="button"
                                @click="form.color = c"
                                :class="[
                                    'px-3 py-1.5 text-xs font-medium rounded-full border-2 transition-all',
                                    form.color === c
                                        ? `${colorActiveClasses[c]} ring-2 ring-offset-1 dark:ring-offset-gray-800`
                                        : `${colorClasses[c]} border-transparent hover:border-gray-300`
                                ]"
                            >{{ c }}</button>
                        </div>
                    </div>

                    <!-- Pin Toggle -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Options
                        </label>
                        <label class="inline-flex items-center cursor-pointer">
                            <input
                                type="checkbox"
                                v-model="form.is_pinned"
                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700"
                            />
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">📌 Pin this note to top</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-between items-center">
                <router-link
                    :to="{ name: 'admin.sample-module.notes' }"
                    class="px-6 py-2 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 transition-colors"
                >
                    Cancel
                </router-link>
                <button
                    type="submit"
                    :disabled="saving"
                    class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 transition-colors"
                >
                    {{ saving ? 'Saving...' : (isEditMode ? 'Update Note' : 'Create Note') }}
                </button>
            </div>
        </form>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const router = useRouter();

const loading = ref(false);
const saving = ref(false);
const errors = ref<Record<string, string[]>>({});

const isEditMode = computed(() => !!route.params.id);

const form = ref({
    title: '',
    content: '',
    color: 'blue',
    is_pinned: false,
});

const colors = ['blue', 'green', 'yellow', 'red', 'purple', 'gray'];

const colorClasses: Record<string, string> = {
    blue:   'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
    green:  'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
    yellow: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
    red:    'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    purple: 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
    gray:   'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
};

const colorActiveClasses: Record<string, string> = {
    blue:   'bg-blue-200 text-blue-800 border-blue-500 ring-blue-300',
    green:  'bg-green-200 text-green-800 border-green-500 ring-green-300',
    yellow: 'bg-yellow-200 text-yellow-800 border-yellow-500 ring-yellow-300',
    red:    'bg-red-200 text-red-800 border-red-500 ring-red-300',
    purple: 'bg-purple-200 text-purple-800 border-purple-500 ring-purple-300',
    gray:   'bg-gray-200 text-gray-800 border-gray-500 ring-gray-300',
};

const fetchNote = async () => {
    if (!isEditMode.value) return;

    loading.value = true;
    try {
        const res = await axios.get(`/api/v1/sample-module/notes/${route.params.id}`);
        const note = res.data.data;
        form.value = {
            title: note.title,
            content: note.content || '',
            color: note.color || 'blue',
            is_pinned: note.is_pinned || false,
        };
    } catch (error) {
        console.error('Error fetching note:', error);
        router.push({ name: 'admin.sample-module.notes' });
    } finally {
        loading.value = false;
    }
};

const handleSubmit = async () => {
    saving.value = true;
    errors.value = {};

    try {
        if (isEditMode.value) {
            await axios.put(`/api/v1/sample-module/notes/${route.params.id}`, form.value);
        } else {
            await axios.post('/api/v1/sample-module/notes', form.value);
        }
        router.push({ name: 'admin.sample-module.notes' });
    } catch (error: any) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors || {};
        } else {
            alert(error.response?.data?.message || 'Failed to save note');
        }
    } finally {
        saving.value = false;
    }
};

onMounted(() => fetchNote());
</script>
