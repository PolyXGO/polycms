import { ref, computed, type Ref, type ComputedRef } from 'vue';

export interface PaginationMeta {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from?: number;
    to?: number;
}

export interface UsePaginationOptions {
    /** Items per page, default 15 */
    perPage?: number;
    /** Callback when page changes */
    onPageChange?: (page: number) => void | Promise<void>;
}

export interface UsePaginationReturn {
    currentPage: Ref<number>;
    lastPage: Ref<number>;
    perPage: Ref<number>;
    total: Ref<number>;
    from: Ref<number>;
    to: Ref<number>;
    loading: Ref<boolean>;

    /** Update state from a Laravel paginate response */
    updateFromResponse: (meta: Partial<PaginationMeta> & Record<string, any>) => void;
    /** Go to a specific page (bounds-checked) */
    goToPage: (page: number) => void;
    /** Go to next page */
    nextPage: () => void;
    /** Go to previous page */
    prevPage: () => void;
    /** Reset to page 1 */
    reset: () => void;

    /** Can go to next page? */
    hasNext: ComputedRef<boolean>;
    /** Can go to previous page? */
    hasPrev: ComputedRef<boolean>;
    /** Is on the first page? */
    isFirstPage: ComputedRef<boolean>;
    /** Is on the last page? */
    isLastPage: ComputedRef<boolean>;
    /** Should pagination be shown? */
    showPagination: ComputedRef<boolean>;
    /** Array of page numbers for numbered pagination (with ellipsis as -1) */
    pageNumbers: ComputedRef<(number | -1)[]>;
    /** Computed query params for API requests */
    paginationParams: ComputedRef<{ page: number; per_page: number }>;
}

export function usePagination(options: UsePaginationOptions = {}): UsePaginationReturn {
    const currentPage = ref(1);
    const lastPage = ref(1);
    const perPage = ref(options.perPage ?? 15);
    const total = ref(0);
    const from = ref(0);
    const to = ref(0);
    const loading = ref(false);

    const updateFromResponse = (meta: Partial<PaginationMeta> & Record<string, any>) => {
        if (meta.current_page != null) currentPage.value = meta.current_page;
        if (meta.last_page != null) lastPage.value = meta.last_page;
        if (meta.per_page != null) perPage.value = meta.per_page;
        if (meta.total != null) total.value = Array.isArray(meta.total) ? meta.total[0] : meta.total;
        if (meta.from != null) from.value = meta.from;
        if (meta.to != null) to.value = meta.to;
    };

    const goToPage = (page: number) => {
        const p = Math.max(1, Math.min(page, lastPage.value));
        if (p === currentPage.value) return;
        currentPage.value = p;
        options.onPageChange?.(p);
    };

    const nextPage = () => goToPage(currentPage.value + 1);
    const prevPage = () => goToPage(currentPage.value - 1);
    const reset = () => { currentPage.value = 1; };

    const hasNext = computed(() => currentPage.value < lastPage.value);
    const hasPrev = computed(() => currentPage.value > 1);
    const isFirstPage = computed(() => currentPage.value === 1);
    const isLastPage = computed(() => currentPage.value >= lastPage.value);
    const showPagination = computed(() => lastPage.value > 1);

    /**
     * Generate smart page number array with ellipsis.
     * Example for page 5 of 20: [1, -1, 4, 5, 6, -1, 20]
     */
    const pageNumbers = computed((): (number | -1)[] => {
        const pages: (number | -1)[] = [];
        const last = lastPage.value;
        const current = currentPage.value;

        if (last <= 7) {
            // Show all pages
            for (let i = 1; i <= last; i++) pages.push(i);
            return pages;
        }

        // Always show first page
        pages.push(1);

        if (current > 3) {
            pages.push(-1); // Ellipsis
        }

        // Pages around current
        const rangeStart = Math.max(2, current - 1);
        const rangeEnd = Math.min(last - 1, current + 1);
        for (let i = rangeStart; i <= rangeEnd; i++) {
            pages.push(i);
        }

        if (current < last - 2) {
            pages.push(-1); // Ellipsis
        }

        // Always show last page
        if (last > 1) pages.push(last);

        return pages;
    });

    const paginationParams = computed(() => ({
        page: currentPage.value,
        per_page: perPage.value,
    }));

    return {
        currentPage,
        lastPage,
        perPage,
        total,
        from,
        to,
        loading,
        updateFromResponse,
        goToPage,
        nextPage,
        prevPage,
        reset,
        hasNext,
        hasPrev,
        isFirstPage,
        isLastPage,
        showPagination,
        pageNumbers,
        paginationParams,
    };
}
