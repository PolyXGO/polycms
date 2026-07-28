import { ref, type Ref } from 'vue';

export interface UseSortableOptions<T> {
    onSort?: (items: T[]) => void | Promise<void>;
    dragHandleClass?: string;
    dropEffect?: 'copy' | 'move' | 'link' | 'none';
}

export function useSortable<T>(items: Ref<T[]>, options: UseSortableOptions<T> = {}) {
    const draggingIndex = ref<number | null>(null);
    const dragOverIndex = ref<number | null>(null);

    const handleDragStart = (index: number, event: DragEvent) => {
        // If dragHandleClass is specified, ensure we're dragging by the handle
        if (options.dragHandleClass) {
            const target = event.target as HTMLElement;
            const isHandle = target.classList.contains(options.dragHandleClass) || 
                             target.closest(`.${options.dragHandleClass}`);
            if (!isHandle) {
                event.preventDefault();
                return;
            }
        }

        draggingIndex.value = index;
        if (event.dataTransfer) {
            event.dataTransfer.effectAllowed = options.dropEffect || 'move';
            // Set some data for broader compatibility if needed
            event.dataTransfer.setData('text/plain', index.toString());
        }
    };

    const handleDragOver = (index: number, event: DragEvent) => {
        event.preventDefault();
        if (draggingIndex.value === null) return;
        
        dragOverIndex.value = index;
        
        if (event.dataTransfer) {
            event.dataTransfer.dropEffect = options.dropEffect || 'move';
        }
    };

    const handleDragEnd = () => {
        draggingIndex.value = null;
        dragOverIndex.value = null;
    };

    const handleDrop = async (index: number, event: DragEvent) => {
        event.preventDefault();
        if (draggingIndex.value === null || draggingIndex.value === index) {
            handleDragEnd();
            return;
        }

        const newItems = [...items.value];
        const [movedItem] = newItems.splice(draggingIndex.value, 1);
        newItems.splice(index, 0, movedItem);

        // Update the reactive ref
        items.value = newItems;

        // Callback for persistence
        if (options.onSort) {
            try {
                await options.onSort(newItems);
            } catch (error) {
                console.error('Failed to persist sort order:', error);
                // Optionally revert if needed, but usually we trust the UI update
            }
        }

        handleDragEnd();
    };

    // Manual move utility
    const moveItem = (from: number, to: number) => {
        if (from === to) return;
        const newItems = [...items.value];
        const [movedItem] = newItems.splice(from, 1);
        newItems.splice(to, 0, movedItem);
        items.value = newItems;
        if (options.onSort) options.onSort(newItems);
    };

    return {
        draggingIndex,
        dragOverIndex,
        handleDragStart,
        handleDragOver,
        handleDragEnd,
        handleDrop,
        moveItem
    };
}
