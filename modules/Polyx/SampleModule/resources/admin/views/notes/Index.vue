<!--
  Notes/Index.vue — CRUD List View
  =================================
  Demonstrates the standard PolyCMS admin list pattern:
  - Paginated data table with search
  - Color-coded badges
  - Pin/unpin toggle
  - Delete with confirmation
  - Responsive design with dark mode
-->
<template>
    <div>
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">📝 Notes</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    CRUD demo — create, read, update, delete
                </p>
            </div>
            <router-link
                :to="{ name: 'admin.sample-module.notes.create' }"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors"
            >
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                New Note
            </router-link>
        </div>

        <!-- Filters Bar -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-4 p-4">
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1">
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search notes..."
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-indigo-500 focus:border-indigo-500"
                        @input="debouncedFetch"
                    />
                </div>
                <select
                    v-model="filterColor"
                    class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500"
                    @change="fetchNotes"
                >
                    <option value="">All Colors</option>
                    <option v-for="c in colors" :key="c" :value="c">{{ c }}</option>
                </select>
            </div>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
        </div>

        <!-- Empty State -->
        <div v-else-if="notes.length === 0" class="text-center py-12 bg-white dark:bg-gray-800 rounded-lg shadow">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <p class="mt-2 text-gray-500 dark:text-gray-400">No notes yet</p>
            <router-link
                :to="{ name: 'admin.sample-module.notes.create' }"
                class="mt-3 inline-flex items-center text-indigo-600 dark:text-indigo-400 hover:underline"
            >
                Create your first note →
            </router-link>
        </div>

        <!-- Notes Table -->
        <div v-else class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-8"></th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Title</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase hidden md:table-cell">Color</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase hidden lg:table-cell">Author</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase hidden lg:table-cell">Date</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <tr
                        v-for="note in notes"
                        :key="note.id"
                        class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors"
                    >
                        <!-- Pin Icon -->
                        <td class="px-6 py-4">
                            <button
                                @click="togglePin(note)"
                                :class="note.is_pinned ? 'text-amber-500' : 'text-gray-300 dark:text-gray-600 hover:text-amber-400'"
                                :title="note.is_pinned ? 'Unpin' : 'Pin'"
                            >
                                <svg class="w-4 h-4" :fill="note.is_pinned ? 'currentColor' : 'none'" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                                </svg>
                            </button>
                        </td>
                        <!-- Title -->
                        <td class="px-6 py-4">
                            <router-link
                                :to="{ name: 'admin.sample-module.notes.edit', params: { id: note.id } }"
                                class="text-sm font-medium text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400"
                            >
                                {{ note.title }}
                            </router-link>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 truncate max-w-xs">
                                {{ note.excerpt || '(empty)' }}
                            </p>
                        </td>
                        <!-- Color -->
                        <td class="px-6 py-4 hidden md:table-cell">
                            <span :class="colorClasses[note.color] || colorClasses.gray" class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full">
                                {{ note.color }}
                            </span>
                        </td>
                        <!-- Author -->
                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400 hidden lg:table-cell">
                            {{ note.user?.name || 'Unknown' }}
                        </td>
                        <!-- Date -->
                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 hidden lg:table-cell">
                            {{ formatDate(note.created_at) }}
                        </td>
                        <!-- Actions -->
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-2">
                                <router-link
                                    :to="{ name: 'admin.sample-module.notes.edit', params: { id: note.id } }"
                                    class="text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400"
                                    title="Edit"
                                >
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </router-link>
                                <button
                                    @click="deleteNote(note)"
                                    class="text-gray-400 hover:text-red-600 dark:hover:text-red-400"
                                    title="Delete"
                                >
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div v-if="meta.last_page > 1" class="px-6 py-3 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Showing {{ (meta.current_page - 1) * meta.per_page + 1 }}–{{ Math.min(meta.current_page * meta.per_page, meta.total) }} of {{ meta.total }}
                </p>
                <div class="flex gap-1">
                    <button
                        v-for="p in meta.last_page"
                        :key="p"
                        @click="goToPage(p)"
                        :class="[
                            'px-3 py-1 text-sm rounded',
                            p === meta.current_page
                                ? 'bg-indigo-600 text-white'
                                : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'
                        ]"
                    >{{ p }}</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';

const loading = ref(true);
const notes = ref<any[]>([]);
const meta = ref({ current_page: 1, last_page: 1, per_page: 10, total: 0 });
const search = ref('');
const filterColor = ref('');
const colors = ['blue', 'green', 'yellow', 'red', 'purple', 'gray'];

const colorClasses: Record<string, string> = {
    blue:   'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
    green:  'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
    yellow: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
    red:    'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    purple: 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
    gray:   'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
};

let debounceTimer: ReturnType<typeof setTimeout>;
const debouncedFetch = () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => fetchNotes(), 300);
};

const fetchNotes = async (page = 1) => {
    loading.value = true;
    try {
        const params: Record<string, any> = { page };
        if (search.value) params.search = search.value;
        if (filterColor.value) params.color = filterColor.value;

        const res = await axios.get('/api/v1/sample-module/notes', { params });
        notes.value = res.data.data;
        meta.value = res.data.meta;
    } catch (error) {
        console.error('Error fetching notes:', error);
    } finally {
        loading.value = false;
    }
};

const togglePin = async (note: any) => {
    try {
        await axios.put(`/api/v1/sample-module/notes/${note.id}`, {
            is_pinned: !note.is_pinned,
        });
        note.is_pinned = !note.is_pinned;
    } catch (error) {
        console.error('Error toggling pin:', error);
    }
};

const deleteNote = async (note: any) => {
    if (!confirm(`Delete "${note.title}"?`)) return;

    try {
        await axios.delete(`/api/v1/sample-module/notes/${note.id}`);
        notes.value = notes.value.filter(n => n.id !== note.id);
        meta.value.total--;
    } catch (error) {
        console.error('Error deleting note:', error);
    }
};

const goToPage = (page: number) => fetchNotes(page);

const formatDate = (dateStr: string) => {
    if (!dateStr) return '';
    return new Date(dateStr).toLocaleDateString('en-US', {
        year: 'numeric', month: 'short', day: 'numeric',
    });
};

onMounted(() => fetchNotes());
</script>
