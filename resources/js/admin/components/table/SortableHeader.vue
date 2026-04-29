<template>
    <th
        :class="[
            'select-none group whitespace-nowrap',
            align === 'right' ? 'text-right' : align === 'center' ? 'text-center' : 'text-left',
            sortKey ? 'cursor-pointer hover:text-indigo-400 transition-colors duration-150' : '',
            isActive ? 'text-indigo-400 dark:text-indigo-300' : 'text-gray-500 dark:text-gray-400',
        ]"
        :aria-sort="ariaSort"
        role="columnheader"
        :tabindex="sortKey ? 0 : undefined"
        @click="handleClick"
        @keydown.enter.prevent="handleClick"
        @keydown.space.prevent="handleClick"
    >
        <span class="inline-flex items-center gap-1">
            <slot>{{ label }}</slot>
            <span v-if="sortKey" class="inline-flex flex-col text-[9px] leading-none opacity-60 group-hover:opacity-100 transition-opacity">
                <svg :class="['w-3 h-3 -mb-0.5', currentDir === 'asc' && isActive ? 'text-indigo-400' : 'text-gray-400']" viewBox="0 0 16 16" fill="currentColor">
                    <path d="M8 4l4 5H4l4-5z"/>
                </svg>
                <svg :class="['w-3 h-3 -mt-0.5', currentDir === 'desc' && isActive ? 'text-indigo-400' : 'text-gray-400']" viewBox="0 0 16 16" fill="currentColor">
                    <path d="M8 12l4-5H4l4 5z"/>
                </svg>
            </span>
        </span>
    </th>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import type { SortDirection } from '../../composables/useTableSort';

const props = defineProps<{
    /** Display label for the column header */
    label?: string;
    /** The key/field this column sorts by. Omit to render a non-sortable header. */
    sortKey?: string;
    /** Current active sort key from useTableSort */
    currentKey?: string | null;
    /** Current active sort direction from useTableSort */
    currentDir?: SortDirection;
    /** Text alignment */
    align?: 'left' | 'center' | 'right';
}>();

const emit = defineEmits<{
    sort: [key: string];
}>();

const isActive = computed(() => props.sortKey != null && props.sortKey === props.currentKey && props.currentDir != null);

const ariaSort = computed(() => {
    if (!isActive.value) return undefined;
    return props.currentDir === 'asc' ? 'ascending' : 'descending';
});

const handleClick = () => {
    if (props.sortKey) {
        emit('sort', props.sortKey);
    }
};
</script>
