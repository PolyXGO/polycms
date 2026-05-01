<template>
    <div class="absolute -left-12 top-0 flex flex-col bg-white border border-gray-200 rounded shadow-lg z-10">
        <button
            type="button"
            @click="$emit('moveUp')"
            :disabled="!canMoveUp"
            class="p-2 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            title="Move up"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
            </svg>
        </button>
        <button
            type="button"
            @click="$emit('moveDown')"
            :disabled="!canMoveDown"
            class="p-2 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            title="Move down"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>
        <button
            type="button"
            @click="$emit('duplicate')"
            class="p-2 hover:bg-gray-100 transition-colors"
            title="Duplicate"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
            </svg>
        </button>
        <button
            type="button"
            @click="$emit('delete')"
            class="p-2 hover:bg-red-50 text-red-600 transition-colors"
            title="Delete"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
        </button>
    </div>
</template>

<script setup lang="ts">
defineProps<{
    canMoveUp: boolean;
    canMoveDown: boolean;
}>();

defineEmits<{
    moveUp: [];
    moveDown: [];
    duplicate: [];
    delete: [];
}>();
</script>
