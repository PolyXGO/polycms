<template>
 <div v-if="showPagination" :class="['flex items-center', modeClass]">
 <!-- Info Text -->
 <div v-if="showInfo && total > 0" class="text-sm text-admin-theme-text-secondary">
 {{ t('Showing') }} <span class="font-medium text-admin-theme-text">{{ from }}</span>
 {{ t('to') }} <span class="font-medium text-admin-theme-text">{{ to }}</span>
 {{ t('of') }} <span class="font-medium text-admin-theme-text">{{ total }}</span>
 {{ t('results') }}
 </div>

 <!-- Mode: Numbered -->
 <nav v-if="mode ==='numbered'" class="inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
 <!-- Prev -->
 <button
 :disabled="!hasPrev || isLoading"
 @click="$emit('page-change', currentPage - 1)"
 class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-admin-theme-border hover:bg-admin-theme-base disabled:opacity-40 disabled:cursor-not-allowed transition-colors bg-admin-theme-surface"
 :aria-label="t('Previous')"
 >
 <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" /></svg>
 </button>

 <!-- Page Numbers -->
 <template v-for="(page, idx) in pageNumbers" :key="idx">
 <span
 v-if="page === -1"
 class="relative inline-flex items-center px-3 py-2 text-sm text-gray-500 ring-1 ring-inset ring-admin-theme-border bg-admin-theme-surface"
 >…</span>
 <button
 v-else
 :disabled="isLoading"
 @click="page !== currentPage && $emit('page-change', page)"
 :class="[
'relative inline-flex items-center px-3 py-2 text-sm font-semibold ring-1 ring-inset transition-colors',
 page === currentPage
 ?'z-10 bg-admin-theme-primary text-admin-theme-primary-content ring-indigo-600 dark:bg-admin-theme-primary dark:ring-indigo-500'
 :'text-admin-theme-text-secondary ring-admin-theme-border hover:bg-admin-theme-base bg-admin-theme-surface'
 ]"
 :aria-current="page === currentPage ?'page' : undefined"
 >{{ page }}</button>
 </template>

 <!-- Next -->
 <button
 :disabled="!hasNext || isLoading"
 @click="$emit('page-change', currentPage + 1)"
 class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-admin-theme-border hover:bg-admin-theme-base disabled:opacity-40 disabled:cursor-not-allowed transition-colors bg-admin-theme-surface"
 :aria-label="t('Next')"
 >
 <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" /></svg>
 </button>
 </nav>

 <!-- Mode: Simple (Prev / Next only) -->
 <div v-else-if="mode ==='simple'" class="flex items-center gap-3">
 <button
 :disabled="!hasPrev || isLoading"
 @click="$emit('page-change', currentPage - 1)"
 class="inline-flex items-center gap-1 px-3 py-1.5 text-sm font-medium rounded-md border border-admin-theme-border bg-admin-theme-surface text-admin-theme-text-secondary hover:bg-admin-theme-base disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
 >
 <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" /></svg>
 {{ t('Previous') }}
 </button>
 <span class="text-sm text-admin-theme-text-muted">
 {{ currentPage }} / {{ lastPage }}
 </span>
 <button
 :disabled="!hasNext || isLoading"
 @click="$emit('page-change', currentPage + 1)"
 class="inline-flex items-center gap-1 px-3 py-1.5 text-sm font-medium rounded-md border border-admin-theme-border bg-admin-theme-surface text-admin-theme-text-secondary hover:bg-admin-theme-base disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
 >
 {{ t('Next') }}
 <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" /></svg>
 </button>
 </div>

 <!-- Mode: Load More -->
 <div v-else-if="mode ==='loadmore'" class="flex flex-col items-center gap-2 w-full">
 <button
 v-if="hasNext"
 :disabled="isLoading"
 @click="$emit('load-more')"
 :class="[
'inline-flex items-center gap-2 px-5 py-2 text-sm font-medium rounded-lg border transition-all duration-200',
 isLoading
 ?'border-admin-theme-border bg-admin-theme-base text-gray-400 cursor-wait'
 :'border-indigo-300 dark:border-admin-theme-primary bg-admin-theme-surface text-admin-theme-primary dark:text-admin-theme-primary hover:bg-admin-theme-primary/10 dark:hover:bg-indigo-900/20 hover:border-indigo-400'
 ]"
 >
 <svg v-if="isLoading" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
 <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
 <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
 </svg>
 <span v-else>{{ t('Load More') }}</span>
 </button>
 <span v-if="total > 0" class="text-xs text-admin-theme-text-muted">
 {{ to }} / {{ total }}
 </span>
 </div>
 </div>
</template>

<script setup lang="ts">
import { computed, inject, type PropType } from'vue';

const props = defineProps({
 currentPage: { type: Number, required: true },
 lastPage: { type: Number, required: true },
 total: { type: Number, default: 0 },
 from: { type: Number, default: 0 },
 to: { type: Number, default: 0 },
 /** Which pagination style to render */
 mode: { type: String as PropType<'numbered' |'simple' |'loadmore'>, default:'numbered' },
 /** Show"Showing X to Y of Z" info text */
 showInfo: { type: Boolean, default: true },
 /** Whether data is currently loading */
 loading: { type: Boolean, default: false },
 /** Array of page numbers (including -1 for ellipsis) */
 pageNumbers: { type: Array as PropType<(number | -1)[]>, default: () => [] },
});

defineEmits<{
'page-change': [page: number];
'load-more': [];
}>();

// Simple translation helper — use injected $t or fallback
const injectedT = inject<((key: string) => string) | null>('$t', null);
const t = (key: string) => injectedT?.(key) ?? key;

const showPagination = computed(() => props.lastPage > 1);
const hasNext = computed(() => props.currentPage < props.lastPage);
const hasPrev = computed(() => props.currentPage > 1);
const isLoading = computed(() => props.loading);

const modeClass = computed(() => {
 if (props.mode ==='loadmore') return'justify-center';
 return props.showInfo ?'justify-between' :'justify-end';
});
</script>
