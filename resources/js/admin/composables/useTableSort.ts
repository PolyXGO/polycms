import { ref, computed, type Ref, type ComputedRef } from 'vue';

export type SortDirection = 'asc' | 'desc' | null;

export interface UseTableSortOptions {
    /** Default column to sort by on initialization */
    defaultKey?: string;
    /** Default sort direction */
    defaultDir?: SortDirection;
    /** Callback when sort changes — use to trigger data re-fetch */
    onSort?: (key: string | null, direction: SortDirection) => void;
}

export interface UseTableSortReturn {
    sortKey: Ref<string | null>;
    sortDirection: Ref<SortDirection>;
    /** Toggle sort for a column: null → asc → desc → null */
    toggleSort: (key: string) => void;
    /** Set sort explicitly */
    setSort: (key: string | null, dir: SortDirection) => void;
    /** Reset to no sort (or back to defaults) */
    resetSort: () => void;
    /** Computed params ready to spread into API request */
    sortParams: ComputedRef<Record<string, string>>;
    /** Check if a specific column is the active sort column */
    isSorted: (key: string) => boolean;
    /** Get the current direction for a column (null if not active) */
    getSortDirection: (key: string) => SortDirection;
    /** Client-side sort helper — sorts an array by the current sort key */
    sortItems: <T>(items: T[], keyAccessor?: (item: T, key: string) => any) => T[];
}

export function useTableSort(options: UseTableSortOptions = {}): UseTableSortReturn {
    const sortKey = ref<string | null>(options.defaultKey ?? null);
    const sortDirection = ref<SortDirection>(options.defaultDir ?? null);

    const toggleSort = (key: string) => {
        if (sortKey.value !== key) {
            // New column — start ascending
            sortKey.value = key;
            sortDirection.value = 'asc';
        } else if (sortDirection.value === 'asc') {
            sortDirection.value = 'desc';
        } else if (sortDirection.value === 'desc') {
            // Cycle back to no sort
            sortKey.value = options.defaultKey ?? null;
            sortDirection.value = options.defaultKey ? (options.defaultDir ?? null) : null;
        }
        options.onSort?.(sortKey.value, sortDirection.value);
    };

    const setSort = (key: string | null, dir: SortDirection) => {
        sortKey.value = key;
        sortDirection.value = dir;
        options.onSort?.(key, dir);
    };

    const resetSort = () => {
        sortKey.value = options.defaultKey ?? null;
        sortDirection.value = options.defaultDir ?? null;
        options.onSort?.(sortKey.value, sortDirection.value);
    };

    const sortParams = computed(() => {
        const params: Record<string, string> = {};
        if (sortKey.value && sortDirection.value) {
            params.sort_by = sortKey.value;
            params.sort_direction = sortDirection.value;
        }
        return params;
    });

    const isSorted = (key: string): boolean => sortKey.value === key && sortDirection.value !== null;

    const getSortDirection = (key: string): SortDirection => {
        return sortKey.value === key ? sortDirection.value : null;
    };

    const sortItems = <T>(items: T[], keyAccessor?: (item: T, key: string) => any): T[] => {
        if (!sortKey.value || !sortDirection.value) return items;
        const key = sortKey.value;
        const dir = sortDirection.value === 'asc' ? 1 : -1;
        const accessor = keyAccessor || ((item: any, k: string) => item[k]);

        return [...items].sort((a, b) => {
            const va = accessor(a, key);
            const vb = accessor(b, key);
            if (va == null && vb == null) return 0;
            if (va == null) return 1;
            if (vb == null) return -1;
            if (typeof va === 'string') return va.localeCompare(vb) * dir;
            return (va > vb ? 1 : va < vb ? -1 : 0) * dir;
        });
    };

    return {
        sortKey,
        sortDirection,
        toggleSort,
        setSort,
        resetSort,
        sortParams,
        isSorted,
        getSortDirection,
        sortItems,
    };
}
